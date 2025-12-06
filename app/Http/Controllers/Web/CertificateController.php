<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CertificateRequest;
use App\Http\Services\CertificateService;
use App\Models\Certificate;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Display certificate templates.
     */
    public function index()
    {
        // Show template selection page
        return view('certificates.index');
    }

    /**
     * Show the form for creating a new resource (template selection).
     */
    public function create()
    {
        return view('certificates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CertificateRequest $request)
    {
        $data = $request->validated();
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $this->certificateService->handleLogoUpload($request->file('logo'));
        }

        // Generate certificate number if not provided
        if (empty($data['certificate_number'])) {
            $data['certificate_number'] = $this->certificateService->generateCertificateNumber();
        }

        Certificate::create($data);

        return redirect()->route('certificates.index')
            ->with('success', 'Certificate created successfully.');
    }

    /**
     * Display the certificate editor (no database, just template).
     */
    public function show($id = null)
    {
        $template = request()->query('template', 'default');
        
        // If it's a new certificate (from template selection)
        if ($id === 'new' || request()->has('template')) {
            $certificate = (object) [
                'id' => 'new',
                'student_name' => 'Student Name',
                'manager_name' => 'Manager Name',
                'teacher_name' => 'Teacher Name',
                'certificate_number' => $this->certificateService->generateCertificateNumber(),
                'issue_date' => now(),
                'logo_path' => null,
            ];
            
            // Return appropriate template view
            if ($template === 'islamic') {
                return view('certificates.show-islamic', compact('certificate'));
            }
            
            if ($template === 'modern') {
                return view('certificates.show-modern', compact('certificate'));
            }
            
            return view('certificates.show', compact('certificate'));
        }

        // For existing certificates (if any exist)
        $certificate = Certificate::findOrFail($id);
        return view('certificates.show', compact('certificate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Certificate $certificate)
    {
        return view('certificates.edit', compact('certificate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CertificateRequest $request, Certificate $certificate)
    {
        $data = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            $this->certificateService->deleteLogo($certificate->logo_path);
            // Upload new logo
            $data['logo_path'] = $this->certificateService->handleLogoUpload($request->file('logo'));
        } else {
            // Keep existing logo if not updating
            unset($data['logo_path']);
        }

        $certificate->update($data);
        $certificate->refresh();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Certificate updated successfully.',
                'logo_url' => $certificate->logo_path ? asset('storage/' . $certificate->logo_path) : null,
            ]);
        }

        return redirect()->route('certificates.index')
            ->with('success', 'Certificate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate)
    {
        // Delete logo file
        $this->certificateService->deleteLogo($certificate->logo_path);
        
        $certificate->delete();

        return redirect()->route('certificates.index')
            ->with('success', 'Certificate deleted successfully.');
    }

    /**
     * Health check for PDF generation system
     */
    public function healthCheck()
    {
        $checks = [
            'browsershot_available' => false,
            'node_available' => false,
            'images_exist' => false,
            'errors' => [],
        ];

        // Check if Browsershot is available
        try {
            $checks['browsershot_available'] = class_exists(\Spatie\Browsershot\Browsershot::class);
        } catch (\Exception $e) {
            $checks['errors'][] = 'Browsershot class not found: ' . $e->getMessage();
        }

        // Check Node.js
        try {
            $nodeVersion = shell_exec('node --version 2>&1');
            $checks['node_available'] = !empty($nodeVersion) && strpos($nodeVersion, 'v') === 0;
            if (!$checks['node_available']) {
                $checks['errors'][] = 'Node.js not found or not in PATH';
            }
        } catch (\Exception $e) {
            $checks['errors'][] = 'Error checking Node.js: ' . $e->getMessage();
        }

        // Check if images exist
        $logoPath = public_path('logo.png');
        $ketmPath = public_path('ketm5.png');
        $checks['images_exist'] = file_exists($logoPath) && file_exists($ketmPath);
        if (!file_exists($logoPath)) {
            $checks['errors'][] = 'logo.png not found at: ' . $logoPath;
        }
        if (!file_exists($ketmPath)) {
            $checks['errors'][] = 'ketm5.png not found at: ' . $ketmPath;
        }

        return response()->json($checks, $checks['browsershot_available'] && $checks['node_available'] && $checks['images_exist'] ? 200 : 503);
    }

    /**
     * Download certificate as PDF (from form data, no database)
     */
    public function download($id = null)
    {
        try {
            // Get template from request (query string for GET, POST data for POST)
            // For Flutter WebView compatibility, prioritize query string
            $template = request()->query('template') ?? request()->post('template', 'default');
            
            // Get certificate data from request (for new certificates) or database
            if ($id === 'new' || request()->has('student_name')) {
                // Parse issue_date safely
                $issueDate = now();
                if (request('issue_date')) {
                    try {
                        $issueDate = \Carbon\Carbon::parse(request('issue_date'));
                    } catch (\Exception $e) {
                        // If parsing fails, use current date
                        $issueDate = now();
                    }
                }
                
                $certificate = (object) [
                    'student_name' => request('student_name', 'Student Name'),
                    'manager_name' => request('manager_name', 'Manager Name'),
                    'teacher_name' => request('teacher_name', 'Teacher Name'),
                    'certificate_number' => request('certificate_number') ?: $this->certificateService->generateCertificateNumber(),
                    'issue_date' => $issueDate,
                    'logo_path' => null, // Fixed logo.png
                ];
            } else {
                // Try to find in database if it's a numeric ID
                if (is_numeric($id)) {
                    $certificate = Certificate::find($id);
                    if (!$certificate) {
                        abort(404);
                    }
                } else {
                    abort(404);
                }
            }

            return $this->certificateService->generatePdf($certificate, $template);
        } catch (\Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot $e) {
            \Log::error('Browsershot error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => request()->all(),
            ]);
            
            // Return a more helpful error message
            return response()->json([
                'error' => true,
                'message' => 'PDF generation failed. Please ensure Browsershot and Puppeteer are properly installed on the server.',
                'details' => config('app.debug') ? $e->getMessage() : 'Contact administrator for assistance.'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Certificate download error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => request()->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            // Return JSON error for API requests, or abort for browser requests
            if (request()->expectsJson() || request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Failed to generate certificate PDF.',
                    'details' => config('app.debug') ? $e->getMessage() : 'An error occurred while generating the certificate.'
                ], 500);
            }
            
            abort(500, 'Failed to generate certificate: ' . (config('app.debug') ? $e->getMessage() : 'Please contact administrator.'));
        }
    }
}
