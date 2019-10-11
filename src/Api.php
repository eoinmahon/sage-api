<?php

namespace EoinMahon\SageApi;

use Illuminate\Http\Response;
use GuzzleHttp;

class Api
{
    public $log      = [];
    public $auth;

    const PATCH_METHOD = 'patch';

    public function __construct(Auth $auth)
    {
        $this->auth         = $auth;
    }

    public function find($resource, $id)
    {
        $response = $this->call('get', $this->urlForResource("{$resource}/{$id}"));
        return $response;
    }

    public function get($resource, $query = '', $fields = ["Id", "Name"])
    {
		$response = $this->call('get', $this->urlForResource("{$resource}"));
        return $response;
    }
    public function put($resource, $id,$data)
    {
        $response = $this->call('put', $this->urlForResource("{$resource}/{$id}"), $data);
        return $response;
    }
    public function post($resource, $data)
    {
        $response = $this->call('post', $this->urlForResource($resource), $data);
        return $response;
    }

    public function patch($resource, $id, $data)
    {
        $response = $this->call(static::PATCH_METHOD, $this->urlForResource("{$resource}/{$id}"), $data);
        return $response ;
    }

    public function delete($resource, $id)
    {
        return $this->call('delete', $this->urlForResource("{$resource}/{$id}"));
    }

    protected function call($method, $url, $data = null)
    {	
		$client = new GuzzleHttp\Client(['base_uri' => $url,'headers' => $this->auth->getAuthHeaders()]);
		$response = $client->request(strtoupper($method), '',['body'=>$data]);
        return $response;
    }

    protected function urlForResource($resource)
    {
		return "{$this->auth->instance_url}/{$resource}";
		
    }

    protected function urlForQueries()
    {
        //return "{$this->auth->instance_url}/services/data/v40.0/query/";
		return "{$this->auth->instance_url}/sales_invoices";
    }

    protected function log($message)
    {
        array_push($this->log, $message);
    }

    protected function getCollection($fields)
    {
        return ($fields instanceof Collection ? $fields->keys() : collect($fields))->implode(',');
    }
}
