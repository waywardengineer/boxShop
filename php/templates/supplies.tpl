{scripts}

<link href="DataTables-1.9.4/media/css/jquery.dataTables.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="Boxshop%20Security%20System_files/jquery-ui-1.css" type="text/css" media="screen">
<script type="text/javascript" language="javascript" src="DataTables-1.9.4/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" language="javascript" src="DataTables-1.9.4/examples/examples_support/jquery.jeditable.js"></script>
<script type="text/javascript">
	var items = {shitHead};
	var oTable;
</script>
<script type="text/javascript">
	$.fn.dataTableExt.afnFiltering.push(
		function( oSettings, aData, iDataIndex ) {
			var categoryInput = document.getElementById('categoryInput').value ;
			var category = aData[0];
			if ( categoryInput == 0 || categoryInput == category)
			{

				return true;
			}

			return false;
		}
	);
			
	function addButtonIfEmpty(){
		if (oTable.fnSettings().fnRecordsDisplay()==0){
			
			$("#itemTable tbody").find("td:first").append('<br><input type="button" onclick="enterRecord()" value="New record">');
		}
	}
		

	function fnFormatDetails ( oTable, nTr ){
		var arr=new Array();
		$('#itemTable').find('thead th').each(function(){
			  arr.push( $(this).width() );
		})
		if (typeof(nTr)==='undefined'){
			var aData = false;
		}
		else {
			
			var aData = oTable.fnGetData( nTr );
		}
		var sOut = '<div id="logbuttons" class="dataForm"><input type="button" onclick = "showLogForm()" value="Log a purchase">';
		if (aData && aData['6'] == ''){
			sOut +=  '<form action="suppliesprocess.php" method="post"><input type="hidden" name="itemIDneeded" id="itemIDneeded" value = "' + aData[1] + '"><input type="submit" value="This item is needed now"></form>';
		}
		sOut += '</div><div id="logform" style="display:none;" class="dataForm"><form action="suppliesprocess.php" method="post"><table cellpadding="5" cellspacing="3" border="0" ><tr>';
		sOut += '<td style="padding: 3px 10px; width: ' + arr[0] + 'px;"><select id="categoryID" name="categoryID" onchange="updateItems()" type="text">{categoryOptions}<option value="0">New Category</option></select><br>';
		sOut += '<div id="newcategory" style="display:none;"><input name="newcategory"></div></td>';
		sOut += '<td style="padding: 3px 10px; width: ' + arr[0] + 'px;"><select id="itemID" name="itemID" onchange="toggleNewEntry()" type="text">{itemOptions}<option value="0">New Item</option></select><br>';	
		sOut += '<div id="newitem" style="display:none;"><input name="newitem"></div></td></tr>';
		sOut += '<tr><td>Quantity: <input type="number" name = "qty" value = "1"></td><td>Cost: <br><input type="number" name="cost"></td></tr>';
		sOut += '<tr><td colspan = 2>Notes: <br><input type="text" name = "notes" size = "50" value = ""></tr>';
		sOut += '<tr><td>Bought as: <br><select name="UID">{userOptions}</select></td><td><input type="submit" value="Save"></td></tr>';
		sOut += '</tr></table>';
		sOut += '</form></div>';
		return sOut;
	}
	function updateItems(){
		var $el = $("#itemID");
		$el.empty(); // remove old options
		$.each(items[$('#categoryID').val()], function(key, value) {
			$el.append($("<option></option>")
			.attr("value", key).text(value));
		});
		$el.append($("<option></option>")
		.attr("value", "0").text("New Item"));
		toggleNewEntry();
	}
	function toggleNewEntry(){
		names = new Array("category", "item");
		$.each(names, function(key, value){
			target = '#new' + value;
			source = '#' + value + 'ID';
			if ($(source).val() == "0"){
				$(target).css('display', 'block');
			}
			else {
				$(target).css('display', 'none');
			}
		});
	}
	function showLogForm(){
		$('#logbuttons').css('display', 'none');
		$('#logform').css('display', 'block');
	}
	function enterRecord(){
		$("#itemTable tbody").find("td:first").empty().append(fnFormatDetails(oTable));
		showLogForm();
		$("#categoryID").val($("#categoryInput").val());
	}
	$(document).ready(function() {
		$("#itemTable tbody tr").click( function( e ) {
			var nTr = $(this)[0];
			if ( oTable.fnIsOpen(nTr) )
			{
				/* This row is already open - close it */
				oTable.fnClose( nTr );
				$(this).removeClass('row_selected');

			}
			else
			{
				oTable.fnClose(oTable.$('tr.row_selected')[0]);
				oTable.$('tr.row_selected').removeClass('row_selected');

				oTable.fnOpen( nTr, fnFormatDetails(oTable, nTr), 'details' );
				$(this).addClass('row_selected');
				var aData = oTable.fnGetData( nTr );

				$('#categoryID').val(aData[0]);
				updateItems();
				$('#itemID').val(aData[1]);

			}
		});
		oTable = $('#itemTable').dataTable({
			"sDom": '<"H">rt<"F"lip>',
			"aaSorting":[],
			"aoColumnDefs": [
				{ "bVisible": false, "aTargets": [ 0 ] },
				{ "bVisible": false, "aTargets": [ 1 ] }
			] } );
	} );



</script>

{/scripts}


{content}

<div style="float:left">
    <span style="margin:4px 4px 4px 0px;">Category:</span><select id="categoryInput" onchange="$('#itemTable').dataTable().fnDraw()" name="categoryInput" maxlength="30" class="formfield" type="text"><option selected="selected" value="0"></option>{categoryOptions}</select>
</div>
<div class="clear"></div>

{itemTable}

{/content}