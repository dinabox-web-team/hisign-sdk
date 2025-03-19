<?php
namespace DinaboxWebTeam\HisignSdk\Users;
use DinaboxWebTeam\HisignSdk\Services\Caller\Caller;
use Error;
use Exception;
class Users{
    private $bearerToken;
    private $host;
    private $partnerCode;
    public function __construct($bearerToken, $host, $partnerCode = null)
    {
        if(empty($bearerToken)){
            throw new Error('Bearer token is required');
        }
        if(empty($host)){
            throw new Error('Host is required');
        }
        $this->bearerToken = $bearerToken;
        $this->host = $host;
        $this->partnerCode = $partnerCode;
    }
    public function create($name, $email, $password, $phone, $birthdate, $cpf, $useTermsAccepted, $activeToken)
    {
        try{
            $caller = new Caller(
                'POST',
                'https',
                $this->host,
                '/users'
            );
            $caller->setBody('multipart',[
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'phone' => $phone,
                'birthdate' => $birthdate,
                'cpf' => $cpf,
                'useTermsAccepted' => $useTermsAccepted,
                'activeToken' => $activeToken,
            ]);
            if($this->partnerCode){
                $caller->setHeader(['partner' => $this->partnerCode]);
            }
            $caller->request();
            $response = $caller->getResponseBody();
            $caller->close();
            return $response;
            
        }catch(Exception $e){
            throw $e;
        }
    }
    public function update()
    {
        /*
        *   Update an User
        */
    }
    
    public function resetPassword()
    {
        /*
        *   Do the password reset to logged user
        */
    }
    public function get($userID)

    {
        /*
        *   Get User
        */
        try{
            $caller = new Caller(
                'GET',
                'https',
                $this->host,
                "/users/{$userID}"
            );
            $caller->request();
            $response = $caller->getResponseBody();
            $caller->close();
            return $response;
            
        }catch(Exception $e){
            throw $e;
        }
    }
}