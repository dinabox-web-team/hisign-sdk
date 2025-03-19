<?php
namespace DinaboxWebTeam\HisignSdk\Tests\Services\CallerTest;
use PHPUnit\Framework\TestCase;
use DinaboxWebTeam\HisignSdk\HiSign\HiSign;
use Exception;
class AuthTest extends TestCase
{
    private static  $hiSign;
    public static function setUpBeforeClass():void
    {
        try{
            self::$hiSign = new HiSign(
                null,
                null,
                NULL,
                "sandbox",
                NULL
            );
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
    public function testApiKeyAuth()
    {
        self::$hiSign->auth(
            "loyiba5s863@rykone.com",
            "SK_b920d09bkc3e1k447ak9b72k7215ea25d5cc",
            NULL
        );
        $this->assertNotFalse(self::$hiSign->getAuthStatus());
    }
}