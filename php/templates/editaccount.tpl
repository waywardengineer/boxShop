{scripts}


{/scripts}


{content}

    <div class="addpass">
    	{editmsg}
        

        <form action="process.php" method="post">
            <input type="hidden" name="subedit" value="1">
            {form_userPassError}
            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Password:</div>
                <input name="userPass" maxlength="30" class="formfield" type="password" value="{form_userPass}" style="position:absolute; top:5px; left:200px;">
            </div>
            {form_userNewPassError}

            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">New Password:</div>
                <input name="userNewPass" maxlength="30" class="formfield" type="password" value="{form_userNewPass}" style="position:absolute; top:5px; left:200px;">
            </div>


           {form_userEmailError}
            <div class="formRow">
                <div style="position:absolute; top:6px; left:0px; width:190px; text-align:right">Email:</div>
                <input name="userEmail" maxlength="50" class="formfield" type="text" value="{form_userEmail}" style="position:absolute; top:5px; left:200px;">
           </div>
            <div class="formRow">
                <input value="Update" class="submitbtn" type="submit" style="position:absolute; top:5px; left:200px;">
            </div>
       </form>
    </div>
    {/content}