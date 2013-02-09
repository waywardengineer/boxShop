	{scripts}
    
    
    {/scripts}
    
    
    {content}
    <p></p>
    {forgotpassmsg}
    <form action="process.php" method="post">
    <b>Username:</b> <input type="text" name="user" maxlength="30" value="{formUser}" class="formfield">
    <input type="hidden" name="subforgot" value="1">
    <input type="submit" value="Get New Password" class="submitbtn">
    </form>
{/content}