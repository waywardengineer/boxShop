{scripts}
<script type="text/javascript">
	  function getRandomCode(inputId){
		$.get("randomcode.php",
		function(data){
			$('#permCode').val(data.code);
		}, "json");
   	  }
</script>


{/scripts}

{content}
    <div class="addpass">
        {regmsg}
        {regform}
        <form action="process.php" method="post">
            <input type="hidden" name="subjoin" value="1">
            {formRegUserError}
            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Username:</div>
                <input name="user" maxlength="30" class="formfield" type="text" value="{formRegUser}" style="position:absolute; top:5px; left:200px;">
            </div>
            {formRegPassError}
            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Password:</div>
                <input name="pass" maxlength="30" class="formfield" type="password" value="{formRegPass}" style="position:absolute; top:5px; left:200px;">
            </div>
            {formRegEmailError}
            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Email:</div>
                <input name="email" maxlength="50" class="formfield" type="text" value="{formRegEmail}" style="position:absolute; top:5px; left:200px;">
            </div>
           {formRegAuthError}
            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Boxshop Authorization Code:</div>
                <input name="auth" maxlength="30" class="formfield" type="text" value="{formRegAuth}" style="position:absolute; top:5px; left:200px;">
            </div>
            <div class="formRow">
                <input value="Join" class="submitbtn" type="submit" style="position:absolute; top:5px; left:200px;">
            </div>
        </form>
        {/regform}
		{regcodeform}
        <form action="proccode.php" method="post">
            <input type="hidden" name="doWhat" value="changePerm">
             {permCodeError}
             <p>Codes must be at least 5 digits long. You can enter numbers or letters corresponding to a phone keypad.</p>
            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Doorcode:</div>
                <input id="permCode" name="permCode" maxlength="30" class="formfield" type="text" value="{permCode}" style="position:absolute; top:5px; left:200px;">
                <input name="Button" type="button" class="submitbtn" style="position: absolute; top: -2px; left: 370px;" value="Make Random Code" onclick="getRandomCode()">
            </div>
            <div class="formRow">
                <input value="Update" class="submitbtn" type="submit" style="position:absolute; top:5px; left:200px;">
            </div>
		</form>       
        {/regcodeform}
    </div>
{/content}