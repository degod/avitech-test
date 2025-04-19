<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GmailService;
use Illuminate\Support\Facades\Session;

class GmailController extends Controller
{
    public function __construct(private GmailService $gmail){}

    public function googleAuth(){
        $client = $this->gmail->getClient();
        $authUrl = $client->createAuthUrl();
        return redirect($authUrl);
    }
    
    public function googleCallback(){
        $client = $this->gmail->getClient();
        $client->fetchAccessTokenWithAuthCode(request('code'));

        $token = $client->getAccessToken();
        Session::put('gmail_token', $token);

        return redirect()->route('gmail.emails');
    }
    
    public function gmailEmails(string $otherEmail){
        // $token = Session::get('gmail_token');
        $token = [
            "access_token" => "ya29.a0AZYkNZgqzp8Mj4Xg6V2NldIJKwYbRwP5YFWThnh3xnBQT7-GjfmCIBvdw6XdWJaqGbA7keHQyZLGVgppGHm813hIJiEewkULH4QGOmGjdrpHAne0V0G00fpFwwDcQ8kkMHfOT0gX8fN4TE1tyxiNmeq5pK4nma__2fXbCvfJaCgYKAcESARASFQHGX2MiSsB34RUhOaggG0zyY8oVUg0175",
            "expires_in" => 3599,
            "refresh_token" => "1//034HuiFmiPmPdCgYIARAAGAMSNwF-L9Ir1NXo9TjjOi_CW-Vi61vqgpRxq-ZIulfJaQEX3OOR9mS8hNO3_2qSZiyGUaqj0r87Qqk",
            "scope" => "https://www.googleapis.com/auth/gmail.readonly",
            "token_type" => "Bearer",
            "refresh_token_expires_in" => 604799,
            "created" => 1745070180
        ];

        // if (!$token) {
        //     return redirect()->route('google.auth');
        // }
        $email = $otherEmail;
        $emails = $this->gmail->listEmails($token, $email);

        return $emails;
    }
}
