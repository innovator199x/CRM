
<link rel="stylesheet" href="/inc/css/lib/datatables-net/datatables.min.css">
<link rel="stylesheet" href="/inc/css/separate/vendor/datatables-net.min.css">
<script src="/inc/js/lib/datatables-net/datatables.min.js"></script>


	<div id="mainDiv">
		
		<h5 class="m-t-lg with-border"><b>GET PROPERTIES</b></h5>
		<button class="btn btn-inline btn-danger ladda-button" id="btnTest" data-style="expand-right"  <?=isset($_GET['code']) ? '' : '' ?>>
			<span class="ladda-label">Property List</span>
		</button>

		<div id="testDiv" >
			<fieldset class="form-group">
				<div id="testText" style="display: none;"></div>
			</fieldset>
		</div>
		
	</div>


<script type="text/javascript">

	jQuery("#btnTest").on('click',function(e){
		
		e.preventDefault();
		var apiUrl = "https://app.propertyme.com:443/api/v1/lots";
		var error = "";

		if(apiUrl==""){
			error += "apiUrl must not be empty\n";
		}

		if(error!=""){
			swal('',error,'error');
			return false;
		}
				// var n = apiUrl.includes("rentals");
				var n = apiUrl.includes("v1/lots?Timestamp=1");

		$('#load-screen').show(); //show loader
		jQuery.ajax({
			type: "POST",
			url: "/property_me/getResource_v2",
			data: { 
				apiUrl: apiUrl,
				n : n
			}
		}).done(function( ret ){
			$('#load-screen').hide(); 
				if (n) {
				$('#testText1').hide();
				$('#testText').show();
				$('#testText').html(ret);
			}else {
				$('#testText').hide();
				$('#testText1').show();
				$('#testText1').val( JSON.parse(ret));
			}
			$('#testDiv').show();
				
		});	
				
	});

</script>