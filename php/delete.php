<?php 
$authkey='boxshop94124';
include('include/user.php');
if (!$user->isTrusted()) die();
$url = $_POST['redirURL'];
if ($_POST['delete'] == 'version'){
	$id = intval($_POST['versionId']);
	$querystring = "SELECT section_id FROM wiki_section_contents WHERE id=$id";
	$result = mysql_query($querystring);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$sectionID = $row['section_id'];
	$querystring = "DELETE FROM wiki_section_contents WHERE id=$id";
	mysql_query($querystring);
	$querystring = "SELECT id FROM wiki_section_contents WHERE section_id=$sectionID ORDER BY date_ent DESC LIMIT 1";
	$result = mysql_query($querystring);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$querystring = "UPDATE wiki_sections SET lastContentsID = " . $row['id'] . " WHERE id = $sectionID";
	mysql_query($querystring);	
}
if ($_POST['delete'] == 'section'){
	$id = intval($_POST['sectionId']);
	$querystring = "DELETE FROM wiki_section_contents WHERE section_id=$id";
	mysql_query($querystring);
	$querystring = "DELETE FROM wiki_sections WHERE id=$id";
	mysql_query($querystring);
}
header("Location: ".$url);

?>



