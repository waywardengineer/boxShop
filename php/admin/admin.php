<?php
$authkey='';
date_default_timezone_set('America/Los_Angeles');

include("../include/user.php");
include("../include/template.php");
if(!$user->isAdmin()){
   header("Location: ../index.php");
   die();
}
$showsections = array('page_admin', 'page_admin2');

$html = new Template('../templates/main.tpl');
$welcome_msg='Admin Panel';
$toplink['Home']='../index.php';
$toplink['Logout'] = '../process.php';
$q = "SELECT users.UID, users.username, users.userlevel, users.email, users.timestamp, codes.code, codes.keyPadK, codes.keyPadL FROM users LEFT JOIN codes ON users.UID = codes.UID AND codes.startdate = 0 WHERE users.UID > 0";
$result =  $database->query($q);
$html->makeUserList($result);
$html->makeLinkBars($toplink, 'toplinks');
$html->set('welcome', $welcome_msg);
$html->set('dir', '../');
echo $html->doOutput($showsections);

