<?php
namespace ClianDinabox\HisignSdk\Tests\Services\CallerTest;
use PHPUnit\Framework\TestCase;
use ClianDinabox\HisignSdk\HiSign\HiSign;
use CURLFile;
use Exception;
class AccountTest extends TestCase
{
    private static  $documentID     =   NULL;
    private static  $hiSign         =   NULL;
    private static  $accountInfo    =   NULL;
    public static function setUpBeforeClass():void
    {
        try{
            self::$hiSign = new HiSign(
                NULL,
                "loyiba5863@rykone.com",
                "TesT@3652",
                "sandbox",
                NULL
            );
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
    public function testAccountCreation()
    {
        $accountController  =   self::$hiSign->account();
        $account            =   $accountController->createNewAccount(
            "Test Account",
            "teste@teste.com",
            "11999999999",
            "1990-01-01",
            "00123456789",
            "Test@3652",
            true,
            true,
            "38507a8f-f821-432d-b3ca-ed8e0ccc0461"
        );
    }
    public function testAccountDelete()
    {
        $accountController  =   self::$hiSign->account();
        $account            =   $accountController->deleteAccount(
            $this->accountInfo->id
        );
    }
}