<?php

$auth='auth';
include("include/common.php");
$html = new Template('templates/main.tpl');
$html->createNav();

include("include/alarm.php");
include("include/templateAddons.php");
print_r($form);
if (!$user->isTrusted()){
	header("Location: index.php");
	die();
}
$subTemplate = new codesTemplate('templates/codes.tpl');
$alarm = new Alarm();
$guestCodes=new Guestcodes();
$welcome_msg = "Welcome, $user->username";
$subTemplate->setMulti(array('codeCode'=> $form->value("codeCode"), 'codeCodeError'=>$form->error("codeCodeError"), 'codeDate'=>$form->value("codeDate"), 'codeDateError'=>$form->error("codeDateError"), 'codeNotes'=>$form->value("codeNotes")));
$subTemplate->makePermCodesList($guestCodes->getPermCodes($user->uid));
$subTemplate->makeGuestCodesList($guestCodes->getGuestCodes($user->uid));
$html->setMulti(array('msg' => $welcome_msg, 'content' => $subTemplate->doOutput()));
echo $html->doOutput(array('codescripts'));