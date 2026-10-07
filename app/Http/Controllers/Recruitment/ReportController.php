<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentDepartment;
use App\Models\RecruitmentPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin,lead_manager,telecallers');
    }

    /**
     * Display recruitment reports.
     */
    public function index(Request $request)
    {
        // Base query with filters
        $query = RecruitmentCandidate::query();

        // Apply filters
        if ($request->filled('department_id')) {
            $query->where('recruitment_department_id', $request->department_id);
        }
        if ($request->filled('position_id')) {
            $query->where('recruitment_position_id', $request->position_id);
        }
        if ($request->filled('interview_status')) {
            $query->where('interview_status', $request->interview_status);
        }
        if ($request->filled('interview_result')) {
            $query->where('interview_result', $request->interview_result);
        }
        if ($request->filled('joining_status')) {
            $query->where('joining_status', $request->joining_status);
        }
        if ($request->filled('reference')) {
            $query->where('reference', $request->reference);
        }
        if ($request->filled('decision_reason')) {
            $query->where('decision_reason', $request->decision_reason);
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // ── Candidate Summary ──────────────────────────────────
        $summary = [
            'total'      => (clone $query)->count(),
            'selected'   => (clone $query)->where('interview_result', 'Selected')->count(),
            'rejected'   => (clone $query)->where('interview_result', 'Rejected')->count(),
            'hold'       => (clone $query)->where('interview_result', 'Hold')->count(),
            'joined'     => (clone $query)->where('joining_status', 'Joined')->count(),
            'pending'    => (clone $query)->where('joining_status', 'Pending')->count(),
            'not_joined' => (clone $query)->where('joining_status', 'Not Joined')->count(),
        ];

        // ── Department-wise Report ─────────────────────────────
        $departmentReport = RecruitmentDepartment::select('recruitment_departments.id', 'recruitment_departments.name')
            ->withCount(['candidates as total_candidates' => function ($q) use ($request) {
                $this->applyFiltersToRelation($q, $request);
            }])
            ->withCount(['candidates as selected_count' => function ($q) use ($request) {
                $this->applyFiltersToRelation($q, $request);
                $q->where('interview_result', 'Selected');
            }])
            ->withCount(['candidates as joined_count' => function ($q) use ($request) {
                $this->applyFiltersToRelation($q, $request);
                $q->where('joining_status', 'Joined');
            }])
            ->having('total_candidates', '>', 0)
            ->orderBy('total_candidates', 'desc')
            ->get();

        // ── Position-wise Report ───────────────────────────────
        $positionReport = RecruitmentPosition::select('recruitment_positions.id', 'recruitment_positions.name', 'recruitment_positions.recruitment_department_id')
            ->with('department:id,name')
            ->withCount(['candidates as total_candidates' => function ($q) use ($request) {
                $this->applyFiltersToRelation($q, $request);
            }])
            ->withCount(['candidates as selected_count' => function ($q) use ($request) {
                $this->applyFiltersToRelation($q, $request);
                $q->where('interview_result', 'Selected');
            }])
            ->withCount(['candidates as joined_count' => function ($q) use ($request) {
                $this->applyFiltersToRelation($q, $request);
                $q->where('joining_status', 'Joined');
            }])
            ->having('total_candidates', '>', 0)
            ->orderBy('total_candidates', 'desc')
            ->get();

        // ── Interview Status Report ────────────────────────────
        $interviewStatusReport = DB::table('recruitment_candidates')
            ->select('interview_status', DB::raw('COUNT(*) as count'))
            ->whereNull('deleted_at');
        $this->applyRawFilters($interviewStatusReport, $request);
        $interviewStatusReport = $interviewStatusReport->groupBy('interview_status')
            ->orderByDesc('count')
            ->get();

        // ── Joining Status Report ──────────────────────────────
        $joiningStatusReport = DB::table('recruitment_candidates')
            ->select('joining_status', DB::raw('COUNT(*) as count'))
            ->whereNull('deleted_at');
        $this->applyRawFilters($joiningStatusReport, $request);
        $joiningStatusReport = $joiningStatusReport->groupBy('joining_status')
            ->orderByDesc('count')
            ->get();

        // ── Reference Source Report ────────────────────────────
        $referenceReport = DB::table('recruitment_candidates')
            ->select('reference', DB::raw('COUNT(*) as count'))
            ->whereNull('deleted_at')
            ->whereNotNull('reference');
        $this->applyRawFilters($referenceReport, $request);
        $referenceReport = $referenceReport->groupBy('reference')
            ->orderByDesc('count')
            ->get();

        // Dropdown data for filters
        $departments = RecruitmentDepartment::active()->orderBy('name')->get();
        $positions = RecruitmentPosition::active()->orderBy('name')->get();

        return view('recruitment.reports.index', compact(
            'summary', 'departmentReport', 'positionReport',
            'interviewStatusReport', 'joiningStatusReport', 'referenceReport',
            'departments', 'positions'
        ));
    }

    /**
     * Export report data to .xlsx
     */
    public function export(Request $request)
    {
        try {
            $query = RecruitmentCandidate::with(['position', 'department', 'interviewer']);

            // Apply same filters
            if ($request->filled('department_id')) {
                $query->where('recruitment_department_id', $request->department_id);
            }
            if ($request->filled('position_id')) {
                $query->where('recruitment_position_id', $request->position_id);
            }
            if ($request->filled('interview_status')) {
                $query->where('interview_status', $request->interview_status);
            }
            if ($request->filled('interview_result')) {
                $query->where('interview_result', $request->interview_result);
            }
            if ($request->filled('joining_status')) {
                $query->where('joining_status', $request->joining_status);
            }
            if ($request->filled('reference')) {
                $query->where('reference', $request->reference);
            }
            if ($request->filled('decision_reason')) {
                $query->where('decision_reason', $request->decision_reason);
            }
            if ($request->filled('date_from')) {
                $query->where('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
            }

            $candidates = $query->orderBy('created_at', 'desc')->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Recruitment Report');

            $headers = [
                'SL No', 'Candidate Name', 'Phone', 'Email', 'Position', 'Department',
                'Interview Date', 'Interview Status', 'Interview Result',
                'Joining Status', 'Expected Joining Date', 'Decision Reason', 'Reference'
            ];

            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            $lastCol = chr(ord('A') + count($headers) - 1);
            $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            $row = 2;
            foreach ($candidates as $i => $c) {
                $sheet->setCellValue('A' . $row, $i + 1);
                $sheet->setCellValue('B' . $row, $c->full_name);
                $sheet->setCellValue('C' . $row, $c->phone);
                $sheet->setCellValue('D' . $row, $c->email ?? '');
                $sheet->setCellValue('E' . $row, optional($c->position)->name ?? '');
                $sheet->setCellValue('F' . $row, optional($c->department)->name ?? '');
                $sheet->setCellValue('G' . $row, $c->interview_date ? $c->interview_date->format('d-m-Y') : '');
                $sheet->setCellValue('H' . $row, $c->interview_status);
                $sheet->setCellValue('I' . $row, $c->interview_result);
                $sheet->setCellValue('J' . $row, $c->joining_status);
                $sheet->setCellValue('K' . $row, $c->expected_join_date ? $c->expected_join_date->format('d-m-Y') : '');
                $sheet->setCellValue('L' . $row, $c->decision_reason ?? '');
                $sheet->setCellValue('M' . $row, $c->reference ?? '');
                $row++;
            }

            foreach (range('A', $lastCol) as $colLetter) {
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            $fileName = 'recruitment_report_' . now()->format('Y-m-d_His') . '.xlsx';
            $writer = new Xlsx($spreadsheet);

            Log::info('Recruitment report export', [
                'user' => auth()->id(),
                'filters' => $request->all(),
                'count' => $candidates->count(),
            ]);

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);

        } catch (\Exception $e) {
            Log::error('Report export error: ' . $e->getMessage());
            return back()->withErrors(['export' => 'Error exporting report: ' . $e->getMessage()]);
        }
    }

    /**
     * Apply filters to Eloquent relationship subquery.
     */
    private function applyFiltersToRelation($query, Request $request)
    {
        if ($request->filled('date_from')) {
            $query->where('recruitment_candidates.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('recruitment_candidates.created_at', '<=', $request->date_to . ' 23:59:59');
        }
        if ($request->filled('reference')) {
            $query->where('recruitment_candidates.reference', $request->reference);
        }
        if ($request->filled('decision_reason')) {
            $query->where('recruitment_candidates.decision_reason', $request->decision_reason);
        }
    }

    /**
     * Apply filters to raw DB queries.
     */
    private function applyRawFilters($query, Request $request)
    {
        if ($request->filled('department_id')) {
            $query->where('recruitment_department_id', $request->department_id);
        }
        if ($request->filled('position_id')) {
            $query->where('recruitment_position_id', $request->position_id);
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }
        if ($request->filled('reference')) {
            $query->where('reference', $request->reference);
        }
        if ($request->filled('decision_reason')) {
            $query->where('decision_reason', $request->decision_reason);
        }
    }
}
