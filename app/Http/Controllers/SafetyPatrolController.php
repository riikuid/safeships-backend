<?php

namespace App\Http\Controllers;

use App\Helpers\FcmHelper;
use App\Models\SafetyPatrol;
use App\Models\SafetyPatrolApproval;
use App\Models\SafetyPatrolAction;
use App\Models\SafetyPatrolFeedback;
use App\Models\SafetyPatrolFeedbackApproval;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="SafetyPatrols",
 *     description="API Endpoints for managing safety patrol reports"
 * )
 */
class SafetyPatrolController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/safety-patrols",
     *     tags={"SafetyPatrols"},
     *     summary="Get list of safety patrol reports",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending_super_admin", "pending_manager", "pending_action", "action_in_progress", "pending_feedback_approval", "done", "rejected"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reports retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="status", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil laporan")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = SafetyPatrol::with(['user', 'manager', 'approvals.approver', 'action.actor', 'feedbacks'])
                ->withTrashed();

            if ($user->role !== 'super_admin') {
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('manager_id', $user->id)
                        ->orWhereHas('action', function ($q) use ($user) {
                            $q->where('actor_id', $user->id);
                        });
                });
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $patrols = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'message' => 'Laporan berhasil diambil',
                'data' => $patrols,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil laporan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/safety-patrols",
     *     tags={"SafetyPatrols"},
     *     summary="Create a new safety patrol report",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="manager_id", type="integer", example=2),
     *                 @OA\Property(property="report_date", type="string", format="date", example="2025-05-19"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="type", type="string", enum={"condition", "unsafe_action"}, example="condition"),
     *                 @OA\Property(property="description", type="string", example="Peralatan rusak di area produksi"),
     *                 @OA\Property(property="location", type="string", example="Gudang Utama")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Safety patrol report created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan safety patrol berhasil dibuat"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="report_date", type="string"),
     *                 @OA\Property(property="status", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validasi gagal"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal membuat laporan"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'manager_id' => 'required|exists:users,id,role,manager',
                'report_date' => 'required|date',
                'image' => 'required|image|max:5120', // Maks 5MB
                'type' => 'required|in:condition,unsafe_action',
                'description' => 'required|string',
                'location' => 'required|string|max:255',
            ]);

            $image = $request->file('image');
            $timestamp = now()->format('YmdHis');
            $imageName = $timestamp . '_' . Str::slug($request->location) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('safety_patrols', $imageName, 'public');

            $patrol = SafetyPatrol::create([
                'user_id' => Auth::id(),
                'manager_id' => $request->manager_id,
                'report_date' => $request->report_date,
                'image_path' => $imagePath,
                'type' => $request->type,
                'description' => $request->description,
                'location' => $request->location,
                'status' => 'pending_super_admin',
            ]);

            $superAdmins = User::where('role', 'super_admin')->get();
            foreach ($superAdmins as $admin) {
                SafetyPatrolApproval::create([
                    'safety_patrol_id' => $patrol->id,
                    'approver_id' => $admin->id,
                    'status' => 'pending',
                ]);

                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Laporan Safety Patrol Baru',
                    'message' => "Laporan di {$patrol->location} diunggah oleh " . Auth::user()->name . ".",
                    'reference_type' => 'safety_patrol',
                    'reference_id' => $patrol->id,
                ]);

                FcmHelper::send(
                    $admin->fcm_token,
                    'Laporan Safety Patrol Baru',
                    "Laporan di {$patrol->location} diunggah oleh " . Auth::user()->name . ".",
                    [
                        'reference_type' => 'safety_patrol',
                        'reference_id' => (string) $patrol->id,
                    ]
                );
            }

            Notification::create([
                'user_id' => Auth::id(),
                'title' => 'Laporan Safety Patrol Berhasil Dibuat',
                'message' => "Laporan di {$patrol->location} menunggu persetujuan super admin.",
                'reference_type' => 'safety_patrol',
                'reference_id' => $patrol->id,
            ]);

            return response()->json([
                'message' => 'Laporan safety patrol berhasil dibuat',
                'data' => $patrol->load(['user', 'manager']),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat laporan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/safety-patrols/{id}/detail",
     *     tags={"SafetyPatrols"},
     *     summary="Get a specific safety patrol report",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Report retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="status", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $patrol = SafetyPatrol::with(['user', 'manager', 'approvals.approver', 'action.actor', 'feedbacks.approvals.approver'])
                ->withTrashed()
                ->findOrFail($id);

            $user = Auth::user();
            if (
                !in_array($user->role, ['super_admin', 'manager']) &&
                $patrol->user_id !== $user->id &&
                ($patrol->action ? $patrol->action->actor_id !== $user->id : true)
            ) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json([
                'message' => 'Laporan berhasil diambil',
                'data' => $patrol,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Laporan tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil laporan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/safety-patrols/{id}/approve",
     *     tags={"SafetyPatrols"},
     *     summary="Approve a safety patrol report",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="actor_id", type="integer", example=3, description="Required for manager approval to assign an actor"),
     *             @OA\Property(property="deadline", type="string", format="date", example="2025-06-01", description="Required for manager approval")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Report approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan berhasil disetujui")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status or role",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validasi gagal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function approve(Request $request, $id)
    {
        try {
            $patrol = SafetyPatrol::with(['user', 'manager', 'approvals.approver'])->findOrFail($id);
            $user = Auth::user();

            if ($user->role === 'super_admin' && $patrol->status !== 'pending_super_admin') {
                return response()->json(['message' => 'Laporan tidak dalam status pending_super_admin'], 400);
            }
            if ($user->role === 'manager' && ($patrol->status !== 'pending_manager' || $patrol->manager_id !== $user->id)) {
                return response()->json(['message' => 'Laporan tidak dalam status pending_manager atau Anda bukan manajer'], 400);
            }

            if ($user->role === 'manager') {
                $request->validate([
                    'actor_id' => 'required|exists:users,id',
                    'deadline' => 'required|date|after:today',
                ]);
            }

            $approval = SafetyPatrolApproval::where('safety_patrol_id', $id)
                ->where('approver_id', $user->id)
                ->firstOrFail();

            $approval->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            if ($user->role === 'super_admin') {
                $superAdminApprovals = SafetyPatrolApproval::where('safety_patrol_id', $id)
                    ->whereIn('approver_id', User::where('role', 'super_admin')->pluck('id'))
                    ->get();

                if ($superAdminApprovals->every(fn($a) => $a->status === 'approved')) {
                    $patrol->update(['status' => 'pending_manager']);
                    SafetyPatrolApproval::create([
                        'safety_patrol_id' => $id,
                        'approver_id' => $patrol->manager_id,
                        'status' => 'pending',
                    ]);

                    Notification::create([
                        'user_id' => $patrol->manager_id,
                        'title' => 'Laporan Menunggu Persetujuan Anda',
                        'message' => "Laporan di {$patrol->location} telah disetujui super admin.",
                        'reference_type' => 'safety_patrol',
                        'reference_id' => $patrol->id,
                    ]);

                    FcmHelper::send(
                        $patrol->manager->fcm_token,
                        'Laporan Menunggu Persetujuan',
                        "Laporan di {$patrol->location} telah disetujui super admin.",
                        [
                            'reference_type' => 'safety_patrol',
                            'reference_id' => (string) $patrol->id,
                        ]
                    );

                    Notification::create([
                        'user_id' => $patrol->user_id,
                        'title' => 'Laporan Disetujui Super Admin',
                        'message' => "Laporan di {$patrol->location} menunggu persetujuan manajer.",
                        'reference_type' => 'safety_patrol',
                        'reference_id' => $patrol->id,
                    ]);

                    FcmHelper::send(
                        $patrol->user->fcm_token,
                        'Laporan Disetujui Super Admin',
                        "Laporan di {$patrol->location} menunggu persetujuan manajer.",
                        [
                            'reference_type' => 'safety_patrol',
                            'reference_id' => (string) $patrol->id,
                        ]
                    );
                }
            } elseif ($user->role === 'manager') {
                $patrol->update(['status' => 'pending_action']);
                SafetyPatrolAction::create([
                    'safety_patrol_id' => $patrol->id,
                    'actor_id' => $request->actor_id,
                    'deadline' => $request->deadline,
                ]);

                $actor = User::find($request->actor_id);
                Notification::create([
                    'user_id' => $actor->id,
                    'title' => 'Ditunjuk sebagai Penindak',
                    'message' => "Anda ditunjuk untuk menindak laporan di {$patrol->location}. Deadline: {$request->deadline}.",
                    'reference_type' => 'safety_patrol',
                    'reference_id' => $patrol->id,
                ]);

                FcmHelper::send(
                    $actor->fcm_token,
                    'Ditunjuk sebagai Penindak',
                    "Anda ditunjuk untuk menindak laporan di {$patrol->location}. Deadline: {$request->deadline}.",
                    [
                        'reference_type' => 'safety_patrol',
                        'reference_id' => (string) $patrol->id,
                    ]
                );

                Notification::create([
                    'user_id' => $patrol->user_id,
                    'title' => 'Laporan Disetujui Manajer',
                    'message' => "Laporan di {$patrol->location} telah disetujui. Penindak ditunjuk.",
                    'reference_type' => 'safety_patrol',
                    'reference_id' => $patrol->id,
                ]);

                FcmHelper::send(
                    $patrol->user->fcm_token,
                    'Laporan Disetujui Manajer',
                    "Laporan di {$patrol->location} telah disetujui. Penindak ditunjuk.",
                    [
                        'reference_type' => 'safety_patrol',
                        'reference_id' => (string) $patrol->id,
                    ]
                );
            }

            return response()->json([
                'message' => 'Laporan berhasil disetujui',
                'data' => $patrol->fresh(['user', 'manager', 'approvals.approver', 'action.actor']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Laporan atau persetujuan tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menyetujui laporan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/safety-patrols/{id}/reject",
     *     tags={"SafetyPatrols"},
     *     summary="Reject a safety patrol report",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="comments", type="string", example="Laporan tidak lengkap")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Report rejected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan berhasil ditolak")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status or role",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validasi gagal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function reject(Request $request, $id)
    {
        try {
            $request->validate([
                'comments' => 'required|string',
            ]);

            $patrol = SafetyPatrol::with(['user', 'manager'])->findOrFail($id);
            $user = Auth::user();

            if ($user->role === 'super_admin' && $patrol->status !== 'pending_super_admin') {
                return response()->json(['message' => 'Laporan tidak dalam status pending_super_admin'], 400);
            }
            if ($user->role === 'manager' && ($patrol->status !== 'pending_manager' || $patrol->manager_id !== $user->id)) {
                return response()->json(['message' => 'Laporan tidak dalam status pending_manager atau Anda bukan manajer'], 400);
            }

            $approval = SafetyPatrolApproval::where('safety_patrol_id', $id)
                ->where('approver_id', $user->id)
                ->firstOrFail();

            $approval->update([
                'status' => 'rejected',
                'comments' => $request->comments,
            ]);

            $patrol->update(['status' => 'rejected']);

            Notification::create([
                'user_id' => $patrol->user_id,
                'title' => 'Laporan Ditolak',
                'message' => "Laporan di {$patrol->location} ditolak oleh {$user->name}. Alasan: {$request->comments}.",
                'reference_type' => 'safety_patrol',
                'reference_id' => $patrol->id,
            ]);

            FcmHelper::send(
                $patrol->user->fcm_token,
                'Laporan Ditolak',
                "Laporan di {$patrol->location} ditolak oleh {$user->name}. Alasan: {$request->comments}.",
                [
                    'reference_type' => 'safety_patrol',
                    'reference_id' => (string) $patrol->id,
                ]
            );

            return response()->json([
                'message' => 'Laporan berhasil ditolak',
                'data' => $patrol->fresh(['user', 'manager', 'approvals.approver']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Laporan atau persetujuan tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menolak laporan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/safety-patrols/{id}/submit-feedback",
     *     tags={"SafetyPatrols"},
     *     summary="Submit feedback for a safety patrol report",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="feedback_date", type="string", format="date", example="2025-05-25"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="description", type="string", example="Peralatan telah diperbaiki")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Feedback submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Feedback berhasil diunggah")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validasi gagal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function submitFeedback(Request $request, $id)
    {
        try {
            $request->validate([
                'feedback_date' => 'required|date',
                'image' => 'required|image|max:5120',
                'description' => 'required|string',
            ]);

            $patrol = SafetyPatrol::with(['action'])->findOrFail($id);
            $user = Auth::user();

            if ($patrol->status !== 'pending_action' || $patrol->action->actor_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $image = $request->file('image');
            $timestamp = now()->format('YmdHis');
            $imageName = $timestamp . '_' . Str::slug($patrol->location) . '_feedback.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('safety_patrol_feedbacks', $imageName, 'public');

            $feedback = SafetyPatrolFeedback::create([
                'safety_patrol_id' => $patrol->id,
                'actor_id' => $user->id,
                'feedback_date' => $request->feedback_date,
                'image_path' => $imagePath,
                'description' => $request->description,
                'status' => 'pending',
            ]);

            $patrol->update(['status' => 'pending_feedback_approval']);

            $approvers = User::where('role', 'super_admin')->get()->merge(collect([$patrol->manager]));
            foreach ($approvers as $approver) {
                SafetyPatrolFeedbackApproval::create([
                    'feedback_id' => $feedback->id,
                    'approver_id' => $approver->id,
                    'status' => 'pending',
                ]);

                Notification::create([
                    'user_id' => $approver->id,
                    'title' => 'Feedback Safety Patrol Baru',
                    'message' => "Feedback untuk laporan di {$patrol->location} diunggah oleh {$user->name}.",
                    'reference_type' => 'safety_patrol_feedback',
                    'reference_id' => $feedback->id,
                ]);

                FcmHelper::send(
                    $approver->fcm_token,
                    'Feedback Safety Patrol Baru',
                    "Feedback untuk laporan di {$patrol->location} diunggah oleh {$user->name}.",
                    [
                        'reference_type' => 'safety_patrol_feedback',
                        'reference_id' => (string) $feedback->id,
                    ]
                );
            }

            Notification::create([
                'user_id' => $patrol->user_id,
                'title' => 'Feedback Laporan Diunggah',
                'message' => "Feedback untuk laporan di {$patrol->location} telah diunggah.",
                'reference_type' => 'safety_patrol',
                'reference_id' => $patrol->id,
            ]);

            FcmHelper::send(
                $patrol->user->fcm_token,
                'Feedback Laporan Diunggah',
                "Feedback untuk laporan di {$patrol->location} telah diunggah.",
                [
                    'reference_type' => 'safety_patrol',
                    'reference_id' => (string) $patrol->id,
                ]
            );

            return response()->json([
                'message' => 'Feedback berhasil diunggah',
                'data' => $feedback->load(['safetyPatrol', 'actor']),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Laporan tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengunggah feedback',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/safety-patrols/feedback/{id}/approve",
     *     tags={"SafetyPatrols"},
     *     summary="Approve a safety patrol feedback",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Feedback approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Feedback berhasil disetujui")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status or role",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Feedback not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Feedback tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function approveFeedback(Request $request, $id)
    {
        try {
            $feedback = SafetyPatrolFeedback::with(['safetyPatrol', 'approvals.approver'])->findOrFail($id);
            $patrol = $feedback->safetyPatrol;
            $user = Auth::user();

            if ($patrol->status !== 'pending_feedback_approval' || ($user->role !== 'super_admin' && $user->id !== $patrol->manager_id)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $approval = SafetyPatrolFeedbackApproval::where('feedback_id', $id)
                ->where('approver_id', $user->id)
                ->firstOrFail();

            $approval->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            $approvals = SafetyPatrolFeedbackApproval::where('feedback_id', $id)->get();
            if ($approvals->every(fn($a) => $a->status === 'approved')) {
                $feedback->update(['status' => 'approved']);
                $patrol->update(['status' => 'done']);

                Notification::create([
                    'user_id' => $patrol->user_id,
                    'title' => 'Laporan Selesai',
                    'message' => "Laporan di {$patrol->location} telah diselesaikan dan disetujui.",
                    'reference_type' => 'safety_patrol',
                    'reference_id' => $patrol->id,
                ]);

                FcmHelper::send(
                    $patrol->user->fcm_token,
                    'Laporan Selesai',
                    "Laporan di {$patrol->location} telah diselesaikan dan disetujui.",
                    [
                        'reference_type' => 'safety_patrol',
                        'reference_id' => (string) $patrol->id,
                    ]
                );

                Notification::create([
                    'user_id' => $feedback->actor_id,
                    'title' => 'Feedback Disetujui',
                    'message' => "Feedback Anda untuk laporan di {$patrol->location} telah disetujui.",
                    'reference_type' => 'safety_patrol_feedback',
                    'reference_id' => $feedback->id,
                ]);

                FcmHelper::send(
                    $feedback->actor->fcm_token,
                    'Feedback Disetujui',
                    "Feedback Anda untuk laporan di {$patrol->location} telah disetujui.",
                    [
                        'reference_type' => 'safety_patrol_feedback',
                        'reference_id' => (string) $feedback->id,
                    ]
                );
            }

            return response()->json([
                'message' => 'Feedback berhasil disetujui',
                'data' => $feedback->fresh(['safetyPatrol', 'approvals.approver']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Feedback atau persetujuan tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menyetujui feedback',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/safety-patrols/feedback/{id}/reject",
     *     tags={"SafetyPatrols"},
     *     summary="Reject a safety patrol feedback",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="comments", type="string", example="Feedback tidak memadai")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Feedback rejected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Feedback berhasil ditolak")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status or role",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validasi gagal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Feedback not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Feedback tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function rejectFeedback(Request $request, $id)
    {
        try {
            $request->validate([
                'comments' => 'required|string',
            ]);

            $feedback = SafetyPatrolFeedback::with(['safetyPatrol'])->findOrFail($id);
            $patrol = $feedback->safetyPatrol;
            $user = Auth::user();

            if ($patrol->status !== 'pending_feedback_approval' || ($user->role !== 'super_admin' && $user->id !== $patrol->manager_id)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $approval = SafetyPatrolFeedbackApproval::where('feedback_id', $id)
                ->where('approver_id', $user->id)
                ->firstOrFail();

            $approval->update([
                'status' => 'rejected',
                'comments' => $request->comments,
            ]);

            $feedback->update(['status' => 'rejected']);
            $patrol->update(['status' => 'pending_action']);

            Notification::create([
                'user_id' => $feedback->actor_id,
                'title' => 'Feedback Ditolak',
                'message' => "Feedback untuk laporan di {$patrol->location} ditolak. Alasan: {$request->comments}. Silakan unggah feedback baru.",
                'reference_type' => 'safety_patrol_feedback',
                'reference_id' => $feedback->id,
            ]);

            FcmHelper::send(
                $feedback->actor->fcm_token,
                'Feedback Ditolak',
                "Feedback untuk laporan di {$patrol->location} ditolak. Alasan: {$request->comments}. Silakan unggah feedback baru.",
                [
                    'reference_type' => 'safety_patrol_feedback',
                    'reference_id' => (string) $feedback->id,
                ]
            );

            Notification::create([
                'user_id' => $patrol->user_id,
                'title' => 'Feedback Laporan Ditolak',
                'message' => "Feedback untuk laporan di {$patrol->location} ditolak. Menunggu feedback baru.",
                'reference_type' => 'safety_patrol',
                'reference_id' => $patrol->id,
            ]);

            FcmHelper::send(
                $patrol->user->fcm_token,
                'Feedback Laporan Ditolak',
                "Feedback untuk laporan di {$patrol->location} ditolak. Menunggu feedback baru.",
                [
                    'reference_type' => 'safety_patrol',
                    'reference_id' => (string) $patrol->id,
                ]
            );

            return response()->json([
                'message' => 'Feedback berhasil ditolak',
                'data' => $feedback->fresh(['safetyPatrol', 'approvals.approver']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Feedback atau persetujuan tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menolak feedback',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/safety-patrols/my-submissions",
     *     tags={"SafetyPatrols"},
     *     summary="Get user's safety patrol submissions",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending_super_admin", "pending_manager", "pending_action", "action_in_progress", "pending_feedback_approval", "done", "rejected"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Submissions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="status", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil laporan")
     *         )
     *     )
     * )
     */
    public function mySubmissions(Request $request)
    {
        try {
            $user = Auth::user();
            $query = SafetyPatrol::with(['manager', 'approvals.approver', 'action.actor', 'feedbacks'])
                ->withTrashed()
                ->where('user_id', $user->id);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $patrols = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'message' => 'Laporan berhasil diambil',
                'data' => $patrols,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil laporan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/safety-patrols/managerial",
     *     tags={"SafetyPatrols"},
     *     summary="Get safety patrol reports for managerial roles",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending_super_admin", "pending_manager", "pending_action", "action_in_progress", "pending_feedback_approval", "done", "rejected"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reports retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="status", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil laporan")
     *         )
     *     )
     * )
     */
    public function managerial(Request $request)
    {
        try {
            $user = Auth::user();

            if (!in_array($user->role, ['super_admin', 'manager'])) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $query = SafetyPatrol::with(['user', 'manager', 'approvals.approver', 'action.actor', 'feedbacks.approvals.approver'])
                ->withTrashed();

            if ($user->role === 'manager') {
                $query->whereHas('approvals', function ($q) use ($user) {
                    $q->where('approver_id', $user->id);
                })->orWhere('manager_id', $user->id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $patrols = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'message' => 'Laporan berhasil diambil',
                'data' => $patrols,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil laporan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/safety-patrols/my-actions",
     *     tags={"SafetyPatrols"},
     *     summary="Get safety patrol tasks assigned to the user as an actor",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending_action", "action_in_progress", "pending_feedback_approval", "done", "rejected"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tasks retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tugas berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="deadline", type="string", format="date")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil tugas")
     *         )
     *     )
     * )
     */
    public function myActions(Request $request)
    {
        try {
            $user = Auth::user();
            $query = SafetyPatrol::with(['user', 'manager', 'action.actor', 'feedbacks'])
                ->whereHas('action', function ($q) use ($user) {
                    $q->where('actor_id', $user->id);
                });

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $patrols = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'message' => 'Tugas berhasil diambil',
                'data' => $patrols,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil tugas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/safety-patrols/{id}",
     *     tags={"SafetyPatrols"},
     *     summary="Delete a safety patrol report",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Report deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan berhasil dihapus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Laporan tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $patrol = SafetyPatrol::withTrashed()->findOrFail($id);
            $user = Auth::user();

            if ($user->role !== 'super_admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            if ($patrol->image_path && Storage::disk('public')->exists($patrol->image_path)) {
                Storage::disk('public')->delete($patrol->image_path);
            }

            foreach ($patrol->feedbacks as $feedback) {
                if ($feedback->image_path && Storage::disk('public')->exists($feedback->image_path)) {
                    Storage::disk('public')->delete($feedback->image_path);
                }
            }

            $patrol->status = 'rejected';
            $patrol->save();
            $patrol->delete();

            SafetyPatrolApproval::where('safety_patrol_id', $patrol->id)
                ->where('approver_id', $user->id)
                ->update(['status' => 'rejected']);

            Notification::create([
                'user_id' => $patrol->user_id,
                'title' => 'Laporan Dihapus',
                'message' => "Laporan di {$patrol->location} telah dihapus oleh super admin.",
                'reference_type' => 'safety_patrol',
                'reference_id' => $patrol->id,
            ]);

            FcmHelper::send(
                $patrol->user->fcm_token,
                'Laporan Dihapus',
                "Laporan di {$patrol->location} telah dihapus oleh super admin.",
                [
                    'reference_type' => 'safety_patrol',
                    'reference_id' => (string) $patrol->id,
                ]
            );

            return response()->json([
                'message' => 'Laporan berhasil dihapus',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Laporan tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus laporan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
