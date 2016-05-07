{scripts}

<link href="DataTables-1.9.4/media/css/jquery.dataTables.css" rel="stylesheet" type="text/css">
<script type="text/javascript" language="javascript" src="DataTables-1.9.4/media/js/jquery.dataTables.js"></script>

<script type="text/javascript">

	var oTable;

	$(document).ready(function() {
		
		oTable = $('#eventLog').dataTable({
			"sDom": '<"H">rt<"F"lip>',
			"aaSorting":[],
			"aLengthMenu": [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "All"]],
			"iDisplayLength": 100
			 } );
		/* Initialise datatables */
	} );
</script>					

{/scripts}



{content}

<h4>{title}</h4>

{logs}
{/content}