<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Boxshop Security System</title>
<link href="{dir}style.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{dir}css/ui-darkness/jquery-ui-1.8.16.custom.css" type="text/css" media="screen" />
<script type="text/javascript" src="{dir}scripts/jquery.js"></script>


{calscripts}
<script type="text/javascript" src="{dir}scripts/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript">
	$(function() {
		$( "#codeStartDate" ).datepicker();
	});
</script>
{/calscripts}

<script type="text/javascript">
	  function changeInfoView(){
		 pageName = $('#infoView').val() + '.php';
	  	$('#eventlogcontainer').load(pageName);

	   	
	  }	
	  
	  function getRandomCode(){
		$.get("randomcode.php",
		function(data){
			$('#codeCode').val(data.code);
		}, "json");
   	  }
   
</script>

{page_admin}
	<script type="text/javascript">
        function changeLevel(uid, username){
            var formname = 'adminForm' + uid;
            var theform=document.forms[formname];


            var fieldname = 'action' + uid;
            theform.elements[fieldname].value = "level";

            fieldname = 'level' + uid;
            var thefield=theform.elements[fieldname].value; 



            var strings = [];
            strings[-1]="Really delete " + username + '?';
            strings[1]="Really demote " + username + ' to a "new user"?';
            strings[9]="Make " + username + " an admin?";
            if (thefield != 2) {
                var answer = confirm  (strings[thefield]);
            }
            else {
                var answer=1;
            }
            if (answer){
                theform.submit();
            }
        }

        function changeDoor(uid){
            var formname = 'adminForm' + uid;
            var theform=document.forms[formname];


            var fieldname = 'action' + uid;
            theform.elements[fieldname].value = "door";
            theform.submit();
        }


    </script>
{/page_admin}
</head>
<body>
<div class="topbar">{welcome}{toplinks}</div>
{page_index}
    <div class="alarmstatus">{alarmstatus}</div>
    {button2}
    {admin}

    {/admin}
	{trusted}
	
    <div id = "eventlogcontainer">
     {bargraph}
   
    </div>
    {/trusted}

{/page_index}
{page_forgotpass}
	<p></p>
    {msg}
    <form action="process.php" method="POST">
    <b>Username:</b> <input type="text" name="user" maxlength="30" value="{form_user}" class="formfield">
    <input type="hidden" name="subforgot" value="1">
    <input type="submit" value="Get New Password" class="submitbtn">
    </form>
{/page_forgotpass}
{page_admin2}
    {users}
{/page_admin2}
{page_codes}
    <div class="addpass">
        <h3>Make a temporary door password for a guest</h3>
             <p>Codes must be at least 5 digits long. You can enter numbers or letters corresponding to a phone keypad.</p>
            <form action="proccode.php" method="post">
            	<input type="hidden" name="doWhat" value="add" />
            	{codeStartDate_err}
            	<div class="formRow">
                    <div style="position:absolute; top:5px; left:25px;">Date:</div>
                    <input name="codeStartDate" id="codeStartDate" maxlength="30" class="formfield" type="text" value="{codeStartDate}" style="position:absolute; top:5px; left:65px;">
 
				</div> 
            	<div class="formRow">
                    <div style="position:absolute; top:5px; left:-10px;">Starting at:</div>
 
                  <select name="codeStartTime" id="codeStartTime" class="formfield" style="position:absolute; top:5px; left:65px;">
                    <option value="6">6AM</option>
                    <option value="10" selected="selected">10AM</option>
                    <option value="14">2PM</option>
                    <option value="18">6PM</option>
                    <option value="22">10PM</option>
                  </select>

                     <div style="position:absolute; top:5px; left:142px;">Lasting for:</div>
 
                  <select name="codeDuration" id="codeDuration" class="formfield" style="position:absolute; top:5px; left:218px;">
                    <option value="2">2 hours</option>
                    <option value="4" selected="selected">4 hours</option>
                    <option value="6">6 hours</option>
                    <option value="8">8 hours</option>
                    <option value="12">12 hours</option>
                  </select>
                </div>
                {codeCode_err}
            	<div class="formRow">
                    <div style="position:absolute; top:5px; left:21px;">Code:</div>
                    <input id="codeCode" name="codeCode" maxlength="30" class="formfield" type="text" value="{codeCode}" style="position:absolute; top:5px; left:65px;">
                    <input name="Button" type="button" class="submitbtn" style="position: absolute; top: -2px; left: 221px;" value="Make Random Code" onclick="getRandomCode()">

                </div>
            	<div class="formRow">
                    <div style="position:absolute; top:5px; left:20px;">Notes:</div>
                    <input name="codeNotes" maxlength="100" class="formfield" type="text" value="{codeNotes}" style="position:absolute; top:5px; left:65px;">
                </div>
            	<div class="formRow">
                    <input value="Add Temp Code" class="submitbtn" type="submit" style="position:absolute; top:5px; left:65px;">
				</div>
            </form>
        {codes}
    </div>
{/page_codes}
{page_register}
    <div class="addpass">
        {regmsg}

        {regform}

        <form action="process.php" method="post">

            <input type="hidden" name="subjoin" value="1">

            {form_reg_user_err}

            <div class="formRow">

                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Username:</div>

                <input name="user" maxlength="30" class="formfield" type="text" value="{form_reg_user}" style="position:absolute; top:5px; left:200px;">

            </div>

            {form_reg_pass_err}

            <div class="formRow">

                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Password:</div>

                <input name="pass" maxlength="30" class="formfield" type="password" value="{form_reg_pass}" style="position:absolute; top:5px; left:200px;">

            </div>

            {form_reg_email_err}

            <div class="formRow">

                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Email:</div>

                <input name="email" maxlength="50" class="formfield" type="text" value="{form_reg_email}" style="position:absolute; top:5px; left:200px;">

            </div>

            {form_reg_auth_err}

            <div class="formRow">

                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Boxshop Authorization Code:</div>

                <input name="auth" maxlength="30" class="formfield" type="text" value="{form_reg_auth}" style="position:absolute; top:5px; left:200px;">

            </div>

            <div class="formRow">

                <input value="Join" class="submitbtn" type="submit" style="position:absolute; top:5px; left:200px;">

            </div>

        </form>

        {/regform}
		{regcodeform}
        <form action="process.php" method="post">
            <input type="hidden" name="subedit" value="1">
             {form_userCode_err}
             <p>Codes must be at least 5 digits long. You can enter numbers or letters corresponding to a phone keypad.</p>
            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Doorcode:</div>
                <input id="codeCode" name="userCode" maxlength="30" class="formfield" type="text" value="{form_userCode}" style="position:absolute; top:5px; left:200px;">
                <input name="Button" type="button" class="submitbtn" style="position: absolute; top: -2px; left: 370px;" value="Make Random Code" onclick="getRandomCode()">

            </div>
            <div class="formRow">
                <input value="Update" class="submitbtn" type="submit" style="position:absolute; top:5px; left:200px;">
            </div>




		</form>       
        
        
        {/regcodeform}
    </div>





{/page_register}



{page_edit}

    <div class="addpass">
    	{editmsg}
        
        {editform}
                     <p>Doorcodes must be at least 5 digits long. You can enter numbers or letters corresponding to a phone keypad.</p>

        <form action="process.php" method="post">
            <input type="hidden" name="subedit" value="1">
            {form_userPass_err}
            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Password:</div>
                <input name="userPass" maxlength="30" class="formfield" type="password" value="{form_userPass}" style="position:absolute; top:5px; left:200px;">
            </div>
            {form_userNewPass_err}

            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">New Password:</div>
                <input name="userNewPass" maxlength="30" class="formfield" type="password" value="{form_userNewPass}" style="position:absolute; top:5px; left:200px;">
            </div>


            {form_userCode_err}
            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Doorcode:</div>
                <input id="codeCode" name="userCode" maxlength="30" class="formfield" type="text" value="{form_userCode}" style="position:absolute; top:5px; left:200px;">
                <input name="Button" type="button" class="submitbtn" style="position: absolute; top: -2px; left: 370px;" value="Make Random Code" onclick="getRandomCode()">

            </div>
            {form_userEmail_err}
            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Email:</div>
                <input name="userEmail" maxlength="50" class="formfield" type="text" value="{form_userEmail}" style="position:absolute; top:5px; left:200px;">
           </div>
            <div class="formRow">
                <input value="Update" class="submitbtn" type="submit" style="position:absolute; top:5px; left:200px;">
            </div>
       </form>
        {/editform}
    </div>


{/page_edit}







<div class="bottombar">

	{bottomlinks}
	{page_index2}
    <label for="infoView">Information to view:</label>
    <select name="infoView" id="infoView" onchange="changeInfoView()">
      <option value="daygraph">Last 24 Hours</option>
      <option value="weekgraph" selected="selected">Last Week</option>
      <option value="yeargraph">Last Year</option>
      <option value="eventlog">Event Log</option>
    </select>
    <script>
		changeInfoView();
    </script>
	{/page_index2}
    {login}

        <form action="process.php" method="POST">

            <p>Username:

            <input name="user" type="text" value="{form_login_user}" class="formfield" />

            {form_login_user_err}

            Password:<input type="password" name="pass" maxlength="30" value="{form_login_pass}" class="formfield" />{form_login_pass_err}

            <input type="submit" value="Login" class="submitbtn" /><input type="checkbox" name="remember"  />Remember me next time</p>

            <input type="hidden" name="sublogin" value="1" />

        

            <a href="forgotpass.php">Forgot Password</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="register.php">New Account(Boxshop Members only)</a>

        </form>

    {/login}

</div>



</body>

</html>

