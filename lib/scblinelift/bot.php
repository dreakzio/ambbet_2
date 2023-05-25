<?php
// An example of using php-webdriver.
// Do not forget to run composer install before. You must also have Selenium server started and listening on port 4444.

namespace Facebook\WebDriver;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
require_once('deathbycaptcha.php');
use DeathByCaptcha_SocketClient;
use DeathByCaptcha_Client;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverSelect;
use mysql_xdevapi\Exception;
use PHP_CodeSniffer\Util\Cache;

ini_set("display_errors",0);
error_reporting(E_ALL & ~E_NOTICE);

require_once('vendor/autoload.php');

//require_once ('config_angel.php');
//require_once ('function.php');
ini_set('max_execution_time',0);

$captcha_username = 'ufa577';
$captcha_password = '5Tgbvfr43edc';
//retryCapcha();

// $host = 'http://192.168.100.35:4444/';
// $host = 'https://selenium-grid-o6kgfv7ymq-et.a.run.app/';
$host = 'https://selenium-grid-o6kgfv7ymq-et.a.run.app/';

$options = new ChromeOptions();
$options->addArguments(array(
    // '--headless',
    "--allow-insecure-localhost",
    '--window-size=1280x1400'
));

$capabilities = DesiredCapabilities::chrome();
$K=1;

$capabilities = DesiredCapabilities::chrome();
$capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

$driver = RemoteWebDriver::create($host,$capabilities, 5000);
$driver->manage()->window()->setSize(new WebDriverDimension(1225, 996));
login($driver);
function startBot(){
    global $host;
    // $host = 'http://localhost:9515';

    $options = new ChromeOptions();
    $options->addArguments(array(
        // '--headless',
        '--window-size=1280x1400'
    ));

    $capabilities = DesiredCapabilities::chrome();
    $K=1;

    $capabilities = DesiredCapabilities::chrome();
    $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

    $driver = RemoteWebDriver::create($host,$capabilities, 5000);
    $driver->manage()->window()->setSize(new WebDriverDimension(1225, 996));
    login($driver);
}
//$driver->close();
function retryCapcha($driver){
    global $captcha_username,$captcha_password;
    sleep(3);
    
    $dbc = new DeathByCaptcha_SocketClient($captcha_username, $captcha_password);
   
    $img = 'image.png';
    $driver->takeScreenshot($img);
    $src = imagecreatefrompng($img);
    
// create an image resource of your expected size 30x20
    $dest = imagecreatetruecolor(163, 67);
// Copy the image
    imagecopy(
        $dest,
        $src,
        0,    // 0x of your destination
        0,    // 0y of your destination
        407,   // middle x of your source
        313,   // middle y of your source
        163,  // 30px of width
        67   // 20px of height
    );

// The second parameter should be the path of your destination
    imagepng($dest, 'image2.png');

    imagedestroy($dest);
    imagedestroy($src);
    
    //file_put_contents($img, file_get_contents($imgCapcha));
    echo "ติด capcha\r\n";

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
    echo $captcha = trim($get_captcha['text']);

    $driver->findElement(WebDriverBy::name('captcha'))
        ->sendKeys($captcha);

    sleep(3);
    echo '\r\n กดปุ่ม log in';
    $submitBt = $driver->findElement(WebDriverBy::xpath('//*[@id="app"]/div/div/div/div[2]/div/form/fieldset/div[4]/button'));
    $submitBt->click();
    
}
function keyfield($driver){
    global $K;
    $S_USERNAME ='kaewmanee7178@gmail.com';
    $S_PASSWORD ='Kaew140242';
  
    $K++;
    $search_box1 = $driver->findElement(WebDriverBy::name('tid'));
    $search_box1->sendKeys($S_USERNAME);

    $search_box = $driver->findElement(WebDriverBy::name('tpasswd'));
    $search_box->sendKeys($S_PASSWORD);

    ////*[@id="app"]/div/div/div/div[2]/div/form/fieldset/div[3]/button
    try {
         $captcha = $driver->findElement(WebDriverBy::name('captcha'));
         retryCapcha($driver);
        }catch (WebDriverException $e) {
           // echo $e->getMessage();
            $submitBt = $driver->findElement(WebDriverBy::xpath('//*[@id="app"]/div/div/div/div[2]/div/form/fieldset/div[3]/button'));
            $submitBt->click();
            sleep(3);
            if(strpos($driver->getCurrentURL(),'oauth2/v2.1/')!==false){
                keyfield($driver);
            }
        }

        /* if(strpos($driver->getCurrentURL(),'oauth2/v2.1/')!==false){
            echo "\r\nloop\r\n";
            sleep(rand(2,5));
            keyfield($driver);
        }  */

    sleep(2);
    //$driver->manage()->getCookieNamed('Facebook\WebDriver\Cookie');
    /*$cookie= $driver->manage()->getCookies();
    var_dump($cookie[3]);*/
    $cookieJarFilePath = 'cookies.txt';

    $cookies = $driver->manage()->getCookies();
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
       // print_r($cookieArgs);

        echo $cookieStringLine = implode("\t" , $cookieArgs) . "\n";

    }
    // $driver->close();
    sleep(5);
    echo ($test[0]);
    $curl = curl_init();
    $accNum = "4310449168"; //เลขบช.ของคุณ
    curl_setopt_array($curl, [
        CURLOPT_URL => base64_decode(
            "aHR0cHM6Ly9hcHAtcHJvZC5zY2IuY28udGgvYXBpL2p1c3Rmb3J5b3UvdHJhbnNhY3Rpb24vdjEvZGVwb3NpdHMvaW5mbG93L21vbnRobHlUcmFuc2FjdGlvbnM="
        ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>
            '{"accountNo":' .
            $accNum .
            ',"month":"03/2023","categoryCode":"IUNCATE"}',
        CURLOPT_HTTPHEADER => [
            "content-type: application/json",
            "cookie: auth-token=" . trim($test[0]),
            "user-agent: Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1 Edg/109.0.0.0",
        ],
    ]);
    echo "start curl :";
    $response = curl_exec($curl);
    var_dump($response);
    curl_close($curl);
    print_r($response);
    sleep(30);

    // startBot();
}

function login($driver,$step=1){

    $driver->get(base64_decode("aHR0cHM6Ly9saWZmLmxpbmUubWUvMTUwNjk3NTg2MC03TlF4R3p2Yi9aTkNsOFg="));
    sleep(8);
    keyfield($driver);
}
?>
