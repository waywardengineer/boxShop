<?php
$auth='auth';
include("include/common.php");
$html = new Template('templates/main.tpl');
$html->createNav();

include("include/templateAddons.php");

if (!$user->isTrusted()){
	header("Location: index.php");
	die();
}

if (!$l) {
	$l = 'home';
}
$subTemplate = new wikiTemplate("templates/wikiHistory.tpl");
$querystring="SELECT id, date_ent, edited_by FROM wiki_section_contents WHERE section_id = $id ORDER BY date_ent DESC";
$result=mysql_query($querystring);
$versionSelector = $subTemplate->makeHistoryButtons($result);

$tabs =array();
	$tabs[] = array('text' => 'View', 'url' => "index.php?l=$l", 'selected' => false);
	$tabs[] = array('text' => 'Edit', 'url' => "edit.php?id=$id&l=$l", 'selected' => false);
	$tabs[] = array('text' => 'History', 'url' => '', 'selected' => true);
	if ($user->isAdmin()){
		$tabs[] = array('text' => 'Delete', 'onclick' => "deleteSection($id)", 'selected' => false);
	}

$querystring = "SELECT heading, txt, edited_by, date_ent FROM wiki_section_contents WHERE section_id = $id ORDER BY date_ent DESC LIMIT 1";
$result=mysql_query($querystring);
$row=@mysql_fetch_array($result, MYSQL_ASSOC);
$subTemplate->setMulti(array('id'=>$id, 'l'=>$l, 'content'=>$row['txt'], 'tabs'=> $subTemplate->makeTabs($tabs), 'heading'=>$row['heading'], 'versionSelector'=>$versionSelector, 'lastEdit'=>"Last edited by {$row['edited_by']} on {$row['date_ent']}"));



$output = $subTemplate->doOutput();
	
$html->set("content", $output);
echo $html->doOutput(array('wikiscripts', 'wikiform'));

?>



