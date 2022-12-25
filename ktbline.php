<?php
set_time_limit(0);
Loopdata:
$servername = "xxx";
$username = "xxx";
$password = "xxx";
$dbname = "xxx";
$id		= 0; // ID Agent

header('Content-Type: application/json; charset=utf-8');

$accountTokenNumber = 'A20220425769dc6a5a8aa4f6e9666c09683879a90';
$userIdentity = 'U42e8bad23f803fba0d3b4a337aefdeab';
$userTokenIdentity = 'C202204251555a917092b4c4e8bb62911fcabd6a2';

$row['accountTokenNo'] 	= $accountTokenNumber;
$row['userIdentity'] 	= $userIdentity;
$row['tokenID'] 		= $userTokenIdentity;

//List IP
$host = 'proxyprivates.com';if($socket =@fsockopen($host, 3128, $errno, $errstr, 2)) {fclose($socket);} else {echo 'offline.';exit;}
$proxy_array	= array(
				'proxyprivates.com'
				);
//proxy
$loginpassw = 'proxydata:f6Hj2DBefuNd7xNs';
$proxy_ip = $proxy_array[array_rand($proxy_array)];
$proxy_port = '3128';
//echo $proxy_ip."<br>";
// Create connection
// $conn = new mysqli($servername, $username, $password, $dbname);
// // Check connection
// if ($conn->connect_error) {
//   die("Connection failed: " . $conn->connect_error);
// }

// $sql = "SELECT * FROM bot_ktb where id = ".$id."";
// $result = $conn->query($sql);
// if ($result->num_rows > 0) {
//   // output data of each row
//   $row = $result->fetch_assoc();
// } else {
//   echo "0 results";
// }
// $conn->close();

function DateMount($strMonth)
{
	$strMonthCut 	= Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
	$strMonthThai	= $strMonthCut[$strMonth];
	$strMonthThai	= array_search($strMonth, $strMonthCut);
	return sprintf('%02d', $strMonthThai);
}



// print_r($codeBankreData);exit;

//print_r($row);exit;
$UrlGetConfig	= "https://www.krungthaiconnext.ktb.co.th/KTB-Line-Balance/deposit/statement-content";
$data_en		= '{"action":"UPDATE","accountTokenNumber":"'.$row['accountTokenNo'].'","activeIndex":"0","lastSeq":"0","userIdentity":"'.$row['userIdentity'].'","hasViewMore":false,"transaction":[]}';

// $UrlGetConfig	= "https://www.krungthaiconnext.ktb.co.th/KTB-Line-Balance/deposit/account-detail";
// $data_en		= "accountTokenNumber=".$row['accountTokenNo']."&userIdentity=".$row['userIdentity']."&userTokenIdentity=".$row['tokenID']."&channel=Krungthai+Next&language=TH";

$ch            = curl_init();
curl_setopt($ch, CURLOPT_URL, $UrlGetConfig);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_en);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		// 'Content-Type: application/x-www-form-urlencoded'
		'Content-Type: application/json'
		));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
curl_setopt($ch, CURLOPT_PROXY, $proxy_ip);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $loginpassw);

$response = curl_exec($ch);
curl_close($ch);
print_r($response);exit;
preg_match_all('/{"(.*)}/', $response, $matches);
$reData		= json_decode($matches[0][0], true);

$i=0;
foreach($reData['transactions'] as $datas => $value){
	print_r($value);
	$ktbdata['data'][$i]['endingBalance'] = $reData['availableBalance'];
	$ktbdata['data'][$i]['transAmt'] = $value['balance'];
	$ktbdata['data'][$i]['cmt'] = $value['cmt'];
	$typeData	= explode("-",$value['cmt']);
	// foreach($codeBankreData['th'] as $datas2 => $value2){
		// print_r($value2);
		// if($value2['code'] == $typeData[0])
		// {
		// 	$ktbdata['data'][$i]['bankCode'] = strtoupper($value2['type']);
		// 	break;
		// }
	// }
	$ktbdata['data'][$i]['transCmt'] = $value['cmt'];
	// if($value['type'] == "โอนเงินเข้า"){
	// 	$descTH		=	"Transfer in";
	// } else {
	// 	$descTH		=	"Transfer out";
	// }
	$ktbdata['data'][$i]['transCodeDescEn'] = $value['type'];//$descTH;
	$dateTimeData	= explode(" ",$value['dateTime']);
	$ktbdata['data'][$i]['transDate'] = date("Y").DateMount($dateTimeData[1]).$dateTimeData[0];
	$ktbdata['data'][$i]['transTime'] = $dateTimeData[2];
	$ktbdata['data'][$i]['transSeqNo'] = $value['transSeqNo'];
	$ktbdata['data'][$i]['isNegativeBalance'] = $value['isNegativeBalance'];
	//$ktbdata['data'][$i]['account']		= $row['account'];
	$i++;
	// print_r($value);exit;
}
// print_r($ktbdata);exit;

if($ktbdata['status'] == "ERROR"){
	// goto Loopdata;
} else {
	$dataTrue	= json_encode(array('data'=>$ktbdata['data']));
	print_r($dataTrue);
}
?>
