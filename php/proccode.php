<?php
$authkey='';
include("include/user.php");
include("include/alarm.php");
$guestcodes=new Guestcodes();
if ($user->isTrusted()){
	if($_POST['doWhat']=='add'){
		if($_POST['codeDate']){
			$codeDate=strtotime($_POST['codeDate']);
			$form->setValue('codeDate', $_POST['codeDate']);
			if (!$codeDate){
				$form->setError("codeDate_err", "* Couldn't make date out of input");	
			}
		}
		else {
			$form->setError("codeDate_err", "* No Date Entered");
		}
		if($_POST['codeCode']){
			$codeCode=$_POST['codeCode'];
			$form->setValue('codeCode', $codeCode);
			$output=$guestcodes->validateAndConvert($codeCode, $user->uid);
			$errDescrips=array(1=>'* The code must be 5 or more digits long', 2=>'* The code must contain only numbers or letters', 3=>'* That code\'s being used by somebody else already');
			if ($output['err']>0){
				$form->setError("codeCode_err", $errDescrips[$output['err']]);	
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
			$q="INSERT INTO codes(UID, codeDate, uses, notes, code) VALUES ('" . $user->uid . "', " . $codeDate . ", 0, '" . @mysql_real_escape_string($notes) . "', '" . $code . "')";
			$database->query($q);
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
		}
	}
	header("Location: codes.php");
}
else {
	header("Location: index.php");
}

