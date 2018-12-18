<?php
class api {
    private $headers = false;
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function urlGetContentJSON($url){
        $json = $this->urlGetContent($url);
        return json_decode($json, true);
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function urlGetContentPostJSON($url, $post){
        $json = $this->urlGetContentPost($url, $post);
        return json_decode($json, true);
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function urlGetContentJSON_multi($urls){
        $jsonArray = $this->urlGetContent_multi($urls);
        $return = [];
        foreach ($jsonArray as $json) {
            $return[] = json_decode($json, true);
        }
        return $return;
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function urlGetContent($url, $attempt=0){
        if($attempt > 10){ echo 'url not worked: '.$url; return ''; }
        if($attempt > 0){ sleep(5); }
        $response = $this->curlCall($url);
        $attempt++;
        return (!empty($response)) ? $response : $this->urlGetContent($url, $attempt);
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function urlGetContentPost($url, $post, $attempt=0){
        if($attempt > 10){ echo 'url not worked: '.$url; return ''; }
        if($attempt > 0){ sleep(5); }
        $response = $this->curlCallPost($url, $post);
        $attempt++;
        return (!empty($response)) ? $response : $this->urlGetContentPost($url, $post, $attempt);
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function urlGetContent_multi($urls){
        $response = $this->curlMulti($urls);
        foreach ( $response as $k => $val ){
            if (empty($val)){
                $response[$k] = $this->urlGetContent($urls[$k]);
            }
        }
        return $response;
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function curlCall($url){
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, $this->headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    private function curlCallPost($url, $post){
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    private function curlMulti($urls) {
        $mh = curl_multi_init();
        $connectionArray = array();
        foreach($urls as $key => $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, $this->headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($mh, $ch);
            $connectionArray[$key] = $ch;
        }
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        }while($running > 0);
        $response = array();
        foreach($connectionArray as $key => $ch){
            $response[$key] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);
   
        return $response;
    }
}
