<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SafetyPatrolCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $userId = auth()->id();
        $approvalStatus = $this->approvals
            ?->firstWhere('approver_id', $userId)
            ?->status ?? 'pending';

        // Jika status pending_feedback_approval, cek feedback approvals
        if ($this->status === 'pending_feedback_approval') {
            $approvalStatus = $this->feedbacks
                ?->last()
                ?->approvals
                ?->firstWhere('approver_id', $userId)
                ?->status ?? 'pending';
        }

        return [
            'id' => $this->id,
            'type' => $this->type,
            'location' => $this->location,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'user_approval_status' => $approvalStatus,
        ];
    }
}
