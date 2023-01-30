<?php
//MisterNT
//https://goqr.me/ generate qrcode
require_once('./KkpClass.php');

$idCard= "1470500112872";
$pin= "110739";

$data = array(
    "idCard" => trim($idCard),
    "pin" => trim($pin),
);

$kkp = new KkpClass($data);

//$res = $kkp->RequestOTP();
//$res = $kkp->SummitOTP("773626","ZHVDN","d5bfa17a-b9ce-4a1a-9d78-066a1e1d8331-gbz");
//$res = $kkp->LoginPin();
// step add login

// $res = $kkp->setPublicKey(); //ใช้ครั้งเดียว (ลบ)
//$res = $kkp->verifyTransfer("4193524973","006","1.00");
//sleep(4);
//$res = $kkp->ConfirmTransfer($res);
// $res = $kkp->getTransaction();

//E-wallet พร้อมเพย์
// $res = $kkp->promptPayTransfer('140000972223531', '1.00'); 
// $res = $kkp->ConfirmPromptPayTransfer($res);

// $res = $kkp->summary();
//$res = json_encode($res,true);
// $res = $kkp->ScanSlip("0039000600000101030690218TR02210131120044825102TH91043284");
// $res = $kkp->SlipDetail("TR0221013112004482","069");

print_r($res);
