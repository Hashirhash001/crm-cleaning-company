<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Customer;
use League\Csv\Reader;
use App\Models\Service;
use App\Models\LeadImport;
use App\Models\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class LeadBulkImportController extends Controller
{
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

        // Main Sheet - Data Entry
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Lead Data');

        // Get dynamic data
        $services = Service::where('is_active', true)->get();
        $sources = LeadSource::where('is_active', true)->get();

        // Define headers with descriptions
        $headers = [
            'A1' => ['Name *', 'Full name of the lead'],
            'B1' => ['Email', 'Email address (optional)'],
            'C1' => ['Phone *', 'Primary phone (10 digits)'],
            'D1' => ['Alternative Phone', 'Secondary phone (10 digits, optional)'],
            'E1' => ['Address', 'Full address (optional)'],
            'F1' => ['District', 'District/City name (optional)'],
            'G1' => ['Property Type', 'Select from dropdown'],
            'H1' => ['SQFT', 'Area in square feet (optional)'],
            'I1' => ['Service Type', 'Select from dropdown'],
            'J1' => ['Services', 'Select from dropdown (multiple)'],
            'K1' => ['Source', 'Select from dropdown'],
            'L1' => ['Description', 'Additional notes (optional)'],
            'M1' => ['Amount', 'Total service cost (optional)'],
            'N1' => ['Advance Paid', 'Advance payment (optional)'],
            'O1' => ['Payment Mode', 'Select from dropdown'],
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

        // Set headers
        foreach ($headers as $cell => $data) {
            $sheet->setCellValue($cell, $data[0]);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
            $sheet->getComment($cell)->getText()->createTextRun($data[1]);
            $sheet->getComment($cell)->setWidth('200pt');
            $sheet->getComment($cell)->setHeight('50pt');
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(25);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setWidth(35);
        $sheet->getColumnDimension('M')->setWidth(15);
        $sheet->getColumnDimension('N')->setWidth(15);
        $sheet->getColumnDimension('O')->setWidth(18);

        // Freeze header row
        $sheet->freezePane('A2');

        // Add example row
        $sheet->setCellValue('A2', 'John Doe');
        $sheet->setCellValue('B2', 'john@example.com');
        $sheet->setCellValue('C2', '9876543210');
        $sheet->setCellValue('D2', '9876543211');
        $sheet->setCellValue('E2', '123 Main Street, Suburb Name');
        $sheet->setCellValue('F2', 'Mumbai');
        $sheet->setCellValue('G2', 'Residential');
        $sheet->setCellValue('H2', '1200');
        $sheet->setCellValue('I2', 'Commercial');
        $sheet->setCellValue('J2', $services->first()->name ?? 'Deep Cleaning');
        $sheet->setCellValue('K2', $sources->first()->name ?? 'Website');
        $sheet->setCellValue('L2', 'Customer wants deep cleaning service for their office');
        $sheet->setCellValue('M2', '15000');
        $sheet->setCellValue('N2', '5000');
        $sheet->setCellValue('O2', 'Cash');

        // Style example row
        $exampleStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0F2FE'],
            ],
        ];
        $sheet->getStyle('A2:O2')->applyFromArray($exampleStyle);

        // Create Reference Sheet for Dropdown Values
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Reference Data');

        // Property Types
        $propertyTypes = ['Residential', 'Commercial', 'Industrial'];
        $refSheet->setCellValue('A1', 'Property Types');
        foreach ($propertyTypes as $index => $type) {
            $refSheet->setCellValue('A' . ($index + 2), $type);
        }

        // Service Types
        $serviceTypes = ['Commercial', 'Residential'];
        $refSheet->setCellValue('B1', 'Service Types');
        foreach ($serviceTypes as $index => $type) {
            $refSheet->setCellValue('B' . ($index + 2), $type);
        }

        // Services List
        $refSheet->setCellValue('C1', 'Services');
        foreach ($services as $index => $service) {
            $refSheet->setCellValue('C' . ($index + 2), $service->name);
        }

        // Sources List
        $refSheet->setCellValue('D1', 'Sources');
        foreach ($sources as $index => $source) {
            $refSheet->setCellValue('D' . ($index + 2), $source->name);
        }

        // Payment Modes
        $paymentModes = ['Cash', 'Card', 'UPI', 'Bank Transfer'];
        $refSheet->setCellValue('E1', 'Payment Modes');
        foreach ($paymentModes as $index => $mode) {
            $refSheet->setCellValue('E' . ($index + 2), $mode);
        }

        // Style reference headers
        $refSheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        $refSheet->getColumnDimension('A')->setWidth(20);
        $refSheet->getColumnDimension('B')->setWidth(20);
        $refSheet->getColumnDimension('C')->setWidth(30);
        $refSheet->getColumnDimension('D')->setWidth(30);
        $refSheet->getColumnDimension('E')->setWidth(20);

        // Back to main sheet for data validation
        $sheet = $spreadsheet->setActiveSheetIndex(0);

        // Add Data Validation (Dropdowns) for 100 rows
        for ($row = 2; $row <= 101; $row++) {
            // Property Type Dropdown (Column G)
            $validation = $sheet->getCell('G' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Invalid Property Type');
            $validation->setError('Please select from the dropdown list');
            $validation->setPromptTitle('Property Type');
            $validation->setPrompt('Select: Residential, Commercial, or Industrial');
            $validation->setFormula1('"' . implode(',', $propertyTypes) . '"');

            // Service Type Dropdown (Column I)
            $validation = $sheet->getCell('I' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Invalid Service Type');
            $validation->setError('Please select from the dropdown list');
            $validation->setPromptTitle('Service Type');
            $validation->setPrompt('Select: Commercial or Residential');
            $validation->setFormula1('"' . implode(',', $serviceTypes) . '"');

            // Services Dropdown (Column J)
            $validation = $sheet->getCell('J' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Invalid Service');
            $validation->setError('Please select from the dropdown list');
            $validation->setPromptTitle('Services');
            $validation->setPrompt('Select service name from dropdown');
            $validation->setFormula1('\'Reference Data\'!$C$2:$C$' . (count($services) + 1));

            // Source Dropdown (Column K)
            $validation = $sheet->getCell('K' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Invalid Source');
            $validation->setError('Please select from the dropdown list');
            $validation->setPromptTitle('Lead Source');
            $validation->setPrompt('Select where this lead came from');
            $validation->setFormula1('\'Reference Data\'!$D$2:$D$' . (count($sources) + 1));

            // Payment Mode Dropdown (Column O)
            $validation = $sheet->getCell('O' . $row)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Invalid Payment Mode');
            $validation->setError('Please select from the dropdown list');
            $validation->setPromptTitle('Payment Mode');
            $validation->setPrompt('Select: Cash, Card, UPI, or Bank Transfer');
            $validation->setFormula1('"' . implode(',', $paymentModes) . '"');
        }

        // Add Instructions Sheet
        $instructionSheet = $spreadsheet->createSheet();
        $instructionSheet->setTitle('Instructions');

        $instructions = [
            ['LEAD IMPORT INSTRUCTIONS', ''],
            ['', ''],
            ['Step 1:', 'Fill in the "Lead Data" sheet with your lead information'],
            ['Step 2:', 'Use the dropdowns provided for fields like Property Type, Service Type, etc.'],
            ['Step 3:', 'Required fields are marked with * (asterisk)'],
            ['Step 4:', 'See the "Reference Data" sheet for all available dropdown values'],
            ['Step 5:', 'Save the file and upload it to the bulk import page'],
            ['', ''],
            ['FIELD DESCRIPTIONS:', ''],
            ['', ''],
            ['Name *', 'Required. Full name of the lead/customer'],
            ['Email', 'Optional. Valid email address'],
            ['Phone *', 'Required. 10-digit phone number without country code'],
            ['Alternative Phone', 'Optional. 10-digit secondary phone number'],
            ['Address', 'Optional. Complete address'],
            ['District', 'Optional. District or city name'],
            ['Property Type', 'Optional. Select from dropdown: Residential, Commercial, or Industrial'],
            ['SQFT', 'Optional. Area in square feet (numbers only)'],
            ['Service Type', 'Optional. Select from dropdown: Commercial or Residential'],
            ['Services', 'Optional. Select service name from dropdown'],
            ['Source', 'Optional. Select lead source from dropdown'],
            ['Description', 'Optional. Additional notes or requirements'],
            ['Amount', 'Optional. Total service cost (numbers only)'],
            ['Advance Paid', 'Optional. Advance payment amount (numbers only)'],
            ['Payment Mode', 'Optional. Select from dropdown: Cash, Card, UPI, or Bank Transfer'],
            ['', ''],
            ['IMPORTANT NOTES:', ''],
            ['', ''],
            ['• Phone numbers must be exactly 10 digits', ''],
            ['• Do not delete the example row (row 2) until you understand the format', ''],
            ['• You can add up to 100 leads in one file', ''],
            ['• Use the dropdowns instead of typing values manually', ''],
            ['• If a row has errors, it will be skipped and reported', ''],
            ['• All changes are saved in a database transaction for safety', ''],
        ];

        foreach ($instructions as $index => $row) {
            $instructionSheet->setCellValue('A' . ($index + 1), $row[0]);
            $instructionSheet->setCellValue('B' . ($index + 1), $row[1]);
        }

        // Style instruction sheet
        $instructionSheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '2563eb'],
            ],
        ]);

        $instructionSheet->getStyle('A9')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '059669'],
            ],
        ]);

        $instructionSheet->getStyle('A27')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'dc2626'],
            ],
        ]);

        $instructionSheet->getColumnDimension('A')->setWidth(25);
        $instructionSheet->getColumnDimension('B')->setWidth(60);

        // Set active sheet back to Lead Data
        $spreadsheet->setActiveSheetIndex(0);

        // Create Excel file
        $writer = new Xlsx($spreadsheet);

        // Set headers for download
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

        // Create import record
        $import = LeadImport::create([
            'user_id' => auth()->id(),
            'filename' => $request->file('csv_file')->getClientOriginalName(),
            'status' => 'processing',
        ]);

        $file = $request->file('csv_file');
        $extension = $file->getClientOriginalExtension();

        $records = [];

        // Handle both CSV and Excel files
        if (in_array($extension, ['xlsx', 'xls'])) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getSheetByName('Lead Data') ?? $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            // Get headers from first row
            $headers = [];
            $headerRow = $worksheet->rangeToArray('A1:O1')[0];
            foreach ($headerRow as $index => $header) {
                $cleanHeader = strtolower(trim(str_replace('*', '', $header)));
                $cleanHeader = str_replace(' ', '_', $cleanHeader);
                $headers[$index] = $cleanHeader;
            }

            // Read data rows
            for ($row = 2; $row <= $highestRow; $row++) {
                $rawRowData = [];

                foreach ($headers as $colIndex => $headerName) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                    $cell = $worksheet->getCell($colLetter . $row);

                    // Special handling for phone numbers
                    if (in_array($headerName, ['phone', 'alternative_phone'])) {
                        try {
                            $cellValue = $cell->getValue();

                            if (is_numeric($cellValue)) {
                                $phoneNumber = sprintf('%.0f', $cellValue);
                                $rawRowData[$headerName] = $phoneNumber;
                            } else {
                                $rawRowData[$headerName] = preg_replace('/[^0-9]/', '', $cellValue);
                            }
                        } catch (\Exception $e) {
                            $rawRowData[$headerName] = '';
                        }
                    } else {
                        $rawRowData[$headerName] = trim($cell->getFormattedValue());
                    }
                }

                // Skip empty rows
                $hasData = false;
                foreach ($rawRowData as $val) {
                    if (!empty(trim($val))) {
                        $hasData = true;
                        break;
                    }
                }

                if (!$hasData) {
                    continue;
                }

                // Skip example row
                if (!empty($rawRowData['name']) && !empty($rawRowData['email'])) {
                    $nameLower = strtolower(trim($rawRowData['name']));
                    $emailLower = strtolower(trim($rawRowData['email']));

                    if ($nameLower === 'john doe' && $emailLower === 'john@example.com') {
                        continue;
                    }
                }

                if (!empty($rawRowData['name'])) {
                    $records[] = $rawRowData;
                }
            }
        } else {
            // CSV handling
            $csvContent = file_get_contents($file->getRealPath());
            $csv = Reader::createFromString($csvContent);
            $csv->setHeaderOffset(0);
            $csvRecords = iterator_to_array($csv->getRecords());

            foreach ($csvRecords as $row) {
                if (empty($row['name'])) continue;

                $nameLower = strtolower(trim($row['name']));
                $emailLower = !empty($row['email']) ? strtolower(trim($row['email'])) : '';

                if ($nameLower === 'john doe' && $emailLower === 'john@example.com') {
                    continue;
                }

                $records[] = $row;
            }
        }

        $totalRows = count($records);

        if ($totalRows === 0) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'No valid data rows found. Please add leads below the header row.',
            ], 400);
        }

        $import->update(['total_rows' => $totalRows]);

        $processedRows = 0;
        $successfulRows = 0;
        $failedRows = 0;
        $errors = [];

        foreach ($records as $index => $row) {
            try {
                $rowNumber = $index + 2;

                // Normalize keys
                $row = array_combine(
                    array_map(fn($k) => strtolower(str_replace([' ', '*'], ['_', ''], trim($k))), array_keys($row)),
                    $row
                );

                // Clean phone numbers
                if (!empty($row['phone'])) {
                    $row['phone'] = preg_replace('/[^0-9]/', '', $row['phone']);
                    if (strlen($row['phone']) > 10) {
                        $row['phone'] = substr($row['phone'], -10);
                    }
                }

                if (!empty($row['alternative_phone'])) {
                    $row['alternative_phone'] = preg_replace('/[^0-9]/', '', $row['alternative_phone']);
                    if (strlen($row['alternative_phone']) > 10) {
                        $row['alternative_phone'] = substr($row['alternative_phone'], -10);
                    }
                }

                // Validate
                $validator = Validator::make($row, [
                    'name' => 'required|string|max:255',
                    'email' => 'nullable|email|max:255',
                    'phone' => 'required|digits:10',
                    'alternative_phone' => 'nullable|digits:10',
                    'property_type' => 'nullable|in:Residential,Commercial,Industrial,residential,commercial,industrial',
                    'sqft' => 'nullable|numeric',
                    'service_type' => 'nullable|in:Commercial,Residential,commercial,residential,cleaning,pest_control',
                    'services' => 'nullable|string',
                    'source' => 'nullable|string',
                    'amount' => 'nullable|numeric|min:0',
                    'advance_paid' => 'nullable|numeric|min:0',
                    'payment_mode' => 'nullable|in:Cash,Card,UPI,Bank Transfer,cash,card,upi,bank_transfer,neft',
                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'data' => $row['name'],
                        'errors' => $validator->errors()->all(),
                    ];
                    $failedRows++;
                    $processedRows++;
                    continue;
                }

                // Check duplicates - phone
                if (!empty($row['phone'])) {
                    $existingLead = Lead::where('phone', $row['phone'])->first();
                    if ($existingLead) {
                        $errors[] = [
                            'row' => $rowNumber,
                            'data' => $row['name'],
                            'errors' => ["Duplicate phone: {$row['phone']} (Lead: {$existingLead->lead_code})"],
                        ];
                        $failedRows++;
                        $processedRows++;
                        continue;
                    }

                    $existingCustomer = Customer::where('phone', $row['phone'])->first();
                    if ($existingCustomer) {
                        $errors[] = [
                            'row' => $rowNumber,
                            'data' => $row['name'],
                            'errors' => ["Phone {$row['phone']} belongs to customer: {$existingCustomer->customer_code}"],
                        ];
                        $failedRows++;
                        $processedRows++;
                        continue;
                    }
                }

                // Check duplicates - email
                if (!empty($row['email'])) {
                    if (Lead::where('email', $row['email'])->exists()) {
                        $errors[] = [
                            'row' => $rowNumber,
                            'data' => $row['name'],
                            'errors' => ["Duplicate email: {$row['email']}"],
                        ];
                        $failedRows++;
                        $processedRows++;
                        continue;
                    }

                    if (Customer::where('email', $row['email'])->exists()) {
                        $errors[] = [
                            'row' => $rowNumber,
                            'data' => $row['name'],
                            'errors' => ["Email {$row['email']} belongs to existing customer"],
                        ];
                        $failedRows++;
                        $processedRows++;
                        continue;
                    }
                }

                // Find service by name or code (case-insensitive)
                $serviceId = null;
                if (!empty($row['services'])) {
                    $serviceName = trim($row['services']);

                    $service = Service::where('is_active', true)
                        ->where(function($q) use ($serviceName) {
                            $q->whereRaw('LOWER(name) = ?', [strtolower($serviceName)])
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($serviceName) . '%']);
                        })
                        ->first();

                    if ($service) {
                        $serviceId = $service->id;
                    } else {
                        $errors[] = [
                            'row' => $rowNumber,
                            'data' => $row['name'],
                            'errors' => ["Service '{$serviceName}' not found. Available services in Reference Data sheet."],
                        ];
                        $failedRows++;
                        $processedRows++;
                        continue;
                    }
                }

                // Find lead source by name OR code (case-insensitive) - THIS IS THE KEY FIX
                $leadSourceId = null;
                if (!empty($row['source'])) {
                    $sourceName = trim($row['source']);

                    // Try to match by name first, then by code
                    $leadSource = LeadSource::where('is_active', true)
                        ->where(function($q) use ($sourceName) {
                            $q->whereRaw('LOWER(name) = ?', [strtolower($sourceName)])
                              ->orWhereRaw('LOWER(code) = ?', [strtolower($sourceName)])
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($sourceName) . '%']);
                        })
                        ->first();

                    if ($leadSource) {
                        $leadSourceId = $leadSource->id;
                    } else {
                        $errors[] = [
                            'row' => $rowNumber,
                            'data' => $row['name'],
                            'errors' => ["Source '{$sourceName}' not found. Please use exact name from Reference Data sheet (e.g., 'Website', 'Google Ads', 'WhatsApp')."],
                        ];
                        $failedRows++;
                        $processedRows++;
                        continue;
                    }
                } else {
                    // Source is required!
                    $errors[] = [
                        'row' => $rowNumber,
                        'data' => $row['name'],
                        'errors' => ["Source is required. Please select from dropdown in column K."],
                    ];
                    $failedRows++;
                    $processedRows++;
                    continue;
                }

                // Normalize values
                $propertyType = !empty($row['property_type']) ? strtolower($row['property_type']) : null;

                // Map service type values
                $serviceType = null;
                if (!empty($row['service_type'])) {
                    $serviceTypeInput = strtolower(trim($row['service_type']));
                    // Map "Commercial" or "Residential" to "cleaning" or "pest_control"
                    if (in_array($serviceTypeInput, ['commercial', 'residential'])) {
                        $serviceType = 'cleaning'; // Default to cleaning
                    } elseif (in_array($serviceTypeInput, ['cleaning', 'pest_control'])) {
                        $serviceType = $serviceTypeInput;
                    }
                }

                $paymentMode = !empty($row['payment_mode']) ? strtolower(str_replace(' ', '_', $row['payment_mode'])) : null;

                // Create lead
                $leadData = [
                    'name' => $row['name'],
                    'email' => $row['email'] ?? null,
                    'phone' => $row['phone'],
                    'phone_alternative' => $row['alternative_phone'] ?? null,
                    'address' => $row['address'] ?? null,
                    'district' => $row['district'] ?? null,
                    'property_type' => $propertyType,
                    'sqft' => $row['sqft'] ?? null,
                    'service_type' => $serviceType,
                    'lead_source_id' => $leadSourceId, // Required
                    'service_id' => $serviceId, // Optional
                    'description' => $row['description'] ?? null,
                    'amount' => $row['amount'] ?? null,
                    'advance_paid_amount' => $row['advance_paid'] ?? null,
                    'payment_mode' => $paymentMode,
                    'status' => 'pending',
                    'branch_id' => auth()->user()->branch_id,
                    'created_by' => auth()->id(),
                    'assigned_to' => auth()->id(),
                ];

                $lead = Lead::create($leadData);

                // Attach services to pivot table if service_id is set
                if ($serviceId) {
                    $lead->services()->sync([$serviceId]);
                }

                $successfulRows++;
                $processedRows++;

                Log::info("✓ Imported: {$lead->lead_code} - {$lead->name}");

                $import->update([
                    'processed_rows' => $processedRows,
                    'successful_rows' => $successfulRows,
                    'failed_rows' => $failedRows,
                ]);

            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();

                // Simplify common errors
                if (str_contains($errorMsg, "lead_source_id")) {
                    $errorMsg = "Source is required and must be selected from dropdown.";
                } elseif (str_contains($errorMsg, "Duplicate entry")) {
                    if (str_contains($errorMsg, "phone")) {
                        $errorMsg = "Duplicate phone number detected.";
                    } elseif (str_contains($errorMsg, "email")) {
                        $errorMsg = "Duplicate email detected.";
                    } else {
                        $errorMsg = "Duplicate entry detected.";
                    }
                } else {
                    $errorMsg = substr($errorMsg, 0, 200);
                }

                $errors[] = [
                    'row' => $rowNumber,
                    'data' => $row['name'] ?? 'Unknown',
                    'errors' => [$errorMsg],
                ];
                $failedRows++;
                $processedRows++;

                Log::error("Row {$rowNumber} error:", [
                    'error' => $e->getMessage(),
                    'data' => $row
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

        if ($successfulRows === 0) {
            return response()->json([
                'success' => false,
                'message' => "Import failed! All {$failedRows} rows had errors.",
                'import_id' => $import->id,
                'stats' => [
                    'total' => $totalRows,
                    'processed' => $processedRows,
                    'successful' => $successfulRows,
                    'failed' => $failedRows,
                ],
                'errors' => $errors,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully imported {$successfulRows} leads!" . ($failedRows > 0 ? " {$failedRows} rows failed." : ""),
            'import_id' => $import->id,
            'stats' => [
                'total' => $totalRows,
                'processed' => $processedRows,
                'successful' => $successfulRows,
                'failed' => $failedRows,
            ],
            'errors' => $errors,
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
