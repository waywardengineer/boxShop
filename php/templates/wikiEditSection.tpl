{xinhaScript}{tabs}
<form name="edit" id="pageEditor" method="post" action="editwiki.php?l={l}">
Page Heading: <input type="text" name="pageheading" value="{heading}" /><br />
<p><input type="submit" value="Save" /></p>

<textarea id="editor" name="sectioncontents" style="width:100%; height:500px;">{content}</textarea>
<input type="hidden" name="id" value="{ID}" />
<input type="hidden" name="pagename" value="{l}" />
<input type="hidden" name="savesection" value="TRUE" />
<p><input type="submit" value="Save" /></p>
</form>


<form name="deletesection" id="deletesection" method="post" action="delete.php">
<input type="hidden" name="delete" value="section" />
<input type="hidden" name="redirURL" value="wiki.php?l={l}" />
<input type="hidden" name="sectionId" id="sectionId" value="{id}" />
</form>';
