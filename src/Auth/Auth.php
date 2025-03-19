<?php
namespace ClianDinabox\HisignSdk\Auth;
use ClianDinabox\HisignSdk\Services\Caller\Caller;
use Exception;
use Error;
class Auth{
    private $apiKey;
    private $bearerToken;
    private $apiToken;
    private $email;
    private $password;
    private $host;
    private $userID;
    public function __construct($host, $email, $apiKey = NULL, $password = NULL)
    {
        if(!$apiKey && !$password){
            throw new Exception('HiSign SDK needs either API Token, or Password to be set. Either set API Token, Bearer Token or Password in the constructor or use setters. 
            Mode is optional and defaults to sandbox');
        }
        $this->host         = $host;
        $this->email        = $email;
        $this->apiKey       = $apiKey;
        $this->password     = $password;
        
    }
    public function auth()
    {
        if($this->password){
            $caller = new Caller(
                'POST',
                'https',
                $this->host,
                '/auth/login'
            );
            $caller->setBody('json', [
                'username'    => $this->email,
                'password' => $this->password,
            ]);
            $caller->request();
            if($caller->getResponseCode() != 200 && $caller->getResponseCode() != 201){
                throw new Error("Server responds: {$caller->getResponseCode()}. Maybe the credentials are wrong");
            }elseif(!$caller->getResponseBody()->access_token){
                throw new Error('Server does not return a valid token.');
            }
            $this->bearerToken  =   $caller->getResponseBody()->access_token;
            $this->userID       =   $caller->getResponseBody()->id;
        }else{
            $caller = new Caller(
                'POST',
                'https',
                $this->host,
                '/auth/apikey'
            );
            $caller->setBody('json', [
                'email'    => $this->email,
                'apikey' => $this->apiKey,
                'expires' => 3000
            ]);
            $caller->request();
            $this->apiToken = $caller->getResponseBody()->token;
            $caller2 = new Caller(
                'POST',
                'https',
                $this->host,
                '/auth/token'
            );
            $caller2->setBody("json", [
                'token' => $this->apiToken,
                'expires' => 3000,
            ]);
            $caller2->request();
            $this->bearerToken  =   $caller2->getResponseBody()->access_token;
            $this->userID       =   $caller2->getResponseBody()->id;
        }
    }
    public function getBearerToken()
    {
        // Authenticate with HiSign API
        // Return Bearer Token
        return $this->bearerToken;
    }
    public function getUserID()
    {
        // Authenticate with HiSign API
        // Return User ID
        return $this->userID;
    }

}