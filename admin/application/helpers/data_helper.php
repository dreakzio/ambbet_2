<?php
function getBankList(){
	return array(
		'01' => 'กรุงเทพ',
		'02' => 'กสิกรไทย',
		'03' => 'กรุงไทย',
		'04' => 'ทีเอ็มบีธนชาต',
		'05' => 'ไทยพาณิชย์',
		'06' => 'กรุงศรีอยุธยา',
		'07' => 'ออมสิน',
		'08' => 'ทีเอ็มบีธนชาต',
		'09' => 'ธ.ก.ส.',
		'1' => 'กรุงเทพ',
		'2' => 'กสิกรไทย',
		'3' => 'กรุงไทย',
		'4' => 'ทีเอ็มบีธนชาต',
		'5' => 'ไทยพาณิชย์',
		'6' => 'กรุงศรีอยุธยา',
		'7' => 'ออมสิน',
		'8' => 'ทีเอ็มบีธนชาต',
		'9' => 'ธ.ก.ส.',
		'10' => 'ทรูมันนี่วอลเล็ท',
	);
}
function getBankListUniqueCode(){
	return array(
		'01' => 'กรุงเทพ',
		'02' => 'กสิกรไทย',
		'03' => 'กรุงไทย',
		'04' => 'ทีเอ็มบีธนชาต',
		'05' => 'ไทยพาณิชย์',
		'06' => 'กรุงศรีอยุธยา',
		'07' => 'ออมสิน',
		'08' => 'ทีเอ็มบีธนชาต',
		'09' => 'ธ.ก.ส.',
		'10' => 'ทรูมันนี่วอลเล็ท',
	);
}
function getBankListUniqueTextCode(){
	return array(
		'002' => 'กรุงเทพ',
		'004' => 'กสิกรไทย',
		'006' => 'กรุงไทย',
		'011' => 'ทีเอ็มบีธนชาต',
		'014' => 'ไทยพาณิชย์',
		'025' => 'กรุงศรีอยุธยา',
		'030' => 'ออมสิน',
		'065' => 'ทีเอ็มบีธนชาต',
		'034' => 'ธ.ก.ส.',
	);
}
function getKeyBankList(){
	return array(
		'กรุงเทพ' => ['01','1'],
		'กสิกรไทย' => ['02','2'],
		'กรุงไทย' => ['03','3'],
		'ทีเอ็มบีธนชาต' => ['04','4'],
		'ไทยพาณิชย์' => ['05','5'],
		'กรุงศรีอยุธยา' => ['06','6'],
		'ออมสิน' => ['07','7'],
		'ทีเอ็มบีธนชาต' => ['08','8'],
		'ธ.ก.ส.' => ['09','9'],
	);
}
function member_credit_data($form_data)
{
	return $form_data;
}
function member_turn_data($form_data)
{
	$from_date = new DateTime((isset($form_data['date_begin'])?$form_data['date_begin']:date("Y-m-d")));
	$end_date = new DateTime((isset($form_data['date_end'])?$form_data['date_end']:date("Y-m-d")));
	$interval = $from_date->diff($end_date);
	$days = (int)$interval->format('%a');
	if($days > 31){
		$from_date = $end_date->sub(new DateInterval('P30D'));
		$from_date = new DateTime($from_date->format("Y-m-d"));
	}
	$from_date = $from_date->format("Y-m-d");
	$end_date = $end_date->format("Y-m-d");
	$data = [
		'start_date' => $from_date,
		'end_date' => $end_date,
		'username' =>  $form_data['username'],
		'account_agent_username' =>  $form_data['username'],
	];
	return $data;
}
function game_code_list(){
	return [
		"FOOTBALL",
		"STEP",
		"PARLAY",
		"GAME",
		"CASINO",
		"LOTTO",
		"M2",
		"MULTI_PLAYER",
		"TRADING",
		"KENO",
	];
}
function game_code_text_list(){
	return [
		"FOOTBALL" => "FOOTBALL",
		"STEP" => "STEP",
		"PARLAY" => "PARLAY",
		"GAME" => "GAME",
		"CASINO" => "CASINO",
		"LOTTO" => "LOTTO",
		"M2" => "M2",
		"MULTI_PLAYER" => "MULTI_PLAYER",
		"TRADING" => "HOTGRAPH",
		"KENO" => "KENO",
	];
}
function getTypeLogAddCreditList(){
	return array(
		'bonus_not_use_promotion' => "ไม่รับโปรโมชั่น",
		'bonus_promotion' => "โบนัสจากยอดฝาก (โปรโมชั่น)",
		'bonus_credit_free' => "โบนัสเครดิตฟรี",
		'bonus_from_not_approve_withdraw' => "โบนัสเติมเครดิตกลับ (จากถอนเงิน=>ไม่อนุมัติ)",
		'bonus_return_balance_winlose' => "โบนัสจากคืนยอดเสีย (Win/Lose)",
		'bonus_return_balance_turnover' => "โบนัสจากคืนยอดเสีย (Turnover)",
		'bonus_wheel' => "โบนัสจากวงล้อพารวย",
		'bonus_commission' => "โบนัสจาก Commission",
		'bonus_checkin' => "โบนัสจากกิจกรรมเช็คอิน",
	);
}
function getBankCodeForKbank(){
	return array(
		"02" => '001', 	    //KBANK
		"01"  => '003', 		//BBL
		"03"  => '004',		//KTB
		"06"  => '017',		//KRUNGSRI
		"04"  => '007',		//TMBTHANACHART
		"08"  => '007',		//TMBTHANACHART
		"05"  => '010',		//SCB
		"07"  => '022',		//GSB
		"09"  => '026',		//BAAC
		"004" => '001', 		//KBANK
		"002"  => '003', 		//BBL
		"006"  => '004',		//KTB
		"025"  => '017',		//KRUNGSRI
		"011"  => '007',		//TMBTHANACHART
		"065"  => '007',		//TMBTHANACHART
		"014"  => '010',		//SCB
		"030"  => '022',		//GSB
		"034"  => '026'		//BAAC
	);
}
function getBankCodeForKrungsri(){
	return array(
		"02" => '004', 	    //KBANK
		"01"  => '002',     //BBL
		"03"  => '006',		//KTB
		"06"  => '025',		//KRUNGSRI
		"04"  => '011',		//TMBTHANACHART
		"08"  => '011',		//TMBTHANACHART
		"05"  => '014',		//SCB
		"07"  => '030',		//GSB
		"09"  => '034',		//BAAC
		"004" => '004', 		//KBANK
		"002"  => '002', 		//BBL
		"006"  => '006',		//KTB
		"025"  => '025',		//KRUNGSRI
		"011"  => '011',		//TMBTHANACHART
		"065"  => '011',		//TMBTHANACHART
		"014"  => '014',		//SCB
		"030"  => '030',		//GSB
		"034"  => '034'		//BAAC
	);
}
