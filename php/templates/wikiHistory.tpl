




<form name="deleteVersion" method="post" action="delete.php">

<div style="height:200px; overflow:auto;">
{versionSelector}
</div>
<div id="historybox">{content}</div>
<p><input type="button" value="Delete Selected Version" onclick="dropVersion()" /></p>
<input type="hidden" name="delete" value="version" />
<input type="hidden" name="redirURL" value="history.php?id={id}&l={l}" />
</form>
