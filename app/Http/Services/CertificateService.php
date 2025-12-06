<?php

namespace App\Http\Services;

use App\Models\Certificate;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CertificateService
{
    /**
     * Generate a unique certificate number
     */
    public function generateCertificateNumber(): string
    {
        do {
            $number = 'CERT-' . strtoupper(Str::random(8));
        } while (Certificate::where('certificate_number', $number)->exists());

        return $number;
    }

    /**
     * Generate PDF for a certificate
     */
    public function generatePdf(object $certificate, string $template = 'default')
    {
        try {
            // Get certificate number, handling both model and stdClass
            $certificateNumber = $certificate instanceof Certificate 
                ? $certificate->certificate_number 
                : ($certificate->certificate_number ?? 'new');
            
            // Ensure issue_date is a Carbon instance
            if (isset($certificate->issue_date) && !($certificate->issue_date instanceof \Carbon\Carbon)) {
                try {
                    $certificate->issue_date = \Carbon\Carbon::parse($certificate->issue_date);
                } catch (\Exception $e) {
                    $certificate->issue_date = now();
                }
            } elseif (!isset($certificate->issue_date)) {
                $certificate->issue_date = now();
            }
            
            // Select appropriate template view
            if ($template === 'islamic') {
                $templateView = 'certificates.template-islamic';
            } elseif ($template === 'modern') {
                $templateView = 'certificates.template-modern';
            } else {
                $templateView = 'certificates.template';
            }
            
            return Pdf::view($templateView, [
                'certificate' => $certificate,
            ])
            ->format('a4')
            ->name('certificate-' . $certificateNumber . '.pdf')
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->margins(0, 0, 0, 0)
                    ->showBackground()
                    ->waitUntilNetworkIdle()
                    ->timeout(120) // Increase timeout to 120 seconds
                    ->setOption('printBackground', true)
                    ->setOption('preferCSSPageSize', true)
                    ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox']) // Add for server environments
                    ->paperSize(210, 200, 'mm') // Custom height ~200mm (shorter than A4's 297mm)
                    ->dismissDialogs()
                    ->ignoreHttpsErrors();
            });
        } catch (\Exception $e) {
            \Log::error('PDF generation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'certificate' => $certificate,
                'template' => $template,
            ]);
            throw $e;
        }
    }

    /**
     * Handle logo upload
     */
    public function handleLogoUpload($file): ?string
    {
        if (!$file) {
            return null;
        }

        $path = $file->store('certificates/logos', 'public');
        return $path;
    }

    /**
     * Delete logo file
     */
    public function deleteLogo(?string $logoPath): void
    {
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            Storage::disk('public')->delete($logoPath);
        }
    }
}

