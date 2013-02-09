<?php
date_default_timezone_set('America/Los_Angeles');
include("include/user.php");
include("include/template.php");
$vars=array('l','action', 'id');
foreach($vars as $var) {
	$$var= isset($_GET[$var]) ? htmlentities($_GET[$var]) : false;
}


?>