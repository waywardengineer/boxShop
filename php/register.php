<?php
$authkey='';
include("include/user.php");
include("include/template.php");
$showsections=array('page_register');
$html = new Template('templates/main.tpl');
if($user->logged_in){
	header("Location: index.php");
	exit();
}
else if(isset($_SESSION['regsuccess'])){
   /* Registration was successful */
   if($_SESSION['regsuccess']){
	   $regmsg='<h1>Registered!</h1>
      <p>Thanks <strong> ' .$_SESSION['reguname']. '</strong>, you may now log in</p>';
	  session_unset ();
	  
   }
   /* Registration failed */
   else{
	   $regmsg="<h1>Registration Failed</h1>
     <p>We're sorry, but an error has occurred and your registration for the username <strong>".$_SESSION['reguname']." </strong>
          could not be completed.<br>Please try again at a later time.</p>";
   }
   unset($_SESSION['regsuccess']);
   unset($_SESSION['reguname']);
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
