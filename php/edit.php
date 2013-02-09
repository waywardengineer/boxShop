<?php
$auth='';
include("include/user.php");
include("include/template.php");
if (!$user->isTrusted()){
	header("Location: index.php");
	die();
}
$html = new Template('templates/main.tpl');
$welcome_msg = "Welcome, $user->username";
$showsections = array('page_edit', 'calscripts', 'page_admin2');
$toplink['Home'] = 'index.php';
if($user->isAdmin()){
	$toplink['Admin']= 'admin/admin.php';
}
if ($form->num_errors == 0){
	$q="SELECT code FROM codes WHERE UID = " .  $user->uid . " AND startDate = 0;";
	$result=$database->query($q);
	$row = @mysql_fetch_array($result);
	if ($row){
		$form->setValue('userCode', $row['code']);
	}
	$q="SELECT email FROM users WHERE UID = " .  $user->uid . ";";
	//echo $q;
	$result=$database->query($q);
	$row = @mysql_fetch_array($result);
	//echo $row['email'];
	$form->setValue('userEmail', $row['email']);
}
else {
	$html->set('editmsg', '<h2>Sorry, there were errors in your changes:</h2>');
}
if ($_SESSION['useredit']){
	$html->set('editmsg', '<h2>Changes saved successfully!</h2>');
	unset($_SESSION['useredit']);
}
$formItems = array('userEmail', 'userPass', 'userNewPass', 'userCode');
foreach($formItems as $item){
	$str = "form_$item";
	$html->set($str, $form->value($item));
	$str .= '_err';
	$html->set($str, $form->error($item));

}
$toplink['Logout'] = 'process.php';
$html->makeLinkBars($toplink, 'toplinks');
$html->set('welcome', $welcome_msg);
echo $html->doOutput($showsections);