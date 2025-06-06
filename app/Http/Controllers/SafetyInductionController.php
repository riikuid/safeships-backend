<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Question;
use App\Models\QuestionPackage;
use App\Models\SafetyInduction;
use App\Models\SafetyInductionAttempt;
use App\Helpers\FcmHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="SafetyInductionModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="type", type="string", example="karyawan"),
 *     @OA\Property(property="address", type="string", example="Jl. Contoh No. 123"),
 *     @OA\Property(property="phone_number", type="string", example="081234567890"),
 *     @OA\Property(property="email", type="string", example="john@example.com"),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="SafetyInductionAttemptModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="safety_induction_id", type="integer", example=1),
 *     @OA\Property(property="question_package_id", type="integer", example=1),
 *     @OA\Property(property="score", type="integer", example=70),
 *     @OA\Property(property="passed", type="boolean", example=false),
 *     @OA\Property(property="attempt_date", type="string", format="date"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="CertificateModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="safety_induction_id", type="integer", example=1),
 *     @OA\Property(property="issued_date", type="string", format="date"),
 *     @OA\Property(property="expired_date", type="string", format="date"),
 *     @OA\Property(property="url", type="string", example="certificates/safety_induction_1_20250602184800.pdf"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="QuestionModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="question_package_id", type="integer", example=1),
 *     @OA\Property(property="text", type="string", example="Apa prosedur keselamatan?"),
 *     @OA\Property(
 *         property="options",
 *         type="object",
 *         @OA\Property(property="A", type="string", example="Memakai helm"),
 *         @OA\Property(property="B", type="string", example="Berlari"),
 *         @OA\Property(property="C", type="string", example="Abaikan tanda"),
 *         @OA\Property(property="D", type="string", example="Tanpa APD")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class SafetyInductionController extends Controller
{
    /**
     * Get list of locations
     *
     * @OA\Get(
     *     path="/api/safety-inductions/locations",
     *     summary="Get list of locations with YouTube URLs",
     *     tags={"Safety Induction"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Daftar lokasi berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Warehouse A"),
     *                     @OA\Property(property="youtube_url", type="string", example="https://youtube.com/watch?v=abc123")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil daftar lokasi"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function getLocations()
    {
        try {
            $locations = Location::all(['id', 'name', 'youtube_url']);
            return response()->json([
                'message' => 'Daftar lokasi berhasil diambil',
                'data' => $locations,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil daftar lokasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new safety induction request
     *
     * @OA\Post(
     *     path="/api/safety-inductions",
     *     summary="Create a new safety induction request",
     *     tags={"Safety Induction"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="type", type="string", enum={"karyawan", "mahasiswa", "tamu", "kontraktor"}, example="karyawan"),
     *             @OA\Property(property="address", type="string", example="Jl. Contoh No. 123"),
     *             @OA\Property(property="phone_number", type="string", example="081234567890"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="force_create", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Request created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pengajuan safety induction berhasil dibuat"),
     *             @OA\Property(property="data", ref="#/components/schemas/SafetyInductionModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Pending submission exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Anda masih memiliki pengajuan yang belum selesai"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="pending_submission", ref="#/components/schemas/SafetyInductionModel"),
     *                 @OA\Property(property="options", type="array", @OA\Items(type="string", example="Buat Pengajuan Baru"))
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
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal membuat pengajuan"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:karyawan,mahasiswa,tamu,kontraktor',
                'address' => 'nullable|string',
                'phone_number' => 'nullable|string|max:20',
                'email' => 'nullable|email',
                'force_create' => 'sometimes|boolean',
            ]);

            $user = Auth::user();
            $pendingSubmission = SafetyInduction::where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if ($pendingSubmission && !$request->input('force_create', false)) {
                return response()->json([
                    'message' => 'Anda masih memiliki pengajuan yang belum selesai',
                    'data' => [
                        'pending_submission' => $pendingSubmission->load(['user', 'attempts', 'certificate']),
                        'options' => ['Buat Pengajuan Baru', 'Lanjutkan Pengajuan Sebelumnya'],
                    ],
                ], 409);
            }

            // If force_create is true, delete the existing pending submission
            if ($pendingSubmission && $request->input('force_create', false)) {
                $pendingSubmission->delete();
            }

            $induction = SafetyInduction::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'type' => $request->type,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'status' => 'pending',
            ]);

            // Notification::create([
            //     'user_id' => $user->id,
            //     'title' => 'Pengajuan Safety Induction',
            //     'message' => 'Pengajuan safety induction Anda telah dibuat. Silakan lanjutkan ke tes.',
            //     'reference_type' => 'safety_induction',
            //     'reference_id' => $induction->id,
            // ]);

            // FcmHelper::send(
            //     $user->fcm_token,
            //     'Pengajuan Safety Induction',
            //     'Pengajuan safety induction Anda telah dibuat. Silakan lanjutkan ke tes.',
            //     [
            //         'reference_type' => 'safety_induction',
            //         'reference_id' => (string) $induction->id,
            //     ]
            // );

            return response()->json([
                'message' => 'Pengajuan safety induction berhasil dibuat',
                'data' => $induction->load(['user', 'attempts', 'certificate']),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat pengajuan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get questions for a safety induction test
     *
     * @OA\Get(
     *     path="/api/safety-inductions/{id}/questions",
     *     summary="Get questions for a safety induction test",
     *     tags={"Safety Induction"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the safety induction",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Soal berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="package",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Paket A")
     *                 ),
     *                 @OA\Property(
     *                     property="questions",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/QuestionModel")
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
     *         description="Induction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pengajuan tidak ditemukan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pengajuan sudah selesai")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil soal"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function getQuestions($id)
    {
        try {
            $induction = SafetyInduction::findOrFail($id);
            $user = Auth::user();

            if ($induction->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            if ($induction->status !== 'pending') {
                return response()->json(['message' => 'Pengajuan sudah selesai'], 400);
            }

            $attempts = SafetyInductionAttempt::where('safety_induction_id', $id)->get();
            if ($attempts->isEmpty()) {
                $package = QuestionPackage::where('type', 'standard')->inRandomOrder()->first();
            } else {
                $lastAttempt = $attempts->last();
                if ($lastAttempt->passed) {
                    return response()->json(['message' => 'Pengajuan ini sudah lulus'], 400);
                }
                $package = QuestionPackage::where('type', 'easy')->first();
            }

            if (!$package) {
                return response()->json(['message' => 'Paket soal tidak ditemukan'], 404);
            }

            $questions = Question::where('question_package_id', $package->id)->get(['id', 'text', 'options']);

            return response()->json([
                'message' => 'Soal berhasil diambil',
                'data' => [
                    'package' => ['id' => $package->id, 'name' => $package->name],
                    'questions' => $questions,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil soal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark a safety induction as failed
     *
     * @OA\Post(
     *     path="/api/safety-inductions/{id}/fail",
     *     summary="Mark a safety induction as failed",
     *     tags={"Safety Induction"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the safety induction",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Induction marked as failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pengajuan telah ditandai sebagai gagal")
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
     *         description="Induction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pengajuan tidak ditemukan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal menandai pengajuan sebagai gagal"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function markAsFailed($id)
    {
        try {
            $induction = SafetyInduction::findOrFail($id);
            $user = Auth::user();

            if ($induction->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            if ($induction->status !== 'pending') {
                return response()->json(['message' => 'Pengajuan sudah selesai'], 400);
            }

            $induction->update(['status' => 'failed']);

            // Notification::create([
            //     'user_id' => $user->id,
            //     'title' => 'Pengajuan Safety Induction Gagal',
            //     'message' => 'Pengajuan safety induction Anda telah ditandai sebagai gagal.',
            //     'reference_type' => 'safety_induction',
            //     'reference_id' => $induction->id,
            // ]);

            // FcmHelper::send(
            //     $user->fcm_token,
            //     'Pengajuan Safety Induction Gagal',
            //     'Pengajuan safety induction Anda telah ditandai sebagai gagal.',
            //     [
            //         'reference_type' => 'safety_induction',
            //         'reference_id' => (string) $induction->id,
            //     ]
            // );

            return response()->json([
                'message' => 'Pengajuan telah ditandai sebagai gagal',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Pengajuan tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menandai pengajuan sebagai gagal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit answers for a safety induction test
     *
     * @OA\Post(
     *     path="/api/safety-inductions/{id}/submit-answers",
     *     summary="Submit answers for a safety induction test",
     *     tags={"Safety Induction"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the safety induction",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="question_package_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="answers",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="question_id", type="integer", example=1),
     *                     @OA\Property(property="selected_answer", type="string", example="A")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Answers submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Jawaban berhasil disubmit"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="attempt", ref="#/components/schemas/SafetyInductionAttemptModel"),
     *                 @OA\Property(property="can_retry", type="boolean", example=true),
     *                 @OA\Property(property="certificate", ref="#/components/schemas/CertificateModel", nullable=true)
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
     *         description="Induction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pengajuan tidak ditemukan")
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
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal memproses jawaban"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function submitAnswers(Request $request, $id)
    {
        try {
            $request->validate([
                'question_package_id' => 'required|exists:question_packages,id',
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|exists:questions,id',
                'answers.*.selected_answer' => 'required|string|in:A,B,C,D',
            ]);

            $induction = SafetyInduction::findOrFail($id);
            $user = Auth::user();

            if ($induction->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            if ($induction->status !== 'pending') {
                return response()->json(['message' => 'Pengajuan sudah selesai'], 400);
            }

            // Validasi question_package_id berdasarkan jumlah attempt
            $attemptCount = $induction->attempts()->count();
            $questionPackageId = $request->input('question_package_id');
            $easyPackage = QuestionPackage::where('type', 'easy')->first();

            if ($attemptCount >= 1 && $questionPackageId != $easyPackage->id) {
                return response()->json(['message' => 'Paket soal harus easy untuk attempt kedua atau lebih'], 400);
            } elseif ($attemptCount == 0 && $questionPackageId == $easyPackage->id) {
                return response()->json(['message' => 'Paket soal easy hanya untuk attempt kedua atau lebih'], 400);
            }

            $answers = $request->input('answers');
            $questions = Question::whereIn('id', array_column($answers, 'question_id'))->get();
            $correctCount = 0;

            foreach ($answers as $answer) {
                $question = $questions->find($answer['question_id']);
                if ($question && $question->correct_answer === $answer['selected_answer']) {
                    $correctCount++;
                }
            }

            $score = ($correctCount / $questions->count()) * 100;
            $passed = $score >= 80;

            $attempt = SafetyInductionAttempt::create([
                'safety_induction_id' => $id,
                'question_package_id' => $questionPackageId,
                'score' => $score,
                'passed' => $passed,
                'attempt_date' => now(),
            ]);

            $responseData = [
                'attempt' => $attempt,
                'can_retry' => !$passed,
                'certificate' => null,
            ];

            if ($passed) {
                $induction->update(['status' => 'completed']);
                $certificate = Certificate::create([
                    'user_id' => $induction->user_id,
                    'safety_induction_id' => $induction->id,
                    'issued_date' => now(),
                    'expired_date' => now()->addYear(),
                    'url' => $this->generateCertificate($induction),
                ]);

                $responseData['certificate'] = $certificate;

                // Notification::create([
                //     'user_id' => $user->id,
                //     'title' => 'Tes Safety Induction Lulus',
                //     'message' => 'Selamat, Anda lulus tes safety induction! Sertifikat telah diterbitkan.',
                //     'reference_type' => 'safety_induction_view',
                //     'reference_id' => $induction->id,
                // ]);

                // FcmHelper::send(
                //     $user->fcm_token,
                //     'Tes Safety Induction Lulus',
                //     'Selamat, Anda lulus tes safety induction! Sertifikat telah diterbitkan.',
                //     [
                //         'reference_type' => 'safety_induction_view',
                //         'reference_id' => (string) $induction->id,
                //     ]
                // );
            } else {
                // Notification::create([
                //     'user_id' => $user->id,
                //     'title' => 'Tes Safety Induction Gagal',
                //     'message' => 'Nilai Anda kurang dari 80. Silakan coba lagi.',
                //     'reference_type' => 'safety_induction',
                //     'reference_id' => $induction->id,
                // ]);

                // FcmHelper::send(
                //     $user->fcm_token,
                //     'Tes Safety Induction Gagal',
                //     'Nilai Anda kurang dari 80. Silakan coba lagi.',
                //     [
                //         'reference_type' => 'safety_induction',
                //         'reference_id' => (string) $induction->id,
                //     ]
                // );
            }

            return response()->json([
                'message' => 'Jawaban berhasil disubmit',
                'data' => $responseData,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Pengajuan tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            Log::error('Error submitting answers: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memproses jawaban',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get result of a safety induction
     *
     * @OA\Get(
     *     path="/api/safety-inductions/{id}/result",
     *     summary="Get result of a safety induction",
     *     tags={"Safety Induction"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the safety induction",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hasil tes berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="induction", ref="#/components/schemas/SafetyInductionModel"),
     *                 @OA\Property(property="attempts", type="array", @OA\Items(ref="#/components/schemas/SafetyInductionAttemptModel")),
     *                 @OA\Property(property="certificate", ref="#/components/schemas/CertificateModel", nullable=true)
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
     *         description="Induction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pengajuan tidak ditemukan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil hasil"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function getResult($id)
    {
        try {
            $induction = SafetyInduction::with(['attempts', 'certificate'])->findOrFail($id);
            $user = Auth::user();

            if ($induction->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json([
                'message' => 'Hasil tes berhasil diambil',
                'data' => $induction,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Pengajuan tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil hasil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get certificate of a safety induction
     *
     * @OA\Get(
     *     path="/api/safety-inductions/{id}/certificate",
     *     summary="Get certificate of a safety induction",
     *     tags={"Safety Induction"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the safety induction",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sertifikat berhasil diambil"),
     *             @OA\Property(property="data", ref="#/components/schemas/CertificateModel")
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
     *         description="Induction or certificate not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sertifikat tidak ditemukan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil sertifikat"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function getCertificate($id)
    {
        try {
            $induction = SafetyInduction::findOrFail($id);
            $user = Auth::user();

            if ($induction->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $certificate = Certificate::where('safety_induction_id', $id)->firstOrFail();

            return response()->json([
                'message' => 'Sertifikat berhasil diambil',
                'data' => $certificate,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Sertifikat tidak ditemukan',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil sertifikat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's safety induction submissions
     *
     * @OA\Get(
     *     path="/api/safety-inductions/my-submissions",
     *     summary="Get user's safety induction submissions",
     *     tags={"Safety Induction"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pengajuan berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/SafetyInductionModel")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil pengajuan"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function mySubmissions()
    {
        try {
            $submissions = SafetyInduction::with(['attempts', 'certificate'])
                ->where('user_id', Auth::id())
                ->get();

            return response()->json([
                'message' => 'Pengajuan berhasil diambil',
                'data' => $submissions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil pengajuan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate certificate PDF
     *
     * @param SafetyInduction $induction
     * @return string
     */

    protected function generateCertificate(SafetyInduction $induction)
    {
        try {
            Log::info('Generating certificate for induction ID: ' . $induction->id);

            // Ambil nama pengguna dan sederhanakan jika lebih dari 15 karakter
            $fullName = $induction->user->name ?? 'Unknown';
            $nameParts = explode(' ', trim($fullName));
            $simplifiedName = $nameParts[0]; // Ambil kata pertama
            if (count($nameParts) > 1) {
                $simplifiedName .= ' ' . $nameParts[1]; // Tambahkan kata kedua
            }
            if (strlen($fullName) > 15 && count($nameParts) > 2) {
                $initials = array_map(function ($part) {
                    return strtoupper(substr(trim($part), 0, 1));
                }, array_slice($nameParts, 2));
                $simplifiedName .= ' ' . implode(' ', $initials);
            }

            // Tanggal penerbitan dan kadaluarsa
            $issuedDate = now()->format('d M Y'); // 03 Jun 2025
            $expiredDate = now()->addYear()->format('d M Y'); // 03 Jun 2026

            // HTML untuk overlay teks di atas gambar
            $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            width: 100%;
            background-image: url('storage/templates/safety_induction_certificate.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        .text-overlay {
            position: absolute;
            text-align: center;
            width: 100%;
            color: #000;
            font-family: 'Helvetica';
        }
       .name {
            font-size: 58px;
            font-weight: bold;
            margin-top: 528px; /* Nama di tengah vertikal */
        }
        .issued-date {
            font-size: 24px;
            margin-top: 198px; /* Tanggal penerbitan di bawah nama */
        }
        .expired-date {
            font-size: 24px;
            margin-top: 88px; /* Tanggal kadaluarsa di bawah tanggal penerbitan */
        }
    </style>
</head>
<body>
    <div class="text-overlay">
        <div class="name">$simplifiedName</div>
        <div class="issued-date">$issuedDate</div>
        <div class="expired-date">$expiredDate</div>
    </div>
</body>
</html>
HTML;

            // Generate PDF dari HTML
            $pdf = Pdf::loadHTML($html);

            // Set ukuran kertas berdasarkan gambar (misalnya, A4)
            $pdf->setPaper('a4', 'portrait');

            // Simpan PDF ke storage
            $fileName = 'certificates/safety_induction_' . $induction->id . '_' . now()->format('YmdHis') . '.pdf';
            Storage::disk('public')->put($fileName, $pdf->output());

            $url = Storage::url($fileName);
            Log::info('Certificate generated successfully: ' . $url);

            return $url;
        } catch (\Exception $e) {
            Log::error('Error generating certificate for induction ID: ' . $induction->id . ': ' . $e->getMessage());
            throw new \Exception('Failed to generate certificate: ' . $e->getMessage());
        }
    }

    /**
     * Generate a certificate based on name
     *
     * @OA\Post(
     *     path="/api/generate-certificate",
     *     operationId="generateCertificateByName",
     *     tags={"Certificate"},
     *     summary="Generate a safety certificate based on name",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="Name to be printed on the certificate", example="Fahmi Wahyu Alifian")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificate generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Certificate generated successfully"),
     *             @OA\Property(property="certificate_url", type="string", example="http://example.com/certificates/certificate_1625149200.pdf")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to generate certificate"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function generateCertificateByName(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Ambil nama dan sederhanakan jika lebih dari 15 karakter
            $fullName = $request->input('name');
            $nameParts = explode(' ', trim($fullName));
            $simplifiedName = $nameParts[0]; // Ambil kata pertama
            if (count($nameParts) > 1) {
                $simplifiedName .= ' ' . $nameParts[1]; // Tambahkan kata kedua
            }
            if (strlen($fullName) > 15 && count($nameParts) > 2) {
                $initials = array_map(function ($part) {
                    return strtoupper(substr(trim($part), 0, 1));
                }, array_slice($nameParts, 2));
                $simplifiedName .= ' ' . implode(' ', $initials);
            }

            // Tanggal penerbitan dan kadaluarsa
            $issuedDate = now()->format('d M Y'); // 03 Jun 2025
            $expiredDate = now()->addYear()->format('d M Y'); // 03 Jun 2026

            // HTML untuk overlay teks di atas gambar
            $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            width: 100%;
            background-image: url('storage/templates/safety_induction_certificate.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        .text-overlay {
            position: absolute;
            text-align: center;
            width: 100%;
            color: #000;
            font-family: 'Helvetica';
        }
        .name {
            font-size: 58px;
            font-weight: bold;
            margin-top: 528px; /* Nama di tengah vertikal */
        }
        .issued-date {
            font-size: 24px;
            margin-top: 198px; /* Tanggal penerbitan di bawah nama */
        }
        .expired-date {
            font-size: 24px;
            margin-top: 88px; /* Tanggal kadaluarsa di bawah tanggal penerbitan */
        }
    </style>
</head>
<body>
    <div class="text-overlay">
        <div class="name">$simplifiedName</div>
        <div class="issued-date">$issuedDate</div>
        <div class="expired-date">$expiredDate</div>
    </div>
</body>
</html>
HTML;

            // Generate PDF dari HTML
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('a4', 'portrait');

            // Simpan PDF ke storage
            $fileName = 'certificates/custom_certificate_' . now()->format('YmdHis') . '.pdf';
            Storage::disk('public')->put($fileName, $pdf->output());

            $url = Storage::url($fileName);
            Log::info('Custom certificate generated successfully: ' . $url);

            return response()->json([
                'message' => 'Certificate generated successfully',
                'certificate_url' => $url,
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation error for custom certificate: ' . json_encode($e->errors()));
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error generating custom certificate: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to generate certificate',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
