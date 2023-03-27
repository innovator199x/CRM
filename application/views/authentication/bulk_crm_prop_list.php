
<link rel="stylesheet" href="/inc/css/lib/jqueryui/jquery-ui.min.css">
<link rel="stylesheet" href="/inc/css/lib/datatables-net/datatables.min.css">
<link rel="stylesheet" href="/inc/css/separate/vendor/datatables-net.min.css">
<script src="/inc/js/lib/jquery/jquery-3.2.1.min.js"></script>
<script src="/inc/js/lib/datatables-net/datatables.min.js"></script>
<style type="text/css">
.dataTables_paginate {
  display: inline-block;
}

.dataTables_paginate a {
  color: black;
  float: left;
  padding: 8px 16px;
  text-decoration: none;
  transition: background-color .3s;
  border: 1px solid #ddd;
}

.dataTables_paginate a.active {
  background-color: #4CAF50;
  color: white;
  border: 1px solid #4CAF50;
}

.dataTables_filter {
display: none; 
}
.dataTables_paginate {
	float: left;
	margin-top: 40px !important;
}
.btn-square-icon {
    height: 70px !important;
}
</style>
						
<table id="crmProp" class="display table table-striped table-bordered" cellspacing="0" width="100%">
	<thead>
		<tr>
			<th>Address</th>
		</tr>
	</thead>
	<tfoot style="display: none;">
		<tr>
			<th>Address</th>
		</tr>
	</tfoot>
	<tbody>
		<?php 
			foreach ($lists as $row) { 
            	$fullAdd = $row['address_1']." ".$row['address_2']." ".$row['address_3']." ".$row['state']." ".$row['postcode'];
            	$count = false;
	            foreach ($pme_prop as $val) {
	            	if ($fullAdd == $val) {
	            		$count = true;
	            	}
	            }
		?>
			<tr class="<?=$count ? "selected" : ""?>">
				<td class="crmAdd" dat-main-id="<?=$row['property_id']?>">
					<?=$row['address_1']?> <?=$row['address_2']?> <?=$row['address_3']?> <?=$row['state']?> <?=$row['postcode']?>
					
				</td>
			</tr>
		<?php
			}
		?>
	</tbody>
</table>


<script type="text/javascript">
 
	$(document).ready(function() {
    	var crmTable = $('#crmProp').DataTable( {
		  "lengthChange": false, "ordering": false, "searching": false
		});
	})
</script>