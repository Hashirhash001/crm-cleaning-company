<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadSubmittedNotification extends Notification
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
            'created_by' => $this->lead->createdBy->name,
            'message' => "New lead '{$this->lead->name}' submitted for approval",
            'type' => 'lead_pending_approval',
            'url' => route('leads.show', $this->lead->id),
        ];
    }
}
