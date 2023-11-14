<?php

namespace CartHooks;

class AliyunOssUploader{

    private $post_data;
    private $expire;
    private $dir_prefix;
    private $host;
    
    function __construct($token_data){
        $this->expire = $token_data['expire'];
        $this->dir_prefix = $token_data['dir'];
        $this->host = $token_data['host'];
        
        $this->post_data = [
            'policy' => $token_data['policy'],
            'OSSAccessKeyId' => $token_data['accessid'],
            'success_action_status' => '200',
            'signature' => $token_data['signature'],
        ];
    }

    private function append_multi($boundary, &$post_data, $name, $contents, $headers=[]){
        $post_data .= "--$boundary\r\n";
        $post_data .= "Content-Disposition: form-data; name=\"$name\"\r\n\r\n";
        $post_data .= "$contents\r\n";
    }

    public function upload($file_path){
        $file_name = basename($file_path);
        $file_content = fopen($file_path, 'r');
        $boundary = '----WebKitFormBoundary' . uniqid();
        
        $headers = array(
            "Content-Type: multipart/form-data; boundary=$boundary",
        );
        
        $post_data = '';
        $this->append_multi($boundary, $post_data, 'key', $this->dir_prefix.$file_name);
        
        foreach ($this->post_data as $name => $contents) {
            $this->append_multi($boundary, $post_data, $name, $contents);
        }
    
        
        $post_data .= "--$boundary\r\n";
        $post_data .= "Content-Disposition: form-data; name=\"file\"; filename=\"$file_name\"\r\n";
        $post_data .= "Content-Type: application/octet-stream\r\n\r\n";    
        $footer = "\r\n--$boundary--\r\n";
        
        $ch = curl_init();
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->host,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_READFUNCTION => function($ch, $fd, $length) use ($post_data, $file_content, $footer) {
                static $firstCall = true;
                static $dataSent = false;
        
                // Send the post data on the first call
                if ($firstCall) {
                    $firstCall = false;
                    return $post_data;
                }
        
                // Send the file content
                if (!$dataSent && !feof($file_content)) {
                    $data = fread($file_content, $length);
                    if (feof($file_content)) {
                        $dataSent = true;
                    }
                    return $data;
                }
        
                // Send the footer
                if ($dataSent) {
                    $dataSent = false; // Make sure we don't call this again
                    return $footer;
                }
        
                return ''; // Nothing left to send
            },
            CURLOPT_INFILESIZE => strlen($post_data) + filesize($file_path) + strlen($footer),
            CURLOPT_RETURNTRANSFER => true,
        ));
        
        $output = curl_exec($ch);
        fclose($file_content);  

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        } else {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if($http_code == 200){
                return $file_name;
            }
        }
    }
}