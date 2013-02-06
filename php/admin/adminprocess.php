<?php
/**
 * AdminProcess.php
 * 
 * The AdminProcess class is meant to simplify the task of processing
 * admin submitted forms from the admin center, these deal with
 * member system adjustments.
 *
 * Written by: Jpmaster77 a.k.a. The Grandmaster of C++ (GMC)
 * Last Updated: August 15, 2004
 */
$authkey='';
include("../include/user.php");

class AdminProcess
{
   /* Class constructor */
    public function __construct(){
      global $user;
      /* Make sure administrator is accessing page */
      if(!$user->isAdmin()){

         header("Location: ../index.php");
         return;
      }
	  if ($_GET['subuser']) {
		  
		  $subuser=$_GET['subuser'];
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
      global $user, $database;
      /* Username error checking */
         $q = "DELETE FROM ".TBL_USERS." WHERE UID = $subuser LIMIT 1";
         $database->query($q);
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
