
<form name="edit" id="pageEditor" method="post" action="edit.php?l={PAGENAME}">
Page Heading: <input type="text" name="pageheading" value="{EDITSECTIONHEADING}" /><br />
<p><input type="submit" value="Save" /></p>

<textarea id="editor" name="sectioncontents">{EDITSECTIONCONTENTS}</textarea>
<input type="hidden" name="id" value="{ID}" />
<input type="hidden" name="pagename" value="{PAGENAME}" />
<input type="hidden" name="savesection" value="TRUE" />
<p><input type="submit" value="Save" /></p>