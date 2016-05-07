<?php 

$authkey='boxshop94124';
include("include/common.php");
if (!$user->isTrusted()){
	header("Location: index.php");
	die();
}
$showsections = array();
$html->addSubTemplate('templates/editaccount.tpl');
if ($form->numErrors == 0){
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
if (isset($_SESSION['useredit'])){
	$html->set('editmsg', '<h2>Changes saved successfully!</h2>');
	unset($_SESSION['useredit']);
}
$formItems = array('userEmail', 'userPass', 'userNewPass');
foreach($formItems as $item){
	$str = "form_$item";
	$html->set($str, $form->value($item));
	$str .= 'Error';
	$html->set($str, $form->error($item));

}
echo $html->doOutput($showsections);