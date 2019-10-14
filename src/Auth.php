<?php

namespace EoinMahon\SageApi;

use Illuminate\Http\Response;
use File;
use GuzzleHttp;

class Auth
{
    public $access_token;
    public $refresh_token;
    public $instance_url='https://api.accounting.sage.com/v3.1';

    protected $authUrl  = "https://oauth.accounting.sage.com/token";
    protected $tokenUrl = "https://oauth.accounting.sage.com/token";

    private $client_id;
    private $client_secret;
	private $token_path;

    public function __construct($client_id, $client_secret,$token_path)
    {
        $this->client_id        = $client_id;
        $this->client_secret    = $client_secret;
		$this->token_path 		= $token_path;
		
		$this->refreshToken();
    }
    public function refreshToken()
    {
		
		$this->refresh_token=File::get(storage_path($this->token_path));
		$data =[
            "grant_type"    => 'refresh_token',
            "client_id"     => $this->client_id,
            "client_secret" => $this->client_secret,
            "refresh_token" => $this->refresh_token,
        ];
		$client = new GuzzleHttp\Client(['base_uri' => $this->tokenUrl]);
		$response = $client->request('POST', '',['form_params'=>$data]);
		
        return $this->parseResponse($response);
    }

    public function loginCallback($redirect_uri, $code)
    {
        return $this->parseResponse(Zttp::asFormParams()->post($this->tokenUrl, [
            "grant_type"    => "authorization_code",
            "client_id"     => $this->client_id,
            "client_secret" => $this->client_secret,
            "redirect_uri"  => $redirect_uri,
            "code"          => $code
        ]));
    }

    public function saveAuthKeys($basics)
    {
        $this->access_token     = $basics->access_token;
        $this->refresh_token    = $basics->refresh_token;
		File::replace(storage_path($this->token_path),$basics->refresh_token);
        return $this;
    }

    public function setAuthHeaders()
    {
        return [
            "Authorization" => "Bearer {$this->access_token}", "Content-Type" => "application/json"
        ];
    }

    protected function parseResponse($response)
    {
        
        return $this->saveAuthKeys(json_decode($response->getBody()));
    }
}
