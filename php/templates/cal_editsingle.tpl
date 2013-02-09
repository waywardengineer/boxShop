<form name="eventsedit" id="eventsedit" action="calendar.php?action={formaction}" method="post"><fieldset>
<p><label for="title">Event Title</label><br /><input type="text" id="title" name="title" value="{formtitle}" />{titleasterisk}</p>
<p><label for="day">Date</label><br /><input type="text" name="day" id="day" value="{formday}" />{dayasterisk}</p>
<p><label for="start">Start Time</label><br /><input type="text" name="start" value="{formstart}" />{startasterisk}<select name="ampm"><option value="pm" {pmselected}>PM</option><option value="am" {amselected}>AM</option></select></p>
<p><label for="length">Duration</label><br /><input type="text" name="length" value="{formlength}" />Hours{lengthasterisk}</p>
<p>
<label for="description">Description</label><br /><textarea name="description" id="description">{formdescription}</textarea>{descriptionasterisk}</p>
<input type="hidden" id="formsubmitted" name="formsubmitted" value="{formsubmitted}" />
<input type="hidden" name="editid" value="{editid}" />
<input type="submit" value="Save" />
<br />{delete} </fieldset>
</form>


<script type="text/javascript">
	$(function() {
		$( "#day" ).datepicker();
	});

   
</script>






