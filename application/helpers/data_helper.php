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
	$CI = & get_instance();
	$bank_lists = $CI->Bank_register_ignore_model->bank_register_ignore_list([
		'status' => 1
	]);
	$bank_ignore_code_list = [];
	foreach($bank_lists as $bank_list){
		$bank_ignore_code_list[] = $bank_list['code'];
	}
	$bank_ignore_list = [];
	$master_data = array(
		'02' => 'ธนาคารกสิกรไทย (KBANK)',
		'05' => 'ธนาคารไทยพาณิชย์ (SCB)',
		'03' => 'ธนาคารกรุงไทย (KTB)',
		'04' => 'ธนาคารทีเอ็มบีธนชาต (TTB)',
		'07' => 'ธนาคารออมสิน (GSB)',
		'01' => 'ธนาคารกรุงเทพ (BBL)',
		'06' => 'ธนาคารกรุงศรีอยุธยา (BAY)',
		'09' => 'ธนาคารธ.ก.ส. (BAAC)',
		'10' => 'ทรูมันนี่วอลเล็ท (TRUE MONEY WALLET)',
	);
	foreach ($master_data as $key => $value){
		if(!in_array($key,$bank_ignore_code_list)){
			$bank_ignore_list[$key] = $value;
		}
	}
	return $bank_ignore_list;
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
		'username' => $form_data['username'],
		'account_agent_username' => $form_data['username'],
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
