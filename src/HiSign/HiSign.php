<?php
namespace ClianDinabox\HisignSdk\HiSign;
use ClianDinabox\HisignSdk\Users\Users;
use ClianDinabox\HisignSdk\Documents\Documents;
use ClianDinabox\HisignSdk\Auth\Auth;
use ClianDinabox\HisignSdk\Account\Account;
use Exception;
class HiSign
{
    private $apiToken;
    private $bearerToken;
    private $email;
    private $password;
    private $host;
    private $partnerCode;
    private $apikey;
    private $userID;
    public function __construct(
        $apikey         =   null,
        $email          =   null,
        $password       =   null,
        $mode           =   'sandbox', // sandbox or production
        $partnerCode    =   null // optional
    ){
        
        $this->host = $this->buildHost($mode);

        $this->apikey       = $apikey;
        $this->email        = $email;
        $this->password     = $password;
        $this->partnerCode  = $partnerCode;
        if($apikey OR $password){
            $auth = new Auth(
                $this->host,
                $this->email,
                $this->apikey,
                $this->password
            );
            $auth->auth();
            $this->setBearerToken($auth->getBearerToken());
            $this->userID   =   $auth->getUserID();
        }
        
        
        

    }
    public function getAuthStatus(): Bool
    {
        return $this->bearerToken ? true : false;
    }
    private function buildHost($mode)
    {
        return $mode ==='production' ? 'api.hisign.com.br' : 'api.staging.hisign.com.br';
    }
    public function setApiToken(  $apiToken )
    {
        $this->apiToken = $apiToken;
    }
    public function setBearerToken(  $bearerToken )
    {
        if(!is_string($bearerToken)){
            throw new Exception('Bearer Token is required. Empty string given');
        }elseif($bearerToken == ""){
            throw new Exception('Bearer Token is required. Is not a string');
        }
        $this->bearerToken = $bearerToken;
    }
    public function getBearerToken()
    {
        return $this->bearerToken;
    }
    public function users(){
        /*
        *   Users Service
        */
        return new Users($this->apiToken, $this->bearerToken, $this->host);
    }
    public function documents()
    {
        /*
        *   Documents Service
        */
        return new Documents(
            $this->getBearerToken(),
            $this->host,
            $this->partnerCode,
            $this->userID
        );
    }
    public function signatories()
    {
        /*
        *   Signatories Service
        */
        // TODO: Implement Signatories Service
    }
    public function account()
    {
        /*
        *   Account Service
        */
        return new Account(
            $this->host,
            $this->bearerToken,
            $this->partnerCode
        );
    }
    public function auth(
        $email      = null,
        $apikey     = null,
        $password   = null
    )
    {
        $this->email    =   $email;
        $this->apikey   =   $apikey;
        $this->password =   $password;
        $auth = new Auth(
            $this->host,
            $this->email,
            $this->apikey,
            $this->password
        );
        $auth->auth();
        if(!$auth->getBearerToken()){
            throw new Exception('Bearer Token is required. Empty string given');
        }
        $this->setBearerToken($auth->getBearerToken());
        $this->userID   =   $auth->getUserID();
    }
}
