<?php

namespace App\Http\Resources;

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

        // Determine if the user is an actor to show deadline instead of approval status
        $isActor = $this->action && $this->action->actor_id === $userId;
        $deadline = $isActor ? $this->action->deadline : null;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'location' => $this->location,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            // Use deadline for actors, otherwise user_approval_status for approvers
            $isActor ? 'deadline' : 'user_approval_status' => $isActor ? $deadline : $approvalStatus,
        ];
    }
}
