<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Lead;

class LeadPolicy
{
    public function create(User $user)
    {
        return $user->role === 'lead_manager';
    }

    public function view(User $user, Lead $lead)
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        return $user->id === $lead->created_by;
    }

    public function update(User $user, Lead $lead)
    {
        return $user->role === 'lead_manager' && $user->id === $lead->created_by && $lead->isPending();
    }

    public function delete(User $user, Lead $lead)
    {
        return $user->role === 'lead_manager' && $user->id === $lead->created_by && $lead->isPending();
    }

    public function approve(User $user, Lead $lead)
    {
        return $user->role === 'super_admin' && $lead->isPending();
    }

    public function reject(User $user, Lead $lead)
    {
        return $user->role === 'super_admin' && $lead->isPending();
    }
}
