<?php
namespace ClianDinabox\HisignSdk\Tests\Services\CallerTest;
use PHPUnit\Framework\TestCase;
use ClianDinabox\HisignSdk\Services\Caller\Caller;
use CURLFile;

class CallerTest extends TestCase
{
    public function testCallerConstruction()
    {
        $caller = new Caller(
            'POST', 
            'https', 
            'api.staging.hisign.com.br', 
            '/auth/login', 
        );
        $this->assertInstanceOf(Caller::class, $caller);
    }
    
    public function testCallerPostWithFileUpload()
    {
        $caller = new Caller(
            'POST', 
            'https', 
            'webhook.site', 
            '/fdc79c3f-2968-465e-8489-b401e27fedde'
        );
        $caller->setBody("multipart", [
            'file' => new CURLFile(__DIR__. '/file_test.pdf', 'application/pdf'  ),
            'data' => ['key' => 'value']
        ]);
        $caller->request();
        $response = $caller->getResponseBody();
        $caller->close();
        $this->assertEquals(200, $caller->getResponseCode(), "Response code is not 200: ".json_encode($response));
    }
}
