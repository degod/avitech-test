<?php

namespace App\Http\Controllers;

use App\Services\PdfReaderService;
use App\Services\ImageService;
use App\Services\PdfComposerService;
use App\Services\PdfMergerService;
use Illuminate\Support\Facades\File;

class PDFController extends Controller
{
    public function generatePdf(
        PdfReaderService $readerService,
        ImageService $imageService,
        PdfComposerService $composerService,
        PdfMergerService $mergerService
    ) {
        try {
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

            $pdfPaths = $composerService->composeEmailChain($content, $base64Image);

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
}

