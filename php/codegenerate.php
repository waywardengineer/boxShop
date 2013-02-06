<?php
for ($i=0; $i < 10; $i++){
	$code = '';
	for ($j=0; $j < 10; $j++){
		if ($i != $j){
			$str = "INSERT INTO prohibitedcodes (code) VALUES ('" . $i . $j . $i . $j . $i . "');";
			echo $str;
			$str = "INSERT INTO prohibitedcodes (code) VALUES ('" . $i . $j . $i . $j . $i . $j . "');";
			echo $str;	
		}
	}
}
?>
	