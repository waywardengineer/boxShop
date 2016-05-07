
{scripts}
<link href="css/calendar.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="scripts/calfunctions.js"></script>
<script type="text/javascript" src="scripts/jquery.validate.min.js"></script>


<link rel="stylesheet" href="css/ui-darkness/jquery-ui-1.8.16.custom.css" type="text/css" media="screen">

<script type="text/javascript" src="scripts/jquery-ui-1.8.16.custom.min.js"></script>

		  
		  
		  
		  



	<script type="text/javascript">
		$(document).ready(function(){
				$("#calendardiv").load('cal_calendar.php');
		});
    </script>


{/scripts}




{content}

{message}
<a class="tab" href="calendar.php?action=addsingle">Add a non-repeating event</a><a class="tab" href="calendar.php?action=addmulti">Add a repeating event</a>
{calForms}
<div id="eventMenu"></div>
<div id="calendardiv">


</div>
<div style="height:200px; clear:both;"></div>
<form><input type="hidden" id="locationID" value="all" /><input type="hidden" id="categoryID"  value="all" /></form>

{/content}

