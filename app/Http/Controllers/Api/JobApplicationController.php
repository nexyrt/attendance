<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class JobApplicationController
{
    //
    public function store(Request $request): JsonResponse
    {
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'nomor_telepon' => 'required|string|max:20',
            'email' => 'required|email|unique:job_applications,email',
            'posisi' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'sumber' => [
                'nullable',
                Rule::in([
                    'instagram', 'facebook', 'linkedin', 'twitter',
                    'jobstreet', 'indeed', 'referral', 'website', 
                    'walk_in', 'other'
                ])
            ],
            'daftar_melalui' => [
                'required',
                Rule::in([
                    'manual', 'email', 'website', 'whatsapp',
                    'social_media', 'referral', 'other'
                ])
            ],
            'catatan' => 'nullable|string',
            'files.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048', // 2MB max per file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle file uploads
            $filePaths = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $index => $file) {
                    $fileName = time() . '_' . $index . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('job-applications', $fileName, 'public');
                    $filePaths[] = $filePath;
                }
            }

            // Create the job application
            $jobApplication = JobApplication::create([
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'nomor_telepon' => $request->nomor_telepon,
                'email' => $request->email,
                'status_penerimaan' => JobApplication::STATUS_PENDING, // Default status
                'posisi' => $request->posisi,
                'department_id' => $request->department_id,
                'sumber' => $request->sumber,
                'daftar_melalui' => $request->daftar_melalui ?? JobApplication::METHOD_MANUAL,
                'file_terkait' => $filePaths,
                'catatan' => $request->catatan,
                'tanggal_apply' => now(),
            ]);

            // Load the department relationship
            $jobApplication->load('department');

            return response()->json([
                'success' => true,
                'message' => 'Job application submitted successfully',
                'data' => [
                    'id' => $jobApplication->id,
                    'nama' => $jobApplication->nama,
                    'email' => $jobApplication->email,
                    'posisi' => $jobApplication->posisi,
                    'department' => $jobApplication->department->name,
                    'status' => $jobApplication->getStatusLabel(),
                    'tanggal_apply' => $jobApplication->tanggal_apply->format('Y-m-d H:i:s'),
                    'file_count' => $jobApplication->getFileCount(),
                ]
            ], 201);

        } catch (\Exception $e) {
            // Clean up uploaded files if database save fails
            if (!empty($filePaths)) {
                foreach ($filePaths as $filePath) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit job application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all job applications with filtering
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = JobApplication::with('department');

            // Apply filters
            if ($request->has('status') && $request->status !== '') {
                $query->where('status_penerimaan', $request->status);
            }

            if ($request->has('department_id') && $request->department_id !== '') {
                $query->where('department_id', $request->department_id);
            }

            if ($request->has('posisi') && $request->posisi !== '') {
                $query->where('posisi', 'like', '%' . $request->posisi . '%');
            }

            if ($request->has('sumber') && $request->sumber !== '') {
                $query->where('sumber', $request->sumber);
            }

            // Search by name or email
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $applications = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $applications->items(),
                'pagination' => [
                    'current_page' => $applications->currentPage(),
                    'total_pages' => $applications->lastPage(),
                    'total_items' => $applications->total(),
                    'per_page' => $applications->perPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve job applications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific job application
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $application = JobApplication::with('department')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $application->id,
                    'nama' => $application->nama,
                    'alamat' => $application->alamat,
                    'nomor_telepon' => $application->nomor_telepon,
                    'email' => $application->email,
                    'status_penerimaan' => $application->status_penerimaan,
                    'status_label' => $application->getStatusLabel(),
                    'posisi' => $application->posisi,
                    'department' => [
                        'id' => $application->department->id,
                        'name' => $application->department->name,
                    ],
                    'skor_kandidat' => $application->skor_kandidat,
                    'score_rating' => $application->getScoreRating(),
                    'sumber' => $application->sumber,
                    'sumber_label' => $application->getSumberLabel(),
                    'daftar_melalui' => $application->daftar_melalui,
                    'daftar_melalui_label' => $application->getDaftarMelaluiLabel(),
                    'file_terkait' => $application->file_terkait,
                    'file_count' => $application->getFileCount(),
                    'catatan' => $application->catatan,
                    'tanggal_apply' => $application->tanggal_apply->format('Y-m-d H:i:s'),
                    'days_old' => $application->getDaysOld(),
                    'created_at' => $application->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $application->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Job application not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update job application status
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => [
                'required',
                Rule::in([
                    'pending', 'interview', 'accepted', 'rejected', 'on_hold'
                ])
            ],
            'skor_kandidat' => 'nullable|numeric|between:0,10',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $application = JobApplication::findOrFail($id);
            
            $updateData = [
                'status_penerimaan' => $request->status,
            ];

            if ($request->has('skor_kandidat')) {
                $updateData['skor_kandidat'] = $request->skor_kandidat;
            }

            if ($request->has('catatan')) {
                $updateData['catatan'] = $request->catatan;
            }

            $application->update($updateData);
            $application->load('department');

            return response()->json([
                'success' => true,
                'message' => 'Job application status updated successfully',
                'data' => [
                    'id' => $application->id,
                    'nama' => $application->nama,
                    'status' => $application->status_penerimaan,
                    'status_label' => $application->getStatusLabel(),
                    'skor_kandidat' => $application->skor_kandidat,
                    'score_rating' => $application->getScoreRating(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update job application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get departments for dropdown
     *
     * @return JsonResponse
     */
    public function getDepartments(): JsonResponse
    {
        $departments = Department::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'data' => $departments
        ]);
    }

    /**
     * Get form options (sources, methods, statuses)
     *
     * @return JsonResponse
     */
    public function getFormOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'status_options' => JobApplication::getStatusOptions(),
                'sumber_options' => JobApplication::getSumberOptions(),
                'method_options' => JobApplication::getMethodOptions(),
            ]
        ]);
    }

    /**
     * Download file
     *
     * @param int $id
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
     */
    public function downloadFile(int $id, string $filename)
    {
        try {
            $application = JobApplication::findOrFail($id);
            
            if (!$application->hasFiles()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No files found for this application'
                ], 404);
            }

            // Find the file path
            $filePath = null;
            foreach ($application->file_terkait as $file) {
                if (basename($file) === $filename) {
                    $filePath = $file;
                    break;
                }
            }

            if (!$filePath || !Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            return Storage::disk('public')->download($filePath);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
