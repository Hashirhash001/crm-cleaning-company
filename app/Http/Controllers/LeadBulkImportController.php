<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Customer;
use App\Models\Service;
use App\Models\LeadImport;
use App\Models\LeadSource;
use App\Models\Branch;
use App\Models\User;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\Validator;

class LeadBulkImportController extends Controller
{
    // Service name mapping from CSV to database
    private function getServiceMapping()
    {
        return [
            // Existing mappings
            'DEEP CLEANING' => 'Deep Cleaning Full House',
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

        // Remove all non-numeric characters (spaces, +, -, etc.)
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Must be at least 10 digits
        if (strlen($phone) < 10) {
            return null;
        }

        // Return full number (with country code if present)
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
                    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                    Log::info("Processing sheet: {$sheetName} with {$highestRow} rows and {$highestColumn} columns");

                    // Find header row dynamically (look for "Customer Name")
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

                    // Validate that required columns exist
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
                        $rowArray = $worksheet->rangeToArray('A'.$row.':'.$highestColumn.$row, null, true, false)[0];

                        // Extract customer name using dynamic mapping
                        $customerName = isset($rowArray[$columnMapping['customer_name']])
                            ? trim($rowArray[$columnMapping['customer_name']])
                            : '';

                        // Skip empty rows or header-like rows
                        if (empty($customerName) ||
                            stripos($customerName, 'Customer Name') !== false ||
                            stripos($customerName, 'WORK TRACKER') !== false) {
                            continue;
                        }

                        // Extract Contact Number using dynamic mapping
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

                        // Build row data using dynamic mapping
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

            $import->update(['total_rows' => $totalRows]);

            $processedRows = 0;
            $successfulRows = 0;
            $failedRows = 0;
            $errors = [];
            $createdCustomers = 0;
            $createdJobs = 0;

            foreach ($records as $index => $row) {
                try {
                    $rowIdentifier = "{$row['sheet_name']} Row {$row['excel_row']}";

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

                    // Find branch (nullable)
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

                    // 3. Service is required - from work description
                    $service = null;
                    $serviceType = 'cleaning';
                    $serviceIds = [];

                    if (!empty($row['work_description'])) {
                        $serviceMapping = $this->getServiceMapping();
                        $workDescription = strtoupper(trim($row['work_description']));

                        // Try exact mapping first
                        $dbServiceName = $serviceMapping[$workDescription] ?? null;

                        // If no exact match, try partial match in mapping keys
                        if (!$dbServiceName) {
                            foreach ($serviceMapping as $key => $value) {
                                // Check if work description contains the key or vice versa
                                if (stripos($workDescription, $key) !== false || stripos($key, $workDescription) !== false) {
                                    $dbServiceName = $value;
                                    Log::info("Fuzzy matched '{$workDescription}' to '{$key}' => '{$value}'");
                                    break;
                                }
                            }
                        }

                        // Fallback to original work description if no mapping found
                        if (!$dbServiceName) {
                            $dbServiceName = $row['work_description'];
                        }

                        // Try exact match in database
                        $service = Service::whereRaw('LOWER(name) = ?', [strtolower($dbServiceName)])
                            ->where('is_active', true)
                            ->first();

                        // Try partial match in database
                        if (!$service) {
                            $service = Service::where('is_active', true)
                                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($dbServiceName) . '%'])
                                ->first();
                        }

                        // Try matching keywords if still not found
                        if (!$service) {
                            // Extract keywords from work description
                            $keywords = ['DEEP', 'CLEANING', 'PACKAGE', 'FULL'];
                            $hasDeep = false;
                            $hasCleaning = false;

                            foreach ($keywords as $keyword) {
                                if (stripos($workDescription, $keyword) !== false) {
                                    if ($keyword === 'DEEP') $hasDeep = true;
                                    if ($keyword === 'CLEANING') $hasCleaning = true;
                                    if ($keyword === 'PACKAGE') $hasDeep = true; // Package implies deep
                                    if ($keyword === 'FULL') $hasDeep = true; // Full implies deep
                                }
                            }

                            // If it contains "deep" or "package" or "full", try to find deep cleaning service
                            if ($hasDeep || stripos($workDescription, 'PACKAGE') !== false) {
                                $service = Service::where('is_active', true)
                                    ->where(function($query) {
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
                        throw new \Exception("Service is required. Work description '{$row['work_description']}' not found in services. Please add this service or update the mapping.");
                    }

                    // Find telecaller and assign (this is the assigned_to field)
                    $assignedTo = null;
                    if (!empty($row['telecaller_name'])) {
                        $telecallerName = strtolower(trim($row['telecaller_name']));

                        // Try exact name match with role check
                        $telecaller = User::where(function($query) {
                                $query->where('role', 'telecallers')
                                    ->orWhere('role', 'telecaller')
                                    ->orWhere('role', 'Telecaller');
                            })
                            ->whereRaw('LOWER(name) = ?', [$telecallerName])
                            ->where('is_active', true)
                            ->first();

                        // If not found, try partial match
                        if (!$telecaller) {
                            $telecaller = User::where(function($query) {
                                    $query->where('role', 'telecallers')
                                        ->orWhere('role', 'telecaller')
                                        ->orWhere('role', 'Telecaller');
                                })
                                ->whereRaw('LOWER(name) LIKE ?', ['%' . $telecallerName . '%'])
                                ->where('is_active', true)
                                ->first();
                        }

                        // Last resort - try without role filter
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

                    // Amount (nullable)
                    $finalAmount = !empty($row['final_amount']) ? floatval($row['final_amount']) : 0;

                    // Check if payment is received - normalize the paid status
                    $paidStatus = strtoupper(trim($row['paid']));
                    $isPaymentReceived = in_array($paidStatus, ['RECEIVED', 'YES', 'PAID', 'DONE', 'COMPLETED']);

                    // Calculate advance paid (nullable)
                    $advancePaid = 0;
                    if ($finalAmount > 0 && $isPaymentReceived) {
                        $advancePaid = $finalAmount;
                    }

                    // Map payment mode (nullable)
                    $paymentMode = null;
                    if (!empty($row['payment_mode'])) {
                        $paymentMode = strtolower(str_replace(' ', '_', trim($row['payment_mode'])));
                        if (!in_array($paymentMode, ['cash', 'upi', 'card', 'bank_transfer', 'neft', 'gpay', 'phonepe', 'paytm', 'amazonpay'])) {
                            $paymentMode = null;
                        }
                    }

                    // Find lead source
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

                    // Parse the date from Excel (before lead creation)
                    $leadCreatedAt = $this->parseExcelDate($row['date']);
                    if (!$leadCreatedAt) {
                        $leadCreatedAt = now(); // Fallback to current time
                    }

                    Log::info("Processing row with data:", [
                        'customer_name' => $row['customer_name'],
                        'phone' => $phone,
                        'excel_date' => $row['date'],
                        'parsed_date' => $leadCreatedAt->format('Y-m-d H:i:s'),
                        'payment_received' => $isPaymentReceived,
                    ]);

                    // ==================================================
                    // CHECK IF LEAD ALREADY EXISTS WITH THIS PHONE
                    // ==================================================
                    $existingLead = Lead::where('phone', $phone)->first();
                    $existingCustomer = Customer::where('phone', $phone)->first();

                    if ($existingLead && $isPaymentReceived) {
                        // CASE: Lead exists and payment is received
                        // We'll create a new job for the existing lead/customer

                        Log::info("Found existing lead {$existingLead->lead_code} for phone {$phone}. Payment received - creating additional job.");

                        // Use existing customer or create if not exists
                        if (!$existingCustomer) {
                            $existingCustomer = $this->createCustomerFromLead($existingLead, $leadCreatedAt);
                            Log::info("✓ Created customer {$existingCustomer->customer_code} from existing lead {$existingLead->lead_code}");
                            $createdCustomers++;
                        }

                        // Create new job for this service
                        $job = $this->createJobFromLead($existingLead, $existingCustomer, $serviceIds, $finalAmount, $row, $leadCreatedAt);

                        Log::info("✓ Created additional job {$job->job_code} for existing customer {$existingCustomer->customer_code}");

                        $createdJobs++;
                        $successfulRows++;
                        $processedRows++;

                        continue; // Skip to next row
                    }

                    if ($existingLead && !$isPaymentReceived) {
                        // Lead exists but payment not received - skip duplicate
                        throw new \Exception("Phone {$phone} already exists in leads (Lead Code: {$existingLead->lead_code})");
                    }

                    if ($existingCustomer && !$isPaymentReceived) {
                        // Customer exists but no lead - this shouldn't happen in normal flow
                        throw new \Exception("Phone {$phone} already exists in customers (Customer Code: {$existingCustomer->customer_code})");
                    }

                    // ==================================================
                    // CREATE NEW LEAD
                    // ==================================================
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
                        'status' => $isPaymentReceived ? 'approved' : 'pending', // Auto-approve if paid
                        'branch_id' => $branch->id,
                        'created_by' => auth()->id(),
                        'assigned_to' => $assignedTo,
                        'description' => !empty($row['work_description']) ? $row['work_description'] : null,
                    ]);

                    // Set timestamps manually
                    $lead->created_at = $leadCreatedAt;
                    $lead->updated_at = $leadCreatedAt;
                    $lead->save();

                    // Sync services
                    if (!empty($serviceIds)) {
                        $lead->services()->sync($serviceIds);
                    }

                    Log::info("✓ Created lead: {$lead->lead_code} - {$lead->name} - Source: {$leadSource->name} [{$rowIdentifier}]");

                    // ==================================================
                    // IF PAYMENT RECEIVED: CREATE CUSTOMER & JOB
                    // ==================================================
                    if ($isPaymentReceived) {
                        // Create customer from lead
                        $customer = $this->createCustomerFromLead($lead, $leadCreatedAt);

                        Log::info("✓ Created customer: {$customer->customer_code} - {$customer->name}");
                        $createdCustomers++;

                        // Create job from lead
                        $job = $this->createJobFromLead($lead, $customer, $serviceIds, $finalAmount, $row, $leadCreatedAt);

                        Log::info("✓ Created job: {$job->job_code} for customer {$customer->customer_code}");
                        $createdJobs++;
                    }

                    $successfulRows++;
                    $processedRows++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $rowIdentifier,
                        'data' => ($row['customer_name'] ?? 'Unknown') . ' (' . ($row['contact_number'] ?? 'No phone') . ')',
                        'errors' => [$e->getMessage()],
                    ];
                    $failedRows++;
                    $processedRows++;

                    Log::error("Row {$rowIdentifier} error:", [
                        'error' => $e->getMessage(),
                        'customer' => $row['customer_name'] ?? 'N/A',
                    ]);
                }
            }

            $import->update([
                'status' => $failedRows === $totalRows ? 'failed' : 'completed',
                'processed_rows' => $processedRows,
                'successful_rows' => $successfulRows,
                'failed_rows' => $failedRows,
                'errors' => $errors,
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
                'errors' => count($errors) > 20 ? array_slice($errors, 0, 20) : $errors,
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
        $import = LeadImport::findOrFail($importId);

        return response()->json([
            'status' => $import->status,
            'total_rows' => $import->total_rows,
            'processed_rows' => $import->processed_rows,
            'successful_rows' => $import->successful_rows,
            'failed_rows' => $import->failed_rows,
            'percentage' => $import->total_rows > 0 ? round(($import->processed_rows / $import->total_rows) * 100, 2) : 0,
        ]);
    }
}
