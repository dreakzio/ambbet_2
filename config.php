<?php
$scb = [
	//"name"=>"", //ชื่อ
	//"accnum"=>"", //เลขบช
	//"deviceid"=>"", //deviceid
	//"api_refresh"=>"", ////api_refresh
];
$ktb = [
	//"name"=>"", //ชื่อ
	//"accnum"=>"", //เลขบช
	//"accountTokenNo"=>"",
	//"userTokenId"=>"",
];
define("SECRET_KEY_SALT",'JjPZxrYjAf<8y4+&_.Rke&$c=(W7v');
define("API_TOKEN_KEY",'HLk3WpNTAS3Q4bsn5T72AzV2aCWsjt'); // SCB & KTB
function encrypt($data, $password){
	$iv = substr(sha1(mt_rand()), 0, 16);
	$password = sha1($password);

	$salt = sha1(mt_rand());
	$saltWithPassword = hash('sha256', $password.$salt);

	$encrypted = openssl_encrypt(
		"$data", 'aes-256-cbc', "$saltWithPassword", null, $iv
	);
	$msg_encrypted_bundle = "$iv:$salt:$encrypted";
	return $msg_encrypted_bundle;
}


function decrypt($msg_encrypted_bundle, $password){
	$password = sha1($password);

	$components = explode( ':', $msg_encrypted_bundle );
	$iv            = $components[0];
	$salt          = hash('sha256', $password.$components[1]);
	$encrypted_msg = $components[2];

	$decrypted_msg = openssl_decrypt(
		$encrypted_msg, 'aes-256-cbc', $salt, null, $iv
	);

	if ( $decrypted_msg === false )
		return false;

	$msg = substr( $decrypted_msg, 41 );
	return $decrypted_msg;
}
?>
