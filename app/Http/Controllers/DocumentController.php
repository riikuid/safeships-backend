<?php
// app/Http/Controllers/DocumentController.php
namespace App\Http\Controllers;

use App\Helpers\FcmHelper;
use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentUpdateRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Exception;


/**
 * @OA\Tag(
 *     name="Documents",
 *     description="API Endpoints for managing documents"
 * )
 */
class DocumentController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/documents/categories",
     *     tags={"Documents"},
     *     summary="Get list of all categories",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kategori berhasil diambil"),
     *             @OA\Property(
     *                 property="categories",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Penggunaan APAR")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil kategori"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function getAllCategories(Request $request)
    {
        try {
            $categories = Category::get();

            return response()->json([
                'message' => 'Kategori berhasil diambil',
                'categories' => $categories,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil kategori',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/documents",
     *     tags={"Documents"},
     *     summary="Get list of documents",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter documents by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Documents retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumen berhasil diambil"),
     *             @OA\Property(property="documents", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil dokumen"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Document::with(['user', 'category', 'documentApprovals.approver']);

            // Super admin dan manager bisa melihat semua dokumen
            if (!in_array($user->role, ['super_admin', 'manager'])) {
                // Role lain hanya bisa melihat dokumen approved
                $query->where('status', 'approved');
            }

            // Filter berdasarkan category_id jika ada
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $documents = $query->get();

            return response()->json([
                'message' => 'Dokumen berhasil diambil',
                'documents' => $documents,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/documents",
     *     tags={"Documents"},
     *     summary="Upload a new document",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="manager_id", type="integer", example=2),
     *                 @OA\Property(property="title", type="string", example="Penggunaan APAR"),
     *                 @OA\Property(property="description", type="string", example="Dokumen pelatihan"),
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Document uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumen berhasil diunggah, menunggu persetujuan"),
     *             @OA\Property(property="document", type="object")
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
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:categories,id',
                'manager_id' => 'required|exists:users,id,role,manager',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'required|file|mimes:pdf,mp4|max:10240', // Maks 10MB
            ]);

            $filePath = $request->file('file')->store('documents', 'public');

            $document = Document::create([
                'user_id' => Auth::id(),
                'category_id' => $request->category_id,
                'manager_id' => $request->manager_id,
                'file_path' => $filePath,
                'title' => $request->title,
                'description' => $request->description,
                'status' => 'pending_super_admin',
                'version' => 1,
            ]);


            // Buat entri persetujuan untuk super admin
            $superAdmins = User::where('role', 'super_admin')->get();
            foreach ($superAdmins as $admin) {
                DocumentApproval::create([
                    'document_id' => $document->id,
                    'approver_id' => $admin->id,
                    'status' => 'pending',
                ]);

                // Buat notifikasi untuk super admin
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Dokumen Baru Menunggu Persetujuan',
                    'message' => "Dokumen '{$document->title}' diunggah oleh " . Auth::user()->name . ".",
                    'reference_type' => 'document',
                    'reference_id' => $document->id,
                ]);

                // TODO: Kirim FCM ke $admin->fcm_token
                FcmHelper::send(
                    $admin->fcm_token,
                    'Dokumen Baru Menunggu Persetujuan',
                    "Dokumen '{$document->title}' diunggah oleh " . Auth::user()->name . ".",
                    [
                        'reference_type' => 'document',
                        'reference_id' => (string) $document->id,
                    ]
                );
            }

            Notification::create([
                'user_id' => Auth::id(),
                'title' => 'Berhasil Unggah Dokumen Baru, Menunggu Persetujuan',
                'message' => "Dokumen '{$document->title}' berhasil diunggah. Menunggu persetujuan admin",
                'reference_type' => 'document',
                'reference_id' => $document->id,
            ]);

            return response()->json([
                'message' => 'Dokumen berhasil diunggah, menunggu persetujuan',
                'document' => $document,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengunggah dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/documents/{id}/detail",
     *     tags={"Documents"},
     *     summary="Get a specific document",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumen berhasil diambil"),
     *             @OA\Property(property="document", type="object")
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
     *         description="Document not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumen tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $document = Document::with(['user', 'category', 'documentApprovals.approver'])
                ->findOrFail($id);

            $user = Auth::user();
            // Super_admin dan manager bisa melihat semua dokumen
            // User atau non_user bisa melihat dokumen mereka sendiri
            // Role lain hanya bisa melihat dokumen approved
            if (
                !in_array($user->role, ['super_admin', 'manager']) &&
                $document->user_id !== $user->id &&
                $document->status !== 'approved'
            ) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json([
                'message' => 'Dokumen berhasil diambil',
                'document' => $document,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dokumen tidak ditemukan',
                'error' => 'Document not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/documents/{id}/approve",
     *     tags={"Documents"},
     *     summary="Approve a document",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumen berhasil disetujui")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid document status or role",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
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
     *         description="Document or approval not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumen atau persetujuan tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function approve(Request $request, $id)
    {
        try {
            $document = Document::findOrFail($id);
            $uploader = User::find($document->user_id);
            $user = Auth::user();

            // Validasi role dan status dokumen
            if ($user->role === 'super_admin' && $document->status !== 'pending_super_admin') {
                return response()->json(['message' => 'Dokumen tidak dalam status pending_super_admin'], 400);
            }
            if ($user->role === 'manager' && ($document->status !== 'pending_manager' || $document->manager_id !== $user->id)) {
                return response()->json(['message' => 'Dokumen tidak dalam status pending_manager atau Anda bukan manajer'], 400);
            }

            $approval = DocumentApproval::where('document_id', $id)
                ->where('approver_id', $user->id)
                ->firstOrFail();

            $approval->update(['status' => 'approved']);


            if ($user->role === 'super_admin') {
                // Cek apakah kedua super admin sudah menyetujui
                $superAdminApprovals = DocumentApproval::where('document_id', $id)
                    ->whereIn('approver_id', User::where('role', 'super_admin')->pluck('id'))
                    ->get();


                if ($superAdminApprovals->every(fn($a) => $a->status === 'approved')) {
                    $document->update(['status' => 'pending_manager']);
                    DocumentApproval::create([
                        'document_id' => $id,
                        'approver_id' => $document->manager_id,
                        'status' => 'pending',
                    ]);

                    // NOTIF KE MANAJER
                    Notification::create([
                        'user_id' => $document->manager_id,
                        'title' => 'Dokumen Menunggu Persetujuan Anda',
                        'message' => "Dokumen '{$document->title}' diunggah oleh " . Auth::user()->name . " telah disetujui super admin. Menunggu persetujuan anda sebagai manajer",
                        'reference_type' => 'document',
                        'reference_id' => $document->id,
                    ]);
                    $manager = User::find($document->manager_id);
                    FcmHelper::send(
                        $manager->fcm_token,
                        'Dokumen Baru Menunggu Persetujuan',
                        "Dokumen '{$document->title}' diunggah oleh " . Auth::user()->name . " telah disetujui super admin. Menunggu persetujuan anda sebagai manajer",
                        [
                            'reference_type' => 'document',
                            'reference_id' => (string) $document->id,
                        ]
                    );

                    // NOTIF KE USER
                    Notification::create([
                        'user_id' => $document->user_id,
                        'title' => 'Dokumen Disetujui oleh Super Admin',
                        'message' => "Dokumen '{$document->title}' disetujui super admin. Menunggu persetujuan manajer",
                        'reference_type' => 'document',
                        'reference_id' => $document->id,
                    ]);
                    FcmHelper::send(
                        $uploader->fcm_token,
                        'Dokumen Baru Menunggu Persetujuan',
                        "Dokumen '{$document->title}' diunggah oleh " . Auth::user()->name . " telah disetujui super admin. Menunggu persetujuan anda sebagai manajer",
                        [
                            'reference_type' => 'document',
                            'reference_id' => (string) $document->id,
                        ]
                    );
                }
            } elseif ($user->role === 'manager') {
                $document->update(['status' => 'approved']);
                Notification::create([
                    'user_id' => $document->user_id,
                    'title' => 'Dokumen Disetujui',
                    'message' => "Dokumen '{$document->title}' telah disetujui dan dipublikasikan.",
                    'reference_type' => 'document',
                    'reference_id' => $document->id,
                ]);
                // TODO: Kirim FCM ke user
                FcmHelper::send(
                    $uploader->fcm_token,
                    'Dokumen Disetujui',
                    "Dokumen '{$document->title}' telah disetujui dan dipublikasikan.",
                    [
                        'reference_type' => 'document',
                        'reference_id' => (string) $document->id,
                    ]
                );
            }

            return response()->json(['message' => 'Dokumen berhasil disetujui']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dokumen atau persetujuan tidak ditemukan',
                'error' => 'Resource not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menyetujui dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/documents/{id}/reject",
     *     tags={"Documents"},
     *     summary="Reject a document",
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
     *             @OA\Property(property="comments", type="string", example="Dokumen tidak sesuai")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document rejected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumen berhasil ditolak")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid document status or role",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
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
     *         response=404,
     *         description="Document or approval not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumen atau persetujuan tidak ditemukan")
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

            $document = Document::findOrFail($id);
            $user = Auth::user();

            // Validasi role dan status dokumen
            if ($user->role === 'super_admin' && $document->status !== 'pending_super_admin') {
                return response()->json(['message' => 'Dokumen tidak dalam status pending_super_admin'], 400);
            }
            if ($user->role === 'manager' && ($document->status !== 'pending_manager' || $document->manager_id !== $user->id)) {
                return response()->json(['message' => 'Dokumen tidak dalam status pending_manager atau Anda bukan manajer'], 400);
            }

            $approval = DocumentApproval::where('document_id', $id)
                ->where('approver_id', $user->id)
                ->firstOrFail();

            $approval->update([
                'status' => 'rejected',
                'comments' => $request->comments,
            ]);

            $document->update(['status' => 'rejected']);
            Notification::create([
                'user_id' => $document->user_id,
                'title' => 'Dokumen Ditolak',
                'message' => "Dokumen '$document->title' ditolak oleh $user->name. Alasan: $request->comments.",
                'reference_type' => 'document',
                'reference_id' => $document->id,
            ]);
            $uploader = User::find($document->user_id);
            FcmHelper::send(
                $uploader->fcm_token,
                'Dokumen Ditolak',
                "Dokumen '$document->title' ditolak oleh $user->name. Alasan: $request->comments.",
                [
                    'reference_type' => 'document',
                    'reference_id' => (string) $document->id,
                ]
            );

            return response()->json(['message' => 'Dokumen berhasil ditolak']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dokumen atau persetujuan tidak ditemukan',
                'error' => 'Resource not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menolak dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/documents/{id}/request-update",
     *     tags={"Documents"},
     *     summary="Request an update for a document",
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
     *             @OA\Property(property="comments", type="string", example="Perbarui konten"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Update request sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permintaan pembaruan berhasil dikirim")
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
     *             @OA\Property(property="message", type="string", example="Validasi gagal"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Document not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumen tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function requestUpdate(Request $request, $id)
    {
        try {
            $request->validate([
                'comments' => 'required|string',
            ]);

            $document = Document::findOrFail($id);
            $user = Auth::user();

            if ($user->role !== 'manager' || $document->manager_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            DocumentUpdateRequest::create([
                'document_id' => $document->id,
                'manager_id' => $user->id,
                'comments' => $request->comments,
            ]);

            Notification::create([
                'user_id' => $document->user_id,
                'title' => 'Pembaruan Dokumen Diperlukan',
                'message' => "Manajer meminta pembaruan untuk '{$document->title}'. Alasan: {$request->comments}.",
                'reference_type' => 'document',
                'reference_id' => $document->id,
            ]);

            $uploader = User::find($document->user_id);
            FcmHelper::send(
                $uploader->fcm_token,
                'Pembaruan Dokumen Diperlukan',
                "Manajer meminta pembaruan untuk '{$document->title}'. Alasan: {$request->comments}.",
                [
                    'reference_type' => 'document',
                    'reference_id' => (string) $document->id,
                ]
            );

            return response()->json(['message' => 'Permintaan pembaruan berhasil dikirim']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dokumen tidak ditemukan',
                'error' => 'Document not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim permintaan pembaruan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/documents/my-submissions",
     *     tags={"Documents"},
     *     summary="Get list of documents submitted by the authenticated user",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter documents by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending_super_admin", "pending_manager", "approved", "rejected"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Submissions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pengajuan dokumen berhasil diambil"),
     *             @OA\Property(
     *                 property="submissions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Penggunaan APAR"),
     *                     @OA\Property(property="status", type="string", example="pending_super_admin"),
     *                     @OA\Property(property="category", type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string")),
     *                     @OA\Property(
     *                         property="document_approvals",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="approver_id", type="integer"),
     *                             @OA\Property(property="status", type="string", example="pending"),
     *                             @OA\Property(property="comments", type="string", example="Dokumen perlu revisi", nullable=true),
     *                             @OA\Property(property="approver", type="object", @OA\Property(property="name", type="string"))
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil pengajuan dokumen"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function mySubmissions(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Document::with(['category', 'documentApprovals.approver'])
                ->where('user_id', $user->id);

            // dd($user);
            // Filter berdasarkan status jika ada
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $submissions = $query->get();

            return response()->json([
                'message' => 'Pengajuan dokumen berhasil diambil',
                'submissions' => $submissions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil pengajuan dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
