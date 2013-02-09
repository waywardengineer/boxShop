<?php 
$auth='auth';
date_default_timezone_set('America/Los_Angeles');
include("include/common.php");
$html = new Template('templates/main.tpl');
$html->createNav();


$subTemplate = new Template("templates/wikiAddPage.tpl");

$html->set("content", $subTemplate->doOutput());
echo $html->doOutput();

?>