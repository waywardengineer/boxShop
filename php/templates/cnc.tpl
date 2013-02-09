{scripts}

<link href="DataTables-1.9.4/media/css/jquery.dataTables.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="Boxshop%20Security%20System_files/jquery-ui-1.css" type="text/css" media="screen">
<script type="text/javascript" language="javascript" src="DataTables-1.9.4/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" language="javascript" src="DataTables-1.9.4/examples/examples_support/jquery.jeditable.js"></script>
<script type="text/javascript">
	var oTable;
	$.fn.dataTableExt.afnFiltering.push(
		function( oSettings, aData, iDataIndex ) {
			var thicknessInput = document.getElementById('thicknessInput').value ;
			var materialInput = document.getElementById('materialInput').value;
			var material = aData[0];
			var thickness = aData[1];
			if ( materialInput == 0 && thicknessInput == 0 )
			{

				return true;
			}
			else if ( materialInput == 0 && thicknessInput == thickness )
			{
				return true;
			}
			else if ( materialInput == material &&  thicknessInput == 0)
			{
				return true;
			}
			else if ( materialInput == material &&  thicknessInput == thickness)
			{
				return true;
			}

			return false;
		}
	);
			
	function addButtonIfEmpty(){
		if (oTable.fnSettings().fnRecordsDisplay()==0){
			$("#cncLogs tbody").find("td:first").append('<br><input type="button" onclick="enterRecord()" value="New record">');

		}
	}
	function fnFormatDetails ( oTable, nTr )
	{
		var arr=new Array();
		$('#cncLogs').find('thead th').each(function(){
			  arr.push( $(this).width() ); // <== returns 0 ???
		})
		var aData = oTable.fnGetData( nTr );
		var sOut = '<form action="cncprocess.php" method="post"><table cellpadding="5" cellspacing="3" border="0" ><tr>';
		sOut += '<tr><td style="padding: 3px 10px; width: ' + arr[0] + 'px;"><select id="materialID" name="materialID" type="text">{materialOptions}</select></td>';
		sOut += '<td style="padding: 3px 10px; width: ' + arr[1] + 'px;"><select id="thicknessID" name="thicknessID" type="text">{thicknessOptions}</select></td>';
		sOut += '<td style="padding: 3px 10px; width: ' + arr[2] + 'px;"><input class="cncRecordSmallInput" id="amps" name="amps"  type="text" value="' + aData[4] +'"></td>';
		sOut += '<td style="padding: 3px 10px; width: ' + arr[3] + 'px;"><input class="cncRecordSmallInput" id="volts" name="volts" type="text" value="' + aData[5] +'"></td>';
		sOut += '<td style="padding: 3px 10px; width: ' + arr[4] + 'px;"><input class="cncRecordSmallInput" id="feedrate" name="feedrate" maxlength="20" type="text" value="' + aData[6] +'"></td>';
		sOut += '<td style="padding: 3px 10px; width: ' + arr[5] + 'px;"><input class="cncRecordSmallInput" id="pierceHeight" name="pierceHeight" maxlength="20" type="text" value="' + aData[7] +'"></td>';
		sOut += '<td style="padding: 3px 10px; width: ' + arr[6] + 'px;"><input class="cncRecordSmallInput" id="initCutHeight" name="initCutHeight" maxlength="20" type="text" value="' + aData[8] +'"></td>';
		sOut += '<td style="padding: 3px 10px; width: ' + arr[7] + 'px;"><input class="cncRecordSmallInput" id="pierceDelay" name="pierceDelay" maxlength="20" type="text" value="' + aData[9] +'"></td>';
		sOut += '<td style="padding: 3px 10px; width: ' + arr[8] + 'px;"><input class="cncRecordSmallInput" id="PSI" name="PSI" maxlength="20" type="text" value="' + aData[10] +'"></td>';
		sOut += '<td style="padding: 3px 10px; width: ' + arr[9] + 'px;"><input class="cncRecordSmallInput" id="kerf" name="kerf" maxlength="20" type="text" value="' + aData[11] +'"></td>';
		sOut += '<td style="padding: 3px 10px; width: ' + arr[10] + 'px;"><input id="notes" name="notes" maxlength="20" type="text" value="' + aData[12] +'"></td>';

		sOut += '</tr></table>';
		sOut += '<input type="submit" value="Use setting"></form>';
		return sOut;
	}
	function enterRecord(){
		$("#cncLog tbody").find("td:first").empty().append(fnFormatDetails(oTable));
		$("#materialID").val($("#materialInput").val());
	}

	$(document).ready(function() {
		$("#cncLogs tbody tr").click( function( e ) {
			
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

				$('#materialID').val(aData[0]);
				$('#thicknessID').val(aData[1]);

			}
		});
		
		oTable = $('#cncLogs').dataTable({
			"sDom": '<"H">rt<"F"lip>',
			"aoColumnDefs": [
				{ "bVisible": false, "aTargets": [ 0 ] },
				{ "bVisible": false, "aTargets": [ 1 ] }
			] } );
		addButtonIfEmpty();
	} );					


</script>
{/scripts}


{content}

<div style="float:left">
    <span style="margin:4px 4px 4px 0px;">Material:</span><select id="materialInput" onchange="$('#cncLogs').dataTable().fnDraw(); addButtonIfEmpty()" name="materialInput" maxlength="30" class="formfield" type="text">{materialOptions}</select>
    <span style="margin:4px 4px 4px 20px;;">Thickness:</span><select id="thicknessInput" onchange="$('#cncLogs').dataTable().fnDraw()" name="thicknessInput" maxlength="30" class="formfield" type="text">{thicknessOptions}</select>
</div>
<div class="clear"></div>

{cncLog}
{/content}