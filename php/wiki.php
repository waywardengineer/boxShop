<?php 
$authkey='boxshop94124';
include("include/common.php");
include("include/templateAddons.php");
if (!$user->isTrusted()){
	header("Location: index.php");
	die();
}

if (!$l) {
	$l = 'home';
}


$querystring="SELECT wiki_sections.id AS id, wiki_sections.page_name AS pagename, wiki_section_contents.txt AS contents, wiki_section_contents.date_ent AS date_ent, wiki_section_contents.edited_by AS editedby, wiki_section_contents.heading AS heading
	FROM wiki_section_contents INNER JOIN wiki_sections ON wiki_sections.lastContentsID=wiki_section_contents.id
	WHERE wiki_sections.page_name = '$l' ORDER BY wiki_section_contents.date_ent DESC" ;
$result=$database->query($querystring);
$output = '';
while ($section=@mysql_fetch_array($result, MYSQL_ASSOC)){
	$subTemplate = new wikiTemplate("templates/wikiSection.tpl");
	$tabs =array();
	$tabs[] = array('text' => 'Edit', 'url' => 'editwiki.php?id=' . $section['id'] . '&l=' . $l, 'selected' => false);
	$tabs[] = array('text' => 'New&nbsp;Section', 'url' => 'editwiki.php?id=0&l=' . $l, 'selected' => false);
	$subTemplate->setMulti(array('content'=> stripslashes($section['contents']), 'tabs'=> $subTemplate->makeTabs($tabs), 'heading'=>$section['heading'], 'lastEdit'=>"Last edited by {$section['editedby']} on {$section['date_ent']}"));
	$output .= $subTemplate->doOutput();
}
if ($output == '') {
	$subTemplate = new wikiTemplate("templates/wikiSection.tpl");

	$tabs =array();
	$tabs[] = array('text' => 'Edit', 'url' => 'editwiki.php?id=0&l=' . $l, 'selected' => false);
	$subTemplate->setMulti(array('content'=>'There\'s nothing on this page yet! Click Edit to add something.', 'tabs'=> $subTemplate->makeTabs($tabs), 'heading'=>'Empty Page'));
	$output = $subTemplate->doOutput();
}
$html->set('welcome', $welcome_msg);
$html->set("content", $output);
echo $html->doOutput();

?>

