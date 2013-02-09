<?php
$auth='auth';
date_default_timezone_set('America/Los_Angeles');

include("include/user.php");
include("include/template.php");
if(!$user->isAdmin()){
   header("Location: index.php");
   die();
}

$html = new Template('templates/main.tpl', 'templates/admin.tpl');
$html->createNav();
$welcome_msg='Admin Panel';
$q = "SELECT users.UID, users.username, users.userlevel, users.email, users.timestamp, codes.code, codes.keyPadK, codes.keyPadL FROM users LEFT JOIN codes ON users.UID = codes.UID AND codes.startdate = 0 ";
$result =  $database->query($q);
$html->makeUserList($result);
$html->set('welcome', $welcome_msg);
$html->set('dir', '');
echo $html->doOutput();

