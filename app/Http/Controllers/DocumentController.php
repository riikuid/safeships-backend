<?php

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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
     *     summary="Get list of all categories with hierarchy",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kategori berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Pembangunan Dan Pemeliharaan Komitmen"),
     *                     @OA\Property(property="code", type="string", example="1"),
     *                     @OA\Property(
     *                         property="children",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="name", type="string", example="Kebijakan K3"),
     *                             @OA\Property(property="code", type="string", example="1.1"),
     *                             @OA\Property(
     *                                 property="children",
     *                                 type="array",
     *                                 @OA\Items(
     *                                     type="object",
     *                                     @OA\Property(property="id", type="integer", example=3),
     *                                     @OA\Property(property="name", type="string", example="Sub Kebijakan K3 1"),
     *                                     @OA\Property(property="code", type="string", example="1.1.1")
     *                                 )
     *                             )
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
     *             @OA\Property(property="message", type="string", example="Gagal mengambil kategori"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function getAllCategories(Request $request)
    {
        try {
            $categories = Category::with('children.children')
                ->whereNull('parent_id')
                ->get();

            return response()->json([
                'message' => 'Kategori berhasil diambil',
                'data' => $categories,
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
     *         description="Filter documents by category ID (Level 3)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Documents retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumentasi berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(
     *                         property="category",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="code", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil Dokumentasi"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Document::with(['user', 'category'])
                ->where('status', 'approved');

            if ($request->has('category_id')) {
                $category = Category::findOrFail($request->category_id);
                if ($category->children()->exists()) {
                    return response()->json([
                        'message' => 'Kategori harus berada di level terendah (Level 3)',
                    ], 422);
                }
                $query->where('category_id', $request->category_id);
            }

            $documents = $query->get();

            return response()->json([
                'message' => 'Dokumentasi berhasil diambil',
                'data' => $documents,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan',
                'error' => 'Category not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil Dokumentasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/documents/managerial",
     *     tags={"Documents"},
     *     summary="Get list of documents for managerial roles",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter documents by category ID (Level 3)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Documents retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumentasi berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(
     *                         property="category",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="code", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil Dokumentasi"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function documentsManagerial(Request $request)
    {
        try {
            $user = Auth::user();

            // Validasi role pengguna
            if (!in_array($user->role, ['super_admin', 'manager'])) {
                return response()->json([
                    'message' => 'Akses ditolak. Hanya super_admin atau manager yang dapat mengakses.',
                ], 403);
            }

            // Query dasar dengan relasi
            $query = Document::with(['user', 'category', 'documentApprovals.approver'])
                ->withTrashed(); // Sertakan Dokumentasi yang di-soft-delete

            // Filter berdasarkan role
            if ($user->role === 'manager') {
                $query->whereHas('documentApprovals', function ($q) use ($user) {
                    $q->where('approver_id', $user->id);
                });
            }

            // Filter berdasarkan category_id jika ada
            if ($request->has('category_id')) {
                $category = Category::findOrFail($request->category_id);
                if ($category->children()->exists()) {
                    return response()->json([
                        'message' => 'Kategori harus berada di level terendah (Level 3)',
                    ], 422);
                }
                $query->where('category_id', $request->category_id);
            }

            // Ambil Dokumentasi dan urutkan berdasarkan created_at
            $documents = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'message' => 'Dokumentasi berhasil diambil',
                'data' => $documents,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan',
                'error' => 'Category not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil Dokumentasi',
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
     *                 @OA\Property(property="category_id", type="integer", example=3),
     *                 @OA\Property(property="manager_id", type="integer", example=2),
     *                 @OA\Property(property="title", type="string", example="Penggunaan APAR"),
     *                 @OA\Property(property="description", type="string", example="Dokumentasi pelatihan"),
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Document uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumentasi berhasil diunggah, menunggu persetujuan"),
     *             @OA\Property(
     *                 property="document",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(
     *                     property="category",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="code", type="string")
     *                 )
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
                'category_id' => [
                    'required',
                    'exists:categories,id',
                    function ($attribute, $value, $fail) {
                        $category = Category::find($value);
                        if ($category && $category->children()->exists()) {
                            $fail('Kategori harus berada di level terendah (Level 3).');
                        }
                    },
                ],
                'manager_id' => 'required|exists:users,id,role,manager',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'required|file|max:10240', // Maks 10MB
            ]);

            // Dengan kode berikut:
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->format('YmdHis'); // Format: tahun-bulan-tanggal-jam-menit-detik
            $sanitizedTitle = Str::slug($request->title); // Mengubah judul menjadi format URL-friendly
            $fileName = $timestamp . '_' . $sanitizedTitle . '.' . $extension;

            $filePath = $file->storeAs('documents', $fileName, 'public');

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

            $superAdmins = User::where('role', 'super_admin')->get();
            foreach ($superAdmins as $admin) {
                DocumentApproval::create([
                    'document_id' => $document->id,
                    'approver_id' => $admin->id,
                    'status' => 'pending',
                ]);

                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Dokumentasi Baru Menunggu Persetujuan',
                    'message' => "Dokumentasi '{$document->title}' diunggah oleh " . Auth::user()->name . ".",
                    'reference_type' => 'document_approve',
                    'reference_id' => $document->id,
                ]);

                FcmHelper::send(
                    $admin->fcm_token,
                    'Dokumentasi Baru Menunggu Persetujuan',
                    "Dokumentasi '{$document->title}' diunggah oleh " . Auth::user()->name . ".",
                    [
                        'reference_type' => 'document_approve',
                        'reference_id' => (string) $document->id,
                    ]
                );
            }

            return response()->json([
                'message' => 'Dokumentasi berhasil diunggah, menunggu persetujuan',
                'data' => $document->load('category'),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengunggah Dokumentasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get level 3 categories and their documents under a level 2 category.
     *
     * @OA\Get(
     *     path="/api/documents/level3",
     *     operationId="getLevel3CategoriesAndDocuments",
     *     tags={"Documents"},
     *     summary="Get level 3 categories and their documents under a specified level 2 category",
     *     description="Returns a list of level 3 categories under the specified level 2 category, each containing an array of associated documents.",
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="ID of the level 2 category",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Berhasil mendapatkan Dokumentasi pada kategori level 2: Kebijakan K3"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Terdapat kebijakan K3 yang tertulis, bertanggal, ditandatangani oleh pengusaha atau pengurus, secara jelas menyatakan tujuan dan sasaran K3 serta komitmen terhadap peningkatan K3."),
     *                     @OA\Property(property="code", type="string", example="1.1.1"),
     *                     @OA\Property(
     *                         property="items",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="title", type="string", example="Dokumentasi Kebijakan K3 2023"),
     *                             @OA\Property(property="file_path", type="string", example="storage/documents/kebijakan_k3_2023.pdf"),
     *                             @OA\Property(property="description", type="string", example="Dokumentasi resmi kebijakan K3 tahun 2023")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Category is not a level 2 category",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Kategori yang diberikan bukan kategori level 2")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validasi gagal"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="category_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The category_id field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Kategori tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function getLevel3CategoriesAndDocuments(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Ambil kategori level 2
        $category = Category::findOrFail($request->category_id);

        // Pastikan kategori adalah level 2 (memiliki parent_id dan bukan level 3)
        if (!$category->parent_id || Category::where('parent_id', $category->id)->exists()) {
            // Ambil semua kategori level 3 di bawah kategori level 2
            $level3Categories = Category::where('parent_id', $category->id)
                ->with(['documents' => function ($query) {
                    $query->where('status', 'approved')->select('id', 'title', 'category_id', 'file_path', 'description', 'updated_at'); // Sesuaikan kolom
                }])
                ->get()
                ->map(function ($level3Category) {
                    return [
                        'id' => $level3Category->id,
                        'name' => $level3Category->name,
                        'code' => $level3Category->code,
                        'items' => $level3Category->documents->map(function ($document) {
                            return [
                                'id' => $document->id,
                                'title' => $document->title,
                                'file_path' => $document->file_path,
                                'description' => $document->description,
                                'updated_at' => $document->updated_at,
                            ];
                        })->toArray(),
                    ];
                });

            return response()->json([
                'message' => 'Berhasil mendapatkan Dokumentasi pada kategori level 2: ' . $category->name,
                'data' => $level3Categories,
            ], 200);
        }

        return response()->json([
            'message' => 'Kategori yang diberikan bukan kategori level 2',
        ], 400);
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
     *             @OA\Property(property="message", type="string", example="Dokumentasi berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(
     *                     property="category",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="code", type="string")
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
     *         response=404,
     *         description="Document not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumentasi tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $document = Document::with(['user', 'category', 'documentApprovals.approver'])
                ->withTrashed() // Sertakan Dokumentasi yang di-soft-delete
                ->findOrFail($id);

            $user = Auth::user();
            if (
                !in_array($user->role, ['super_admin', 'manager']) &&
                $document->user_id !== $user->id &&
                $document->status !== 'approved'
            ) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json([
                'message' => 'Dokumentasi berhasil diambil',
                'data' => $document,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dokumentasi tidak ditemukan',
                'error' => 'Document not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil Dokumentasi',
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
     *             @OA\Property(property="message", type="string", example="Dokumentasi berhasil disetujui")
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
     *             @OA\Property(property="message", type="string", example="Dokumentasi atau persetujuan tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function approve(Request $request, $id)
    {
        try {
            $document = Document::with(['user', 'category', 'documentApprovals.approver'])->findOrFail($id);
            $uploader = User::find($document->user_id);
            $user = Auth::user();

            if ($user->role === 'super_admin' && $document->status !== 'pending_super_admin') {
                return response()->json(['message' => 'Dokumentasi tidak dalam status pending_super_admin'], 400);
            }
            if ($user->role === 'manager' && ($document->status !== 'pending_manager' || $document->manager_id !== $user->id)) {
                return response()->json(['message' => 'Dokumentasi tidak dalam status pending_manager atau Anda bukan manajer'], 400);
            }

            $approval = DocumentApproval::where('document_id', $id)
                ->where('approver_id', $user->id)
                ->firstOrFail();

            $approval->update(['status' => 'approved']);

            if ($user->role === 'super_admin') {
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

                    Notification::create([
                        'user_id' => $document->manager_id,
                        'title' => 'Dokumentasi Menunggu Persetujuan Anda',
                        'message' => "Dokumentasi '{$document->title}' telah disetujui super admin. Menunggu persetujuan Anda sebagai manajer.",
                        'reference_type' => 'document_approve',
                        'reference_id' => $document->id,
                    ]);
                    $manager = User::find($document->manager_id);
                    FcmHelper::send(
                        $manager->fcm_token,
                        'Dokumentasi Menunggu Persetujuan',
                        "Dokumentasi '{$document->title}' telah disetujui super admin. Menunggu persetujuan Anda sebagai manajer.",
                        [
                            'reference_type' => 'document_approve',
                            'reference_id' => (string) $document->id,
                        ]
                    );

                    Notification::create([
                        'user_id' => $document->user_id,
                        'title' => 'Dokumentasi Disetujui oleh Super Admin',
                        'message' => "Dokumentasi '{$document->title}' disetujui super admin. Menunggu persetujuan manajer.",
                        'reference_type' => 'document_view',
                        'reference_id' => $document->id,
                    ]);
                    FcmHelper::send(
                        $uploader->fcm_token,
                        'Dokumentasi Disetujui oleh Super Admin',
                        "Dokumentasi '{$document->title}' disetujui super admin. Menunggu persetujuan manajer.",
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
                    'title' => 'Dokumentasi Disetujui',
                    'message' => "Dokumentasi '{$document->title}' telah disetujui dan dipublikasikan.",
                    'reference_type' => 'document_view',
                    'reference_id' => $document->id,
                ]);
                FcmHelper::send(
                    $uploader->fcm_token,
                    'Dokumentasi Disetujui',
                    "Dokumentasi '{$document->title}' telah disetujui dan dipublikasikan.",
                    [
                        'reference_type' => 'document_view',
                        'reference_id' => (string) $document->id,
                    ]
                );
            }

            $document = $document->fresh(['user', 'category', 'documentApprovals.approver']);

            return response()->json([
                'message' => 'Dokumentasi berhasil disetujui',
                'data' => $document,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dokumentasi atau persetujuan tidak ditemukan',
                'error' => 'Resource not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menyetujui Dokumentasi',
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
         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="comments", type="string", example="Dokumentasi tidak sesuai")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document rejected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumentasi berhasil ditolak")
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
     *             @OA\Property(property="message", type="string", example="Dokumentasi atau persetujuan tidak ditemukan")
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

            $document = Document::with(['user', 'category', 'documentApprovals.approver'])
                ->findOrFail($id);
            $user = Auth::user();

            if ($user->role === 'super_admin' && $document->status !== 'pending_super_admin') {
                return response()->json(['message' => 'Dokumentasi tidak dalam status pending_super_admin'], 400);
            }
            if ($user->role === 'manager' && ($document->status !== 'pending_manager' || $document->manager_id !== $user->id)) {
                return response()->json(['message' => 'Dokumentasi tidak dalam status pending_manager atau Anda bukan manajer'], 400);
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
                'title' => 'Dokumentasi Ditolak',
                'message' => "Dokumentasi '{$document->title}' ditolak oleh {$user->name}. Alasan: {$request->comments}.",
                'reference_type' => 'document_view',
                'reference_id' => $document->id,
            ]);
            $uploader = User::find($document->user_id);
            FcmHelper::send(
                $uploader->fcm_token,
                'Dokumentasi Ditolak',
                "Dokumentasi '{$document->title}' ditolak oleh {$user->name}. Alasan: {$request->comments}.",
                [
                    'reference_type' => 'document_view',
                    'reference_id' => (string) $document->id,
                ]
            );

            $document = $document->fresh(['user', 'category', 'documentApprovals.approver']);

            return response()->json([
                'message' => 'Dokumentasi berhasil ditolak',
                'data' => $document,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dokumentasi atau persetujuan tidak ditemukan',
                'error' => 'Resource not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menolak Dokumentasi',
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
     *             @OA\Property(property="comments", type="string", example="Perbarui konten")
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
     *             @OA\Property(property="message", type="string", example="Dokumentasi tidak ditemukan")
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
                'title' => 'Pembaruan Dokumentasi Diperlukan',
                'message' => "Manajer meminta pembaruan untuk '{$document->category->name}'. Alasan: {$request->comments}.",
                'reference_type' => 'document_update_request',
                'reference_id' => $document->category->id,
            ]);

            $uploader = User::find($document->user_id);
            FcmHelper::send(
                $uploader->fcm_token,
                'Pembaruan Dokumentasi Diperlukan',
                "Manajer meminta pembaruan untuk '{$document->title}'. Alasan: {$request->comments}.",
                [
                    'reference_type' => 'document_update_request',
                    'reference_id' => (string) $document->category->id,
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
                'message' => 'Dokumentasi tidak ditemukan',
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
     *             @OA\Property(property="message", type="string", example="Pengajuan Dokumentasi berhasil diambil"),
     *             @OA\Property(
     *                 property="submissions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Penggunaan APAR"),
     *                     @OA\Property(property="status", type="string", example="pending_super_admin"),
     *                     @OA\Property(
     *                         property="category",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="code", type="string")
     *                     ),
     *                     @OA\Property(
     *                         property="document_approvals",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="approver_id", type="integer"),
     *                             @OA\Property(property="status", type="string", example="pending"),
     *                             @OA\Property(property="comments", type="string", example="Dokumentasi perlu revisi", nullable=true),
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
     *             @OA\Property(property="message", type="string", example="Gagal mengambil pengajuan Dokumentasi"),
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
                ->withTrashed() // Sertakan Dokumentasi yang di-soft-delete
                ->where('user_id', $user->id);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $submissions = $query->get();

            return response()->json([
                'message' => 'Pengajuan Dokumentasi berhasil diambil',
                'data' => $submissions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil pengajuan Dokumentasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a specific document by ID.
     *
     * @OA\Delete(
     *     path="/api/documents/{id}",
     *     operationId="deleteDocument",
     *     tags={"Documents"},
     *     summary="Delete a specific document by ID",
     *     description="Soft deletes a document, sets its status to DELETED, deletes its file, and updates the approval status to DELETED for the super admin who deletes it. Only super_admin can delete. Sends a notification to the document owner.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the document to delete",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dokumentasi berhasil dihapus")
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
     *             @OA\Property(property="message", type="string", example="Dokumentasi tidak ditemukan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal menghapus Dokumentasi"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $document = Document::withTrashed()->findOrFail($id);
            $user = Auth::user();

            // Hanya super_admin yang dapat menghapus
            if ($user->role !== 'super_admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Hapus file dari penyimpanan
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Ubah status Dokumentasi menjadi DELETED
            $document->status = 'DELETED';
            $document->save();

            // Soft delete Dokumentasi
            $document->delete();

            // Ubah status DocumentApproval untuk super admin yang menghapus menjadi DELETED
            DocumentApproval::where('document_id', $document->id)
                ->where('approver_id', $user->id)
                ->update(['status' => 'DELETED']);

            // Kirim notifikasi ke pemilik Dokumentasi
            $uploader = User::find($document->user_id);
            if ($uploader) {
                Notification::create([
                    'user_id' => $document->user_id,
                    'title' => 'Dokumentasi Dihapus',
                    'message' => "Dokumentasi '{$document->title}' telah dihapus oleh super admin.",
                    'reference_type' => 'document_view',
                    'reference_id' => $document->id,
                ]);

                FcmHelper::send(
                    $uploader->fcm_token,
                    'Dokumentasi Dihapus',
                    "Dokumentasi '{$document->title}' telah dihapus oleh super admin.",
                    [
                        'reference_type' => 'document_view',
                        'reference_id' => (string) $document->id,
                    ]
                );
            }

            return response()->json([
                'message' => 'Dokumentasi berhasil dihapus',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dokumentasi tidak ditemukan',
                'error' => 'Document not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus Dokumentasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete all documents under a level 3 category.
     *
     * @OA\Delete(
     *     path="/api/documents/category/{category_id}",
     *     operationId="deleteDocumentsByCategory",
     *     tags={"Documents"},
     *     summary="Delete all documents under a level 3 category",
     *     description="Soft deletes all documents under the specified level 3 category, sets their status to DELETED, deletes their files, and updates the approval status to DELETED for the super admin who deletes them. Only super_admin can delete. Sends notifications to document owners.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="path",
     *         description="ID of the level 3 category",
     *         required=true,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Documents deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Semua Dokumentasi pada kategori berhasil dihapus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Category is not a level 3 category",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kategori harus berada di level terendah (Level 3)")
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
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kategori tidak ditemukan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal menghapus Dokumentasi"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function destroyByCategory($category_id)
    {
        try {
            $user = Auth::user();

            // Hanya super_admin yang dapat menghapus
            if (!in_array($user->role, ['super_admin', 'admin'])) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Ambil kategori
            $category = Category::findOrFail($category_id);

            // Pastikan kategori adalah level 3
            if ($category->children()->exists()) {
                return response()->json([
                    'message' => 'Kategori harus berada di level terendah (Level 3)',
                ], 400);
            }

            // Ambil semua Dokumentasi di kategori
            $documents = Document::where('category_id', $category_id)->get();

            // Proses setiap Dokumentasi
            foreach ($documents as $document) {
                // Hapus file
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }

                // Ubah status menjadi DELETED
                $document->status = 'DELETED';
                $document->save();

                // Soft delete Dokumentasi
                $document->delete();

                // Ubah status DocumentApproval untuk super admin yang menghapus menjadi DELETED
                DocumentApproval::where('document_id', $document->id)
                    ->where('approver_id', $user->id)
                    ->update(['status' => 'DELETED']);

                // Kirim notifikasi ke pemilik Dokumentasi
                $uploader = User::find($document->user_id);
                if ($uploader) {
                    Notification::create([
                        'user_id' => $document->user_id,
                        'title' => 'Dokumentasi Dihapus',
                        'message' => "Dokumentasi '{$document->title}' dalam kategori '{$category->name}' telah dihapus oleh super admin.",
                        'reference_type' => 'document_view',
                        'reference_id' => $document->id,
                    ]);

                    FcmHelper::send(
                        $uploader->fcm_token,
                        'Dokumentasi Dihapus',
                        "Dokumentasi '{$document->title}' dalam kategori '{$category->name}' telah dihapus oleh super admin.",
                        [
                            'reference_type' => 'document_view',
                            'reference_id' => (string) $document->id,
                        ]
                    );
                }
            }

            return response()->json([
                'message' => 'Semua Dokumentasi pada kategori berhasil dihapus',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan',
                'error' => 'Category not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus Dokumentasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get assessment progress for K3 documentation.
     *
     * @OA\Get(
     *     path="/api/documents/assessment-progress",
     *     operationId="getAssessmentProgress",
     *     tags={"Documents"},
     *     summary="Get progress percentage for K3 assessment levels",
     *     description="Returns the progress percentage for Initial, Transition, and Advanced assessment levels based on approved documents in categories with matching assessment codes.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Assessment progress retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="assessment", type="string", example="Tingkat Awal"),
     *                 @OA\Property(property="progress", type="number", format="float", example=60.5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil progres penilaian"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function getAssessmentProgress(Request $request)
    {
        try {
            // Daftar sub-kriteria per tingkat berdasarkan Dokumentasi
            $initialPoints = [
                '1.1.1',
                '1.1.3',
                '1.2.2',
                '1.2.4',
                '1.2.5',
                '1.2.6',
                '1.3.3',
                '1.4.1',
                '1.4.3',
                '1.4.4',
                '1.4.5',
                '1.4.6',
                '1.4.7',
                '1.4.8',
                '1.4.9',
                '2.1.1',
                '2.4.1',
                '3.1.1',
                '3.2.2',
                '4.1.1',
                '5.1.1',
                '5.1.2',
                '5.2.1',
                '6.1.1',
                '6.1.5',
                '6.1.6',
                '6.1.7',
                '6.2.1',
                '6.3.1',
                '6.3.2',
                '6.4.1',
                '6.4.2',
                '6.4.3',
                '6.4.4',
                '6.5.2',
                '6.5.3',
                '6.5.4',
                '6.5.7',
                '6.5.8',
                '6.5.9',
                '6.7.4',
                '6.7.6',
                '6.8.1',
                '6.8.2',
                '7.1.1',
                '7.2.1',
                '7.2.2',
                '7.2.3',
                '7.4.1',
                '7.4.3',
                '7.4.4',
                '7.4.5',
                '8.3.1',
                '9.1.1',
                '9.1.2',
                '9.2.1',
                '9.2.3',
                '9.3.1',
                '9.3.3',
                '9.3.4',
                '10.1.1',
                '10.1.2',
                '10.2.1',
                '10.2.2',
                '12.2.1',
                '12.2.2',
                '12.3.1',
                '12.5.1'
            ];

            $transitionPoints = array_merge($initialPoints, [
                '1.1.2',
                '1.2.1',
                '1.2.3',
                '1.3.1',
                '1.4.2',
                '2.1.2',
                '2.1.3',
                '2.1.4',
                '2.2.1',
                '2.3.1',
                '2.3.2',
                '2.3.4',
                '3.1.2',
                '3.1.3',
                '3.1.4',
                '3.2.1',
                '4.1.2',
                '4.2.1',
                '5.1.3',
                '6.1.2',
                '6.1.3',
                '6.1.4',
                '6.2.2',
                '6.2.3',
                '6.2.4',
                '6.2.5',
                '6.5.1',
                '6.5.5',
                '6.5.6',
                '6.5.10',
                '6.7.1',
                '6.7.2',
                '6.7.3',
                '6.7.5',
                '6.7.7',
                '7.1.2',
                '7.1.3',
                '7.1.4',
                '7.1.5',
                '7.1.6',
                '7.1.7',
                '7.4.2',
                '8.1.1',
                '8.2.1',
                '8.3.2',
                '9.1.3',
                '9.1.4',
                '9.3.5',
                '10.1.3',
                '10.1.4',
                '11.1.1',
                '11.1.2',
                '11.1.3',
                '12.1.2',
                '12.1.4',
                '12.1.5',
                '12.1.6',
                '12.3.2',
                '12.4.1'
            ]);

            $advancedPoints = array_merge($transitionPoints, [
                '1.1.4',
                '1.1.5',
                '1.2.7',
                '1.3.2',
                '1.4.10',
                '1.4.11',
                '2.1.5',
                '2.1.6',
                '2.2.2',
                '2.2.3',
                '2.3.3',
                '3.2.3',
                '3.2.4',
                '4.1.3',
                '4.1.4',
                '4.2.2',
                '4.2.3',
                '5.1.4',
                '5.1.5',
                '5.3.1',
                '5.4.1',
                '5.4.2',
                '6.1.8',
                '6.6.1',
                '6.6.2',
                '6.9.1',
                '7.3.1',
                '7.3.2',
                '8.3.3',
                '8.3.4',
                '8.3.5',
                '8.3.6',
                '8.4.1',
                '9.2.2',
                '9.3.2',
                '12.1.1',
                '12.1.3',
                '12.1.7',
                '12.3.3'
            ]);

            // Hitung total sub-kriteria per tingkat
            $totalInitial = count($initialPoints);
            $totalTransition = count($transitionPoints);
            $totalAdvanced = count($advancedPoints);

            // Ambil sub-kriteria yang terpenuhi (ada Dokumentasi approved di kategori terkait)
            $fulfilledInitial = Category::whereIn('code', $initialPoints)
                ->whereHas('documents', function ($query) {
                    $query->where('status', 'approved')->whereNull('deleted_at');
                })
                ->pluck('code')
                ->unique()
                ->count();

            $fulfilledTransition = Category::whereIn('code', $transitionPoints)
                ->whereHas('documents', function ($query) {
                    $query->where('status', 'approved')->whereNull('deleted_at');
                })
                ->pluck('code')
                ->unique()
                ->count();

            $fulfilledAdvanced = Category::whereIn('code', $advancedPoints)
                ->whereHas('documents', function ($query) {
                    $query->where('status', 'approved')->whereNull('deleted_at');
                })
                ->pluck('code')
                ->unique()
                ->count();

            // Hitung persentase
            $initialProgress = $totalInitial > 0 ? ($fulfilledInitial / $totalInitial) * 100 : 0;
            $transitionProgress = $totalTransition > 0 ? ($fulfilledTransition / $totalTransition) * 100 : 0;
            $advancedProgress = $totalAdvanced > 0 ? ($fulfilledAdvanced / $totalAdvanced) * 100 : 0;

            // Format data untuk grafik
            $data = [
                [
                    'assessment' => 'Tingkat Awal',
                    'progress' => round($initialProgress, 2)
                ],
                [
                    'assessment' => 'Tingkat Transisi',
                    'progress' => round($transitionProgress, 2)
                ],
                [
                    'assessment' => 'Tingkat Lanjutan',
                    'progress' => round($advancedProgress, 2)
                ]
            ];

            return response()->json([
                'message' => 'Progres penilaian berhasil diambil',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil progres penilaian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/documents/category-progress",
     *     operationId="getCategoryProgress",
     *     tags={"Documents"},
     *     summary="Get category progress with hierarchy",
     *     description="Returns the category hierarchy (Level 1 to Level 3) with completion progress for Level 1 and Level 2, and status (TERISI/BELUM) for Level 3 based on approved documents.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Category progress retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Progres kategori berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="1"),
     *                     @OA\Property(property="name", type="string", example="Pembangunan Dan Pemeliharaan"),
     *                     @OA\Property(property="progress_percentage", type="number", format="float", example=89.0),
     *                     @OA\Property(property="progress_fraction", type="string", example="8/9"),
     *                     @OA\Property(
     *                         property="children",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="code", type="string", example="1.1"),
     *                             @OA\Property(property="name", type="string", example="Kebijakan K3"),
     *                             @OA\Property(property="progress_percentage", type="number", format="float", example=60.0),
     *                             @OA\Property(property="progress_fraction", type="string", example="3/5"),
     *                             @OA\Property(
     *                                 property="children",
     *                                 type="array",
     *                                 @OA\Items(
     *                                     type="object",
     *                                     @OA\Property(property="id", type="integer", example=3),
     *                                     @OA\Property(property="code", type="string", example="1.1.1"),
     *                                     @OA\Property(property="name", type="string", example="Sub Kebijakan K3 1"),
     *                                     @OA\Property(property="status", type="string", example="TERISI")
     *                                 )
     *                             )
     *                         )
     *                     )
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
     *             @OA\Property(property="message", type="string", example="Gagal mengambil progres kategori"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function getCategoryProgress(Request $request)
    {
        try {


            // Ambil semua kategori Level 1 dengan hierarki anak
            $categories = Category::with(['children.children.documents' => function ($query) {
                $query->where('status', 'approved')->whereNull('deleted_at');
            }])
                ->whereNull('parent_id')
                ->get();

            // Transformasi data untuk menyertakan progres
            $data = $categories->map(function ($level1) {
                // Hitung progres untuk Level 1
                $level2Categories = $level1->children;
                $totalLevel2 = $level2Categories->count();
                $filledLevel2 = $level2Categories->filter(function ($level2) {
                    return $level2->children->some(function ($level3) {
                        return $level3->documents->isNotEmpty();
                    });
                })->count();
                $level1Progress = $totalLevel2 > 0 ? ($filledLevel2 / $totalLevel2) * 100 : 0;

                return [
                    'id' => $level1->id,
                    'code' => $level1->code,
                    'name' => $level1->name,
                    'progress_percentage' => round($level1Progress, 2),
                    'progress_fraction' => "$filledLevel2/$totalLevel2",
                    'children' => $level2Categories->map(function ($level2) {
                        // Hitung progres untuk Level 2
                        $level3Categories = $level2->children;
                        $totalLevel3 = $level3Categories->count();
                        $filledLevel3 = $level3Categories->filter(function ($level3) {
                            return $level3->documents->isNotEmpty();
                        })->count();
                        $level2Progress = $totalLevel3 > 0 ? ($filledLevel3 / $totalLevel3) * 100 : 0;

                        return [
                            'id' => $level2->id,
                            'code' => $level2->code,
                            'name' => $level2->name,
                            'progress_percentage' => round($level2Progress, 2),
                            'progress_fraction' => "$filledLevel3/$totalLevel3",
                            'children' => $level3Categories->map(function ($level3) {
                                $isFilled = $level3->documents->isNotEmpty();
                                return [
                                    'id' => $level3->id,
                                    'code' => $level3->code,
                                    'name' => $level3->name,
                                    'status' => $isFilled ? 'TERISI' : 'BELUM'
                                ];
                            })->toArray()
                        ];
                    })->toArray()
                ];
            })->toArray();

            return response()->json([
                'message' => 'Progres kategori berhasil diambil',
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil progres kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download all approved documents as a ZIP file with category hierarchy.
     *
     * @OA\Get(
     *     path="/api/documents/download-all",
     *     operationId="downloadAllDocuments",
     *     tags={"Documents"},
     *     summary="Download all approved documents in a ZIP file",
     *     description="Downloads all approved documents in a ZIP file named 'documentation-YYYYMMDDHHMMSS.zip', organized in a folder structure based on category hierarchy with code prefixes (e.g., '1.1 Kebijakan K3'). Only accessible to super_admin.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="ZIP file downloaded successfully",
     *         @OA\MediaType(
     *             mediaType="application/zip",
     *             @OA\Schema(type="string", format="binary")
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
     *         description="No approved documents found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tidak ada Dokumentasi yang tersedia untuk diunduh")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengunduh Dokumentasi"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function downloadAllZip(Request $request)
    {
        try {
            $user = Auth::user();

            // Hanya super_admin yang dapat mengakses
            if ($user->role !== 'super_admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Ambil semua Dokumentasi approved dengan kategori
            $documents = Document::with(['category.parent.parent'])
                ->where('status', 'approved')
                ->whereNull('deleted_at')
                ->get();

            if ($documents->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada Dokumentasi yang tersedia untuk diunduh',
                ], 404);
            }

            // Buat instance ZipArchive
            $zip = new \ZipArchive();
            $timestamp = now()->format('YmdHis'); // Format: YYYYMMDDHHMMSS
            $zipFileName = "documentation-{$timestamp}.zip";
            $zipFilePath = storage_path('app/temp/' . $zipFileName);

            // Pastikan direktori temp ada
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return response()->json([
                    'message' => 'Gagal membuat file ZIP',
                    'error' => 'Cannot open ZIP archive',
                ], 500);
            }

            // Tambahkan Dokumentasi ke ZIP dengan struktur folder
            foreach ($documents as $document) {
                if (!$document->category || !$document->category->parent || !$document->category->parent->parent) {
                    continue; // Lewati jika hierarki kategori tidak lengkap
                }

                // Ambil kode dan nama kategori untuk Level 1, Level 2, Level 3
                $level1Name = Str::slug("{$document->category->parent->parent->code} {$document->category->parent->parent->name}");
                $level2Name = Str::slug("{$document->category->parent->code} {$document->category->parent->name}");
                $level3Name = Str::slug("{$document->category->code} {$document->category->name}");

                // Path di dalam ZIP
                $zipPath = "{$level1Name}/{$level2Name}/{$level3Name}/" . basename($document->file_path);

                // Pastikan file ada di storage
                $filePath = storage_path('app/public/' . $document->file_path);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $zipPath);
                }
            }

            $zip->close();

            // Periksa apakah ZIP berhasil dibuat
            if (!file_exists($zipFilePath)) {
                return response()->json([
                    'message' => 'Gagal membuat file ZIP',
                    'error' => 'ZIP file not created',
                ], 500);
            }

            // Unduh file ZIP
            return response()->download($zipFilePath, $zipFileName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        } catch (Exception $e) {
            Log::error('Download all documents error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Gagal mengunduh Dokumentasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete all document files and set their status to DELETED.
     *
     * @OA\Delete(
     *     path="/api/documents/delete-all",
     *     operationId="deleteAllDocuments",
     *     tags={"Documents"},
     *     summary="Delete all document files and set status to DELETED",
     *     description="Deletes all document files from the server, sets their status to DELETED, and soft deletes them. Only accessible to super_admin. Sends notifications to document owners. Records remain in the database.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All documents deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Semua Dokumentasi berhasil dihapus")
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
     *         description="No documents found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tidak ada Dokumentasi yang tersedia untuk dihapus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal menghapus Dokumentasi"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function deleteAll(Request $request)
    {
        try {
            $user = Auth::user();

            // Hanya super_admin yang dapat mengakses
            if ($user->role !== 'super_admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Ambil semua Dokumentasi
            $documents = Document::withTrashed()->get();

            if ($documents->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada Dokumentasi yang tersedia untuk dihapus',
                ], 404);
            }

            // Proses setiap Dokumentasi
            foreach ($documents as $document) {
                // Hapus file dari penyimpanan
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }

                // Ubah status Dokumentasi menjadi DELETED
                $document->status = 'DELETED';
                $document->save();

                // Soft delete Dokumentasi
                if (!$document->trashed()) {
                    $document->delete();
                }

                // Ubah status DocumentApproval untuk super admin yang menghapus menjadi DELETED
                DocumentApproval::where('document_id', $document->id)
                    ->where('approver_id', $user->id)
                    ->update(['status' => 'DELETED']);

                // Kirim notifikasi ke pemilik Dokumentasi
                $uploader = User::find($document->user_id);
                if ($uploader) {
                    Notification::create([
                        'user_id' => $document->user_id,
                        'title' => 'Dokumentasi Dihapus',
                        'message' => "Dokumentasi '{$document->title}' telah dihapus oleh super admin.",
                        'reference_type' => 'document_view',
                        'reference_id' => $document->id,
                    ]);

                    FcmHelper::send(
                        $uploader->fcm_token,
                        'Dokumentasi Dihapus',
                        "Dokumentasi '{$document->title}' telah dihapus oleh super admin.",
                        [
                            'reference_type' => 'document_view',
                            'reference_id' => (string) $document->id,
                        ]
                    );
                }
            }

            return response()->json([
                'message' => 'Semua Dokumentasi berhasil dihapus',
            ], 200);
        } catch (Exception $e) {
            Log::error('Delete all documents error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Gagal menghapus Dokumentasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
