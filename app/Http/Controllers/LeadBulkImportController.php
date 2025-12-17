<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Customer;
use App\Models\Service;
use App\Models\LeadImport;
use App\Models\LeadSource;
use App\Models\Branch;
use App\Models\User;
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
            'DEEP CLEANING' => 'Deep Cleaning Full House',
            'BATHROOM DEEP CLEANING' => 'Bathroom Deep',
            'SOFA SHAMPOOING' => 'Sofa Shampooing',
            'TERMITE TREATMENT' => 'Termite Treatment',
            'WATER TANK' => 'Tank Cleaning',
            'FLOOR SCRUBBING' => 'Floor Scrubbing',
            'KITCHEN DEEP' => 'Kitchen Deep',
            'PRESSURE WASH' => 'Pressure Wash',
            'GLASS CLEANING' => 'Glass Cleaning',
            'CARPET SHAMPOO' => 'Carpet Shampoo',
            'GENERAL PEST' => 'General Pest',
            'REGULAR CLEANING' => 'Regular Cleaning',
            'NORMAL CLEANING' => 'Normal Cleaning',
            'BED SHAMPOO' => 'Bed Shampoo',
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

        // Get dynamic data
        $services = Service::where('is_active', true)->get();
        $sources = LeadSource::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        // Define headers matching your Excel format
        $headers = [
            'A1' => ['Date *', 'Format: DD/MM/YYYY'],
            'B1' => ['Name of the Company *', 'Branch name (e.g., BAYLEAF, CTREE)'],
            'C1' => ['Customer Name *', 'Full name of customer'],
            'D1' => ['Work Description', 'Description of service'],
            'E1' => ['Package Amount', 'Total cost'],
            'F1' => ['Final Amount *', 'Final amount'],
            'G1' => ['Paid (Yes/No)', 'Payment status'],
            'H1' => ['Telecaller Name', 'Name of assigned telecaller'],
            'I1' => ['Office Number *', 'Phone with country code (e.g., +919876543210)'],
            'J1' => ['Supervisor Name', 'Supervisor (optional)'],
            'K1' => ['Contact Number', 'Alternative phone'],
            'L1' => ['Payment Mode', 'Cash, UPI, Card, etc.'],
            'M1' => ['Address', 'Full address'],
            'N1' => ['District *', 'Kerala district name'],
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
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(18);
        $sheet->getColumnDimension('L')->setWidth(15);
        $sheet->getColumnDimension('M')->setWidth(35);
        $sheet->getColumnDimension('N')->setWidth(20);

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
        $sheet->setCellValue('J2', 'SURESH');
        $sheet->setCellValue('K2', '9876543211');
        $sheet->setCellValue('L2', 'GPAY');
        $sheet->setCellValue('M2', 'KALLEPULLY');
        $sheet->setCellValue('N2', 'PALAKKAD');

        $exampleStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0F2FE'],
            ],
        ];
        $sheet->getStyle('A2:N2')->applyFromArray($exampleStyle);

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
                            'supervisor_name' => $this->getColumnValue($rowArray, $columnMapping, 'supervisor_name'),
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

                    // Check duplicates
                    if (Lead::where('phone', $phone)->exists()) {
                        throw new \Exception("Phone {$phone} already exists in leads");
                    }

                    if (Customer::where('phone', $phone)->exists()) {
                        throw new \Exception("Phone {$phone} already exists in customers");
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
                        $dbServiceName = $serviceMapping[$workDescription] ?? $row['work_description'];

                        $service = Service::whereRaw('LOWER(name) = ?', [strtolower($dbServiceName)])
                            ->where('is_active', true)
                            ->first();

                        if (!$service) {
                            $service = Service::where('is_active', true)
                                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($dbServiceName) . '%'])
                                ->first();
                        }

                        if ($service) {
                            $serviceIds[] = $service->id;
                            $serviceType = $this->detectServiceType($service->name);
                        }
                    }

                    if (empty($serviceIds)) {
                        throw new \Exception("Service is required. Work description '{$row['work_description']}' not found in services");
                    }

                    // Find telecaller (nullable)
                    $assignedTo = null;
                    if (!empty($row['telecaller_name'])) {
                        $telecaller = User::where('role', 'telecallers')
                            ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($row['telecaller_name']) . '%'])
                            ->where('is_active', true)
                            ->first();
                        $assignedTo = $telecaller ? $telecaller->id : null;
                    }

                    // Amount (nullable)
                    $finalAmount = !empty($row['final_amount']) ? floatval($row['final_amount']) : 0;

                    // Calculate advance paid (nullable)
                    $advancePaid = 0;
                    if ($finalAmount > 0) {
                        $paidStatus = strtoupper(trim($row['paid']));
                        if (in_array($paidStatus, ['RECEIVED', 'YES', 'PAID'])) {
                            $advancePaid = $finalAmount;
                        }
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

                    // Create lead
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
                        'amount_updated_at' => $finalAmount > 0 ? now() : null,
                        'amount_updated_by' => $finalAmount > 0 ? auth()->id() : null,
                        'status' => 'pending',
                        'branch_id' => $branch->id,
                        'created_by' => auth()->id(),
                        'assigned_to' => $assignedTo,
                        'description' => !empty($row['work_description']) ? $row['work_description'] : null,
                    ]);

                    if (!empty($serviceIds)) {
                        $lead->services()->sync($serviceIds);
                    }

                    $successfulRows++;
                    $processedRows++;

                    Log::info("✓ Imported: {$lead->lead_code} - {$lead->name} - Source: {$leadSource->name} [{$rowIdentifier}]");

                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $rowIdentifier,
                        'data' => ($row['customer_name'] ?? 'Unknown') . ' (' . ($row['company'] ?? 'No company') . ')',
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
                ? "Successfully imported {$successfulRows} leads!" . ($failedRows > 0 ? " {$failedRows} rows failed." : "")
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

    // Add these helper methods to the controller

    /**
     * Map Excel column headers to internal field names
     */
    private function mapColumns($headerArray)
    {
        $mapping = [];

        // Define header patterns and their corresponding field names
        $patterns = [
            'date' => ['date', 'created at', 'created date'],
            'company' => ['name of the company', 'company', 'branch'],
            'customer_name' => ['customer name', 'name', 'client name'],
            'work_description' => ['work description', 'service', 'work'],
            'package_amount' => ['package amount', 'package'],
            'final_amount' => ['final amount', 'amount', 'total'],
            'paid' => ['paid', 'payment status', 'paid (yes/no)'],
            'telecaller_name' => ['telecaller name', 'telecaller', 'assigned to'],
            'office_number' => ['office number', 'office phone'],
            'supervisor_name' => ['supervisor name', 'supervisor'],
            'contact_number' => ['contact number', 'phone', 'mobile', 'contact'],
            'payment_mode' => ['payment mode', 'payment method', 'mode'],
            'address' => ['address', 'location'],
            'district' => ['district', 'city'],
            'source' => ['source', 'lead source'],
        ];

        foreach ($headerArray as $index => $header) {
            $header = strtolower(trim($header));

            if (empty($header)) {
                continue;
            }

            foreach ($patterns as $fieldName => $headerPatterns) {
                foreach ($headerPatterns as $pattern) {
                    if (stripos($header, $pattern) !== false || stripos($pattern, $header) !== false) {
                        $mapping[$fieldName] = $index;
                        break 2; // Break both loops once matched
                    }
                }
            }
        }

        return $mapping;
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
