<?php
error_reporting(0);
require_once 'simple_html_dom.php';

class Krungsri{

    protected $username = null;         //username internet banking
    protected $password = null;         //password internet banking
    protected $bank_number = null;      //bank account number

	static protected $proxy_data = [
		"proxy_type" => CURLPROXY_HTTP,
		"proxy_port" => 22225,
		"proxy_user" => "brd-customer-hl_ebdb3c0e-zone-data_center",
		"proxy_pass" => "0pi1xakwwrg5",
		"proxy_list" => [
			"zproxy.lum-superproxy.io",
		]
	];

    public function __construct($username,$password,$bank_number)
    {
        date_default_timezone_set('Asia/Bangkok');
        $this->username = $username;
        $this->password = $password;
        $this->bank_number = $bank_number;
		$proxy_data_list =
			[
				"brd-customer-hl_ebdb3c0e-zone-data_center-ip-158.46.165.248",
				"brd-customer-hl_ebdb3c0e-zone-data_center-ip-158.46.167.239",
				"brd-customer-hl_ebdb3c0e-zone-data_center-ip-158.46.164.53",
				"brd-customer-hl_ebdb3c0e-zone-data_center-ip-158.46.169.132",
				"brd-customer-hl_ebdb3c0e-zone-data_center-ip-158.46.170.238",
				"brd-customer-hl_ebdb3c0e-zone-data_center-ip-103.241.54.159",
				"brd-customer-hl_ebdb3c0e-zone-data_center-ip-158.46.168.227",
				"brd-customer-hl_ebdb3c0e-zone-data_center-ip-216.73.181.94",
				"brd-customer-hl_ebdb3c0e-zone-data_center-ip-158.46.171.170",
				"brd-customer-hl_ebdb3c0e-zone-data_center-ip-158.46.173.0"
			];
		shuffle($proxy_data_list);
		$chk = false;
		foreach ($proxy_data_list as $proxy_data){
			if(!$chk){
				try{
					$desturl = 'https://www.google.com';
					$ci = curl_init($desturl);
					curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ci, CURLOPT_FOLLOWLOCATION, false);
					$rand_proxy_ip = rand(0,count(self::$proxy_data['proxy_list']) - 1);
					curl_setopt($ci, CURLOPT_HTTPPROXYTUNNEL , 1);
					curl_setopt($ci, CURLOPT_PROXY, self::$proxy_data['proxy_list'][$rand_proxy_ip].":".self::$proxy_data['proxy_port']);
					curl_setopt($ci, CURLOPT_PROXYTYPE, self::$proxy_data['proxy_type']);
					curl_setopt($ci, CURLOPT_TIMEOUT, 4);
					curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 4);
					curl_setopt($ci, CURLOPT_PROXYUSERPWD,
						"$proxy_data:".self::$proxy_data["proxy_pass"]);
					$result = curl_exec($ci);
					if(curl_errno($ci)){
						curl_close($ci);
					}else{
						curl_close($ci);
						self::$proxy_data["proxy_user"] = $proxy_data;
						$chk = true;
					}
				}catch (Exception $ex){

				}
			}
		}

    }

    function process($type = "ALL"){
        $login = curl_init();
        $response_data = null;
        try{

            $url_base = 'https://www.krungsrionline.com';
            $url = 'https://www.krungsrionline.com/BAY.KOL.WebSite/Common/Login.aspx';
            $fp = fopen('./application/third_party/'.base64_encode("process_kma_".$this->bank_number), "w");
            fclose($fp);

            curl_setopt($login, CURLOPT_HEADER, 1);
            curl_setopt($login, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($login, CURLOPT_COOKIEJAR, './application/third_party/'.base64_encode("process_kma_".$this->bank_number));
            curl_setopt($login, CURLOPT_COOKIEFILE, './application/third_party/'.base64_encode("process_kma_".$this->bank_number));  //could be empty, but cause problems on some hosts
            curl_setopt($login, CURLOPT_TIMEOUT, 60);
            curl_setopt($login, CURLOPT_CONNECTTIMEOUT, 40);
            curl_setopt($login, CURLOPT_RETURNTRANSFER, TRUE);
			$random_proxy_cust = rand(0,1);
			if($random_proxy_cust == 0){
				curl_setopt($login, CURLOPT_HTTPPROXYTUNNEL , 1);
				curl_setopt($login, CURLOPT_PROXY, "http://gate.dc.smartproxy.com");
				curl_setopt($login, CURLOPT_PROXYPORT, 20000);
				curl_setopt($login, CURLOPT_PROXYUSERPWD, "sp7247890e:Arm042734726");
			}else{
				$rand_proxy_ip = rand(0,count(self::$proxy_data['proxy_list']) - 1);
				curl_setopt($login, CURLOPT_HTTPPROXYTUNNEL , 1);
				curl_setopt($login, CURLOPT_PROXY, self::$proxy_data['proxy_list'][$rand_proxy_ip].":".self::$proxy_data['proxy_port']);
				curl_setopt($login, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); // If expected to call with specific PROXY type
				curl_setopt($login, CURLOPT_PROXYUSERPWD, self::$proxy_data['proxy_user'].":".self::$proxy_data['proxy_pass']);
			}
            curl_setopt($login, CURLOPT_URL, $url);
            curl_setopt($login, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36');
            curl_setopt($login, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($login, CURLOPT_POST, false);
            $response = curl_exec($login);
            $html = str_get_html($response);

            $_data = array(
                "__EVENTTARGET"=>'ctl00$cphForLogin$lbtnLoginNew',
                "__EVENTARGUMENT"=>'',
                "__VIEWSTATE"=>$html->find("[name=__VIEWSTATE]",0)->attr['value'],
                "__VIEWSTATEGENERATOR"=>$html->find("[name=__VIEWSTATEGENERATOR]",0)->attr['value'],
                "__EVENTVALIDATION"=>$html->find("[name=__EVENTVALIDATION]",0)->attr['value'],
                'ctl00$cphForLogin$tbInput'=>'',
                'user'=>'',
                'password'=>'',
                'username'=>'',
                'password'=>'',
                'ctl00$cphForLogin$username'=>$this->username,
                'ctl00$cphForLogin$password'=>'',
                'ctl00$cphForLogin$hdPassword'=>$this->password,
                'ctl00$cphForLogin$hddLanguage'=>'TH',
            );
            $data = http_build_query($_data);
            $headers = [
                'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                'Origin: '.$url_base,
                'Referer: '.$url,
                'Host: www.krungsrionline.com',
                'Upgrade-Insecure-Requests: 1'
            ];
            curl_setopt($login, CURLOPT_HEADER, 1);
            curl_setopt($login, CURLOPT_POSTFIELDS, $data);
            curl_setopt($login, CURLOPT_URL, $url);
            curl_setopt($login, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($login, CURLOPT_POST, true);
            $response = curl_exec($login);
            $response_info = curl_getinfo($login);

            $html = str_get_html($response);
            if(isset($response_info['url']) && $response_info['url'] == "https://www.krungsrionline.com/BAY.KOL.WebSite/Pages/MyPortfolio.aspx?d"){

                if($type == "ALL"){
                    $Balance = self::Get_Balance_Krungsri($this->bank_number,$html);
                    $transactions = self::Get_Statement_Krungsri($login,$html);
                    $response_data = [
                        "balance" => $Balance,
                        "transactions" => $transactions,
                    ];
                }else if($type == "B"){
                    $response_data = self::Get_Balance_Krungsri($this->bank_number,$html);
                }else if($type == "T"){
                    $response_data = self::Get_Statement_Krungsri($login,$html);
                }
            }

            curl_close ($login);
            unset($login);

        }catch (\Exception $ex){
            if(!is_null($login)){
                curl_close ($login);
                unset($login);
            }
        }
        return $response_data;
    }

    static function Get_Balance_Krungsri($Acc_no,$html){
        $Container_table = $html->find("#ctl00_cphSectionData_pnlAsset",0);
        $Account_Row = array();
        if(!empty($Container_table)){
            $Table_Acc =  $Container_table->find("table[class=myport_table]",0);
            if(!empty($Table_Acc)){
                $Row_body = $Table_Acc->find("tbody",0);
                if(!empty($Row_body)){
                    foreach ($Row_body->find('tr') as $i => $tr) {
                        $Acc = $tr->find('td',1);
                        $Bal = $tr->find('td',2);
                        if(!empty($Acc)){
                            $key = str_replace('-','',trim($Acc->plaintext));
                            $RowAccId = $Acc->find('a',0);
                            $Account_Row[strtoupper($key)] = array(
                                'balance' => empty($Bal) ? null :  preg_replace('/[^0-9.]/','',trim($Bal->plaintext)),
                                'account_id' => empty($RowAccId) ? null :  trim($RowAccId->attr['account']),
                                'account_no' => trim($Acc_no),
                                'event_target' => empty($RowAccId) ? null :  str_replace("_","$",trim($RowAccId->attr['id'])),
                            );
                        }
                    }
                }
            }
        }
        foreach ($Account_Row as $k => $row){
            if(!empty($row['account_no']) && !array_key_exists($row['account_no'],$Account_Row)){
                $Account_Row[$row['account_no']] = $row;
            }
        }
        return array_key_exists($Acc_no,$Account_Row) ? $Account_Row[$Acc_no]["balance"] : null;
    }

    static function Get_Statement_Krungsri($login,$html = ""){
        $ArrData = array();
        $acc_detail_link = $html->find("#ctl00_ContentAcclist a");
        $url_statement = "";
        foreach($acc_detail_link as $element){
            if(isset($element->attr['href']) && strpos($element->href,"/BAY.KOL.WebSite/Pages/MyAccount.aspx?") !== false && empty($url_statement)){
                $url_statement = "https://www.krungsrionline.com".$element->href;
            }
        }
        if(empty($url_statement)){
            return $ArrData;
        }
        curl_setopt($login, CURLOPT_URL,$url_statement);
        $result = curl_exec($login);
        $data = self::getdata($result);
        foreach($data as $row) {

            $date = $row[0];
            $date_split = explode(" ",$date);
            $deposit_filter = strpos($row[1],"TW") === false && strpos($row[1],"WN") === false ? $row[2] : "";
            $withdraw_filter = strpos($row[1],"TW") !== false || strpos($row[1],"WN") !== false ? $row[2] : "";
            $date_today_filter = self::datetoServerDate(trim($date_split[0]));
            $time_today_filter = trim($date_split[1]);
            $fromAccountfilter = trim($row[1]);
            $info_filter = trim($row[4]);
            $balance_filter = preg_replace('/[^0-9.]/','',trim($row[3]));

            array_push($ArrData,  array(
                "amount" => $deposit_filter !== "" ? $deposit_filter : $withdraw_filter,
                "type" => $deposit_filter !== "" ? "D" : "W",
                "balance" => $balance_filter,
                "date" => $date_today_filter,
                "time" => $time_today_filter.":00",
                "fromAccount" => $fromAccountfilter,
                "info" => $info_filter
            ));

        }
        if(!empty($ArrData)){
            usort($ArrData, function($a, $b) {
                $Date = $a['date'];
                $Time = $a['time'];
                $Date_2 =$b['date'];
                $Time_2 = $b['time'];
                $t1 = new DateTime($Date." ".$Time);
                $t2 = new DateTime($Date_2." ".$Time_2);
                return $t1->getTimestamp() - $t2->getTimestamp();
            });
        }
		$ArrData_new = [];
		$ArrData = array_reverse($ArrData);
		foreach($ArrData as $index => $data){
			if(($index+1) <= 25){
				$ArrData_new[] = $data;
			}
		}
		$ArrData_new = array_reverse($ArrData_new);
		return $ArrData_new;
    }

    static function tdrows($elements)
    {
        $str = [];
        $chk_add = false;
        foreach ($elements as $index => $element) {

            if(!empty(trim($element->nodeValue))){
                if(strpos(trim($element->nodeValue),"/") !== false && strpos(trim($element->nodeValue),":") !== false && strpos(trim($element->nodeValue)," ") !== false){
                    $chk_add = true;
                }
                if($chk_add){
                    $str[] = trim($element->nodeValue);
                }

            }

        }

        return $str;
    }

    static function getdata($html_str)
    {
        libxml_use_internal_errors(true);
        $contents = $html_str;
        $DOM = new \DOMDocument();
        $DOM->loadHTML($contents);

        $items = $DOM->getElementsByTagName('tr');
        $arr_data = [];
        foreach ($items as $node) {
            $row_data = self::tdrows($node->childNodes);
            if(!empty($row_data)){
                $arr_data[] = $row_data;
            }
        }
        return $arr_data;
    }

    static function datetoServerDate($Date,$Time = ""){
        $Day = substr($Date, 0,2);
        $Month = substr($Date, 3,2);
        $Year = substr($Date, 6,4);
        $serverDate = $Year.'-'.$Month.'-'.$Day;
        return $serverDate;
    }

}
