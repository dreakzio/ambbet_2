<?php

namespace Banking\KMA;

use GuzzleHttp\Utils as GuzzleUtils;
use Psr\Http\Message\ResponseInterface;

class Response
{
    public  $request;
    public  $response;
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function json()
    {
        return GuzzleUtils::jsonDecode($this->body(true), true);
    }

    public function body($chk=false)
    {
		$body = $this->response->getBody()->getContents();
        if ($this->request->encrypt) {
            return $this->request->client->decrypt($body) ?: $body;
        } else if($chk){
			return $this->request->client->decrypt($body);
		}else {
            return $body;
        }
    }

    public function __toString()
    {
        return $this->body();
    }
}
