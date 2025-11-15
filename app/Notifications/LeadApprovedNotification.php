<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public Lead $lead)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->name,
            'approved_by' => $this->lead->approvedBy?->name,
            'message' => "Lead '{$this->lead->name}' has been approved and converted to a job",
            'type' => 'lead_approved',
            'url' => route('leads.show', $this->lead->id),
        ];
    }
}
