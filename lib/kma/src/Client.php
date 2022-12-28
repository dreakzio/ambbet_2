<?php

namespace Banking\KMA;

use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleHttpClient;
use phpseclib3\Crypt\AES;
use Throwable;

class Client
{
    const REGISTER_SERVICE_URI = '/BAY.KMA/Main.Service/RegisterService.aspx';
    const LOGIN_URI = '/BAY.KMA/Main.Service/resource/Login.aspx';
    const CUSTOMER_SERVICE_URI = '/BAY.KMA/Main.Service/CustomerService.aspx';
    const MOBILE_SERVICE_URI = '/BAY.KMA/Main.Service/MobileService.aspx';

    protected  $cipher;
    protected  $httpClient;

    protected  $loginSessionId = null;
    public  $deviceInfo;

    public function __construct($deviceInfo)
    {
        $this->deviceInfo = $deviceInfo;
        $this->httpClient = new GuzzleHttpClient([
            'base_uri' => 'https://www.krungsrimobile.com/',
            'cookies' => true,
        ]);
        $this->cipher = new AES('cbc');
        $this->cipher->setIV('krungsrimobile00');
    }

    public function login($pin)
    {
        $key = $this->doRequestKey();
        $this->cipher->setKey($key);

        return $this->doLogin($pin);
    }

    protected function doRequestKey()
    {
        return $this->request(self::REGISTER_SERVICE_URI, 'RequestKey')->withEncrypt(false)->send()->body();
    }

    protected function doLogin($pin)
    {
        $this->loginSessionId = Utils::guid();

        return $this->request(self::LOGIN_URI, 'AuthenUserNew')->withForm([
            'custmd5' => $this->deviceInfo['custmd5'],
            'password' => $pin,
            'loginby' => 'Button',
            'logintype' => 'PIN',
            'loginfrom' => 'Returning-PIN',
            'dynamictoken' => $this->deviceInfo['dynamictoken'],
            'isUpdateDynamicToken' => 'False',
        ])->send()->json();
    }

    public function getProfileAccount()
    {
        return $this->request(self::CUSTOMER_SERVICE_URI, 'GetProfileAccount')->send()->json();
    }

    public function listPortfolioSingleAccByCustId()
    {
        return $this->request(self::MOBILE_SERVICE_URI, 'listportfoliosingleaccbycustid')->send()->json();
    }

    public function listAccountByCustIDNew()
    {
        return $this->request(self::MOBILE_SERVICE_URI, 'ListAccountByCustIDNew')->send()->json();
    }

    public function listStatement($accountNo, $page = 0, $pageSize = 25, $startDate = null)
    {
        if ($startDate === null) {
            $startDate = Carbon::now();
        }

        return $this->request(self::MOBILE_SERVICE_URI, 'ListStatement')->withForm([
            'accno' => $accountNo,
            'statementday' => '0',
            'startdate' => $startDate->clone()->subMonths(6)->format('d/m/Y'),
            'enddate' => $startDate->format('d/m/Y'),
            'pageindex' => $page,
            'pagesize' => $pageSize,
        ])->send()->json();
    }

    public function getAccountInfoByAccountNo($accountNo)
    {
        return $this->request(self::MOBILE_SERVICE_URI, 'getaccinfobyaccno')->withForm([
            'accno' => $accountNo,
        ])->send()->json();
    }

    public function preTransfer($form)
    {
        return $this->request(self::MOBILE_SERVICE_URI, 'PreTransfer')->withForm([
            'transactiontype' => empty($form['transactiontype']) ? '3' : $form['transactiontype'],
            'recurringtype' => empty($form['recurringtype']) ? '1' : $form['recurringtype'],
            'fraccno' => $form['fraccno'],
            'tobankcode' => $form['tobankcode'],
            'toaccno' => $form['toaccno'],
            'fixeddeptype' => empty($form['fixeddeptype']) ? '' : $form['fixeddeptype'] ,
            'amount' => $form['amount'],
            'memo' => empty($form['memo']) ? '' : $form['memo'],
            'qrcscanvalue' => empty($form['qrcscanvalue']) ? '' : $form['qrcscanvalue'],
            'fraccid' => empty($form['fromAccountId']) ? '0' : $form['fromAccountId'],
            'toaccid' => empty($form['toAccountId']) ? '0' : $form['toAccountId'],
        ])->send()->json();
    }

    public function confirmTransfer()
    {
        return $this->request(self::MOBILE_SERVICE_URI, 'ConfirmTransfer')->send()->json();
    }

    public function fundTransfer($otprefno, $otppassword, $refNo)
    {
        return $this->request(self::MOBILE_SERVICE_URI, 'FundTransfer')->withForm([
            'otprefno' => $otprefno,
            'otppassword' => $otppassword,
            'refNo' => $refNo,
        ])->send()->json();
    }

    public function decrypt($data)
    {
        try {
            return $this->cipher->decrypt(base64_decode($data));
        } catch (Throwable $e) {
            return null;
        }
    }

    public function encrypt($data)
    {
        try {
            return base64_encode($this->cipher->encrypt($data));
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function request($uri, $serviceName)
    {
        return new Request($this, $uri, $serviceName);
    }

    /**
     * @return string|null
     */
    public function getLoginSessionId()
    {
        return $this->loginSessionId;
    }
}
