<?php
$authkey='';
include("include/user.php");
include("include/template.php");
$html = new Template('templates/main.tpl');
$toplink['Home']= 'index.php';
$showsections = array('page_forgotpass');
if(isset($_SESSION['forgotpass'])){
   /**
    * New password was generated for user and sent to user's
    * email address.
    */
   if($_SESSION['forgotpass']){
      $msg= "<p><strong>New Password Generated</strong></p><p>Your new password has been generated and sent to the email associated with your account.";
   }
   /**
    * Email could not be sent, therefore password was not
    * edited in the database.
    */
   else{
      $msg= "<p><strong>New Password Failure</strong></p><p>There was an error sending you the email with the new password, so your password has not been changed.";
   }       
   unset($_SESSION['forgotpass']);
}
else{
	$msg="<p><strong>Forgot Password</strong></p><p>A new password will be generated for you and sent to the email address associated with your account, all you have to do is enter your username.</p>";
	$msg.= $form->error("user");
	$html->set('form_user', $form->value("user"));
}
$html->makeLinkBars($toplink, 'toplinks');
$html->set('msg', $msg);
echo $html->doOutput($showsections);

?>

