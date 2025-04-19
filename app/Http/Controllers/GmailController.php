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
            "access_token" => env('GMAIL_ACCESS'),
            "expires_in" => 3599,
            "refresh_token" => env('GMAIL_REFRESH'),
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
