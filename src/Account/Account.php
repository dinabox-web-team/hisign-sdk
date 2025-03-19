<?php
namespace ClianDinabox\HisignSdk\Account;
use ClianDinabox\HisignSdk\Services\Caller\Caller;
use Exception;
use Error;
class Account{
    private $host;
    private $bearerToken;
    private $partnerCode;
    public function __construct(
        $host,
        $bearerToken    = null,
        $partnerCode    = null
    )
    {
        $this->host         = $host;
        $this->bearerToken  = $bearerToken;
        $this->partnerCode  = $partnerCode;
    }
    public function createNewAccount(
        $name,
        $email,
        $phone,
        $birthDate,
        $cpf,
        $password,
        $useTermsAccepted,
        $activeToken
    )
    {
        $caller = new Caller(
            'POST',
            'https',
            $this->host,
            '/users'
        );
        if($this->partnerCode){
            $caller->setHeader('partner', $this->partnerCode);
        }
        $caller->setBody("multipart",[
            "name"              => $name,
            "email"             => $email,
            "phone"             => $phone,
            "birthdate"         => $birthDate,
            "cpf"               => $cpf,
            "password"          => $password,
            "useTermsAccepted"  => $useTermsAccepted,
            "activeToken"       => $activeToken,
        ]);
        $caller->request();
        return $caller->getResponseBody();
    }
    public function deleteAccount($userID)
    {
        $caller = new Caller(
            'DELETE',
            'https',
            $this->host,
            '/users/'.$userID
        );
        if($this->partnerCode){
            $caller->setHeader('partner', $this->partnerCode);
        }
        $caller->setHeader('Authorization', 'Bearer '.$this->bearerToken);
        $caller->request();
        return $caller->getResponseBody();
    }
}