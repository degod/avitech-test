<?php

namespace App\Http\Controllers;

use App\Services\GmailService;
use App\Services\ImageService;
use App\Services\PdfComposerService;
use App\Services\PdfMergerService;
use App\Services\PdfReaderService;
use Illuminate\Support\Facades\File;

class PDFController extends Controller
{
    public function generatePdf(
        PdfReaderService $readerService,
        ImageService $imageService,
        PdfComposerService $composerService,
        PdfMergerService $mergerService,
        GmailService $gmailService
    ) {
        try {
            $otherEmails = $this->gmailEmails('godwinseeyou@gmail.com', $gmailService) ?? [];

            $readerService->setPath(public_path('assets/Content- 3500KB.pdf'));
            $content = $readerService->getHtmlContent();

            if (!$content) {
                return response()->json(['error' => 'Failed to extract PDF content.'], 500);
            }

            $outputDir = storage_path('app/pdf-html');
            $publicStorageDir = public_path('storage/pdf-html');
            $imageFiles = glob($outputDir . '/*.png');
            foreach ($imageFiles as $imageFile) {
                $newImagePath = $publicStorageDir . '/' . basename($imageFile);
                if (!File::exists($newImagePath)) {
                    File::move($imageFile, $newImagePath);
                }
            }

            $content = preg_replace_callback('/src="([^"]+)"/', function ($matches) {
                $url = $matches[1];
                if (str_contains($url, '/storage/pdf-html/')) {
                    $fileName = basename($url);
                    $absolutePath = public_path('storage/pdf-html/' . $fileName);
                    if (file_exists($absolutePath)) {
                        return 'src="file://' . $absolutePath . '"';
                    }
                }
                return $matches[0];
            }, $content);

            $imagePath = $imageService->generate();
            $base64 = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath);
            $base64Image = "data:{$mimeType};base64,{$base64}";

            $pdfPaths = $composerService->composeEmailChain($content, $base64Image, $otherEmails);

            $finalPath = public_path('document_merged.pdf');
            $mergerService->merge($pdfPaths, $finalPath);

            foreach ($pdfPaths as $part) {
                File::delete($part);
            }

            return "PDF stored here: public/document_merged.pdf";
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    public function gmailEmails(string $correspondEmail, GmailService $gmail){
        $token = [
            "access_token" => env('GMAIL_ACCESS'),
            "expires_in" => 3599,
            "refresh_token" => env('GMAIL_REFRESH'),
            "scope" => "https://www.googleapis.com/auth/gmail.readonly",
            "token_type" => "Bearer",
            "refresh_token_expires_in" => 604799,
            "created" => 1745070180
        ];
        $email = $correspondEmail; //'godwinseeyou@gmail.com';
        $emails = $gmail->listEmails($token, $email);

        return $emails;
    }
}

