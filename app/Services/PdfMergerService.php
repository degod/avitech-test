<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

class PdfMergerService
{
    public function merge(array $pdfPaths, string $outputPath): void
    {
        $pdf = new Fpdi();

        foreach ($pdfPaths as $file) {
            $pageCount = $pdf->setSourceFile($file);

            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }
        }

        $pdf->Output('F', $outputPath);
    }
}
