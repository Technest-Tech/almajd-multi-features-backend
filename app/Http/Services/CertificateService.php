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
        // Get certificate number, handling both model and stdClass
        $certificateNumber = $certificate instanceof Certificate 
            ? $certificate->certificate_number 
            : ($certificate->certificate_number ?? 'new');
        
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
                ->setOption('printBackground', true)
                ->setOption('preferCSSPageSize', true)
                ->paperSize(210, 200, 'mm') // Custom height ~200mm (shorter than A4's 297mm)
                ->dismissDialogs()
                ->ignoreHttpsErrors();
        });
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

