<form name="deleteVersion" method="post" action="delete.php">

<div style="height:200px; overflow:auto;">
<!-- BEGIN versionbutton -->
<label>{versionbutton.DESCRIP}</label><input type="radio" onclick="changeVersion()" name="versionId" id="{versionbutton.ID}" value="{versionbutton.ID}"{versionbutton.SELECTED}>
<br>
<!-- END versionbutton -->
</div>
<p><input type="button" value="Delete Selected Version" onclick="dropVersion()" /></p>
<input type="hidden" name="delete" value="version" />
<input type="hidden" name="redirURL" value="{REDIRURL}" />
</form>

