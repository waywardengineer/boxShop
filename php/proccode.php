<?php
$authkey='';
date_default_timezone_set('America/Los_Angeles');

include("include/user.php");
include("include/alarm.php");
$guestcodes=new Guestcodes();
if ($user->isTrusted()){
	if($_POST['doWhat']=='add'){
		if($_POST['codeStartDate']){
			$codeStartDate=strtotime($_POST['codeStartDate']);
			
			$form->setValue('codeDate', $_POST['codeStartDate']);
			if (!$codeStartDate){
				$form->setError("codeDate_err", "* Couldn't make date out of input");	
			}
			else {
				$codeStartDate += $_POST['codeStartTime'] * 3600;
				$codeEndDate = $codeStartDate + $_POST['codeDuration'] * 3600;
			}

		}
		else {
			$form->setError("codeDate_err", "* No Date Entered");
		}
		if($_POST['codeCode']){
			$codeCode=$_POST['codeCode'];
			$form->setValue('codeCode', $codeCode);
			$output=$guestcodes->validateAndConvert($codeCode, $user->uid);
			if ($output['err']>0){
				$form->setError("codeCode_err", $output['errDescrips']);	
			}
			else {
				$code=$output['code'];
			}
		}
		else {
			$form->setError("codeCode_err", "* No Code Entered");
		}
		if ($_POST['codeNotes']){
			$notes= htmlentities($_POST['codeNotes']);
			$form->setValue('codeNotes', $notes);
		}
		if($form->num_errors == 0){
			$q="INSERT INTO codes(UID,startDate, endDate, notes, code, keyPadK, keyPadL) VALUES ('" . $user->uid . "', " . $codeStartDate . ", " . $codeEndDate . ", '" . @mysql_real_escape_string($notes) . "', '" . $code . "', 1, 0)";
			$database->query($q);
			$guestcodes->doCodeSQLLog($q);

		}
		else {
			$_SESSION['value_array'] = $_POST;
			$_SESSION['error_array'] = $form->getErrorArray();
		}
	}
	else if ($_POST['doWhat']=='delete'){
		if (preg_match('/[0-9]*/', $_POST['deleteWhat'])){
			$id=$_POST['deleteWhat'];
		}
		else {
			exit;
		}
		if ($user->isAdmin()){
			$hasAuth=1;
		}
		else {
			$q="SELECT UID FROM codes WHERE ID=$id";
			$row=@mysql_fetch_array($database->query($q));
			if($row['UID']==$user->uid){
				$hasAuth=1;
			}
		}
		if ($hasAuth){
			$q="DELETE FROM codes WHERE ID=$id LIMIT 1";
			$database->query($q);
			$guestcodes->doCodeSQLLog($q);
		}
	}
	header("Location: codes.php");
}
else {
	header("Location: index.php");
}

