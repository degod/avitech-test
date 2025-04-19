<?php

namespace App\Services;

use Google_Client;
use Google_Service_Gmail;

class GmailService
{
    public function getClient()
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/google/credentials.json'));
        $client->addScope(Google_Service_Gmail::GMAIL_READONLY);
        $client->setRedirectUri(route('google.callback'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        return $client;
    }

    public function listEmails($token, $email)
    {
        $client = $this->getClient();
        $client->setAccessToken($token);

        $service = new Google_Service_Gmail($client);

        $messagesResponse = $service->users_messages->listUsersMessages('me', [
            'maxResults' => 1,
            'q' => "from:{$email}",
        ]);
        
        $messages = $messagesResponse->getMessages();
        if (empty($messages)) {
            return ['message' => "No emails found from {$email}"];
        }
        $latestMessage = $service->users_messages->get('me', $messages[0]->getId());
        $threadId = $latestMessage->getThreadId();

        $thread = $service->users_threads->get('me', $threadId);
        $messages = $thread->getMessages();

        $emails = [];

        foreach ($messages as $message) {
            $msg = $service->users_messages->get('me', $message->getId());
            $payload = $msg->getPayload();
            $headers = $payload->getHeaders();

            $subject = '';
            $from = '';
            $body = '';

            foreach ($headers as $header) {
                if ($header->getName() === 'Subject') {
                    $subject = $header->getValue();
                }
                if ($header->getName() === 'From') {
                    $from = $header->getValue();
                }
            }
            $body = $this->getBodyFromPayload($payload);

            $emails[] = [
                'from' => $from,
                'subject' => $subject,
                'body' => $body,
            ];
        }

        return $emails;
    }

    private function getBodyFromPayload($payload)
    {
        $bodyData = $payload->getBody()->getData();
        if ($bodyData) {
            return $this->decodeBody($bodyData);
        }
        $parts = $payload->getParts();

        foreach ($parts as $part) {
            if ($part->getMimeType() === 'text/html') {
                $data = $part->getBody()->getData();
                if ($data) {
                    return $this->decodeBody($data);
                }
            }

            if ($part->getParts()) {
                foreach ($part->getParts() as $subPart) {
                    if ($subPart->getMimeType() === 'text/html') {
                        $data = $subPart->getBody()->getData();
                        if ($data) {
                            return $this->decodeBody($data);
                        }
                    }
                }
            }
        }

        foreach ($parts as $part) {
            if ($part->getMimeType() === 'text/plain') {
                $data = $part->getBody()->getData();
                if ($data) {
                    return nl2br(e($this->decodeBody($data)));
                }
            }
        }

        return '[No body found]';
    }

    private function decodeBody($data)
    {
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        return base64_decode($data);
    }
}
