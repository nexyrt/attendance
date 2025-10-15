<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class JobApplicationController
{
     /**
     * Store a new job application
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'nomor_telepon' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'unique:job_applications,email'],
            'posisi' => ['required', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'], // Made optional
            'catatan' => ['nullable', 'string'],
            'files.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Auto-determine department if not provided
            $departmentId = $request->department_id;
            if (!$departmentId) {
                $departmentId = $this->determineDepartmentFromPosition($request->posisi, $request->catatan);
            }

            // Handle file uploads - organized by applicant name
            $filePaths = [];
            if ($request->hasFile('files')) {
                // Create folder name based on applicant name
                $folderName = $this->sanitizeFileName($request->nama);
                $applicantFolder = "job-applications/{$folderName}";
                
                foreach ($request->file('files') as $index => $file) {
                    $fileName = time() . '_' . $index . '_' . $this->sanitizeFileName($file->getClientOriginalName());
                    $filePath = $file->storeAs($applicantFolder, $fileName, 'public');
                    $filePaths[] = $filePath;
                }
            }

            // Create the job application
            $jobApplication = JobApplication::create([
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'nomor_telepon' => $request->nomor_telepon,
                'email' => $request->email,
                'posisi' => $request->posisi,
                'department_id' => $departmentId,
                'sumber' => $request->sumber,
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
                    'status' => $jobApplication->status_penerimaan?->value ?? 'pending',
                    'tanggal_apply' => $jobApplication->tanggal_apply->format('Y-m-d H:i:s'),
                    'file_count' => count($filePaths),
                    'folder_path' => !empty($filePaths) ? dirname($filePaths[0]) : null,
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
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
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
            if ($request->filled('status')) {
                $query->where('status_penerimaan', $request->status);
            }

            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->filled('posisi')) {
                $query->where('posisi', 'like', '%' . $request->posisi . '%');
            }

            if ($request->filled('sumber')) {
                $query->where('sumber', $request->sumber);
            }

            // Search by name or email
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Pagination
            $perPage = $request->integer('per_page', 15);
            $applications = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Format the data
            $formattedData = $applications->getCollection()->map(function ($application) {
                return [
                    'id' => $application->id,
                    'nama' => $application->nama,
                    'email' => $application->email,
                    'posisi' => $application->posisi,
                    'department' => $application->department->name ?? 'N/A',
                    'status' => $jobApplication->status_penerimaan?->value ?? 'pending',
                    'status_label' => $application->status_penerimaan->getLabel(),
                    'sumber' => $application->sumber?->value,
                    'sumber_label' => $application->sumber?->getLabel(),
                    'file_count' => count($application->file_terkait ?? []),
                    'tanggal_apply' => $application->tanggal_apply->format('Y-m-d H:i:s'),
                    'created_at' => $application->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'pagination' => [
                    'current_page' => $applications->currentPage(),
                    'total_pages' => $applications->lastPage(),
                    'total_items' => $applications->total(),
                    'per_page' => $applications->perPage(),
                    'from' => $applications->firstItem(),
                    'to' => $applications->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve job applications',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
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
                    'status_penerimaan' => $application->status_penerimaan->value,
                    'status_label' => $application->status_penerimaan->getLabel(),
                    'status_badge_color' => $application->status_penerimaan->getBadgeColor(),
                    'posisi' => $application->posisi,
                    'department' => [
                        'id' => $application->department->id,
                        'name' => $application->department->name,
                    ],
                    'skor_kandidat' => $application->skor_kandidat,
                    'score_rating' => $application->getScoreRating(),
                    'score_color' => $application->getScoreColor(),
                    'sumber' => $application->sumber?->value,
                    'sumber_label' => $application->sumber?->getLabel(),
                    'daftar_melalui' => $application->daftar_melalui->value,
                    'daftar_melalui_label' => $application->daftar_melalui->getLabel(),
                    'file_terkait' => $application->file_terkait,
                    'file_count' => count($application->file_terkait ?? []),
                    'folder_path' => !empty($application->file_terkait) ? dirname($application->file_terkait[0]) : null,
                    'catatan' => $application->catatan,
                    'tanggal_apply' => $application->tanggal_apply->format('Y-m-d H:i:s'),
                    'days_old' => $application->getDaysOld(),
                    'can_be_processed' => $application->canBeProcessed(),
                    'created_at' => $application->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $application->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Job application not found',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
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
            'status' => ['required', Rule::enum(\App\Enums\ApplicationStatus::class)],
            'skor_kandidat' => ['nullable', 'numeric', 'between:0,10'],
            'catatan' => ['nullable', 'string'],
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

            if ($request->filled('skor_kandidat')) {
                $updateData['skor_kandidat'] = $request->skor_kandidat;
            }

            if ($request->filled('catatan')) {
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
                    'status' => $application->status_penerimaan->value,
                    'status_label' => $application->status_penerimaan->getLabel(),
                    'status_badge_color' => $application->status_penerimaan->getBadgeColor(),
                    'skor_kandidat' => $application->skor_kandidat,
                    'score_rating' => $application->getScoreRating(),
                    'score_color' => $application->getScoreColor(),
                    'catatan' => $application->catatan,
                    'updated_at' => $application->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update job application',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
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
        try {
            $departments = Department::select('id', 'name', 'code')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $departments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve departments',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
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
                'status_options' => collect(\App\Enums\ApplicationStatus::cases())
                    ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]),
                'sumber_options' => collect(\App\Enums\ApplicationSource::cases())
                    ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]),
                'method_options' => collect(\App\Enums\ApplicationMethod::cases())
                    ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]),
            ]
        ]);
    }

    /**
     * Get files list for an application
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getApplicationFiles(int $id): JsonResponse
    {
        try {
            $application = JobApplication::findOrFail($id);
            
            if (empty($application->file_terkait)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No files found for this application'
                ]);
            }

            $files = collect($application->file_terkait)->map(function($filePath) use ($application) {
                $filename = basename($filePath);
                $exists = Storage::disk('public')->exists($filePath);
                $size = $exists ? Storage::disk('public')->size($filePath) : 0;
                
                return [
                    'filename' => $filename,
                    'original_name' => $this->extractOriginalName($filename),
                    'path' => $filePath,
                    'folder' => dirname($filePath),
                    'size' => $size,
                    'size_formatted' => $this->formatBytes($size),
                    'exists' => $exists,
                    'extension' => pathinfo($filename, PATHINFO_EXTENSION),
                    'download_url' => route('api.job-applications.download-file', [
                        'id' => $application->id, 
                        'filename' => $filename
                    ])
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $files,
                'total_files' => $files->count(),
                'total_size' => $this->formatBytes($files->sum('size')),
                'applicant_folder' => !empty($application->file_terkait) ? dirname($application->file_terkait[0]) : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get files',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
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
            
            if (empty($application->file_terkait)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No files found for this application'
                ], 404);
            }

            // Find the file path in the applicant's folder
            $filePath = collect($application->file_terkait)
                ->first(fn($file) => basename($file) === $filename);

            if (!$filePath || !Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            // Get original filename for download
            $originalName = $this->extractOriginalName($filename);

            return Storage::disk('public')->download($filePath, $originalName);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download file',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete a job application
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $application = JobApplication::findOrFail($id);

            // Delete associated files
            if (!empty($application->file_terkait)) {
                foreach ($application->file_terkait as $filePath) {
                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                }

                // Try to delete the applicant's folder if empty
                $folderPath = dirname($application->file_terkait[0]);
                if (Storage::disk('public')->exists($folderPath) && 
                    empty(Storage::disk('public')->files($folderPath))) {
                    Storage::disk('public')->deleteDirectory($folderPath);
                }
            }

            $application->delete();

            return response()->json([
                'success' => true,
                'message' => 'Job application deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete job application',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get application statistics
     *
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        try {
            $totalApplications = JobApplication::count();
            $pendingApplications = JobApplication::where('status_penerimaan', \App\Enums\ApplicationStatus::PENDING)->count();
            $acceptedApplications = JobApplication::where('status_penerimaan', \App\Enums\ApplicationStatus::ACCEPTED)->count();
            $rejectedApplications = JobApplication::where('status_penerimaan', \App\Enums\ApplicationStatus::REJECTED)->count();
            
            $recentApplications = JobApplication::where('created_at', '>=', now()->subDays(30))->count();
            
            $departmentStats = Department::withCount('jobApplications')->get()
                ->map(function($dept) {
                    return [
                        'department' => $dept->name,
                        'count' => $dept->job_applications_count ?? 0
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'total_applications' => $totalApplications,
                    'pending_applications' => $pendingApplications,
                    'accepted_applications' => $acceptedApplications,
                    'rejected_applications' => $rejectedApplications,
                    'recent_applications' => $recentApplications,
                    'acceptance_rate' => $totalApplications > 0 ? round(($acceptedApplications / $totalApplications) * 100, 1) : 0,
                    'department_breakdown' => $departmentStats,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Auto-determine department based on position and context
     *
     * @param string $position
     * @param string|null $notes
     * @return int
     */
    private function determineDepartmentFromPosition(string $position, ?string $notes = null): int
    {
        // Get all departments
        $departments = Department::all();
        
        // Create mappings for common positions to departments
        $positionMappings = [
            // Digital Marketing department keywords
            'digital marketing' => 'Digital Marketing',
            'marketing' => 'Digital Marketing',
            'social media' => 'Digital Marketing',
            'content' => 'Digital Marketing',
            'seo' => 'Digital Marketing',
            'ads' => 'Digital Marketing',
            'campaign' => 'Digital Marketing',
            'copywriter' => 'Digital Marketing',
            
            // Sydital department keywords
            'developer' => 'Sydital',
            'programmer' => 'Sydital',
            'software' => 'Sydital',
            'web' => 'Sydital',
            'mobile' => 'Sydital',
            'frontend' => 'Sydital',
            'backend' => 'Sydital',
            'fullstack' => 'Sydital',
            'ui/ux' => 'Sydital',
            'design' => 'Sydital',
            'system' => 'Sydital',
            
            // Detax department keywords  
            'tax' => 'Detax',
            'accounting' => 'Detax',
            'finance' => 'Detax',
            'bookkeeping' => 'Detax',
            'audit' => 'Detax',
            'financial' => 'Detax',
            
            // HR department keywords
            'hr' => 'HR',
            'human resource' => 'HR',
            'recruitment' => 'HR',
            'admin' => 'HR',
            'office' => 'HR',
            'administrative' => 'HR',
        ];

        $searchText = strtolower($position . ' ' . ($notes ?? ''));

        // Find matching department
        foreach ($positionMappings as $keyword => $deptName) {
            if (str_contains($searchText, $keyword)) {
                $department = $departments->firstWhere('name', $deptName);
                if ($department) {
                    return $department->id;
                }
            }
        }

        // Default to first department if no match found
        $defaultDept = $departments->first();
        return $defaultDept ? $defaultDept->id : 1;
    }

    /**
     * Sanitize filename/folder name to be filesystem safe
     *
     * @param string $name
     * @return string
     */
    private function sanitizeFileName(string $name): string
    {
        // Remove special characters and replace spaces with underscores
        $sanitized = preg_replace('/[^a-zA-Z0-9\s\-_.]/', '', $name);
        $sanitized = preg_replace('/\s+/', '_', $sanitized);
        $sanitized = trim($sanitized, '._-');
        
        // Limit length to prevent filesystem issues
        return substr($sanitized, 0, 50);
    }

    /**
     * Extract original filename from timestamped filename
     *
     * @param string $filename
     * @return string
     */
    private function extractOriginalName(string $filename): string
    {
        // Remove timestamp and index from filename (e.g., "1703123456_0_resume.pdf" -> "resume.pdf")
        $parts = explode('_', $filename, 3);
        return isset($parts[2]) ? $parts[2] : $filename;
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = floor(log($bytes, 1024));
        
        return round($bytes / (1024 ** $power), 1) . ' ' . $units[$power];
    }
}
