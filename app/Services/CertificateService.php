<?php

namespace App\Services;

use App\Models\Enrollment;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class CertificateService
{
    public function generateCertificate(Enrollment $enrollment): ?string
    {
        // Ensure enrollment is completed and certificate not already generated
        if ($enrollment->status !== 'completed' || $enrollment->certificate_path !== null) {
            return null;
        }

        // Generate unique certificate number
        $certificateNumber = $this->generateUniqueCertificateNumber();

        // Initialize FPDI
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Add content to PDF
        $pdf->SetXY(10, 30);
        $pdf->Cell(0, 10, 'CERTIFICATE OF COMPLETION', 0, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(10, 50);
        $pdf->Cell(0, 10, 'This certifies that', 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetXY(10, 65);
        $pdf->Cell(0, 10, $enrollment->user->name, 0, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(10, 80);
        $pdf->Cell(0, 10, 'has successfully completed the course', 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetXY(10, 95);
        $pdf->Cell(0, 10, $enrollment->course->name, 0, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY(10, 110);
        $pdf->Cell(0, 10, 'Completion Date: ' . $enrollment->completed_at->format('F j, Y'), 0, 1, 'C');

        if ($enrollment->final_score !== null) {
            $pdf->SetXY(10, 115);
            $pdf->Cell(0, 10, 'Final Score: ' . $enrollment->final_score . '%', 0, 1, 'C');
        }

        $pdf->SetXY(10, 120);
        $pdf->Cell(0, 10, 'Certificate Number: ' . $certificateNumber, 0, 1, 'C');

        // Define storage path
        $fileName = 'certificate_' . $enrollment->user->id . '_' . $enrollment->course->id . '_' . time() . '.pdf';
        $filePath = 'certificates/' . $fileName;

        // Save PDF to storage
        Storage::disk('public')->put($filePath, $pdf->Output('S'));

        // Update enrollment record
        $enrollment->update([
            'certificate_number' => $certificateNumber,
            'certificate_path' => $filePath,
        ]);

        return Storage::disk('public')->url($filePath);
    }

    private function generateUniqueCertificateNumber(): string
    {
        do {
            $number = 'CERT-' . strtoupper(uniqid());
        } while (Enrollment::where('certificate_number', $number)->exists());

        return $number;
    }
}
