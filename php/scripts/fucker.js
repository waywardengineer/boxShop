			$(document).ready(function() {
				/* Initialise datatables */
				var oTable = $('#cncLogs').dataTable();
				
				/* Add event listeners to the two range filtering inputs */
				$('#thicknessInput').keyup( function() { oTable.fnDraw(); } );
				$('#materialInput').keyup( function() { alert('ok'); } );
			} );