<?php

namespace Banking\KMA;

use GuzzleHttp\Psr7\Query;

class Request
{
    public  $method = 'POST';
    public  $options = [];
    public  $form = [];
    public  $encrypt = true;
    public  $client;
    public  $uri;
    public  $serviceName;

    public function __construct($client, $uri, $serviceName)
    {
		$this->client = $client;
        $this->client->deviceInfo = json_decode(json_encode($client->deviceInfo), true);
        $this->uri = $uri;
        $this->serviceName = $serviceName;
    }

    public function withMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    public function withOptions($options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function withForm($params)
    {
        $this->form = array_merge($this->form, $params);

        return $this;
    }

    public function withEncrypt($encrypt)
    {
        $this->encrypt = $encrypt;

        return $this;
    }

    public function send()
    {
        $options = $this->options;
        $options['form_params'] = $this->buildForm();
        $options['headers'] = $this->buildHeaders();

        return new Response($this, $this->client->getHttpClient()->request($this->method, $this->uri, $options));
    }

    protected function buildHeaders()
    {
        $headers = [
            'APPVERSION' => $this->client->deviceInfo['appversion'],
            'LOGIN_SESSION_ID' => $this->client->getLoginSessionId(),
            'DEVICEREQUEST' => Utils::fileTime(),
            'User-Agent' => 'okhttp/3.12.6',
        ];

        return array_merge(array_filter($headers), (empty($this->options['headers']) ? [] : $this->options['headers']));
    }

    protected function buildForm()
    {
        $deviceInfo = $this->client->deviceInfo;
        $form = [
            'servicename' => $this->serviceName,
            'deviceid' => $deviceInfo['deviceid'],
            'pagecode' => '',
            'opercarrier' => $deviceInfo['opercarrier'],
            'macaddress' => $deviceInfo['macaddress'],
            'deviceserialno' => empty($deviceInfo['deviceserialno']) ? 'unknown' : $deviceInfo['deviceserialno'],
            'deviceos' => $deviceInfo['deviceos'],
            'deviceosversion' => $deviceInfo['deviceosversion'],
            'appversion' => $deviceInfo['appversion'],
            'language' => $deviceInfo['language'],
            'mydeviceguid' => $deviceInfo['mydeviceguid'],
            'devicemodel' => $deviceInfo['devicemodel'],
            'devicebrand' => $deviceInfo['devicebrand'],
            'devicehardware' => $deviceInfo['devicehardware'],
            'devicetoken' => $deviceInfo['devicetoken'],
        ];
        $form = array_merge($this->form,$form);

        return $this->encrypt ? [
            'encrypt' => $this->client->encrypt(Query::build($form)),
        ] : $form;
    }
}
