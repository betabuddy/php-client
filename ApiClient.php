<?php

namespace BetaBuddy;

/**
 * BetaBuddy Api Client Sample
 */
class ApiClient
{
    private $apiUrl;
    private $betaId;
    private $secret;
    private $hashingAlgorithm = 'sha1';

    /**
     * Constructor
     * 
     * @param type $apiUrl Url to the BetaBuddy API (no trailing slash)
     * @param type $betaId Your Beta Id
     * @param type $secret Your Secret
     */
    public function __construct($apiUrl, $betaId, $secret)
    {
        $this->apiUrl = $apiUrl;
        $this->betaId = $betaId;
        $this->secret = $secret;
    }


    /**
     * Sign up a user for a chance to participate in the beta.
     * 
     * @param string $moniker Something to uniquely identify the user, usually an email address.
     * @return type
     */
    public function SignupUser($moniker)
    {
        $endpoint = '/user/signup';
        $postdata = array("moniker" => $moniker);
        return $this->executePostRequest($endpoint, $postdata);
    }
    
    public function GetUserByMoniker($moniker)
    {
        $moniker = urlencode($moniker);
        $endpoint = '/user/get_by_moniker/'.$moniker;
        return $this->executeGetRequest($endpoint);
    }
    
    public function GetUserById($id)
    {
        $endpoint = '/user/get_by_id/'.$id;
        return $this->executeGetRequest($endpoint);
    }
    
    public function GetUsers($page=1, $pageSize=25)
    {
        $endpoint = '/user/list/'.$page.'/'.$pageSize;
        return $this->executeGetRequest($endpoint);
    }
    
    public function InviteByMoniker($moniker)
    {
        $endpoint = '/invite';
        $postdata = array("moniker" => $moniker);
        return $this->executePostRequest($endpoint, $postdata);
    }
    
    public function InviteById($id)
    {
        $endpoint = '/invite';
        $postdata = array("id" => $id);
        return $this->executePostRequest($endpoint, $postdata);
    }

    public function ActivateByMoniker($moniker, $token)
    {
        $endpoint = '/activate';
        $postdata = array("moniker" => $moniker, "token" => $token);
        return $this->executePostRequest($endpoint, $postdata);
    }
    
    public function ActivateById($id, $token)
    {
        $endpoint = '/activate';
        $postdata = array("id" => $id, "token" => $token);
        return $this->executePostRequest($endpoint, $postdata);
    }
        
    
    private function executePostRequest($endpoint, array $postdata)
    {
        $curl = getCurlHandle($endpoint);
        
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        $curl_response = curl_exec($curl);
        curl_close($curl);

        return $curl_response;
    }    

    private function executeGetRequest($endpoint)
    {
        $curl = getCurlHandle($endpoint);
        
        $curl_response = curl_exec($curl);
        curl_close($curl);

        return $curl_response;
    } 

    private function getCurlHandle($endpoint)
    {
        $timestamp = microtime(true);
        $url = $this->buildUrl($endpoint, $timestamp );
        $customHeader = $this->getCustomHeaderArray($url, $timestamp);
        
        $curl = curl_init($url);
        
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT ,0); 
        curl_setopt($curl, CURLOPT_TIMEOUT, 400); //timeout in seconds 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER ,false); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $customHeader);

        return $curl;
    }    
    
    private function getCustomHeaderArray($url, $timestamp)
    {
        $hash = $this->getHash($url);
        return array(
                'BetaBuddy-Microtime: '.$timestamp,
                'BetaBuddy-Hash: '.$hash
                );
    }
        
    private function buildUrl($endpoint, $timestamp)
    {
        return $this->apiUrl.'/api/'.$this->betaId.'/'.$timestamp.$endpoint;
    }

    private function getHash($url)
    {
        return hash_hmac($this->hashingAlgorithm, $url, $this->secret);
    }
    
}
