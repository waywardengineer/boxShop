<?php
$auth='auth';
include('include/user.php');
$id=intval($_GET['id']);
if ($user->isTrusted()){
	$querystring="SELECT txt, heading FROM wiki_section_contents WHERE id=$id";
	$result=$database->query($querystring);
	$row=@mysql_fetch_array($result, MYSQL_ASSOC);
	echo '<br /><h2>' . $row['heading'] . '</h2><br />' . $row['txt'];
}
?>