<?php
date_default_timezone_set('America/Los_Angeles');
$auth='auth';
include("include/user.php");
include("include/template.php");
$html = new Template('templates/main.tpl', 'templates/register.tpl');
$html->createNav();
$showsections = array();
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

		  if ($form->numErrors == 0){
			   $regmsg='<h1>Registered!</h1>
			  <p>Thanks <strong> ' .$_SESSION['reguname']. '</strong>, you are now logged in. You can now choose a door code.</p>';
		  }
		  else {
			   $regmsg='<p><strong>Oops</strong>, there\'s a problem with that code</p>';
				$html->set('formUserCode', $form->value('userCode'));
				$html->set('formUserCodeErr', $form->error('userCode'));
			  
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
	if($form->numErrors > 0){
	   $regmsg .= '<p>' .$form->numErrors.' error(s) found</p>';
	}
	$html->set('formRegUser', $form->value("user"));
	$html->set('formRegUserError', $form->error("user"));
	$html->set('formRegPass', $form->value("pass"));
	$html->set('formRegPassError', $form->error("pass"));
	$html->set('formRegEmail', $form->value("email"));
	$html->set('formRegEmailError', $form->error("email"));
	$html->set('formRegAuth', $form->value("auth"));
	$html->set('formRegAuthError', $form->error("auth"));
}


$html->set('welcome', $welcome_msg);
$html->set('regmsg', $regmsg);
echo $html->doOutput($showsections);
