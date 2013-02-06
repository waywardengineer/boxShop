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
		$( "#codeDate" ).datepicker();
	});
</script>
{/calscripts}

<script type="text/javascript">
	  function showEventLog(){
	  	if ($('#eventlogcontainer').css('height') == "0px"){
	  		$('#eventlogcontainer').css('display', 'block');
	  		$('#eventlogcontainer').animate({
	  			height: 220
	  		});
	  		$('#eventlogcontainer').load('eventlog.php');

	  	}
	  	else {
	  		$('#eventlogcontainer').animate({
	  			height: 0
	  		});
	  		$('#eventlogcontainer').empty();
	  		$('#eventlogcontainer').css('display', 'none');
	  	}
	   	
	  }	   
</script>

{page_admin}
	<script type="text/javascript">
        function changed(uid, username){
            var formname = 'adminForm' + uid;
            var theform=document.forms[formname];
            var fieldname = 'level' + uid;
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
    {button1}	
    <div id = "eventlogcontainer" style="display:none">
    
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
        <h3>Make a door password for a guest</h3>
        <p>Create a temporary password for a guest. Password is good for 2 hours after someone enters it for the first time</p>
            <form action="proccode.php" method="post">
            	<input type="hidden" name="doWhat" value="add" />
            	{codeDate_err}
            	<div class="formRow">

                    <div style="position:absolute; top:5px; left:25px;">Date:</div>

                    <input name="codeDate" id="codeDate" maxlength="30" class="formfield" type="text" value="{codeDate}" style="position:absolute; top:5px; left:65px;">

	                

                </div>

                {codeCode_err}

            	<div class="formRow">

                    <div style="position:absolute; top:5px; left:21px;">Code:</div>

                    <input name="codeCode" maxlength="30" class="formfield" type="text" value="{codeCode}" style="position:absolute; top:5px; left:65px;">

                    <div style="position:absolute; top:5px; left:225px;">Numbers or phone keypad letters, no # at end, minimum 5 characters</div>

                </div>

            	<div class="formRow">

                    <div style="position:absolute; top:5px; left:21px;">Notes:</div>

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

    </div>





{/page_register}

<div class="bottombar">

	{bottomlinks}

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

