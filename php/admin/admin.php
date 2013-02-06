<?php
$authkey='';
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
$q = "SELECT UID, username,userlevel,email,timestamp FROM ".TBL_USERS." ORDER BY UID DESC";
$result =  $database->query($q);
$html->makeUserList($result);
$html->makeLinkBars($toplink, 'toplinks');
$html->set('welcome', $welcome_msg);
$html->set('dir', '../');
echo $html->doOutput($showsections);

