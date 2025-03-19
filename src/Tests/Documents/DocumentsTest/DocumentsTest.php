<?php
namespace DinaboxWebTeam\HisignSdk\Tests\Services\CallerTest;
use PHPUnit\Framework\TestCase;
use DinaboxWebTeam\HisignSdk\HiSign\HiSign;
use CURLFile;
use Exception;
class DocumentsTest extends TestCase
{
    private static  $documentID  =   NULL;
    private static  $hiSign      =   NULL;
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
    public function testDocumentsConstruction()
    {
        $documents = self::$hiSign->documents();
        $this->assertInstanceOf(
            'ClianDinabox\HisignSdk\Documents\Documents',
            $documents
        );
    }
    public function testDocumentCreation()
    {
        
        $signatories = [
            [
                "name" => "Tibério Alaúdes",
                "email" => "ala-tiberio@dinabox.email",
                "signatureType", "DEFAULT", // DEFAULT || WHATSAPP
                "phone" => "11999999999",
                "cpf"   => "00123456789"
            ],[
                "name" => "Felicia Pagac",
                "email" => "loyiba5863@rykone.com",
                "signatureType", "WHATSAPP", // DEFAULT || WHATSAPP
                "phone" => "54991480933",
                "cpf"   => "00123456789"
            ]
        ];
        $documents = self::$hiSign->documents();
        $document = $documents->create(
            "Test Document",
            new CURLFile(__dir__."/file_test.pdf", "application/pdf"),
            0,
            $signatories
        );
        
        $this->assertTrue(
            isset($document->id),
            "The API did not return a document ID. Maybe the document was not created."
        );
        if(isset($document->id)){
            self::$documentID = $document->id;
        }
    }
    public function testDocumentGetByID()
    {
        $documents = self::$hiSign->documents();
        $document = $documents->getDocument(self::$documentID);
        $this->assertTrue(isset($document->id), "Cannot get document by ID: " . self::$documentID);
    }

    public function testDocumentSend()
    {
        $documents = self::$hiSign->documents();
        $document = $documents->send(self::$documentID, "Documento de Teste", "Esse é um Documento de Teste do PHP Unit");
        sleep(2);
        $this->assertTrue(
            isset($document->id),
            "The document was not sent to signatories because Document ID is not set. Maybe document was not sent."
        );
    }
    public function testDocumentSing()
    {
        $documents = self::$hiSign->documents();
        $singStatus = $documents->selfSing(self::$documentID);
        $this->assertTrue(
            $singStatus,
            "Sign returns FALSE. Maybe the document was not signed."
        );
    }
    public function testDocumentCancel()
    {
        $documents = self::$hiSign->documents();
        $status = $documents->cancel(self::$documentID);
        $this->assertTrue(
            $status,
            "The document was not canceled because Document ID is not set. Maybe document was not canceled."
        );
    }
    public function testDocumentDelete()
    {
        $documents = self::$hiSign->documents();
        $status = $documents->delete(self::$documentID);
        $this->assertTrue(
            $status,
            "The document was not deleted. Maybe the document was not deleted."
        );
    }
}