<?php
date_default_timezone_set('America/Los_Angeles');
$authkey='';
include("include/user.php");
include("include/template.php");
$showsections=array('page_register');
$html = new Template('templates/main.tpl');
if($user->logged_in and !isset($_SESSION['regsuccess'])){
	header("Location: index.php");
	exit();
}
else if(isset($_SESSION['regsuccess'])){
   /* Registration was successful */
   if($_SESSION['regsuccess']){
	   if (isset($_SESSION['codesuccess'])){
		   $regmsg='<h1>Complete!</h1>
		   		<p>Your doorcode is saved and ready for use. Note that it may take 2 tries to register on the first use, and that machineshop access for your code will have to be granted by Ray or Charlie</p>';
		   unset($_SESSION['regsuccess']);
		   unset($_SESSION['reguname']);
		   unset($_SESSION['codesuccess']);
	   }
	   else {
		$showsections[] = 'regcodeform';

		  if ($form->num_errors == 0){
			   $regmsg='<h1>Registered!</h1>
			  <p>Thanks <strong> ' .$_SESSION['reguname']. '</strong>, you are now logged in. You can now choose a door code.</p>';
		  }
		  else {
			   $regmsg='<p><strong>Oops</strong>, there\'s a problem with that code</p>';
				$html->set('form_userCode', $form->value('userCode'));
				$html->set('form_userCode_err', $form->error('userCode'));
			  
		  }
	   }
   }
   /* Registration failed */
   else{
	   $regmsg="<h1>Registration Failed</h1>
     <p>We're sorry, but an error has occurred and your registration for the username <strong>".$_SESSION['reguname']." </strong>
          could not be completed.<br>Please try again at a later time.</p>";
	   unset($_SESSION['regsuccess']);
	   unset($_SESSION['reguname']);

   }
}
/**
 * The user has not filled out the registration form yet.
 * Below is the page with the sign-up form, the names
 * of the input fields are important and should not
 * be changed.
 */
else{
	$showsections[]='regform';
	$regmsg='<h1>Register</h1>';
	if($form->num_errors > 0){
	   $regmsg .= '<p>' .$form->num_errors.' error(s) found</p>';
	}
	$html->set('form_reg_user', $form->value("user"));
	$html->set('form_reg_user_err', $form->error("user"));
	$html->set('form_reg_pass', $form->value("pass"));
	$html->set('form_reg_pass_err', $form->error("pass"));
	$html->set('form_reg_email', $form->value("email"));
	$html->set('form_reg_email_err', $form->error("email"));
	$html->set('form_reg_auth', $form->value("auth"));
	$html->set('form_reg_auth_err', $form->error("auth"));
}


$html->makeLinkBars(array('Home'=>'index.php'), 'toplinks');
$html->set('welcome', $welcome_msg);
$html->set('regmsg', $regmsg);
echo $html->doOutput($showsections);
