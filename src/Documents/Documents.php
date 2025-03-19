<?php
namespace DinaboxWebTeam\HisignSdk\Documents;
use DinaboxWebTeam\HisignSdk\Services\Caller\Caller;
use Error;
use Exception;
use CURLFile;

/*
*   Interactes to HiSign's Document route
*/
class Documents{
    private $bearerToken;
    private $host;
    private $partnerCode;
    private $userID;
    public function __construct($bearerToken, $host, $partnerCode, $userID = ''){
        if(empty($bearerToken)){
            throw new Error('Bearer token is required');
        }
        if(empty($host)){
            throw new Error('Host is required');
        }
        $this->bearerToken = $bearerToken;
        $this->host = $host;
        $this->partnerCode = $partnerCode;
        $this->userID = $userID;
    }
    /*
    *   Get All Documents
    */
    public function getAll($searchName, $filterActives, $pageNumber, $pageSize)
    {
        try{
            $caller = new Caller(
                'GET',
                'https',
                $this->host,
                '/documents');
            $caller->setQuery([
                'title' => $searchName,
                'active' => $filterActives,
                'page' => $pageNumber,
                'limit' => $pageSize
            ]);
            $caller->setHeader('Authorization', "Bearer {$this->bearerToken}");
            $response = json_decode($caller->getResponseBody());
            $caller->close();
            return $response['data']?? [];
        }catch(Exception $e){
            throw $e;
        }
    }
    public function create($title, $file, $mandatory, $signatories = NULL, $actions = NULL)
    {
        /**
             * Creates a new document with the specified details.
             *
             * @param string $title The title of the document.
             * @param array $files An array of file paths to be uploaded.
             * @param bool $mandatory Whether the document is mandatory or not.
             * @param array $signatories An array of signatories for the document (unused in current implementation).
             * @param array $actions An array of actions associated with the document.
             *
             * @throws Exception If an error occurs during the document creation process.
             *
             * @return void
        */
        try{
            $caller = new Caller(
                'POST',
                'https',
                $this->host,
                '/documents'
                // "webhook.site",
                // "ec09b52e-a415-4b29-bf53-e7daafb5bd11"
                );
            $caller->setHeader('Authorization', "Bearer {$this->bearerToken}");
            $body = [
                'title' => $title,
                'files' => $file,
                'madaritory' => $mandatory?1:0,
            ];
            if($signatories != NULL){
                $caller->setMultiArrayParam($body, $signatories, 'signatories');
            }
            if($actions != NULL){
                $caller->setMultiArrayParam($body, $actions, 'actions');
            }
            $caller->setBody('multipart', $body);
            $caller->request();
            $caller->close();
            return $caller->getResponseBody();
        }catch(Exception $e){
            throw $e;
        }
    }
    public function getDocument($documentID)
    {
        try{
            $caller = new Caller(
                'GET',
                'https',
                $this->host,
                "/documents/{$documentID}");
            $caller->setHeader('Authorization', "Bearer {$this->bearerToken}");
            $caller->request();
            $caller->close();
            if (isset($caller->getResponseBody()->total)){
                return false;
            } else {
                return $caller->getResponseBody();
            }
        }catch(Exception $e){
            throw $e;
        }
    }
    public function update()
    {
        /*
        *   Update a document
        *   Maybe this call ins not needed to be implemented.
        */
    }
    public function factory()
    {
        /*
        * Maybe this call ins not needed to be implemented.
        * If the create method is not be sufficient to create a document
        * and publish it to signatories. This method will be implemented.
        */
    }
    public function send($documentID, $subject, $message)
    {
        /*
        *   Send / publish the documento to be sign
        *   to signatories
        */
        $caller = new Caller(
            'POST',
            'https',
            $this->host,
            "/documents/send");
        $caller->setHeader('Authorization', "Bearer {$this->bearerToken}");
        $caller->setBody('json', [
            'document' => $documentID,
            'subject'   => $subject,
            'message'   => $message
        ]);
        $caller->request();
        $caller->close();
        return $caller->getResponseBody();
    }
    /*
    *   Cancel a document
    */
    public function cancel($documentID)
    {
        $caller = new Caller(
            'PATCH',
            'https',
            $this->host,
            "/documents/cancel/{$documentID}");
        $caller->setHeader('Authorization', "Bearer {$this->bearerToken}");
        $caller->request();
        $caller->close();
        if($caller->getResponseCode() == 200){
            return true;
        } else {
            return false;
        }
    }
    public function selfSing($documentID)
    {
        try{
            $callerDocument = new Caller(
                'GET',
                'https',
                $this->host,
                "/documents/{$documentID}"
            );

            $callerDocument->setHeader('Authorization', "Bearer {$this->bearerToken}");
            $callerDocument->request();
            $callerDocument->close();
            $document =  $callerDocument->getResponseBody();
            $documentVersion = $document->version;
            // find self signatory
            foreach($document->signatories as $signatory){
                if(isset($signatory->user->id)){
                    if($signatory->user->id == $this->userID){
                        $signatoryID = $signatory->id;
                        break;
                    }
                }
            }
            if(!isset($signatoryID)){
                throw new Exception('Self signatory ID not found');
            }
            $files = [];
            foreach($document->files as $file){
                $fileProtocol   =   explode( ':' , $file->url)[0];
                $hostName   =   explode( '/' , $file->url)[2];
                $path       =   explode( '/' , $file->url)[3];
                $extension  =   array_pop(explode( '.' , $file->url));
                $tempFile   =   tempnam(sys_get_temp_dir(), 'doc_').$extension;
                $fileStream =   fopen($tempFile, 'wb');
                $downloader = new Caller(
                    'GET',
                    $fileProtocol,
                    $hostName,
                    $path,
                    $fileStream
                );
                $downloader->request();
                $downloader->close();
                fclose($fileStream);
                //$success = $downloader->getResponseBody();
                if (!$downloader->getResponseBody()) {
                    unlink($tempFile);
                    throw new Exception('Falha no download do arquivo');
                }else{
                    rename($tempFile, $tempFile.'.'.$extension);
                    $tempFile = $tempFile.'.'.$extension;
                }
                $files[] = [
                    "file_dir"  =>  $tempFile,
                    "file_id"   =>  $file->id,
                    "mime_type" =>  $this->getMimeType($tempFile)
                ];
            }
            if(count($files) == 0){
                throw new Exception('Nenhum arquivo foi baixado');
            }
            if(count($files) > 1){
                throw new Exception('Mais de um arquivo foi baixado');
            }
            
            $callerSign = new Caller(
                'POST',
                'https',
                $this->host,
                "/signatories/sign"
            );
            
            $callerSign->setHeader('Authorization', "Bearer {$this->bearerToken}");
            $callerSign->setBody('multipart', [
                'signatory'  =>  $signatoryID,
                'files'      =>  $files[0]['file_id'],
                'signatedFiles'      =>  new CURLFile($files[0]['file_dir'], $files[0]['mime_type']),
                'version'   =>  $documentVersion
            ]);
            $callerSign->request();
            $callerSign->close();
            $this->cleanFiles($files);
            if($callerSign->getResponseCode() == 200 || $callerSign->getResponseCode() == 201){
                return true;
            } else {
                return false;
            }
        }catch(Exception $e){
            throw $e;
        }
    }
    
    public function shareDocumentWhatsapp($signatory, $phoneNumber):void
    {
        $caller = new Caller(
            'POST',
            'https',
            $this->host,
            "/documents/share/whatsapp");
        $caller->setHeader('Authorization', "Bearer {$this->bearerToken}");
        $caller->setBody('json', [
            "signatory" =>  $signatory,
            "phone"     =>  $phoneNumber
        ]);
        $caller->request();
        $caller->close();
    }
    public function shareDocumentSms($signatory, $phoneNumber):void
    {
        $caller = new Caller(
            'POST',
            'https',
            $this->host,
            "/documents/share/sms");
        $caller->setHeader('Authorization', "Bearer {$this->bearerToken}");
        $caller->setBody('json', [
            "signatory" =>  $signatory,
            "phone"     =>  $phoneNumber
        ]);
        $caller->request();
        $caller->close();
    }
    public function delete(String $documentID):bool
    {
        $caller = new Caller(
            'DELETE',
            'https',
            $this->host,
            "/documents/{$documentID}");
        $caller->setHeader('Authorization', "Bearer {$this->bearerToken}");
        $caller->request();
        $caller->close();
        if($caller->getResponseCode() == 200){
            return true;
        } else {
            return false;
        }
    }
    /*
    *   Privated methods
    
    */
    private function getMimeType($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception('Arquivo não encontrado');
        }
    
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (!$finfo) {
            throw new Exception('Não foi possível determinar o tipo do arquivo');
        }
    
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
    
        return $mimeType;
    }
    private function cleanFiles($files){
        foreach($files as $file){
            unlink($file['file_dir']);
        }
    }
}