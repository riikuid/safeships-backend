<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     tags={"Notifications"},
     *     summary="Get list of user's notifications",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="is_read",
     *         in="query",
     *         description="Filter notifications by read status (true for read, false for unread)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notifikasi berhasil diambil"),
     *             @OA\Property(
     *                 property="notifications",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="type", type="string", example="document_uploaded"),
     *                     @OA\Property(property="title", type="string", example="Dokumen Baru Menunggu Persetujuan"),
     *                     @OA\Property(property="message", type="string", example="Dokumen 'Penggunaan APAR' diunggah oleh John Doe."),
     *                     @OA\Property(property="reference_type", type="string", example="document"),
     *                     @OA\Property(property="reference_id", type="integer", example=1),
     *                     @OA\Property(property="is_read", type="boolean", example=false),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil notifikasi"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Notification::where('user_id', $user->id);

            $notifications = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'message' => 'Notifikasi berhasil diambil',
                'notifications' => $notifications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil notifikasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
