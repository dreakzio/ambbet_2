<?php
ini_set("display_errors",0);
error_reporting(E_ALL);

//MisterNT
require_once('KkpClass.php');

//$idCard= "1470500112872";
$idCard= "XXXX";
//$pin= "110739";
$pin= "XXX";

$data = array(
    "idCard" => trim($idCard),
    "pin" => trim($pin),
);

$kkp = new KkpClass($data);

//$res = $kkp->RequestOTP();
//$res = $kkp->SummitOTP("565424","ZQXHG","df2210c1-7322-4525-aa69-8918c20c4db4-4v5");
//$res = $kkp->LoginPin();
// print_r($res);
// $res = $kkp->setPublicKey(); //ใช้ครั้งเดียว ยกเลิก
/*for($i=1;$i<=10;$i++){
	sleep(10);
	echo "ครั้งที่ {$i}";
	$res = $kkp->verifyTransfer("4193524973","006","1.00");
	$res = $kkp->ConfirmTransfer($res);
	echo "<hr/>";
}*/

//$res = $kkp->getTransaction();

//E-wallet พร้อมเพย์
// $res = $kkp->promptPayTransfer('140000972223531', '1.00');
// $res = $kkp->ConfirmPromptPayTransfer($res);

$res = $kkp->summary();
//$res = json_encode($res,true);
// $res = $kkp->ScanSlip("0039000600000101030690218TR02210131120044825102TH91043284");
// $res = $kkp->SlipDetail("TR0221013112004482","069");

//$res = $kkp->generateGuid();


//$res = $kkp->GENERATE_STATEMENT("2002486181", "12");
print_r($res);
