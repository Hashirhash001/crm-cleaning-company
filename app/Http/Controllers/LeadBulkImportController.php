<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Customer;
use App\Models\LeadImport;
use App\Models\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LeadBulkImportController extends Controller
{
    // Service name mapping from CSV to database
    private function getServiceMapping()
    {
        return [
            // Existing mappings
            'DEEP CLEANING' => 'Deep Cleaning Full House',
            'ROOM CLEANING' => 'Deep Cleaning Full House',
            'BATHROOM DEEP CLEANING' => 'Bathroom Deep',
            'BATHROOM DEEP' => 'Bathroom Deep',
            'SOFA SHAMPOOING' => 'Sofa Shampooing',
            'SOFA SHAMPOO' => 'Sofa Shampooing',
            'TERMITE TREATMENT' => 'Termite Treatment',
            'TERMITE' => 'Termite Treatment',
            'WATER TANK' => 'Tank Cleaning',
            'TANK CLEANING' => 'Tank Cleaning',
            'TANK' => 'Tank Cleaning',
            'FLOOR SCRUBBING' => 'Floor Scrubbing',
            'FLOOR SCRUB' => 'Floor Scrubbing',
            'FLOOR CLEANING' => 'Floor Scrubbing',
            'KITCHEN DEEP CLEANING' => 'Kitchen Deep',
            'KITCHEN DEEP' => 'Kitchen Deep',
            'KITCHEN' => 'Kitchen Deep',
            'PRESSURE WASH' => 'Pressure Wash',
            'PRESSURE WASHING' => 'Pressure Wash',
            'GLASS CLEANING' => 'Glass Cleaning',
            'GLASS' => 'Glass Cleaning',
            'CARPET SHAMPOO' => 'Carpet Shampoo',
            'CARPET SHAMPOOING' => 'Carpet Shampoo',
            'CARPET' => 'Carpet Shampoo',
            'GENERAL PEST' => 'General Pest',
            'PEST CONTROL' => 'General Pest',
            'PEST' => 'General Pest',
            'REGULAR CLEANING' => 'Regular Cleaning',
            'NORMAL CLEANING' => 'Normal Cleaning',
            'BED SHAMPOO' => 'Bed Shampoo',
            'BED SHAMPOOING' => 'Bed Shampoo',
            'MATTRESS SHAMPOO' => 'Bed Shampoo',

            // Package aliases
            'DEEP PACKAGE' => 'Deep Cleaning Full House',
            'FULL PACKAGE' => 'Deep Cleaning Full House',
            'COMPLETE PACKAGE' => 'Deep Cleaning Full House',
            'HOUSE DEEP CLEANING' => 'Deep Cleaning Full House',
            'HOUSE CLEANING' => 'Deep Cleaning Full House',

            // Additional common variations
            'DEEP' => 'Deep Cleaning Full House',
            'FULL DEEP' => 'Deep Cleaning Full House',
            'FULL HOUSE CLEANING' => 'Deep Cleaning Full House',
            'COMPLETE CLEANING' => 'Deep Cleaning Full House',
            'FULL HOUSE' => 'Deep Cleaning Full House',
        ];
    }

    // Detect service type based on service name
    private function detectServiceType($serviceName)
    {
        $pestControlServices = ['TERMITE TREATMENT', 'GENERAL PEST', 'Termite Treatment', 'General Pest'];

        foreach ($pestControlServices as $pestService) {
            if (stripos($serviceName, $pestService) !== false) {
                return 'pest_control';
            }
        }

        return 'cleaning'; // Default to cleaning
    }

    // Keep full phone number with country code
    private function cleanPhoneNumber($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Must be between 10-15 digits
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            return null;
        }

        return $phone;
    }

    /**
     * Create customer from lead
     */
    private function createCustomerFromLead($lead, $createdAt)
    {
        // Generate unique customer code
        $customerCount = Customer::count();
        $customerCode = 'CUST' . str_pad($customerCount + 1, 4, '0', STR_PAD_LEFT);

        $customer = Customer::create([
            'customer_code' => $customerCode,
            'lead_id' => $lead->id,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'phone_alternative' => $lead->phone_alternative,
            'email' => $lead->email ?? null,
            'address' => $lead->address,
            'district' => $lead->district,
            'branch_id' => $lead->branch_id,
            'created_by' => auth()->id(),
        ]);

        // Set timestamps
        $customer->created_at = $createdAt;
        $customer->updated_at = $createdAt;
        $customer->save();

        return $customer;
    }

    /**
     * Create job from lead
     */
    private function createJobFromLead($lead, $customer, $serviceIds, $amount, $rowData, $createdAt)
    {
        // Generate unique job code
        $jobCount = Job::count();
        $jobCode = 'JOB' . str_pad($jobCount + 1, 4, '0', STR_PAD_LEFT);

        // Create job title from services
        $services = Service::whereIn('id', $serviceIds)->get();
        $serviceNames = $services->pluck('name')->toArray();
        $jobTitle = implode(', ', $serviceNames);

        // Limit title length
        if (strlen($jobTitle) > 100) {
            $jobTitle = substr($jobTitle, 0, 97) . '...';
        }

        $job = Job::create([
            'job_code' => $jobCode,
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
            'title' => $jobTitle,
            'description' => $rowData['work_description'] ?? null,
            'customer_instructions' => null,
            'service_id' => $serviceIds[0] ?? null,
            'branch_id' => $lead->branch_id,
            'location' => $lead->address,
            'amount' => $amount,
            'status' => 'completed', // Since payment is received, mark as completed
            'created_by' => auth()->id(),
            'assigned_to' => $lead->assigned_to,
            'scheduled_date' => $createdAt->format('Y-m-d'),
            'scheduled_time' => null,
            'completed_at' => $createdAt, // Mark as completed on the same date
        ]);

        // Set timestamps
        $job->created_at = $createdAt;
        $job->updated_at = $createdAt;
        $job->save();

        // Sync services to job
        if (!empty($serviceIds)) {
            $job->services()->sync($serviceIds);
        }

        return $job;
    }

    public function bulkImport()
    {
        $recentImports = LeadImport::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('leads.bulk-import', compact('recentImports'));
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Lead Data');

        // Define headers matching your Excel format
        $headers = [
            'A1' => ['Date *', 'Format: DD/MM/YYYY'],
            'B1' => ['Name of the Company *', 'Branch name (e.g., BAYLEAF, CTREE)'],
            'C1' => ['Customer Name *', 'Full name of customer'],
            'D1' => ['Work Description *', 'Description of service'],
            'E1' => ['Package Amount', 'Total cost'],
            'F1' => ['Final Amount', 'Final amount'],
            'G1' => ['Paid (Yes/No)', 'Payment status: RECEIVED, YES, PAID, DONE, COMPLETED'],
            'H1' => ['Telecaller Name', 'Name of assigned telecaller'],
            'I1' => ['Contact Number *', 'Phone with country code (e.g., +919876543210)'],
            'J1' => ['Payment Mode', 'Cash, UPI, Card, etc.'],
            'K1' => ['Address', 'Full address'],
            'L1' => ['District', 'District name'],
            'M1' => ['Source', 'Lead source (optional)'],
        ];

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563eb'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        foreach ($headers as $cell => $data) {
            $sheet->setCellValue($cell, $data[0]);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
            $sheet->getComment($cell)->getText()->createTextRun($data[1]);
            $sheet->getComment($cell)->setWidth('200pt');
            $sheet->getComment($cell)->setHeight('50pt');
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(35);
        $sheet->getColumnDimension('L')->setWidth(20);
        $sheet->getColumnDimension('M')->setWidth(20);

        // Freeze header row
        $sheet->freezePane('A2');

        // Add example rows
        $sheet->setCellValue('A2', '11/01/2025');
        $sheet->setCellValue('B2', 'BAYLEAF');
        $sheet->setCellValue('C2', 'MALINI');
        $sheet->setCellValue('D2', 'DEEP CLEANING');
        $sheet->setCellValue('E2', '4000');
        $sheet->setCellValue('F2', '4000');
        $sheet->setCellValue('G2', 'RECEIVED');
        $sheet->setCellValue('H2', 'ANEESH');
        $sheet->setCellValue('I2', '+919876543210');
        $sheet->setCellValue('J2', 'GPAY');
        $sheet->setCellValue('K2', 'KALLEPULLY');
        $sheet->setCellValue('L2', 'PALAKKAD');
        $sheet->setCellValue('M2', 'Walk-in');

        $exampleStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0F2FE'],
            ],
        ];
        $sheet->getStyle('A2:M2')->applyFromArray($exampleStyle);

        $writer = new Xlsx($spreadsheet);
        $filename = 'leads_import_template_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function processBulkImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        // Increase execution time for large imports
        set_time_limit(600); // 10 minutes
        ini_set('memory_limit', '512M');

        try {
            DB::beginTransaction();

            $import = LeadImport::create([
                'user_id' => auth()->id(),
                'filename' => $request->file('csv_file')->getClientOriginalName(),
                'status' => 'processing',
            ]);

            $file = $request->file('csv_file');
            $extension = $file->getClientOriginalExtension();
            $records = [];
            $skippedSheets = [];

            // Load Excel file
            if (in_array($extension, ['xlsx', 'xls'])) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());

                $sheetCount = $spreadsheet->getSheetCount();
                Log::info("Excel has {$sheetCount} sheets");

                for ($sheetIndex = 0; $sheetIndex < $sheetCount; $sheetIndex++) {
                    $worksheet = $spreadsheet->getSheet($sheetIndex);
                    $sheetName = $worksheet->getTitle();
                    $highestRow = $worksheet->getHighestRow();
                    $highestColumn = $worksheet->getHighestColumn();

                    Log::info("Processing sheet: {$sheetName} with {$highestRow} rows and {$highestColumn} columns");

                    // Check row limit
                    if ($highestRow > 2001) { // 2000 data rows + 1 header
                        $skippedSheets[] = [
                            'name' => $sheetName,
                            'reason' => 'Sheet has ' . ($highestRow - 1) . ' rows. Maximum allowed is 2000 rows per sheet.'
                        ];
                        continue;
                    }

                    // Find header row dynamically
                    $headerRow = null;
                    for ($row = 1; $row <= 5; $row++) {
                        $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false)[0];
                        foreach ($rowData as $cell) {
                            if (stripos($cell, 'Customer Name') !== false) {
                                $headerRow = $row;
                                break 2;
                            }
                        }
                    }

                    if (!$headerRow) {
                        Log::warning("Skipping sheet '{$sheetName}' - header row not found");
                        $skippedSheets[] = [
                            'name' => $sheetName,
                            'reason' => 'Header row not found (looking for "Customer Name")'
                        ];
                        continue;
                    }

                    Log::info("Found header at row {$headerRow} in sheet {$sheetName}");

                    // Read header row and create column mapping
                    $headerArray = $worksheet->rangeToArray('A' . $headerRow . ':' . $highestColumn . $headerRow, null, true, false)[0];
                    $columnMapping = $this->mapColumns($headerArray);

                    Log::info("Column mapping for {$sheetName}:", $columnMapping);

                    // Validate required columns
                    if (!isset($columnMapping['customer_name']) || !isset($columnMapping['contact_number'])) {
                        Log::warning("Skipping sheet '{$sheetName}' - missing required columns");
                        $skippedSheets[] = [
                            'name' => $sheetName,
                            'reason' => 'Missing required columns (Customer Name or Contact Number)'
                        ];
                        continue;
                    }

                    // Data starts after header
                    $startRow = $headerRow + 1;

                    for ($row = $startRow; $row <= $highestRow; $row++) {
                        $rowArray = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false)[0];

                        // Extract customer name
                        $customerName = isset($rowArray[$columnMapping['customer_name']])
                            ? trim($rowArray[$columnMapping['customer_name']])
                            : '';

                        // Skip empty rows
                        if (
                            empty($customerName) ||
                            stripos($customerName, 'Customer Name') !== false ||
                            stripos($customerName, 'WORK TRACKER') !== false
                        ) {
                            continue;
                        }

                        // Extract Contact Number
                        $contactNumber = '';
                        if (isset($columnMapping['contact_number']) && isset($rowArray[$columnMapping['contact_number']])) {
                            $rawPhone = $rowArray[$columnMapping['contact_number']];

                            if (!empty($rawPhone)) {
                                if (is_numeric($rawPhone)) {
                                    $contactNumber = number_format($rawPhone, 0, '', '');
                                } else {
                                    $contactNumber = strval($rawPhone);
                                }
                                $contactNumber = preg_replace('/[^0-9]/', '', $contactNumber);
                            }
                        }

                        // Build row data
                        $rowData = [
                            'sheet_name' => $sheetName,
                            'excel_row' => $row,
                            'date' => $this->getColumnValue($rowArray, $columnMapping, 'date'),
                            'company' => $this->getColumnValue($rowArray, $columnMapping, 'company'),
                            'customer_name' => $customerName,
                            'work_description' => $this->getColumnValue($rowArray, $columnMapping, 'work_description'),
                            'package_amount' => $this->getColumnValue($rowArray, $columnMapping, 'package_amount'),
                            'final_amount' => $this->getColumnValue($rowArray, $columnMapping, 'final_amount', '0'),
                            'paid' => $this->getColumnValue($rowArray, $columnMapping, 'paid'),
                            'telecaller_name' => $this->getColumnValue($rowArray, $columnMapping, 'telecaller_name'),
                            'office_number' => $this->getColumnValue($rowArray, $columnMapping, 'office_number'),
                            'contact_number' => $contactNumber,
                            'payment_mode' => $this->getColumnValue($rowArray, $columnMapping, 'payment_mode'),
                            'address' => $this->getColumnValue($rowArray, $columnMapping, 'address'),
                            'district' => $this->getColumnValue($rowArray, $columnMapping, 'district'),
                            'source' => $this->getColumnValue($rowArray, $columnMapping, 'source'),
                        ];

                        $records[] = $rowData;
                    }
                }
            }

            $totalRows = count($records);

            if ($totalRows === 0 && count($skippedSheets) > 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No valid data rows found. All sheets were skipped due to incorrect format.',
                    'skipped_sheets' => $skippedSheets,
                ], 400);
            }

            if ($totalRows === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No valid data rows found in any sheet.',
                ], 400);
            }

            if ($totalRows > 3000) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Total rows ({$totalRows}) exceeds maximum limit of 3000 rows. Please split your file into smaller batches.",
                ], 400);
            }

            $import->update(['total_rows' => $totalRows]);

            $processedRows = 0;
            $successfulRows = 0;
            $failedRows = 0;
            $errors = [];
            $createdCustomers = 0;
            $createdJobs = 0;
            $failedRowsData = [];

            // Store initial progress in cache
            $progressKey = "import_progress_{$import->id}";
            Cache::put($progressKey, [
                'total' => $totalRows,
                'processed' => 0,
                'successful' => 0,
                'failed' => 0,
                'current_row' => 0,
                'current_sheet' => null,
                'status' => 'processing',
                'customers_created' => 0,
                'jobs_created' => 0,
            ], now()->addMinutes(30));

            // Process in chunks of 100 rows
            $chunkSize = 100;
            $chunks = array_chunk($records, $chunkSize);

            foreach ($chunks as $chunkIndex => $chunk) {
                Log::info("Processing chunk " . ($chunkIndex + 1) . " of " . count($chunks));

                foreach ($chunk as $index => $row) {
                    try {
                        $rowIdentifier = "{$row['sheet_name']} Row {$row['excel_row']}";

                        // DEBUG: Log every row being processed
                        Log::info("========== PROCESSING ROW {$rowIdentifier} ==========");
                        Log::info("Customer Name: {$row['customer_name']}");
                        Log::info("Phone: {$row['contact_number']}");

                        // Update progress cache every 5 rows for smoother updates
                        if ($processedRows % 5 === 0) {
                            Cache::put($progressKey, [
                                'total' => $totalRows,
                                'processed' => $processedRows,
                                'successful' => $successfulRows,
                                'failed' => $failedRows,
                                'current_row' => $processedRows,
                                'current_sheet' => $row['sheet_name'],
                                'status' => 'processing',
                                'customers_created' => $createdCustomers,
                                'jobs_created' => $createdJobs,
                            ], now()->addMinutes(30));
                        }

                        // Clean phone
                        $phone = $this->cleanPhoneNumber($row['contact_number']);
                        $phoneAlternative = null;

                        // ===== VALIDATION =====

                        // 1. Customer name is required
                        if (empty($row['customer_name'])) {
                            throw new \Exception("Customer name is required");
                        }

                        // 2. Phone is required (10-15 digits)
                        if (empty($phone) || strlen($phone) < 10 || strlen($phone) > 15) {
                            throw new \Exception("Valid phone number (10-15 digits) required");
                        }

                        // Find branch
                        $branch = null;
                        if (!empty($row['company'])) {
                            $branch = Branch::whereRaw('LOWER(name) = ?', [strtolower($row['company'])])->first();
                            if (!$branch) {
                                $branch = Branch::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($row['company']) . '%'])->first();
                            }
                        }
                        if (!$branch) {
                            $branch = Branch::first();
                        }

                        // 3. Service validation
                        $service = null;
                        $serviceType = 'cleaning';
                        $serviceIds = [];

                        if (!empty($row['work_description'])) {
                            $serviceMapping = $this->getServiceMapping();
                            $workDescription = strtoupper(trim($row['work_description']));

                            // Try exact mapping first
                            $dbServiceName = $serviceMapping[$workDescription] ?? null;

                            // Fuzzy match
                            if (!$dbServiceName) {
                                foreach ($serviceMapping as $key => $value) {
                                    if (stripos($workDescription, $key) !== false || stripos($key, $workDescription) !== false) {
                                        $dbServiceName = $value;
                                        Log::info("Fuzzy matched '{$workDescription}' to '{$key}' => '{$value}'");
                                        break;
                                    }
                                }
                            }

                            if (!$dbServiceName) {
                                $dbServiceName = $row['work_description'];
                            }

                            // Database lookup
                            $service = Service::whereRaw('LOWER(name) = ?', [strtolower($dbServiceName)])
                                ->where('is_active', true)
                                ->first();

                            if (!$service) {
                                $service = Service::where('is_active', true)
                                    ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($dbServiceName) . '%'])
                                    ->first();
                            }

                            // Keyword matching
                            if (!$service) {
                                if (
                                    stripos($workDescription, 'DEEP') !== false ||
                                    stripos($workDescription, 'PACKAGE') !== false ||
                                    stripos($workDescription, 'FULL') !== false
                                ) {
                                    $service = Service::where('is_active', true)
                                        ->where(function ($query) {
                                            $query->whereRaw('LOWER(name) LIKE ?', ['%deep%'])
                                                ->orWhereRaw('LOWER(name) LIKE ?', ['%full house%']);
                                        })
                                        ->first();

                                    if ($service) {
                                        Log::info("Keyword matched '{$workDescription}' to '{$service->name}'");
                                    }
                                }
                            }

                            if ($service) {
                                $serviceIds[] = $service->id;
                                $serviceType = $this->detectServiceType($service->name);
                            }
                        }

                        if (empty($serviceIds)) {
                            throw new \Exception("Service is required. Work description '{$row['work_description']}' not found in services");
                        }

                        // Find telecaller
                        $assignedTo = null;
                        if (!empty($row['telecaller_name'])) {
                            $telecallerName = strtolower(trim($row['telecaller_name']));

                            $telecaller = User::where(function ($query) {
                                $query->where('role', 'telecallers')
                                    ->orWhere('role', 'telecaller')
                                    ->orWhere('role', 'Telecaller');
                            })
                                ->whereRaw('LOWER(name) = ?', [$telecallerName])
                                ->where('is_active', true)
                                ->first();

                            if (!$telecaller) {
                                $telecaller = User::where(function ($query) {
                                    $query->where('role', 'telecallers')
                                        ->orWhere('role', 'telecaller')
                                        ->orWhere('role', 'Telecaller');
                                })
                                    ->whereRaw('LOWER(name) LIKE ?', ['%' . $telecallerName . '%'])
                                    ->where('is_active', true)
                                    ->first();
                            }

                            if (!$telecaller) {
                                $telecaller = User::whereRaw('LOWER(name) = ?', [$telecallerName])
                                    ->where('is_active', true)
                                    ->first();
                            }

                            if ($telecaller) {
                                $assignedTo = $telecaller->id;
                                Log::info("✓ Assigned telecaller: {$telecaller->name} (ID: {$telecaller->id}) to lead: {$row['customer_name']}");
                            } else {
                                Log::warning("✗ Telecaller '{$row['telecaller_name']}' not found - Lead will be unassigned");
                            }
                        }

                        // Amount
                        $finalAmount = !empty($row['final_amount']) ? floatval($row['final_amount']) : 0;

                        // Payment status
                        $paidStatus = strtoupper(trim($row['paid']));
                        $isPaymentReceived = in_array($paidStatus, ['RECEIVED', 'YES', 'PAID', 'DONE', 'COMPLETED']);

                        $advancePaid = 0;
                        if ($finalAmount > 0 && $isPaymentReceived) {
                            $advancePaid = $finalAmount;
                        }

                        // Payment mode
                        $paymentMode = null;
                        if (!empty($row['payment_mode'])) {
                            $paymentMode = strtolower(str_replace(' ', '_', trim($row['payment_mode'])));
                            if (!in_array($paymentMode, ['cash', 'upi', 'card', 'bank_transfer', 'neft', 'gpay', 'phonepe', 'paytm', 'amazonpay'])) {
                                $paymentMode = null;
                            }
                        }

                        // Lead source
                        $leadSource = null;

                        if (!empty($row['source'])) {
                            $leadSource = LeadSource::whereRaw('LOWER(name) = ?', [strtolower($row['source'])])
                                ->where('is_active', true)
                                ->first();

                            if (!$leadSource) {
                                $leadSource = LeadSource::where('is_active', true)
                                    ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($row['source']) . '%'])
                                    ->first();
                            }

                            if (!$leadSource) {
                                $leadSource = LeadSource::whereRaw('LOWER(code) = ?', [strtolower($row['source'])])
                                    ->where('is_active', true)
                                    ->first();
                            }
                        }

                        if (!$leadSource) {
                            $leadSource = LeadSource::where('name', 'Others')
                                ->orWhere('code', 'others')
                                ->where('is_active', true)
                                ->first();
                        }

                        if (!$leadSource) {
                            $leadSource = LeadSource::where('name', 'Bulk Import')
                                ->orWhere('code', 'bulk')
                                ->where('is_active', true)
                                ->first();
                        }

                        if (!$leadSource) {
                            $leadSource = LeadSource::where('is_active', true)->first();
                        }

                        // Parse date
                        $leadCreatedAt = $this->parseExcelDate($row['date']);
                        if (!$leadCreatedAt) {
                            $leadCreatedAt = now();
                        }

                        Log::info("Processing row with data:", [
                            'customer_name' => $row['customer_name'],
                            'phone' => $phone,
                            'excel_date' => $row['date'],
                            'parsed_date' => $leadCreatedAt->format('Y-m-d H:i:s'),
                            'payment_received' => $isPaymentReceived,
                        ]);

                        // ==================================================
                        // DUPLICATE CHECK - ONE CUSTOMER PER PHONE
                        // ==================================================
                        $existingCustomer = Customer::where('phone', $phone)->first();
                        $existingLead = Lead::where('phone', $phone)->first();

                        // CASE 1: Customer already exists
                        if ($existingCustomer) {
                            Log::info("Found existing customer {$existingCustomer->customer_code} for phone {$phone}");

                            if (!$isPaymentReceived) {
                                throw new \Exception("Customer with phone {$phone} already exists (Customer Code: {$existingCustomer->customer_code}). Cannot add non-paid entry for existing customer.");
                            }

                            $lead = $existingCustomer->lead ?? $existingLead;

                            if (!$lead) {
                                throw new \Exception("Customer exists but no lead found for phone {$phone}");
                            }

                            // Create new job ONLY - DON'T create lead or customer
                            $job = $this->createJobFromLead($lead, $existingCustomer, $serviceIds, $finalAmount, $row, $leadCreatedAt);

                            Log::info("✓ Created additional job {$job->job_code} for existing customer {$existingCustomer->customer_code}");

                            $createdJobs++;
                            $successfulRows++;
                            $processedRows++;

                            continue; // IMPORTANT: Skip to next row
                        }

                        // CASE 2: Lead exists but no customer
                        if ($existingLead) {
                            Log::info("Found existing lead {$existingLead->lead_code} for phone {$phone}");

                            if (!$isPaymentReceived) {
                                throw new \Exception("Phone {$phone} already exists in leads (Lead Code: {$existingLead->lead_code}). Cannot create duplicate lead without payment.");
                            }

                            // Double-check if customer was created after lead check
                            $existingCustomer = Customer::where('phone', $phone)->first();

                            if ($existingCustomer) {
                                // Customer exists now (race condition or created elsewhere)
                                Log::info("Customer {$existingCustomer->customer_code} found for phone {$phone}, creating job only");

                                $job = $this->createJobFromLead($existingLead, $existingCustomer, $serviceIds, $finalAmount, $row, $leadCreatedAt);
                                Log::info("✓ Created job {$job->job_code} for existing customer {$existingCustomer->customer_code}");
                                $createdJobs++;

                                $successfulRows++;
                                $processedRows++;
                                continue;
                            }

                            // Try to create customer
                            try {
                                $customer = $this->createCustomerFromLead($existingLead, $leadCreatedAt);
                                Log::info("✓ Created customer {$customer->customer_code} from existing lead {$existingLead->lead_code}");
                                $createdCustomers++;

                                // Create job
                                $job = $this->createJobFromLead($existingLead, $customer, $serviceIds, $finalAmount, $row, $leadCreatedAt);
                                Log::info("✓ Created job {$job->job_code} for new customer {$customer->customer_code}");
                                $createdJobs++;

                                $successfulRows++;
                                $processedRows++;
                                continue;
                            } catch (\Exception $customerError) {
                                // Customer creation failed (probably duplicate), check again
                                $existingCustomer = Customer::where('phone', $phone)->first();

                                if ($existingCustomer) {
                                    Log::warning("Customer creation failed but found existing: {$existingCustomer->customer_code}");

                                    $job = $this->createJobFromLead($existingLead, $existingCustomer, $serviceIds, $finalAmount, $row, $leadCreatedAt);
                                    Log::info("✓ Created job {$job->job_code} for existing customer {$existingCustomer->customer_code}");
                                    $createdJobs++;

                                    $successfulRows++;
                                    $processedRows++;
                                    continue;
                                }

                                // Rethrow if it's a different error
                                throw $customerError;
                            }
                        }

                        // CASE 3: New lead (phone doesn't exist anywhere)
                        Log::info("Creating new lead for phone {$phone}");

                        $lead = Lead::create([
                            'name' => $row['customer_name'],
                            'phone' => $phone,
                            'phone_alternative' => $phoneAlternative,
                            'address' => !empty($row['address']) ? $row['address'] : null,
                            'district' => !empty($row['district']) ? $row['district'] : null,
                            'service_type' => $serviceType,
                            'service_id' => $serviceIds[0] ?? null,
                            'lead_source_id' => $leadSource->id,
                            'amount' => $finalAmount,
                            'advance_paid_amount' => $advancePaid,
                            'payment_mode' => $paymentMode,
                            'amount_updated_at' => $finalAmount > 0 ? $leadCreatedAt : null,
                            'amount_updated_by' => $finalAmount > 0 ? auth()->id() : null,
                            'status' => $isPaymentReceived ? 'approved' : 'pending',
                            'branch_id' => $branch->id,
                            'created_by' => auth()->id(),
                            'assigned_to' => $assignedTo,
                            'description' => !empty($row['work_description']) ? $row['work_description'] : null,
                        ]);

                        $lead->created_at = $leadCreatedAt;
                        $lead->updated_at = $leadCreatedAt;
                        $lead->save();

                        if (!empty($serviceIds)) {
                            $lead->services()->sync($serviceIds);
                        }

                        Log::info("✓ Created NEW lead: {$lead->lead_code} - {$lead->name} [{$rowIdentifier}]");

                        // Create customer & job if paid
                        if ($isPaymentReceived) {
                            $customer = $this->createCustomerFromLead($lead, $leadCreatedAt);
                            Log::info("✓ Created customer: {$customer->customer_code} - {$customer->name}");
                            $createdCustomers++;

                            $job = $this->createJobFromLead($lead, $customer, $serviceIds, $finalAmount, $row, $leadCreatedAt);
                            Log::info("✓ Created job: {$job->job_code} for customer {$customer->customer_code}");
                            $createdJobs++;
                        }

                        $successfulRows++;
                        $processedRows++;
                    } catch (\Exception $e) {
                        $errors[] = [
                            'row' => $rowIdentifier,
                            'sheet' => $row['sheet_name'],
                            'excel_row' => $row['excel_row'],
                            'data' => ($row['customer_name'] ?? 'Unknown') . ' (' . ($row['contact_number'] ?? 'No phone') . ')',
                            'errors' => [$e->getMessage()],
                        ];

                        $failedRowsData[] = $row;
                        $failedRows++;
                        $processedRows++;

                        Log::error("Row {$rowIdentifier} error:", [
                            'error' => $e->getMessage(),
                            'customer' => $row['customer_name'] ?? 'N/A',
                        ]);
                    }
                }

                // Update cache after each chunk
                Cache::put($progressKey, [
                    'total' => $totalRows,
                    'processed' => $processedRows,
                    'successful' => $successfulRows,
                    'failed' => $failedRows,
                    'current_row' => $processedRows,
                    'current_sheet' => $row['sheet_name'] ?? null,
                    'status' => 'processing',
                    'customers_created' => $createdCustomers,
                    'jobs_created' => $createdJobs,
                ], now()->addMinutes(30));

                gc_collect_cycles();
            }

            // Final cache update
            Cache::put($progressKey, [
                'total' => $totalRows,
                'processed' => $processedRows,
                'successful' => $successfulRows,
                'failed' => $failedRows,
                'current_row' => $processedRows,
                'status' => 'completed',
                'customers_created' => $createdCustomers,
                'jobs_created' => $createdJobs,
            ], now()->addMinutes(30));

            // Update import record
            $import->update([
                'status' => $failedRows === $totalRows ? 'failed' : 'completed',
                'processed_rows' => $processedRows,
                'successful_rows' => $successfulRows,
                'failed_rows' => $failedRows,
                'errors' => $errors,
                'failed_rows_data' => $failedRowsData,
            ]);

            DB::commit();

            $message = $successfulRows > 0
                ? "Successfully imported {$successfulRows} leads!" .
                ($createdCustomers > 0 ? " Created {$createdCustomers} customers." : "") .
                ($createdJobs > 0 ? " Created {$createdJobs} jobs." : "") .
                ($failedRows > 0 ? " {$failedRows} rows failed." : "")
                : "Import failed! All {$failedRows} rows had errors.";

            if (count($skippedSheets) > 0) {
                $message .= " " . count($skippedSheets) . " sheet(s) skipped due to incorrect format.";
            }

            return response()->json([
                'success' => $successfulRows > 0,
                'message' => $message,
                'import_id' => $import->id,
                'stats' => [
                    'total' => $totalRows,
                    'processed' => $processedRows,
                    'successful' => $successfulRows,
                    'failed' => $failedRows,
                    'customers_created' => $createdCustomers,
                    'jobs_created' => $createdJobs,
                ],
                'skipped_sheets' => $skippedSheets,
                'errors' => $errors,
                'has_failed_rows' => $failedRows > 0,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($import)) {
                $import->update([
                    'status' => 'failed',
                    'errors' => [['message' => $e->getMessage()]],
                ]);
            }

            Log::error('Bulk import failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Map Excel column headers to internal field names
     */
    private function mapColumns($headerArray)
    {
        $mapping = [];

        // Define header patterns - ORDER MATTERS! More specific first
        $patterns = [
            'customer_name' => ['customer name', 'client name'],
            'telecaller_name' => ['telecaller name', 'telecaller'],
            'company' => ['name of the company', 'company name', 'company', 'branch'],
            'date' => ['date', 'created at', 'created date'],
            'work_description' => ['work description', 'service', 'work'],
            'package_amount' => ['package amount', 'package'],
            'final_amount' => ['final amount', 'total amount', 'amount'],
            'paid' => ['paid (yes/no)', 'paid', 'payment status'],
            'office_number' => ['office number', 'office phone'],
            'contact_number' => ['contact number', 'phone', 'mobile', 'contact'],
            'payment_mode' => ['payment mode', 'payment method', 'mode'],
            'address' => ['address', 'location'],
            'district' => ['district', 'city'],
            'source' => ['source', 'lead source'],
        ];

        // First pass - exact matches only
        foreach ($headerArray as $index => $header) {
            $header = strtolower(trim($header));

            if (empty($header)) {
                continue;
            }

            foreach ($patterns as $fieldName => $headerPatterns) {
                foreach ($headerPatterns as $pattern) {
                    if ($header === $pattern) {
                        $mapping[$fieldName] = $index;
                        break 2;
                    }
                }
            }
        }

        // Second pass - partial matches for unmapped fields
        foreach ($headerArray as $index => $header) {
            $header = strtolower(trim($header));

            if (empty($header)) {
                continue;
            }

            foreach ($patterns as $fieldName => $headerPatterns) {
                // Skip if already mapped
                if (isset($mapping[$fieldName])) {
                    continue;
                }

                foreach ($headerPatterns as $pattern) {
                    if (stripos($header, $pattern) !== false) {
                        $mapping[$fieldName] = $index;
                        break 2;
                    }
                }
            }
        }

        return $mapping;
    }

    /**
     * Parse Excel date to Carbon instance
     */
    private function parseExcelDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        try {
            // If it's already a date string (DD/MM/YYYY)
            if (is_string($dateValue) && strpos($dateValue, '/') !== false) {
                $parts = explode('/', $dateValue);
                if (count($parts) === 3) {
                    // DD/MM/YYYY format
                    return \Carbon\Carbon::createFromFormat('d/m/Y', $dateValue)->startOfDay();
                }
            }

            // If it's an Excel serial date number
            if (is_numeric($dateValue)) {
                // Excel stores dates as days since 1900-01-01
                $unixTimestamp = ($dateValue - 25569) * 86400;
                return \Carbon\Carbon::createFromTimestamp($unixTimestamp)->startOfDay();
            }

            // Try parsing as standard date
            return \Carbon\Carbon::parse($dateValue)->startOfDay();
        } catch (\Exception $e) {
            Log::warning("Could not parse date: {$dateValue}");
            return null;
        }
    }

    /**
     * Get column value safely using mapping
     */
    private function getColumnValue($rowArray, $columnMapping, $fieldName, $default = '')
    {
        if (isset($columnMapping[$fieldName]) && isset($rowArray[$columnMapping[$fieldName]])) {
            return trim($rowArray[$columnMapping[$fieldName]]);
        }
        return $default;
    }

    public function getImportProgress($importId)
    {
        $progressKey = "import_progress_{$importId}";
        $progress = Cache::get($progressKey);

        if (!$progress) {
            // Fallback to database
            $import = LeadImport::find($importId);
            if ($import) {
                return response()->json([
                    'total' => $import->total_rows,
                    'processed' => $import->processed_rows,
                    'successful' => $import->successful_rows,
                    'failed' => $import->failed_rows,
                    'status' => $import->status,
                    'percentage' => $import->total_rows > 0 ? round(($import->processed_rows / $import->total_rows) * 100, 1) : 0,
                ]);
            }

            return response()->json(['error' => 'Import not found'], 404);
        }

        $percentage = $progress['total'] > 0 ? round(($progress['processed'] / $progress['total']) * 100, 1) : 0;

        return response()->json([
            'total' => $progress['total'],
            'processed' => $progress['processed'],
            'successful' => $progress['successful'],
            'failed' => $progress['failed'],
            'current_row' => $progress['current_row'],
            'current_sheet' => $progress['current_sheet'] ?? null,
            'status' => $progress['status'],
            'percentage' => $percentage,
            'customers_created' => $progress['customers_created'] ?? 0,
            'jobs_created' => $progress['jobs_created'] ?? 0,
        ]);
    }

    public function downloadFailedRows($importId)
    {
        try {
            $import = LeadImport::findOrFail($importId);

            // Detailed logging
            Log::info('Download attempt:', [
                'import_id' => $import->id,
                'failed_rows' => $import->failed_rows,
                'failed_rows_data_type' => gettype($import->failed_rows_data),
                'failed_rows_data_empty' => empty($import->failed_rows_data),
            ]);

            // Check if failed rows data exists
            $failedData = $import->failed_rows_data;

            if (empty($failedData)) {
                Log::warning('No failed rows data found for import: ' . $importId);
                return redirect()->route('leads.bulk-import')
                    ->with('error', 'No failed rows data available. The data may not have been saved during import.');
            }

            // Decode if it's JSON string (shouldn't happen with proper casting, but just in case)
            if (is_string($failedData)) {
                $failedData = json_decode($failedData, true);
            }

            if (!is_array($failedData) || count($failedData) === 0) {
                Log::warning('Failed rows data is not an array or is empty for import: ' . $importId);
                return redirect()->route('leads.bulk-import')
                    ->with('error', 'Failed rows data is corrupted or empty.');
            }

            Log::info('Starting download with ' . count($failedData) . ' failed rows');

            $filename = 'failed_rows_import_' . $import->id . '_' . date('Ymd_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            return response()->stream(function () use ($import, $failedData) {
                $file = fopen('php://output', 'w');

                // Add UTF-8 BOM for Excel
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Write header
                fputcsv($file, [
                    'Sheet Name',
                    'Excel Row',
                    'Date',
                    'Company',
                    'Customer Name',
                    'Work Description',
                    'Package Amount',
                    'Final Amount',
                    'Paid',
                    'Telecaller Name',
                    'Office Number',
                    'Contact Number',
                    'Payment Mode',
                    'Address',
                    'District',
                    'Source',
                    'Error Reason'
                ]);

                // Get errors
                $errors = $import->errors ?? [];
                if (is_string($errors)) {
                    $errors = json_decode($errors, true);
                }

                $errorsByRow = [];
                foreach ($errors as $error) {
                    $key = ($error['sheet'] ?? '') . '_' . ($error['excel_row'] ?? '');
                    $errorReason = $error['errors'] ?? 'Unknown error';
                    if (is_array($errorReason)) {
                        $errorReason = implode('; ', $errorReason);
                    }
                    $errorsByRow[$key] = $errorReason;
                }

                // Write failed rows
                foreach ($failedData as $row) {
                    $key = ($row['sheet_name'] ?? '') . '_' . ($row['excel_row'] ?? '');
                    $errorReason = $errorsByRow[$key] ?? 'Unknown error';

                    fputcsv($file, [
                        $row['sheet_name'] ?? '',
                        $row['excel_row'] ?? '',
                        $row['date'] ?? '',
                        $row['company'] ?? '',
                        $row['customer_name'] ?? '',
                        $row['work_description'] ?? '',
                        $row['package_amount'] ?? '',
                        $row['final_amount'] ?? '',
                        $row['paid'] ?? '',
                        $row['telecaller_name'] ?? '',
                        $row['office_number'] ?? '',
                        $row['contact_number'] ?? '',
                        $row['payment_mode'] ?? '',
                        $row['address'] ?? '',
                        $row['district'] ?? '',
                        $row['source'] ?? '',
                        $errorReason
                    ]);
                }

                fclose($file);
            }, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Download failed rows error:', [
                'import_id' => $importId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('leads.bulk-import')
                ->with('error', 'Download failed: ' . $e->getMessage());
        }
    }

    /**
     * Pre-validate import data before processing
     * Analyzes the file for duplicates and provides detailed statistics
     */
    public function preValidateImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        try {
            $file = $request->file('csv_file');
            $extension = $file->getClientOriginalExtension();
            $records = [];

            // [KEEP YOUR EXISTING FILE PARSING CODE HERE - lines 1-100]
            // ... (same as before until you get $records array)

            if (in_array($extension, ['xlsx', 'xls'])) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $sheetCount = $spreadsheet->getSheetCount();

                for ($sheetIndex = 0; $sheetIndex < $sheetCount; $sheetIndex++) {
                    $worksheet = $spreadsheet->getSheet($sheetIndex);
                    $highestRow = $worksheet->getHighestRow();
                    $highestColumn = $worksheet->getHighestColumn();

                    if ($highestRow > 2001) continue;

                    $headerRow = null;
                    for ($row = 1; $row <= 5; $row++) {
                        $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false)[0];
                        foreach ($rowData as $cell) {
                            if (stripos($cell, 'Customer Name') !== false) {
                                $headerRow = $row;
                                break 2;
                            }
                        }
                    }

                    if (!$headerRow) continue;

                    $headerArray = $worksheet->rangeToArray('A' . $headerRow . ':' . $highestColumn . $headerRow, null, true, false)[0];
                    $columnMapping = $this->mapColumns($headerArray);

                    if (!isset($columnMapping['customer_name']) || !isset($columnMapping['contact_number'])) continue;

                    $startRow = $headerRow + 1;
                    for ($row = $startRow; $row <= $highestRow; $row++) {
                        $rowArray = $worksheet->rangeToArray('A'.$row.':'.$highestColumn.$row, null, true, false)[0];

                        $customerName = isset($rowArray[$columnMapping['customer_name']])
                            ? trim($rowArray[$columnMapping['customer_name']])
                            : '';

                        if (empty($customerName) ||
                            stripos($customerName, 'Customer Name') !== false ||
                            stripos($customerName, 'WORK TRACKER') !== false) {
                            continue;
                        }

                        $contactNumber = '';
                        if (isset($columnMapping['contact_number']) && isset($rowArray[$columnMapping['contact_number']])) {
                            $rawPhone = $rowArray[$columnMapping['contact_number']];
                            if (!empty($rawPhone)) {
                                if (is_numeric($rawPhone)) {
                                    $contactNumber = number_format($rawPhone, 0, '', '');
                                } else {
                                    $contactNumber = strval($rawPhone);
                                }
                                $contactNumber = preg_replace('/[^0-9]/', '', $contactNumber);
                            }
                        }

                        $records[] = [
                            'customer_name' => $customerName,
                            'contact_number' => $contactNumber,
                            'paid' => $this->getColumnValue($rowArray, $columnMapping, 'paid'),
                        ];
                    }
                }
            }

            if (empty($records)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid data found in the file.',
                ], 400);
            }

            // Clean phone numbers
            $phoneNumbers = [];
            foreach ($records as $record) {
                $phone = $this->cleanPhoneNumber($record['contact_number']);
                if ($phone) {
                    $phoneNumbers[] = $phone;
                }
            }

            $totalRows = count($records);
            $uniquePhones = array_unique($phoneNumbers);

            if (empty($uniquePhones)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid phone numbers found.',
                ], 400);
            }

            // ============================================================
            // BULLETPROOF DATABASE QUERIES - USE RAW SQL
            // ============================================================

            // 1. Get ALL phones that exist as customers (most important)
            $existingCustomerPhones = Customer::whereIn('phone', $uniquePhones)
                ->pluck('phone')
                ->toArray();

            // 2. Get phones that exist ONLY as leads (not in customers table)
            // Use whereNotIn to exclude phones that are already customers
            $existingLeadOnlyPhones = Lead::whereIn('phone', $uniquePhones)
                ->whereNotIn('phone', $existingCustomerPhones) // Exclude customer phones
                ->pluck('phone')
                ->unique()
                ->toArray();

            Log::info('Pre-validation phone analysis:', [
                'total_unique_phones' => count($uniquePhones),
                'existing_as_customers' => count($existingCustomerPhones),
                'existing_as_leads_only' => count($existingLeadOnlyPhones),
                'sample_customer_phones' => array_slice($existingCustomerPhones, 0, 5),
                'sample_lead_only_phones' => array_slice($existingLeadOnlyPhones, 0, 5),
            ]);

            // Combine for total existing
            $allExistingPhones = array_unique(array_merge($existingCustomerPhones, $existingLeadOnlyPhones));
            $existingCount = count($allExistingPhones);
            $newCount = count($uniquePhones) - $existingCount;

            // ============================================================
            // DETAILED ANALYSIS
            // ============================================================

            $newLeadsToCreate = 0;
            $newCustomersToCreate = 0;
            $newJobsToCreate = 0;
            $jobsForExistingCustomers = 0;
            $leadsToConvert = 0; // NEW: Track lead conversions separately
            $skippedDuplicates = 0;
            $processedPhones = [];

            // Log for debugging
            Log::info('Starting analysis of ' . count($records) . ' records');

            foreach ($records as $index => $record) {
                $phone = $this->cleanPhoneNumber($record['contact_number']);
                if (!$phone) {
                    $skippedDuplicates++;
                    Log::debug("Row {$index}: Invalid phone, skipped");
                    continue;
                }

                $paidStatus = strtoupper(trim($record['paid']));
                $isPaid = in_array($paidStatus, ['RECEIVED', 'YES', 'PAID', 'DONE', 'COMPLETED']);

                // Check existence
                $existsAsCustomer = in_array($phone, $existingCustomerPhones);
                $existsAsLeadOnly = in_array($phone, $existingLeadOnlyPhones);
                $existsInDB = $existsAsCustomer || $existsAsLeadOnly;

                // Log each phone's status
                if ($index < 5) { // Log first 5 for debugging
                    Log::debug("Row {$index} - Phone: {$phone}, Paid: " . ($isPaid ? 'Yes' : 'No') .
                            ", Customer: " . ($existsAsCustomer ? 'Yes' : 'No') .
                            ", Lead-only: " . ($existsAsLeadOnly ? 'Yes' : 'No'));
                }

                // Within-file duplicates
                if (isset($processedPhones[$phone])) {
                    if ($isPaid && $existsAsCustomer) {
                        $newJobsToCreate++;
                        $jobsForExistingCustomers++;
                    } else {
                        $skippedDuplicates++;
                    }
                    continue;
                }

                $processedPhones[$phone] = true;

                // ==========================================
                // CASE 1: CUSTOMER EXISTS
                // ==========================================
                if ($existsAsCustomer) {
                    if ($isPaid) {
                        $jobsForExistingCustomers++;
                        $newJobsToCreate++;
                    } else {
                        // Unpaid duplicate customer - skip
                        $skippedDuplicates++;
                    }
                }
                // ==========================================
                // CASE 2: LEAD EXISTS (WITHOUT CUSTOMER)
                // ==========================================
                elseif ($existsAsLeadOnly) {
                    if ($isPaid) {
                        // Will CONVERT existing lead to customer
                        $leadsToConvert++;
                        $newCustomersToCreate++;
                        $newJobsToCreate++;
                        // DON'T increment newLeadsToCreate - lead already exists!
                    } else {
                        // Unpaid duplicate lead - skip
                        $skippedDuplicates++;
                    }
                }
                // ==========================================
                // CASE 3: TRULY NEW PHONE (NOT IN DB AT ALL)
                // ==========================================
                else {
                    // Phone doesn't exist in database
                    $newLeadsToCreate++;
                    if ($isPaid) {
                        $newCustomersToCreate++;
                        $newJobsToCreate++;
                    }
                }
            }

            // ============================================================
            // DIAGNOSTIC: LOG THE 2 NEW PHONE NUMBERS
            // ============================================================

            // Find phones that don't exist in database
            $newPhones = [];
            foreach ($uniquePhones as $phone) {
                $existsAsCustomer = in_array($phone, $existingCustomerPhones);
                $existsAsLeadOnly = in_array($phone, $existingLeadOnlyPhones);

                if (!$existsAsCustomer && !$existsAsLeadOnly) {
                    $newPhones[] = $phone;
                }
            }

            Log::info('NEW PHONE NUMBERS DETECTED:', [
                'count' => count($newPhones),
                'phones' => $newPhones,
            ]);

            // Also log what records in the file have these phones
            if (!empty($newPhones)) {
                foreach ($records as $index => $record) {
                    $phone = $this->cleanPhoneNumber($record['contact_number']);
                    if (in_array($phone, $newPhones)) {
                        Log::info("New phone found in file:", [
                            'row' => $index + 1,
                            'phone' => $phone,
                            'customer_name' => $record['customer_name'],
                            'paid_status' => $record['paid'],
                        ]);
                    }
                }
            }

            Log::info('Analysis complete:', [
                'new_leads' => $newLeadsToCreate,
                'leads_to_convert' => $leadsToConvert,
                'new_customers' => $newCustomersToCreate,
                'new_jobs' => $newJobsToCreate,
                'jobs_for_existing' => $jobsForExistingCustomers,
                'skipped' => $skippedDuplicates,
            ]);

            // ============================================================
            // BUILD WARNING MESSAGE
            // ============================================================

            $needsWarning = false;
            $warningMessage = '';

            if ($existingCount === count($uniquePhones)) {
                // ALL phones exist in database
                $needsWarning = true;
                $warningMessage = "⚠️ All " . count($uniquePhones) . " phone number(s) already exist in the system.";

                if ($leadsToConvert > 0) {
                    $warningMessage .= " {$leadsToConvert} existing lead(s) will be converted to customers.";
                }

                if ($jobsForExistingCustomers > 0) {
                    $warningMessage .= " {$jobsForExistingCustomers} new job(s) will be created for existing customers.";
                }

                if ($skippedDuplicates > 0) {
                    $warningMessage .= " {$skippedDuplicates} duplicate(s) will be skipped (no payment).";
                }

                if ($newJobsToCreate === 0 && $newCustomersToCreate === 0) {
                    $warningMessage .= " ⚠️ WARNING: No new data will be created - complete duplicate!";
                }
            } elseif ($existingCount > 0) {
                // SOME phones are duplicates
                $needsWarning = true;
                $warningMessage = "⚠️ Found {$existingCount} duplicate phone(s) out of " . count($uniquePhones) . " unique.";

                if ($newLeadsToCreate > 0) {
                    $warningMessage .= " {$newLeadsToCreate} new lead(s) will be created.";
                }

                if ($leadsToConvert > 0) {
                    $warningMessage .= " {$leadsToConvert} existing lead(s) will be converted.";
                }

                if ($jobsForExistingCustomers > 0) {
                    $warningMessage .= " {$jobsForExistingCustomers} job(s) for existing customers.";
                }
            }

            return response()->json([
                'success' => true,
                'needs_warning' => $needsWarning,
                'warning_message' => $warningMessage,
                'analysis' => [
                    'total_rows' => $totalRows,
                    'unique_phones' => count($uniquePhones),
                    'existing_count' => $existingCount,
                    'new_count' => $newCount,
                    'will_create_leads' => $newLeadsToCreate,
                    'will_create_customers' => $newCustomersToCreate,
                    'will_create_jobs' => $newJobsToCreate,
                    'new_jobs_for_existing' => $jobsForExistingCustomers,
                    'leads_to_convert' => $leadsToConvert, // NEW: Add this
                    'skipped_duplicates' => $skippedDuplicates,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Pre-validation error:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
            ], 500);
        }
    }


}
