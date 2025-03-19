<?php
    namespace DinaboxWebTeam\HisignSdk\Services\Caller;
    use Exception;

    class Caller{
        private $hostname;
        private $path;
        private $query;
        private $protocol;
        private $method;
        private $headers;
        private $curl;
        private $file;
        private $responseHeaders;
        private $responseInfo;
        private $responseBody;
        private $responseCode;
        private $responseError;
        public function __construct($method, $protocol, $hostname, $path, $file = NULL)
        {
            try{
                $this->curl     = curl_init();
                $this->hostname = $hostname;
                $this->path     = $path;
                $this->protocol = $protocol;
                $this->method   = $method;
                $this->file     = $file;
                curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
            }catch(Exception $e){
                return new Exception($e->getMessage());
            }
        }
        private function buildQueryString($params)
        {
            try{
                return http_build_query($params);
            }catch(Exception $e){
                return new Exception($e->getMessage());
            }
        }
        private function buildHeaders($headers)
        {
            try{
                foreach ($headers as $key => $value) {
                    $buildedHeaders[] = "{$key}: {$value}";
                }
                return $buildedHeaders;
            }catch(Exception $e){
                return new Exception($e->getMessage());
            }
        }
        private function buildUrl()
        {
            try{
                $url = $this->protocol . '://' . $this->hostname;
                if (!empty($this->path)) {
                    $url .= '/' . ltrim($this->path, '/');
                }
                if (!empty($this->query)) {
                    $url .= '?' . ltrim($this->buildQueryString($this->query), '?');
                }
                return $url;
            }catch(Exception $e){
                return new Exception($e->getMessage());
            }
        }
        public function setHeader($key, $value)
        {
            $this->headers[$key] = $value;
        }

        public function setQuery($query)
        {
            $this->query = $query;
        }
        public function setBody($type, $body)
        {
            if($type == 'json'){
                $this->headers['Content-Type'] = 'application/json';
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($body));
            }else if ($type == 'form'){
                $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->buildQueryString($body));
            } elseif ($type == 'multipart'){
                $this->headers['Content-Type'] ='multipart/form-data';
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
            }
        }
        public function setMultiArrayParam(&$bodyArray, $dataArray, $formKeyName)
        {
            foreach($dataArray as $index => $data){
                foreach($data as $key => $value){
                    $bodyArray["{$formKeyName}[{$index}][{$key}]"] = $value;
                }
            }
        }
        public function request()
        {
            try{
                if($this->headers){
                    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->buildHeaders($this->headers));
                }
                curl_setopt($this->curl, CURLOPT_URL, $this->buildUrl());
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
                if($this->file){
                    curl_setopt($this->curl, CURLOPT_HEADER, false);
                    curl_setopt($this->curl, CURLOPT_FILE, $this->file);
                    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, true);
                } else {
                    curl_setopt($this->curl, CURLOPT_HEADER, true);
                }
                curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
                $response               =   curl_exec($this->curl);
                $this->responseError    =   curl_error($this->curl);
                $this->responseCode     =   curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
                $this->responseInfo     =   curl_getinfo($this->curl);
                

                $headers = explode("\r\n", trim($response )) ?? [];
                array_pop($headers);
                $this->responseHeaders  =   $headers;
                $this->responseBody     =   substr($response, curl_getinfo($this->curl, CURLINFO_HEADER_SIZE));
                if ($this->responseError) {
                    throw new Exception("cURL Error: " . $this->responseError);
                }
            }catch(Exception $e){
                throw $e;
            }
        }
        public function close()
        {
            curl_close($this->curl);
        }
        public function getResponseHeaders()
        {
            try{
                return $this->responseHeaders;
            }catch(Exception $e){
                return new Exception($e->getMessage());
            }
        }
        public function getResponseBody()
        {
            try{
                if($this->checkHeadersResponse('Content-Type: application/json')){
                    return json_decode($this->responseBody);
                }else{
                    return $this->responseBody;
                }
            }catch(Exception $e){
                return new Exception($e->getMessage());
            }
        }
        public function getResponseCode()
        {
            try{
                return $this->responseCode;
            }catch(Exception $e){
                return new Exception($e->getMessage());
            }
        }
        public function checkHeadersResponse($stringSearch)
        {
            if(is_array($this->responseHeaders)){
                $results = array_filter($this->responseHeaders, function($item) use ($stringSearch) {
                    return strpos(strtolower($item), strtolower($stringSearch)) === 0;
                });
                return !empty($results);
            } else {
                return false;
            }
        
        }
    }