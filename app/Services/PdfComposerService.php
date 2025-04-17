<?php

namespace App\Services;

use Barryvdh\Snappy\Facades\SnappyPdf;

class PdfComposerService
{
    public function composeEmailChain(string $htmlContent, string $base64Image): array
    {
        $pdfPaths = [];

        for ($j = 1; $j <= 13; $j++) {
            $emailChain = [];
            $sender = ['name' => 'Avitech Hr', 'email' => 'avitech.hr@gmail.com'];
            $receiver = ['name' => 'Godwin Uche', 'email' => 'godwinseeyou@gmail.com'];
            $msgTotal = 2;

            for ($i = 1; $i <= $msgTotal; $i++) {
                $from = $i % 2 === 0 ? $sender : $receiver;
                $to = $i % 2 === 0 ? $receiver : $sender;
                $timestamp = now()->subDays($msgTotal - $i)->format('D, M d, Y \a\t h:i A');

                $fullBody = $htmlContent . str_repeat("<img src='{$base64Image}' style='max-width:100%;height:auto;'/>", 3);

                $emailChain[] = [
                    'from_name' => $from['name'],
                    'from_email' => $from['email'],
                    'to_name' => $to['name'],
                    'to_email' => $to['email'],
                    'timestamp' => $timestamp,
                    'body' => $fullBody,
                    'head' => ($j == 1 && $i == 1),
                ];
            }

            $pdf = SnappyPdf::loadView('pdf-viewer', ['emails' => $emailChain]);
            $pdf->setOptions([
                'page-size' => 'A4',
                'enable-local-file-access' => true,
                'no-images' => false,
            ]);

            $path = public_path("document_part{$j}.pdf");
            $pdf->save($path);
            $pdfPaths[] = $path;
        }

        return $pdfPaths;
    }
}
