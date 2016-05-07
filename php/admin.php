<?php
$authkey='boxshop94124';
include("include/common.php");
if(!$user->isAdmin()){
   header("Location: index.php");
   die();
}

$html->addSubTemplate('templates/admin.tpl');
$q = "SELECT users.UID, users.username, users.userlevel, users.email, users.timestamp, codes.code, codes.keyPadK, codes.keyPadL FROM users LEFT JOIN codes ON users.UID = codes.UID AND codes.startdate = 0 ORDER BY users.timestamp DESC";
$result =  $database->query($q);
$html->makeUserList($result);
$html->set('dir', '');
echo $html->doOutput();

