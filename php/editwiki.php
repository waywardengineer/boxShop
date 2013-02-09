<?php 
$auth='auth';
date_default_timezone_set('America/Los_Angeles');
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

$id = $_REQUEST['id'];
$tabs = array();
$tabs[] = array('text' => 'View Page', 'url' => "wiki.php?l=$l", 'selected' => false);
if ($id > 0){
	$tabs[] = array('text' => 'History', 'url' => "history.php?id=$id&l=$l", 'selected' => false);
	if($user->isAdmin()){
		$tabs[] = array('text' => 'Delete', 'onclick' => 'deleteSection(\'' . $id . '\')', 'selected' => false);
	}

}
else {
	if (!is_dir("content/uploads/$l")){
		mkdir("content/uploads/$l", 0700);
	}

}
if (isset($_POST['savesection'])){
	$subTemplate = new wikiTemplate("templates/wikiSection.tpl");
	$err=false;
	if ($id==0){
		$querystring="INSERT INTO wiki_sections (page_name, lastContentsID) VALUES ('$l', 0);";
		if (@mysql_query($querystring)){
			$querystring="SELECT id FROM wiki_sections WHERE page_name = '$l' ORDER BY id DESC LIMIT 1";
			$result=mysql_query($querystring);
			$row=@mysql_fetch_array($result, MYSQL_ASSOC);
			if ($row['id']){
				$id = $row['id'];
			}
			else {
				$err=true;
			}
			
		}
		else {
			$err=true;
		}
	}
		
	$querystring="INSERT INTO wiki_section_contents (section_id, edited_by, txt, heading) VALUES ($id, '" . $userdata['username'] . "', '" . addslashes($_POST['sectioncontents']) . "', '" . $_POST['pageheading'] . "');";
	if (@mysql_query($querystring) && !$err){
		$querystring="SELECT id FROM wiki_section_contents WHERE section_id = $id ORDER BY id DESC LIMIT 1";
		$result=mysql_query($querystring);
		$row=mysql_fetch_array($result, MYSQL_ASSOC);
		$querystring = 'UPDATE wiki_sections SET lastContentsID = ' . $row['id'] . " WHERE id = $id LIMIT 1";
		mysql_query($querystring);
		$msg = 'Your changes have been saved!';
	}
	else {
		$msg = 'Sorry! there was an error saving your changes :(';
	}
	$subTemplate->setMulti(array('content'=>'', 'tabs'=> $subTemplate->makeTabs($tabs), 'heading'=>$msg));;
}
else {
	$subTemplate = new wikiTemplate("templates/wikiEditSection.tpl");

	if ($id > 0){
		$querystring="SELECT txt, heading FROM wiki_section_contents WHERE section_id = $id ORDER BY date_ent DESC LIMIT 1";
		$result=mysql_query($querystring);
		$section=@mysql_fetch_array($result, MYSQL_ASSOC);
		$subTemplate->setMulti(array('heading' => $section['heading'], 'content' => $section['txt'], 'ID' => $id, 'PAGENAME' => $l));
	}
	$subTemplate->setMulti(array('tabs'=> $subTemplate->makeTabs($tabs), 'id'=>$id, 'l' => $l));
	$subTemplate->xinhaInit($l);

}
$html->set("content", $subTemplate->doOutput());
echo $html->doOutput(array("wikiscripts"));


