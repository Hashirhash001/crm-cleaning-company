<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BranchResolver
{
    /**
     * Resolve the branch model and ID from the incoming request.
     *
     * Priority order:
     * 1. Explicit branch/branch_id from payload or route parameter
     * 2. Domain-based match (branches.domain column vs Origin/Referer hostname)
     * 3. Legacy keyword fallback (stripos on Origin/Referer)
     * 4. Neutral fallback (first active branch — no brand bias)
     *
     * @param Request $request
     * @param int|string|null $routeBranchId
     * @return array
     */
    public function resolve(Request $request, $routeBranchId = null): array
    {
        $origin = $request->header('origin') ?? '';
        $referer = $request->header('referer') ?? '';
        $host = $request->header('host') ?? '';

        $incomingBranch = $request->input('branch') ?? $request->input('branch_id') ?? $routeBranchId;
        $resolvedBranch = null;
        $resolutionMethod = 'none';

        // ── 1. Try explicit payload / route branch parameter ──────────────
        if ($incomingBranch !== null && $incomingBranch !== '') {
            if (is_numeric($incomingBranch)) {
                $resolvedBranch = Branch::where('id', (int) $incomingBranch)
                    ->where('is_active', true)
                    ->first();
                if ($resolvedBranch) {
                    $resolutionMethod = 'explicit_id';
                }
            }

            if (!$resolvedBranch && is_string($incomingBranch)) {
                $cleanBranch = strtolower(trim($incomingBranch));
                $resolvedBranch = Branch::where('is_active', true)
                    ->where(function ($query) use ($cleanBranch) {
                        $query->whereRaw('LOWER(code) = ?', [$cleanBranch])
                              ->orWhereRaw('LOWER(name) = ?', [$cleanBranch])
                              ->orWhereRaw('LOWER(name) LIKE ?', [$cleanBranch . '%']);
                    })->first();

                if ($resolvedBranch) {
                    $resolutionMethod = 'explicit_code_or_name';
                }
            }
        }

        // ── 2. Domain-based matching (authoritative) ──────────────────────
        //    Extract hostname from Referer or Origin, match against branches.domain
        if (!$resolvedBranch) {
            $headerSource = $referer ?: $origin;
            $extractedDomain = $this->extractDomain($headerSource);

            if ($extractedDomain) {
                $resolvedBranch = Branch::where('is_active', true)
                    ->whereNotNull('domain')
                    ->where('domain', '!=', '')
                    ->whereRaw('LOWER(domain) = ?', [strtolower($extractedDomain)])
                    ->first();

                if ($resolvedBranch) {
                    $resolutionMethod = 'domain_match';

                    Log::info('BranchResolver: domain matched', [
                        'extracted_domain' => $extractedDomain,
                        'matched_branch'   => $resolvedBranch->name,
                        'branch_id'        => $resolvedBranch->id,
                    ]);
                }
            }
        }

        // ── 3. Legacy keyword fallback (secondary safety net) ─────────────
        if (!$resolvedBranch) {
            $headerSource = $referer ?: $origin;

            if ($headerSource) {
                if (stripos($headerSource, 'ctree.co.in') !== false || stripos($headerSource, 'ctree') !== false) {
                    $resolvedBranch = $this->findBranchByKeyword('ctree');
                    if ($resolvedBranch) {
                        $resolutionMethod = 'header_ctree';
                    }
                } elseif (stripos($headerSource, 'bayleafclean.com') !== false || stripos($headerSource, 'bayleaf') !== false) {
                    $resolvedBranch = $this->findBranchByKeyword('bayleaf');
                    if ($resolvedBranch) {
                        $resolutionMethod = 'header_bayleaf';
                    }
                }
            }
        }

        // ── 4. Neutral fallback (NO hardcoded brand bias) ─────────────────
        if (!$resolvedBranch) {
            $resolvedBranch = Branch::where('is_active', true)->first();
            $resolutionMethod = 'fallback_default';

            Log::warning('BranchResolver: fell back to default branch — no domain or keyword match', [
                'origin'  => $origin,
                'referer' => $referer,
                'host'    => $host,
                'payload_branch' => $incomingBranch,
            ]);
        }

        return [
            'branch'            => $resolvedBranch,
            'branch_id'         => $resolvedBranch ? $resolvedBranch->id : null,
            'branch_name'       => $resolvedBranch ? $resolvedBranch->name : null,
            'resolution_method' => $resolutionMethod,
            'request_origin'    => $origin,
            'request_referer'   => $referer,
            'request_host'      => $host,
            'incoming_branch'   => $incomingBranch,
        ];
    }

    /**
     * Extract the bare domain (hostname) from a URL string.
     *
     * Examples:
     *   "https://ctree.co.in/contact"           → "ctree.co.in"
     *   "https://www.bayleafclean.com/services"  → "bayleafclean.com"
     *   "http://localhost:8080"                  → "localhost"
     *
     * @param string|null $url
     * @return string|null
     */
    protected function extractDomain(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $parsed = parse_url($url, PHP_URL_HOST);

        if (empty($parsed)) {
            return null;
        }

        // Strip "www." prefix for consistent matching
        $domain = preg_replace('/^www\./i', '', $parsed);

        return strtolower($domain);
    }

    /**
     * Find active branch by keyword in code or name.
     *
     * @param string $keyword
     * @return Branch|null
     */
    protected function findBranchByKeyword(string $keyword): ?Branch
    {
        $keyword = strtolower($keyword);
        return Branch::where('is_active', true)
            ->where(function ($query) use ($keyword) {
                $query->whereRaw('LOWER(code) = ?', [$keyword])
                      ->orWhereRaw('LOWER(name) = ?', [$keyword])
                      ->orWhereRaw('LOWER(name) LIKE ?', [$keyword . '%']);
            })
            ->first();
    }
}

