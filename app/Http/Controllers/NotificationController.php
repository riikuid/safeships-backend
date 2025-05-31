<?php

namespace App\Http\Controllers;

use App\Helpers\FcmHelper;
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
                'data' => $notifications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil notifikasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/test",
     *     summary="Tes pengiriman notifikasi FCM ke diri sendiri (user login)",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "body"},
     *             @OA\Property(property="title", type="string", example="Tes Notifikasi"),
     *             @OA\Property(property="body", type="string", example="Ini adalah pesan notifikasi uji coba."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={"custom_key": "custom_value"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifikasi berhasil dikirim",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tes notifikasi berhasil dikirim")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Tidak terautentikasi",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Terjadi kesalahan server",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengirim notifikasi"),
     *             @OA\Property(property="error", type="string", example="Some error detail")
     *         )
     *     )
     * )
     */
    public function test(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string',
                'body' => 'required|string',
                'data' => 'nullable|array',
            ]);

            $user = Auth::user();

            if (!$user->fcm_token) {
                return response()->json([
                    'message' => 'User tidak memiliki FCM token',
                ], 422);
            }

            FcmHelper::send(
                $user->fcm_token,
                $request->title,
                $request->body,
                $request->data ?? []
            );



            return response()->json([
                'message' => 'Tes notifikasi berhasil dikirim',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim notifikasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
