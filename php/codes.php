<?php

$authkey='boxshop94124';
include("include/common.php");
include("include/alarm.php");
include("include/templateAddons.php");
if (!$user->isTrusted()){
	header("Location: index.php");
	die();
}
$subTemplate = new codesTemplate('templates/codes.tpl');
$alarm = new Alarm();
$guestCodes=new Guestcodes();
$subTemplate->setMulti(array('codeCode'=> $form->value("codeCode"), 'codeCodeError'=>$form->error("codeCodeError"), 'codeDate'=>$form->value("codeDate"), 'codeDateError'=>$form->error("codeDateError"), 'codeNotes'=>$form->value("codeNotes")));
$subTemplate->makePermCodesList($guestCodes->getPermCodes($user->uid));
$subTemplate->makeGuestCodesList($guestCodes->getGuestCodes($user->uid));
$html->set('content', $subTemplate->doOutput());
echo $html->doOutput(array('codescripts'));