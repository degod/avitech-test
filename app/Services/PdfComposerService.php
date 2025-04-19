<?php

namespace App\Services;

use Barryvdh\Snappy\Facades\SnappyPdf;

class PdfComposerService
{
    /**
     * Compose PDFs for an email chain, using real emails if provided,
     * otherwise falling back to static sample data.
     *
     * @param string $htmlContent   The HTML content to append to email bodies.
     * @param string $base64Image   A base64-encoded image string to include.
     * @param array  $realEmails    Array of real emails with keys [from, subject, body].
     * @return array                Array of saved PDF file paths.
     */
    public function composeEmailChain(string $htmlContent, string $base64Image, array $realEmails = []): array
    {
        $pdfPaths = [];

        if ($this->hasValidRealEmails($realEmails)) {
            // Ensure at least 25 messages by duplicating
            $emails = $this->duplicateUntilCount($realEmails, 25);
            $emails = array_slice($emails, 0, 25);
            // Chunk into groups of 2 per PDF
            $chunks = array_chunk($emails, 2);

            foreach ($chunks as $index => $chunk) {
                // Determine sender and receiver from the first two unique headers
                [$sender, $receiver] = $this->determineParticipants($chunk);
                $emailChain = [];
                $msgTotal = count($chunk);

                foreach ($chunk as $i => $emailData) {
                    $isEven = (($i + 1) % 2 === 0);
                    $from = $isEven ? $receiver : $sender;
                    $to   = $isEven ? $sender : $receiver;
                    $timestamp = now()
                        ->subDays($msgTotal - ($i + 1))
                        ->format('D, M d, Y \a\t h:i A');

                    // Prepend real email body, then append HTML content and images
                    $fullBody = $emailData['body']
                        . $htmlContent
                        . str_repeat("<img src='{$base64Image}' style='max-width:100%;height:auto;'/>", 3);

                    $emailChain[] = [
                        'from_name'  => $from['name'],
                        'from_email' => $from['email'],
                        'to_name'    => $to['name'],
                        'to_email'   => $to['email'],
                        'timestamp'  => $timestamp,
                        'body'       => $fullBody,
                        'head'       => ($index === 0 && $i === 0),
                    ];
                }

                $pdfPaths[] = $this->createPdf($emailChain, $index + 1);
            }
        } else {
            // Fallback to original static logic
            for ($j = 1; $j <= 13; $j++) {
                $emailChain = [];
                $sender   = ['name' => 'Avitech Hr', 'email' => 'avitech.hr@gmail.com'];
                $receiver = ['name' => 'Godwin Uche', 'email' => 'godwinseeyou@gmail.com'];
                $msgTotal = 2;

                for ($i = 1; $i <= $msgTotal; $i++) {
                    $from = $i % 2 === 0 ? $sender : $receiver;
                    $to   = $i % 2 === 0 ? $receiver : $sender;
                    $timestamp = now()
                        ->subDays($msgTotal - $i)
                        ->format('D, M d, Y \a\t h:i A');

                    $fullBody = $htmlContent
                        . str_repeat("<img src='{$base64Image}' style='max-width:100%;height:auto;'/>", 3);

                    $emailChain[] = [
                        'from_name'  => $from['name'],
                        'from_email' => $from['email'],
                        'to_name'    => $to['name'],
                        'to_email'   => $to['email'],
                        'timestamp'  => $timestamp,
                        'body'       => $fullBody,
                        'head'       => ($j === 1 && $i === 1),
                    ];
                }

                $pdfPaths[] = $this->createPdf($emailChain, $j);
            }
        }

        return $pdfPaths;
    }

    /**
     * Check if realEmails has at least two distinct 'from' addresses.
     */
    private function hasValidRealEmails(array $emails): bool
    {
        if (count($emails) < 1) {
            return false;
        }
        $addresses = array_unique(array_map(fn($e) => $this->parseNameEmail($e['from'])['email'], $emails));
        return count($addresses) >= 2;
    }

    /**
     * Duplicate the array until it has at least $count items.
     */
    private function duplicateUntilCount(array $emails, int $count): array
    {
        $result = $emails;
        while (count($result) < $count) {
            $result = array_merge($result, $emails);
        }
        return $result;
    }

    /**
     * Determine sender and receiver based on the first two items in the chunk.
     * Returns an array: [sender, receiver]
     */
    private function determineParticipants(array $chunk): array
    {
        $first  = $this->parseNameEmail($chunk[0]['from']);
        $second = isset($chunk[1])
            ? $this->parseNameEmail($chunk[1]['from'])
            : $first;

        return [
            ['name' => $first['name'],  'email' => $first['email']],
            ['name' => $second['name'], 'email' => $second['email']],
        ];
    }

    /**
     * Parse a "Name <email>" string into an associative array.
     */
    private function parseNameEmail(string $input): array
    {
        if (preg_match('/^(.*?)\s*<(.+?)>$/', $input, $matches)) {
            return ['name' => trim($matches[1]), 'email' => trim($matches[2])];
        }
        return ['name' => $input, 'email' => $input];
    }

    /**
     * Create and save the PDF for a given email chain, returning the file path.
     */
    private function createPdf(array $emailChain, int $part): string
    {
        $pdf = SnappyPdf::loadView('pdf-viewer', ['emails' => $emailChain]);
        $pdf->setOptions([
            'page-size'               => 'A4',
            'enable-local-file-access' => true,
            'no-images'               => false,
        ]);

        $path = public_path("document_part{$part}.pdf");
        $pdf->save($path);

        return $path;
    }
}
