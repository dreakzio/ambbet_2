<?PHP
	namespace Facebook\WebDriver;
	use Facebook\WebDriver\Remote\DesiredCapabilities;
	use Facebook\WebDriver\Remote\RemoteWebDriver;
	use Facebook\WebDriver\Chrome\ChromeOptions;
	use DeathByCaptcha_SocketClient;
	use DeathByCaptcha_Client;
	use Facebook\WebDriver\Exception\WebDriverException;
	use Facebook\WebDriver\WebDriverSelect;
	use mysql_xdevapi\Exception;
	use PHP_CodeSniffer\Util\Cache;
	ini_set('display_errors', 0);
	error_reporting(E_ALL);
	//require_once('vendor/autoload.php');
	header("Content-Type: application/json");

	ini_set('max_execution_time',0);
	ini_set('memory_limit',-1);


/*$accno = 'xxxx';
$email = 'xxx';
$pass = 'xxxxx';
$web = 'xxxxx';
$scb = new ScbLine($accno,$email,$pass,'');
$balance = $scb->GetBalance();
print_r($balance);
$gettransection  = $scb->getTransaction();
print_r($gettransection);*/

class Linelift {
	private $captcha_username = 'nextgendevoffice';
	private $captcha_password = '?k7NqzFpCTwSfr9';
	private $cookieJarFilePath = 'lib/scblinelift/cookies/';

	//public $host = 'http://localhost:9515';
	//public $host = 'http://178.128.94.59:32786';
	//public $host = 'https://selenium-grid-o6kgfv7ymq-et.a.run.app';
	//private $host = 'http://localhost:9515/';
	private $host = 'http://178.128.94.59:32771';

	private $driver;
	private $accno = '';
	private $email = '';
	private $pass = '';
	private $cookie = '';
	private $step =0;

	public function __construct($accno,$email,$pass,$init) {
		$this->accno = $accno;
		$this->email = $email;
		$this->pass = $pass;
		
		$this->checkCookie();
		
	}

	public function checkCookie(){
		$cookieJarFilePath = $this->cookieJarFilePath.$this->accno.'.txt';
		if(file_exists($cookieJarFilePath)) {
			$fp = fopen($cookieJarFilePath, "r");
			$this->cookie = fgets($fp);
			fclose($fp);
		}else{
			$this->cookie = $this->login();
		}
	}

	public function login(){
		$options = new ChromeOptions();
		$options->addArguments(array(
			//'--headless',
			//	"--ignore-certificate-errors",
			// "--allow-insecure-localhost",
			'--window-size=480x600'
		));

		$capabilities = DesiredCapabilities::chrome();
		$K=1;

		$capabilities = DesiredCapabilities::chrome();
		$capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
		// $capabilities->setCapability("chrome.switches", array("--ignore-certificate-errors"));

		$this->driver = RemoteWebDriver::create($this->host,$capabilities, 5000);
		$this->driver->manage()->window()->setSize(new WebDriverDimension(480, 996));
		$this->driver->get(base64_decode("aHR0cHM6Ly9saWZmLmxpbmUubWUvMTUwNjk3NTg2MC03TlF4R3p2Yi9aTkNsOFg="));
		sleep(8);

		global $K;
		// $S_USERNAME = 'kaewmanee7178@gmail.com';
		// $S_PASSWORD = 'Kaew140242';
		$this->KeyFields($this->driver);

		sleep(2);

		$cookies = $this->driver->manage()->getCookies();
		foreach ($cookies as $cookie){
			$cookieArgs = [];
			$cookieArgs[] = ($cookie->isHttpOnly() ? '#HttpOnly_' : '') .$cookie->getDomain();
			$cookieArgs[] = 'TRUE';
			$cookieArgs[] = $cookie->getPath();
			$cookieArgs[] = $cookie->isSecure() ? 'TRUE' : 'FALSE';
			$cookieArgs[] = $cookie->getExpiry() ?: '2145906000';
			$cookieArgs[] = $cookie->getName();
			$cookieArgs[] = $cookie->getValue();
			if($cookie->isHttpOnly()){
				$test[] =$cookie->getValue();
			}
			$cookieStringLine = implode("\t" , $cookieArgs) . "\n";

		}
		$this->cookie = $test[0];
		$cookieJarFilePath = 'lib/scblinelift/cookies/'.$this->accno.'.txt';
		$fp = fopen($cookieJarFilePath, 'w');
			fwrite($fp, $this->cookie);
			fclose($fp);
		$this->driver->close();
		return trim($test[0]);
	}
	public function KeyFields(){
		$S_USERNAME = $this->email;
		$S_PASSWORD = $this->pass;

		// echo $K;
		// $K++;
		$search_box1 = $this->driver->findElement(WebDriverBy::name('tid'));
		$search_box1->sendKeys($S_USERNAME);

		$search_box = $this->driver->findElement(WebDriverBy::name('tpasswd'));
		$search_box->sendKeys($S_PASSWORD);

		// echo $K;
		try {
			$captcha = $this->driver->findElement(WebDriverBy::name('captcha'));
			$this->retryCapcha();
		}catch (WebDriverException $e) {
			// echo $e->getMessage();
			$submitBt = $this->driver->findElement(WebDriverBy::xpath('//*[@id="app"]/div/div/div/div[2]/div/form/fieldset/div[3]/button'));
			$submitBt->click();
			sleep(3);
			if(strpos($this->driver->getCurrentURL(),'oauth2/v2.1/login')!==false){
				$this->KeyFielsd($this->driver);
			}
		}
	}

	public function retryCapcha(){
		require_once('deathbycaptcha.php');
		sleep(3);

		$dbc = new DeathByCaptcha_SocketClient($this->captcha_username, $this->captcha_password);

		$img = 'image.png';
		$this->driver->takeScreenshot($img);
		$src = imagecreatefrompng($img);

// create an image resource of your expected size 30x20
		$dest = imagecreatetruecolor(163, 67);
// Copy the image
		imagecopy(
			$dest,
			$src,
			0,    // 0x of your destination
			0,    // 0y of your destination
			24,   // middle x of your source
			270,   // middle y of your source
			163,  // 30px of width
			67   // 20px of height
		);

// The second parameter should be the path of your destination
		imagepng($dest, 'image2.png');

		imagedestroy($dest);
		imagedestroy($src);

		//file_put_contents($img, file_get_contents($imgCapcha));
		//echo "ติด capcha\r\n";

		$captcha_filename = 'image2.png';


		while(true){
			if ($get_captcha = $dbc->decode($captcha_filename, DeathByCaptcha_Client::DEFAULT_TIMEOUT)) {
				//echo "===== get_captcha ======\n";
				//print_r($get_captcha);
				//echo "CAPTCHA {$get_captcha['captcha']} solved: {$get_captcha['text']}\n";
				//echo $dbc->report($captcha['captcha']);
				//$captcha = $get_captcha['text'];
				if($get_captcha['is_correct'] == 1){ break; }
				sleep(5);
			}
		}
		$captcha = trim($get_captcha['text']);

		$this->driver->findElement(WebDriverBy::name('captcha'))
			->sendKeys($captcha);

		sleep(3);
		//echo '\r\n กดปุ่ม log in';
		$submitBt = $this->driver->findElement(WebDriverBy::xpath('//*[@id="app"]/div/div/div/div[2]/div/form/fieldset/div[4]/button'));
		$submitBt->click();
	}

	public function GetBalance() {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://app-prod.scb.co.th/api/justforyou/transaction/v1/deposits/monthlySummary',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>
            '{"accountNo":"' .
            $this->accno .
            '","fromMonth": "03/2023",
			"sortSequence": "A",
			"toMonth": "04/2023"}',
        CURLOPT_HTTPHEADER => [
            "content-type: application/json",
            "cookie: auth-token=" . $this->cookie,
            "user-agent: Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1 Edg/109.0.0.0",
        ],
    ]);

    $res = curl_exec($curl);
    curl_close($curl);
	//print_r($res);

    $d = json_decode($res,true);

		$data = array();
		if($d['status']['code'] == 1000) {

			$a = $d['data']['monthInfo'];
			$last = end($a);
			$data['availableBalance'] = (float) ($last['inflowAmount'] - $last['spendingAmount']);
			//$data['status'] = 'Success';
			// $data['data'] = $d['data'];

		} else {
			if($this->step <=2){
				$this->login();
				++$this->step;
			}else{
				$data['status'] = 0;
			}
		}

    return $data;
	}

	public function getTransaction() {
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://app-prod.scb.co.th/api/justforyou/transaction/v1/deposits/inflow/monthlyTransactions',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>'{
		  "accountNo":"'.$this->accno.'",
		  "categoryCode":"IUNCATE",
		  "month":"04/2023"
		  }',
		   CURLOPT_HTTPHEADER => array(
		    'Cookie: auth-token=' . $this->cookie,
		    'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36',
		    'Content-Type: application/json'
		  ),
		));

		$res = curl_exec($curl);
		curl_close($curl);
		$d = json_decode($res,true);
		//print_r($d);
		$data = array();

		if($d['status']['code'] == 1000) {
			// $data['status_code'] = $d['status']['code'];
			// $data['description'] = $d['status']['description'];
			// $data['accountNo'] = $d['data']['accountNo'];
			//$data['from_date'] = $d['data']['txnHeader']['fromDate'];
			//$data['to_date'] = $d['data']['txnHeader']['toDate'];

			for($i=0, $iMax = count($d['data']['txnDetails']); $i< $iMax; $i++){

				$date_array = explode("-",$d['data']['txnDetails'][$i]['asOfDate']);
				$date_current = date("Y-m-d");
				if($d['data']['txnDetails'][$i]['asOfDate']==$date_current){
					$date  =  $date_array[2].'/'.$date_array[1].'/'.$date_array[0];
					for($x=0, $iMax2 = count($d['data']['txnDetails'][$i]['inflow']); $x< $iMax2; $x++) {
						$detail = $this->getDetailTransection($this->accno, $this->cookie, $date, $d['data']['txnDetails'][$i]['inflow'][$x]['recordId']);
						//print_r($detail);
						//sleep(10);
						preg_match_all("/SCB x(.*) /U", $detail['data']['txnDetail']['stmtDescription'], $scbbank);
						preg_match_all("/ ((.*)) \/X([0-9]+)([0-9]+)([0-9]+)([0-9]+)([0-9]+)([0-9]+)/U", $detail['data']['txnDetail']['stmtDescription'], $otherbank);
						$bankno = "";
						if ($scbbank[0]) {
							$bankno = str_replace(" x", "_", implode($scbbank[0]));
						} else {
							$bankno = str_replace("(", "", str_replace(") ", "_", str_replace("/X", "", implode($otherbank[0]))));
						}

						$times = explode(" ", $detail['data']['txnDetail']['txnDatetime']);
						$data['deposit'][$i]['accountFrom'] = $detail['data']['txnDetail']['accountFrom'];
						$data['deposit'][$i]['deposits'] = $detail['data']['txnDetail']['amount'];
						$data['deposit'][$i]['date'] = $date;
						$data['deposit'][$i]['time'] = $times[1];
						$data['deposit'][$i]['description'] = $bankno;
						$data['deposit'][$i]['description_full'] = $detail['data']['txnDetail']['stmtDescription'];
					}
				}


			}

			//$data['transaction'] = $d['data']['txnDetails'];

		} else {
			$data['status_code'] = $d['status']['code'];
			$data['description'] = $d['status']['description'];
			if($this->step <=2){
				$this->login();
				++$this->step;
			}
		}
		print_r($data);
		return $data;
	}
	private function getDetailTransection($accno,$cookie,$asOfDate,$recordId){
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://app-prod.scb.co.th/api/justforyou/transaction/v1/deposits/inflow/transactionDetail',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{
		    "accountNo": "'.$accno.'",
		    "recordId": "'.$recordId.'",
		    "txnDate": "'.$asOfDate.'"
		}',
			CURLOPT_HTTPHEADER => array(
				'Cookie: auth-token=' . $cookie,
				'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36',
				'Content-Type: application/json'
			),
		));

		$res = curl_exec($curl);
		curl_close($curl);
		$d = json_decode($res,true);
		//print_r($d);
		return $d;
	}
}
?>
