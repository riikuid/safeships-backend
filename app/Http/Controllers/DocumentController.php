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
     *             @OA\Property(property="message", type="string", example="Dokumen berhasil diambil"),
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
                'message' => 'Dokumen berhasil diambil',
                'data' => $documents,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan',
                'error' => 'Category not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil dokumen',
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
     *             @OA\Property(property="message", type="string", example="Dokumen berhasil diambil"),
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
     *             @OA\Property(property="message", type="string", example="Gagal mengambil dokumen"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function documentsManagerial(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Document::with(['user', 'category', 'documentApprovals.approver']);

            if (!in_array($user->role, ['super_admin', 'manager'])) {
                $query->where('status', 'approved');
            }

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

            // Sort documents: unapproved by user at top (asc by created_at), approved by user at bottom (asc by created_at)
            $sortedDocuments = $documents->sortBy(function ($document) use ($user) {
                $isApprovedByUser = $document->documentApprovals->contains(function ($approval) use ($user) {
                    return $approval->approver_id === $user->id && $approval->status !== 'pending';
                });

                // If approved by user, assign a higher sort key to push it to the bottom
                $sortKey = $isApprovedByUser ? 1 : 0;

                // Combine sort key with created_at timestamp for secondary sorting
                return [$sortKey, $document->created_at->timestamp];
            });

            return response()->json([
                'message' => 'Dokumen berhasil diambil',
                'data' => $sortedDocuments->values(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan',
                'error' => 'Category not found',
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
                    'title' => 'Dokumen Baru Menunggu Persetujuan',
                    'message' => "Dokumen '{$document->title}' diunggah oleh " . Auth::user()->name . ".",
                    'reference_type' => 'document',
                    'reference_id' => $document->id,
                ]);

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
                'data' => $document->load('category'),
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
     *                 example="Berhasil mendapatkan dokumen pada kategori level 2: Kebijakan K3"
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
     *                             @OA\Property(property="title", type="string", example="Dokumen Kebijakan K3 2023"),
     *                             @OA\Property(property="file_path", type="string", example="storage/documents/kebijakan_k3_2023.pdf"),
     *                             @OA\Property(property="description", type="string", example="Dokumen resmi kebijakan K3 tahun 2023")
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
                'message' => 'Berhasil mendapatkan dokumen pada kategori level 2: ' . $category->name,
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
     *             @OA\Property(property="message", type="string", example="Dokumen berhasil diambil"),
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
            if (
                !in_array($user->role, ['super_admin', 'manager']) &&
                $document->user_id !== $user->id &&
                $document->status !== 'approved'
            ) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json([
                'message' => 'Dokumen berhasil diambil',
                'data' => $document,
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
            $document = Document::with(['user', 'category', 'documentApprovals.approver'])->findOrFail($id);
            $uploader = User::find($document->user_id);
            $user = Auth::user();

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
                        'title' => 'Dokumen Menunggu Persetujuan Anda',
                        'message' => "Dokumen '{$document->title}' telah disetujui super admin. Menunggu persetujuan Anda sebagai manajer.",
                        'reference_type' => 'document',
                        'reference_id' => $document->id,
                    ]);
                    $manager = User::find($document->manager_id);
                    FcmHelper::send(
                        $manager->fcm_token,
                        'Dokumen Menunggu Persetujuan',
                        "Dokumen '{$document->title}' telah disetujui super admin. Menunggu persetujuan Anda sebagai manajer.",
                        [
                            'reference_type' => 'document',
                            'reference_id' => (string) $document->id,
                        ]
                    );

                    Notification::create([
                        'user_id' => $document->user_id,
                        'title' => 'Dokumen Disetujui oleh Super Admin',
                        'message' => "Dokumen '{$document->title}' disetujui super admin. Menunggu persetujuan manajer.",
                        'reference_type' => 'document',
                        'reference_id' => $document->id,
                    ]);
                    FcmHelper::send(
                        $uploader->fcm_token,
                        'Dokumen Disetujui oleh Super Admin',
                        "Dokumen '{$document->title}' disetujui super admin. Menunggu persetujuan manajer.",
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

            $document = $document->fresh(['user', 'category', 'documentApprovals.approver']);

            return response()->json([
                'message' => 'Dokumen berhasil disetujui',
                'data' => $document,
            ]);
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
         in="path",
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

            $document = Document::with(['user', 'category', 'documentApprovals.approver'])
                ->findOrFail($id);
            $user = Auth::user();

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
                'message' => "Dokumen '{$document->title}' ditolak oleh {$user->name}. Alasan: {$request->comments}.",
                'reference_type' => 'document',
                'reference_id' => $document->id,
            ]);
            $uploader = User::find($document->user_id);
            FcmHelper::send(
                $uploader->fcm_token,
                'Dokumen Ditolak',
                "Dokumen '{$document->title}' ditolak oleh {$user->name}. Alasan: {$request->comments}.",
                [
                    'reference_type' => 'document',
                    'reference_id' => (string) $document->id,
                ]
            );

            $document = $document->fresh(['user', 'category', 'documentApprovals.approver']);

            return response()->json([
                'message' => 'Dokumen berhasil ditolak',
                'data' => $document,
            ]);
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

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $submissions = $query->get();

            return response()->json([
                'message' => 'Pengajuan dokumen berhasil diambil',
                'data' => $submissions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil pengajuan dokumen',
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
     *     description="Deletes a document and its associated approvals and notifications. Only super_admin or the document uploader can delete.",
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
     *             @OA\Property(property="message", type="string", example="Dokumen berhasil dihapus")
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
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal menghapus dokumen"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $document = Document::findOrFail($id);
            $user = Auth::user();

            // Hanya super_admin atau pengguna yang mengunggah dokumen dapat menghapus
            if ($user->role !== 'super_admin' && $document->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Hapus file dari penyimpanan
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Hapus entri terkait
            DocumentApproval::where('document_id', $document->id)->delete();
            Notification::where('reference_type', 'document')
                ->where('reference_id', $document->id)
                ->delete();

            // Hapus dokumen
            $document->delete();

            return response()->json([
                'message' => 'Dokumen berhasil dihapus',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dokumen tidak ditemukan',
                'error' => 'Document not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus dokumen',
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
     *     description="Deletes all documents under the specified level 3 category, including their associated approvals and notifications. Only super_admin can delete.",
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
     *             @OA\Property(property="message", type="string", example="Semua dokumen pada kategori berhasil dihapus")
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
     *             @OA\Property(property="message", type="string", example="Gagal menghapus dokumen"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function destroyByCategory($category_id)
    {
        try {
            $user = Auth::user();

            // Hanya super_admin yang dapat menghapus semua dokumen di kategori
            if ($user->role !== 'super_admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Ambil kategori
            $category = Category::findOrFail($category_id);

            // Pastikan kategori adalah level 3 (tidak memiliki anak)
            if ($category->children()->exists()) {
                return response()->json([
                    'message' => 'Kategori harus berada di level terendah (Level 3)',
                ], 400);
            }

            // Ambil semua dokumen di kategori
            $documents = Document::where('category_id', $category_id)->get();

            // Hapus file, approvals, dan notifikasi untuk setiap dokumen
            foreach ($documents as $document) {
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
                DocumentApproval::where('document_id', $document->id)->delete();
                Notification::where('reference_type', 'document')
                    ->where('reference_id', $document->id)
                    ->delete();
                $document->delete();
            }

            return response()->json([
                'message' => 'Semua dokumen pada kategori berhasil dihapus',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan',
                'error' => 'Category not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus dokumen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
