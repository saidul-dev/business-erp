<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the authenticated user's Current Branch for this request.
 *
 * - 1 assigned branch  -> auto-selected, no picker shown.
 * - >1 assigned branches -> must have a valid selection in session, otherwise
 *   redirected to the branch picker (see ERP Architecture Guideline v1.0).
 * - 0 assigned branches  -> Admin/Super Admin default to "All Branches" (null);
 *   anyone else simply has no branch context yet (nothing to enforce until
 *   a branch-aware module needs one).
 */
class SetCurrentBranch
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($request->routeIs('branches.select', 'branches.switch', 'logout')) {
            return $next($request);
        }

        $assignedBranchIds = $user->branches()->pluck('branches.id');

        if ($assignedBranchIds->count() === 1) {
            $onlyBranchId = $assignedBranchIds->first();

            if ($user->current_branch_id !== $onlyBranchId) {
                $user->update(['current_branch_id' => $onlyBranchId]);
            }

            session(['current_branch_id' => $onlyBranchId]);
        } elseif ($assignedBranchIds->count() > 1) {
            $current = session('current_branch_id', $user->current_branch_id);

            if (! $current || ! $assignedBranchIds->contains($current)) {
                return redirect()->route('branches.select');
            }

            session(['current_branch_id' => $current]);
        } elseif ($user->seesAllBranches()) {
            session(['current_branch_id' => session('current_branch_id', $user->current_branch_id)]);
        }

        return $next($request);
    }
}
