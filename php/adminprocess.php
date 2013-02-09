<?php
$auth='auth';
date_default_timezone_set('America/Los_Angeles');

include("include/user.php");
include("include/alarm.php");
$guestcodes = new Guestcodes();
class AdminProcess
{
   /* Class constructor */
    public function __construct(){
      global $user, $database, $guestcodes;
      /* Make sure administrator is accessing page */
      if(!$user->isAdmin()){

         header("Location: index.php");
         return;
      }
	  if ($_GET['subuser']) {
		  
		  $subuser=$_GET['subuser'];
		  if ($_POST["action$subuser"] == 'level'){
			  if ($_POST["level$subuser"]){
				  $level=$_POST["level$subuser"];
				  if ($level==-1){
					  $this->procDeleteUser($subuser);
				  }
				  else {
					  $this->procUpdateLevel($subuser, $level);
				  }
			  }
		  }
		  else if ($_POST["action$subuser"] == 'door'){
			  $keypads = array('K'=>'frontDoor', 'L'=>'machineDoor');
			  foreach($keypads as $keyPadIndex=>$keyPadName){
				  if ($_POST["$keyPadName$subuser"]){
					  $value = "1";
				  }
				  else {
					  $value = "0";
					  
				  }
				  $q = "UPDATE codes SET keyPad$keyPadIndex = $value WHERE UID = $subuser AND startDate = 0;";
				  //echo $q;		  
				  $database->query($q);
				  $guestcodes->doCodeSQLLog($q);
			  }
      		header("Location: ".$user->referrer);
			  
		  }
	  }
	  
   }

   /**
    * procUpdateLevel - If the submitted username is correct,
    * their user level is updated according to the admin's
    * request.
    */
   private function procUpdateLevel($subuser, $level){
      global  $user, $database; 
      $q = "UPDATE ".TBL_USERS." SET userlevel = '" . $level . "' WHERE UID = '" . $subuser . "'";
	  $database->query($q);
       header("Location: ".$user->referrer);
   }
   
   /**
    * procDeleteUser - If the submitted username is correct,
    * the user is deleted from the database.
    */
   private function procDeleteUser($subuser){
      global $user, $database, $guestcodes;
      /* Username error checking */
         $q = "DELETE FROM users WHERE UID = $subuser LIMIT 1";
         $database->query($q);
          $q = "DELETE FROM codes WHERE UID = $subuser;";
         $database->query($q);
		$guestcodes->doCodeSQLLog($q);

         header("Location: ".$user->referrer);
      
   }
   
   
   
   /**
    * checkUsername - Helper function for the above processing,
    * it makes sure the submitted username is valid, if not,
    * it adds the appropritate error to the form.
    */
   function checkUsername($uname, $ban=false){
      global $database, $form;
      /* Username error checking */
      $subuser = $_POST[$uname];
      $field = $uname;  //Use field name for username
      if(!$subuser || strlen($subuser = trim($subuser)) == 0){
         $form->setError($field, "* Username not entered<br>");
      }
      else{
         /* Make sure username is in database */
         $subuser = stripslashes($subuser);
         if(!eregi("^([0-9a-z])+$", $subuser) ||
            !$database->usernameTaken($subuser)){
            $form->setError($field, "* Username does not exist<br>");
         }
      }
      return $subuser;
   }
};

/* Initialize process */
$adminprocess = new AdminProcess;

?>
