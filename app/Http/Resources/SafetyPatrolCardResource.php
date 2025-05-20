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
            ?->status
            ?->value ?? 'pending';

        // Jika status pending_feedback_approval, cek feedback approvals
        if ($this->status === \App\Enums\SafetyPatrolStatus::PENDING_FEEDBACK_APPROVAL) {
            $approvalStatus = $this->feedbacks
                ?->last()
                ?->approvals
                ?->firstWhere('approver_id', $userId)
                ?->status
                ?->value ?? 'pending';
        }

        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'location' => $this->location,
            'description' => $this->description,
            'status' => $this->status->value,
            'created_at' => $this->created_at->toDateTimeString(),
            'user_approval_status' => $approvalStatus,
        ];
    }
}
