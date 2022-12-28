<?php
require('config.php');
require('conn_cron.php');

addColumn('account','last_activity','DATETIME NULL');
addColumn('account','auto_accept_bonus'," INT(1) NOT NULL DEFAULT '0'");
insertData("INSERT INTO web_setting (id, name, value) VALUES (NULL, 'deposit_with_bonus_auto', '0');");
delFile();
function addColumn($table_name,$column_name,$detail){
	global $obj_con_cron;
	$SqlCheckAcc = "SHOW COLUMNS from `{$table_name}` LIKE '{$column_name}'";

	$dsAcc = $obj_con_cron->query($SqlCheckAcc);

	if(mysqli_num_rows($dsAcc)==0){
		$result = $obj_con_cron->query('ALTER TABLE '.$table_name.' ADD '.$column_name.' '.$detail);
		echo "Add Column {$column_name} in table {$table_name} success !!! \r\n";
	}else{
		echo "aleady column <span style='color: #721c24;' >last_activity</span> in <span style='color: #0C102A;' ><b>account</b></span><br/>";
	}
}

function insertData($sql){
	global $obj_con_cron;
	if($obj_con_cron->query($sql)){
		echo " Insert Data Success <br/>";
	}else{
		echo "Cannot Insert Data <br/>";
	}
}
function delFile(){
	$path="update.php";

	if(@unlink($path)) {echo "Deleted file "; }

	else{
		echo "File can't be deleted";
	}
}
/**/
?>
