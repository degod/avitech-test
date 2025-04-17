<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PdfReaderService
{
    private string $pdfPath;
    private string $outputDir;
    private string $publicStorageDir;

    public function __construct()
    {
        $this->outputDir = storage_path('app/pdf-html');
        $this->publicStorageDir = public_path('storage/pdf-html');
    }

    public function setPath(string $pdfPath): void
    {
        if (!File::exists($pdfPath)) {
            throw new \InvalidArgumentException("PDF file does not exist at path: {$pdfPath}");
        }

        $this->pdfPath = $pdfPath;
    }

    public function getHtmlContent(): ?string
    {
        set_time_limit(600);

        try {
            if (!File::exists($this->outputDir)) {
                File::makeDirectory($this->outputDir, 0755, true);
            }
            File::cleanDirectory($this->outputDir);

            if (!File::exists($this->publicStorageDir)) {
                File::makeDirectory($this->publicStorageDir, 0755, true);
            }

            $command = "pdftohtml -c -hidden -zoom 1.5 -noframes \"{$this->pdfPath}\" \"{$this->outputDir}/output.html\"";
            exec($command);

            $htmlPath = "{$this->outputDir}/output.html";
            if (!File::exists($htmlPath)) {
                throw new \RuntimeException("Failed to convert PDF to HTML. Output HTML not found.");
            }

            $html = File::get($htmlPath);
            $html = preg_replace_callback('/src="(output\d+\.png)"/', function ($matches) {
                return 'src="' . asset('storage/pdf-html/' . $matches[1]) . '"';
            }, $html);

            return explode('<hr/>', $html)[0] ?? $html;
        } catch (\Throwable $e) {
            Log::error('PDF read failed: ' . $e->getMessage());
            return null;
        }
    }
}
