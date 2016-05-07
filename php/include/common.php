<?php

if ($authkey!='boxshop94124') {die();};

date_default_timezone_set('America/Los_Angeles');
include("include/user.php");
include("include/template.php");
$html = new Template('templates/main.tpl');
$html->createNav();

$vars=array('l','action', 'id');
foreach($vars as $var) {
	$$var= isset($_GET[$var]) ? htmlentities($_GET[$var]) : false;
}
if($user->logged_in){
	$welcome_msg = '<a href="/editaccount.php">Welcome, ' . "$user->username</a>";
}
else {
	$welcome_msg="Hello, guest";
}
$html->set('welcome', $welcome_msg)

?>