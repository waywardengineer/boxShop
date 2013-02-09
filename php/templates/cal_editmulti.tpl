<form name="eventsedit" id="eventsedit" action="calendar.php?action=addmulti" method="post"><fieldset>
<p><label for="title">Event Title</label><br /><input type="text" id="title" name="title" value="{formtitle}" />{titleasterisk}<br /></p>

<p>

<label for="repeatfrequency">This event will be on</label>
<select name="repeatfrequency" id="repeatfrequency">
    <option value="1">The first</option>
    <option value="2">The second</option>
    <option value="3">The third</option>
    <option value="-1">The last</option>
    <option value="0">Every</option>
</select>{repeatfrequencyasterisk}

<select name="repeatday" id="repeatday">
    <option value="0">Sunday</option>
    <option value="1">Monday</option>
    <option value="2">Tuesday</option>
    <option value="3">Wednesday</option>
    <option value="4">Thursday</option>
    <option value="5">Friday</option>
    <option value="6">Saturday</option>
</select>{repeatdayasterisk} of every month</p>

<p><label for="repeatcount">Add </label><input type="text" name="repeatcount" id="repeatcount" value="{formrepeatcount}" />{repeatcountasterisk} events ahead</p>


<p><label for="start">Start Time</label><br /><input type="text" name="start" value="{formstart}" />{startasterisk}<select name="ampm"><option value="pm" {pmselected}>PM</option><option value="am" {amselected}>AM</option></select></p>
<p><label for="length">Duration</label><br /><input type="text" name="length" value="{formlength}" />Hours{lengthasterisk}</p>
<div id="newlocationdiv" style="display:none;"><span>New Location</span><input type="text" name="newlocation" value="{formnewlocation}" />{newlocationasterisk}<br /></div>
<p><label for="description">Description</label><br /><textarea name="description" id="description">{formdescription}</textarea>{descriptionasterisk}</p>
<input type="hidden" name="formsubmitted" id="formsubmitted" value="multi" />
<input type="hidden" name="editid" value="{editid}" />
<input type="submit" value="Save" /> </fieldset>
</form>


