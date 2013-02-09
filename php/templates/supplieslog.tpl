{scripts}

<link href="DataTables-1.9.4/media/css/jquery.dataTables.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="Boxshop%20Security%20System_files/jquery-ui-1.css" type="text/css" media="screen">
<script type="text/javascript" language="javascript" src="DataTables-1.9.4/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" language="javascript" src="DataTables-1.9.4/examples/examples_support/jquery.jeditable.js"></script>

<script type="text/javascript">
var items = {shitHead};
	$.fn.dataTableExt.afnFiltering.push(
		function( oSettings, aData, iDataIndex ) {
			var categoryInput = document.getElementById('categoryInput').value ;
			var itemInput = document.getElementById('itemInput').value ;
			var userInput = document.getElementById('userInput').value ;
			var categoryID = aData[0];
			var itemID = aData[1];
			var UID = aData[2];

			$output = true;
			if ( categoryInput != 0 && categoryInput != categoryID){
				$output = false;
			}
			if ( itemInput != 0 && itemInput != itemID){
				$output = false;
			}
			if ( userInput != 0 && userInput != UID){
				$output = false;
			}

			return $output;
		}
	);
	
	var oTable;

	$(document).ready(function() {
		
		oTable = $('#itemTable').dataTable({
			"sDom": '<"H">rt<"F"lip>',
			"aaSorting":[],
			"aoColumnDefs": [
				{ "bVisible": false, "aTargets": [ 0 ] },
				{ "bVisible": false, "aTargets": [ 1 ] },
				{ "bVisible": false, "aTargets": [ 2 ] }
			] } );
		/* Initialise datatables */
	} );					

	function changedCat(){
		var $el = $("#itemInput");
		$el.empty(); // remove old options
		$el.append($("<option></option>")
		.attr("value", 0).text('All'));

		if ($('#categoryInput').val() == '0'){
			$('#itemInputDiv').css('display', 'none');
		}
		else {
			$('#itemInputDiv').css('display', 'inline');
			$.each(items[$('#categoryInput').val()], function(key, value) {
				$el.append($("<option></option>")
				.attr("value", key).text(value));
			});
		}
		$('#itemTable').dataTable().fnDraw();
	}
</script>

{/scripts}


{content}

<div style="float:left">
    <span style="margin:4px 4px 4px 0px;">Category:</span><select id="categoryInput" onchange="changedCat()" name="categoryInput" maxlength="30" class="formfield" type="text"><option selected="selected" value="0">All</option>{categoryOptions}</select>
    <div id="itemInputDiv" style="display:none;"><span style="margin:4px 4px 4px 0px;">Item:</span><select id="itemInput" onchange="$('#itemTable').dataTable().fnDraw()" name="itemInput" maxlength="30" class="formfield" type="text"></select></div><br>
    <span style="margin:4px 4px 4px 0px;">User:</span><select id="userInput" onchange="$('#itemTable').dataTable().fnDraw()" name="userInput" maxlength="30" class="formfield" type="text"><option selected="selected" value="0">All</option>{userOptions}</select>

</div>
<div class="clear"></div>

{itemTable}

{/content}