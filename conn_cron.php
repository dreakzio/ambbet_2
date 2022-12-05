<?php
	$str_server = "127.0.0.1";
	$str_username = "root";
	$str_password = '';
	$str_dbname = "db_amb";
	$obj_con_cron = mysqli_connect($str_server,$str_username,$str_password,$str_dbname);
	mysqli_set_charset($obj_con_cron, "utf8");
?>
