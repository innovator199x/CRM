

	    </div><!--.container-fluid-->
	</div><!--.page-content-->



<!-- Fancybox Start -->
<a href="javascript:void(0);" id="search_fb_link" class="fb_trigger" data-fancybox data-src="#search_fb">Trigger the fancybox</a>
<div id="search_fb" class="fancybox" style="display:none;" >

<h4>Search</h4>

	<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('sys/search_results',$form_attr);
		?>

		<div class="form-group row">
			<select id="search_type" name="search_type" class="form-control">
				<option value="1">Job ID</option>
				<option value="2">Property ID</option>
				<option value="3">Phone</option>
				<option value="4">Address</option>
				<option value="5">Landlord</option>
			</select>
		</div>
		<div class="form-group row">
			<input style="width: 300px;" class="form-control search_val" type="text" name="search_val" />
		</div>
		<div class="form-group row" style="margin-bottom:0;">
			<button type="submit" style="margin-bottom:0;" class="btn btn-inline">Go</button>
		</div>

	</form>

</div>

<!-- KMS update -->
<?php
 //get kms by staff id
 //$this->load->model('tech_model');
 $kms_query = $this->tech_model->getKmsByStaffId($this->session->staff_id);
 if( $kms_query->num_rows() > 0 ){

	$kms = $kms_query->row_array();
	$kms_num = $kms['kms'];
	$kms_latest_date = $this->system_model->formatDate($kms['kms_updated']);
	$vehicle_id = $kms['v_vehicle_id'];

 }

?>
<div style="display:none" id="kmspopup">

	<h4>Please enter your Vehicle KMs</h4>

	<div class="form-group">
		<label>KMS</label>
		<input type="number" id="modal_kms" class="form-control" value="<?php echo $kms_num ?>">
	</div>

    <div class="form-group">
        <div class="checkbox">
            <input name="sortbatch" type="checkbox" id="roof_ladder_secured">
            <label for="roof_ladder_secured">I have checked that any ladders on the roof of my vehicle are securely attached to the vehicle</label>
        </div>
	</div>

	<div class="form-group">
		<input type="hidden" value="<?php echo $vehicle_id ?>" id="modal_vehicle_id">
		<button id="modal_btn_submit_kms" class="btn" type="button">Submit KMS</button>
	</div>

</div>

<!-- update stocktake -->
<?php
//get current tech_stock date
$tech_stock  = $this->db->select('tech_stock_id, date')->from('tech_stock')->where('vehicle', $vehicle_id)->order_by('date','DESC')->limit(1)->get()->row_array();
$tech_stock_date = date('Y-m-d', strtotime($tech_stock['date']));

$plus_7days = strtotime('+7 day', strtotime($tech_stock_date));
$next_7days = date('Y-m-d', $plus_7days);
?>
<div style="display:none" id="stockpopup">
    <h4>Please enter/update stock</h4>
    <a href="/stock/update_tech_stock/<?php echo $this->session->staff_id ?>" class="btn">Yes Enter/Update Stock</a>
</div>


<div style="display:none" id="stocktake_update_fb">

    <h4>Please Enter/Update Alarm Stock</h4>   
    <table class="table stock_tbl">
        <thead>
            <th>Code</th>
            <th>Name</th>
            <th>Carton</th>
            <th>Qty</th>
        </thead>
        <?php 
        // get stocks     
        $stocks_sql_str = "
        SELECT 
            `stocks_id`,
            `code`,
            `item`,
            `carton`
        FROM `stocks`
        WHERE `status` = 1
        AND `show_on_stocktake` = 1
        AND `is_alarm` = 1
        ";
        $stocks_sql = $this->db->query($stocks_sql_str);              

        foreach( $stocks_sql->result() as $stocks_row ){ ?>
            <tr>
                <td>
                    <input type="hidden" name="stocks_id_arr[]" class="stocks_id" value="<?php echo $stocks_row->stocks_id; ?>" />
                    <?php echo $stocks_row->code; ?>
                </td>
                <td>
                    <?php echo $stocks_row->item; ?>
                </td>
                <td>
                    <?php echo $stocks_row->carton; ?>                    
                </td>
                <td>
                    <?php  
                    // get latest tech stocktake
                    $tech_stocktake_sql_str = "
                    SELECT `tech_stock_id`
                    FROM `tech_stock`
                    WHERE `staff_id` = {$this->session->staff_id}
                    AND `country_id` = {$this->config->item('country')}
                    ORDER BY `date` DESC
                    LIMIT 1
                    ";
                    $tech_stocktake_sql = $this->db->query($tech_stocktake_sql_str);
                    $tech_st_row = $tech_stocktake_sql->row();

                    $stock_quantity = null;
                    if( $tech_stocktake_sql->num_rows() > 0 ){

                        // get stocktake items
                        $tech_ts_stocks_sql_str = "
                        SELECT tsi.`stocks_id`, tsi.`quantity`
                        FROM `tech_stock_items` AS tsi        
                        LEFT JOIN `tech_stock` AS ts_main ON tsi.`tech_stock_id` = ts_main.`tech_stock_id`
                        WHERE ts_main.`staff_id` = {$this->session->staff_id}
                        AND tsi.`tech_stock_id` = {$tech_st_row->tech_stock_id}            
                        ";
                        $tech_ts_stocks_sql = $this->db->query($tech_ts_stocks_sql_str); 
                        
                        // get tech stocktake data                              
                        foreach( $tech_ts_stocks_sql->result() as $tech_ts_stocks_row ){
                            if( $tech_ts_stocks_row->stocks_id == $stocks_row->stocks_id ){
                                $stock_quantity = $tech_ts_stocks_row->quantity; // get quantity
                            }
                        }

                    }  
                                        
                    ?>    
                    <input type="number" name="stock_qty_arr[]" class="form-control stock_qty" value="<?php echo $stock_quantity; ?>" >                                    
                </td>
            </tr>
        <?php       
        }    
        ?>               
    </table>

    <div class="text-right mt-2">
        <input type="hidden" name="tech_stock_id" id="tech_stock_id" value="<?php echo $tech_st_row->tech_stock_id; ?>" />
        <button type="button" id="save_tech_stocktake" class="btn" >Save</button> 
    </div>   

</div>


<!-- Fancybox END -->

<div class="modal bs-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
      	<h7>You've been idle for 30 Minutes <i class="fa fa-clock-o"></i></h7>
      </div>
      <div class="modal-body">
		<p>You will be logout in <span id="counter">15</span> second(s) unless you press 'Extend Session'.</p>
      	<i class="fa fa-question-circle"></i> Do you want to extend?
	  </div>
      <div class="modal-footer"><a href="javascript:;" id="extendBtn" class="btn btn-primary btn-block">Extend Session</a></div>
    </div>
  </div>
</div>

<?php
if( $is_tech_run_map == true ){ // used on tech run
?>
	<script>
	function callbackGoogleAPI() {
		try {
			initGoogleAPI();
		}
		catch(ex) {}
	}
	</script>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->config->item('gmap_api_key'); ?>&callback=callbackGoogleAPI&v=3"></script>
<?php
}else{ // used on google address autocomplete
?>
	<script>
	function initPlaces() {
		try {
			initAutocomplete();
		}
		catch(ex) {}
	}
	</script>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->config->item('gmap_api_key'); ?>&callback=initPlaces&libraries=places" async defer></script>
<?php
}
?>

<script src="/inc/js/lib/input-mask/jquery.mask.min.js"></script>
<script src="/inc/js/gherx.js"></script>

<script>

    function trigger_kms_update(){

        // trigger lightbox
        $.fancybox.open({
            src  : '#kmspopup'
        });

    }

	$(document).idle({
		onIdle: function(){
			$('.bs-example-modal-sm').modal('show');
			var cTimer = setInterval(function(){ countdown(); },1000);
		jQuery("#extendBtn").click(function(){
					jQuery.ajax({
						type: "POST",
						url: "<?php echo base_url('/sys/check_agency_session') ?>",
					}).done(function(data){
						if (data == "true") {
							$('#counter').html(15)
						clearInterval(cTimer)
						$('.bs-example-modal-sm').modal('hide');
						}else {
							location.href = '<?=base_url()?>sys/logout';
						}
					});
		});
		},
		// extend to 120 minutes
		idle: 60000*120
	})

	function countdown() {
			var i = document.getElementById('counter');
			if (parseInt(i.innerHTML)<=1) {
				$('.bs-example-modal-sm').modal('hide');
					location.href = '<?=base_url()?>sys/logout';
			}
			i.innerHTML = parseInt(i.innerHTML)-1;
	}

	function no_tech_run(){
		// show swal success alert
		swal({
			title:"",
			text: "There is no run sheet for today",
			type: "warning",
			showCancelButton: false,
			showConfirmButton: true
		});
	}

	$(document).ready(function() {

        // check lunch break script
        if( Cookies.get('dont_show_tech_break_alert') == undefined ){

            var lb_interval = setInterval(function() {

                jQuery.ajax({
                    type: "POST",
                    url: "/tech_run/check_if_tech_has_taken_break"
                }).done(function( ret ) {

                    if( parseInt(ret) == 1 ){

                        clearInterval(lb_interval);
                        Cookies.set('dont_show_tech_break_alert', 1); // to avoid annoying the techs

                        // show alert
                        swal({
                            title:"Lunch Break",
                            text: "Please ensure you take your break, call the office if you don't have time.",
                            type: "warning"
                        });

                    }

                });

            }, 60 * 1000); // 60 minutes interval

        }

		<?php
		// check unread messages
		$staff_id = $this->session->staff_id;
        $msg_sql_str = "
            SELECT
                m3.`message_id`,
                m3.`message_header_id`,
                m3.`message`,
                m3.`date`,

                mg2.`staff_id`,

                mrb2.`read`
            FROM `message` AS m3
            INNER JOIN(

                SELECT
                    m.`message_header_id`,
                    MAX(m.`date`) as latest_date

                FROM `message` AS m
                LEFT JOIN `message_read_by` AS mrb ON ( m.`message_id` = mrb.`message_id` AND mrb.`staff_id` = {$staff_id} )
                LEFT JOIN `message_header` AS mh ON m.`message_header_id` = mh.`message_header_id`
                INNER JOIN `message_group` AS mg ON ( mh.`message_header_id` = mg.`message_header_id` )
                WHERE mg.`staff_id` = {$staff_id}
                GROUP BY m.`message_header_id`

            ) AS m4 ON ( m3.message_header_id = m4.message_header_id AND m3.date = m4.latest_date )
            LEFT JOIN `message_read_by` AS mrb2 ON ( m3.`message_id` = mrb2.`message_id` AND mrb2.`staff_id` = {$staff_id} )
            LEFT JOIN `message_header` AS mh2 ON m3.`message_header_id` = mh2.`message_header_id`
            INNER JOIN `message_group` AS mg2 ON ( mh2.`message_header_id` = mg2.`message_header_id` )
            WHERE mg2.`staff_id` = {$staff_id}
            AND mrb2.`read` IS NULL
            ORDER by m3.date DESC
        ";
		$msg_sql = $this->db->query($msg_sql_str);


        $new_message_count_tot = 0;

        foreach( $msg_sql->result() as $msg ){

            // get last read message
            $last_read_by_str = "
            SELECT m.`message_id`
                FROM `message_read_by` AS mrb
                LEFT JOIN `message` AS m ON mrb.`message_id` = m.`message_id`
                WHERE m.`message_header_id` = {$msg->message_header_id}
                AND mrb.`staff_id` = {$this->session->staff_id}
                ORDER BY `message_id` DESC
                LIMIT 1
            ";
            $last_read_by_sql = $this->db->query($last_read_by_str);

            $last_read_by = $last_read_by_sql->row();
            $last_read_msg_id = $last_read_by->message_id;

            if( $last_read_by_sql->num_rows() > 0 ){ // if user have ready by data (at least seen the convo)

                // count number of new messages from last read
                $read_by_str = "
                    SELECT COUNT(m.`message_id`) AS mcount
                    FROM `message` AS m
                    WHERE m.`message_header_id` = {$msg->message_header_id}
                    AND m.`message_id` > {$last_read_msg_id}
                ";
                $read_by_sql = $this->db->query($read_by_str);
                $new_message_count = $read_by_sql->row()->mcount;

            }else{ // no ready by data (has not checked convo)

                // get all messages count
                $all_msg_str = "
                    SELECT COUNT(m.`message_id`) AS mcount
                    FROM `message` AS m
                    WHERE m.`message_header_id` = {$msg->message_header_id}
                ";
                $all_msg_sql = $this->db->query($all_msg_str);
                $new_message_count = $all_msg_sql->row()->mcount;

            }

            $new_message_count_tot += $new_message_count;

		}


		// remind new unread message
        if( $new_message_count_tot > 0 && $new_message_count_tot > $this->session->unread_msg_count ){


			// create session array
			 $sess_arr = array(
				'unread_msg_count' => $new_message_count_tot
            );
            // set session
			$this->session->set_userdata($sess_arr);



        ?>
			// show swal success alert
			swal({
				html: true,
				title:"New Message!",
				text: "You have new <a href='/messages/index'>unread messages</a>, please check your inbox",
				type: "warning",
				showCancelButton: false,
				showConfirmButton: true
			});
        <?php
        }

        // KMS check
        if($kms_query->num_rows()>0){
            if( $kms_latest_date != date('Y-m-d') && $this->session->kms_pop_up_date != date('Y-m-d') ){

			// create session array
			$sess_arr = array(
				'kms_pop_up_date' => date('Y-m-d')
            );
            // set session
            $this->session->set_userdata($sess_arr);

        ?>
                trigger_kms_update();
        <?php
            }
        }
        ?>


         
        <?php
        //show only stock popup if kms is already updated
        if( $kms_latest_date == date('Y-m-d') ){
            // shows in the next 7 days after its last update?
            if( date('Y-m-d') >= $next_7days  && $this->session->stocktake_update_pop_up_date != date('Y-m-d') ){

				// create session array
				$sess_arr = array(
					'stocktake_update_pop_up_date' => date('Y-m-d'),
                    'tech_stocktake_pop_up_date_alarms_only' => date('Y-m-d')
				);
				// set session
				$this->session->set_userdata($sess_arr);               

            ?>
                $.fancybox.open({
                    src  : '#stockpopup'
                });                

            <?php
            }else if( $this->session->tech_stocktake_pop_up_date_alarms_only != date('Y-m-d') ){ 
                
                // create session array
				$sess_arr = array(					
                    'tech_stocktake_pop_up_date_alarms_only' => date('Y-m-d')
				);
				// set session
				$this->session->set_userdata($sess_arr);   
            ?>                    

                // display pop up
                $.fancybox.open({
                    src  : '#stocktake_update_fb'
                });
                 
            <?php
            }
        }        
        ?>  



		// about page
		jQuery("#search_icon_fb").click(function(){
			jQuery("#search_fb_link").click();
		});

		// about page
		jQuery("#about_page_link").click(function(){
			jQuery("#about_page_fb_link").click();
		});

		// prevent session time out
		var refreshTime = 300000; // every 5 minutes in milliseconds
		window.setInterval( function() {
				jQuery.ajax({
						cache: false,
						type: "GET",
						url: "sys/refreshSession",
						success: function(data) {
							console.log('Refresh Session');
						}
				});
		}, refreshTime );

		// loader
		$("#status").fadeOut(250);
		$("#preloader").delay(250).fadeOut("slow");

		//init datepicker
		jQuery('.flatpickr').flatpickr({
			dateFormat: "d/m/Y",
			locale: {
				firstDayOfWeek: 1
			}
		});


		// region filter
		jQuery(document).mouseup(function (e){
			var container = jQuery("#region_dp_div");
			if (!container.is(e.target) // if the target of the click isn't the container...
				&& container.has(e.target).length === 0) {
				container.hide();
			}
		});
		jQuery("#region_filter_state").click(function(){
			jQuery("#region_dp_div").show();
		});



        jQuery("#kms_update").click(function(){
            // trigger lightbox
            trigger_kms_update();
        });


        // KMS update
        $('#modal_btn_submit_kms').click(function(){

            var kms = $('#modal_kms').val();
            var vehicle_id = $('#modal_vehicle_id').val();
            var roof_ladder_secured = ( jQuery("#roof_ladder_secured").prop("checked") == true )?1:0;

            var err = "";

            if(kms==""){
                err +="KMS must not be empty\n";
            }

            if( roof_ladder_secured == 0 ){
                err +="Roof ladder secured checkbox is required\n";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            //update kms via ajax
            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/tech_run/ajax_add_kms",
                dataType: 'json',
                data: {
                    kms: kms,
                    vehicles_id: vehicle_id,
                    roof_ladder_secured: roof_ladder_secured
                }
            }).done(function( ret ) {

                $('#load-screen').hide();
                if(ret.status){

                    // show swal success alert
                    swal({
                        title:"Success!",
                        text: "KMS Successfully Added",
                        type: "success",
                        showCancelButton: false,
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });

                    // close fancybox
                    $.fancybox.close();

                }else{

                    //swal('','Server error please contact admin.','error');
                    swal('','KMs Field cannot be 0.','error');

                }

            });


        });                  
        
        
        jQuery("#save_tech_stocktake").click(function(){

            var fb_dom = jQuery("#stocktake_update_fb");

            var tech_stock_id = fb_dom.find("#tech_stock_id").val();
            var error = '';

            // stocks
            var stocks_id_arr = [];
            fb_dom.find(".stocks_id").each(function(){
                var stocks_id = jQuery(this).val();
                stocks_id_arr.push(stocks_id);            
            });

            // quantity
            var stock_qty_arr = [];
            var has_empty_qty = false;
            fb_dom.find(".stock_qty").each(function(){

                var stock_qty_dom = jQuery(this);
                var stock_qty = stock_qty_dom.val();

                if( stock_qty == '' ){
                    has_empty_qty = true;
                    stock_qty_dom.addClass('border-danger'); 
                }else{
                    stock_qty_dom.removeClass('border-danger'); 
                } 

                stock_qty_arr.push(stock_qty);                  

            });

            if( has_empty_qty == true ){
                swal('','Stocktake quantity cannot be empty\n','error');
            }else{

                // ajax update
                jQuery.ajax({
                    type: "POST",
                    url: "/home/ajax_save_tech_stocktake",
                    data: {
                        tech_stock_id: tech_stock_id,
                        stocks_id_arr: stocks_id_arr,
                        stock_qty_arr: stock_qty_arr
                    }
                }).done(function( ret ) {
                    
                    swal({
                        title: "Success!",
                        text: "Stocktake Saved!",
                        type: "success",
                        confirmButtonClass: "btn-success",
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });

                    $.fancybox.close();

                });

            }                                    

        });


        <?php        
        // check send back to tech        
        $today =  date("Y-m-d");		
		
        $sbtt_sql_str = "
        SELECT 
            j.`id` AS jid,

            p.`address_1`,
            p.`address_2`,
            p.`address_3`,
            p.`state`,
            p.`postcode`
        FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        WHERE j.`precomp_jobs_moved_to_booked` = 1
        AND j.`date` = '{$today}'
        AND j.`assigned_tech` = {$this->session->staff_id}
        AND j.`status` = 'Booked'
        ";
        $sbtt_sql = $this->db->query($sbtt_sql_str);
        $data['sbtt_sql'] = $sbtt_sql;

        if( $sbtt_sql->num_rows() > 0 ){ 
        $sbtt_row = $sbtt_sql->row();
        $prop_address = "{$sbtt_row->address_1} {$sbtt_row->address_2} {$sbtt_row->address_3} {$sbtt_row->state} {$sbtt_row->postcode}";  
        $ts_link = "/jobs/tech_sheet/?job_id={$sbtt_row->jid}"; // techsheet CI link 
        ?>

            if( Cookies.get('dont_show_send_back_to_tech_alert') == undefined ){

                // show alert
                swal({
                    html:true,
                    title:"",                
                    text: "There is an issue with <?php echo $prop_address; ?>. Please click <a href='<?php echo $ts_link; ?>''>HERE</a> to review your data.",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                    timer: <?php echo $this->config->item('timer') ?>
                });

                Cookies.set('dont_show_send_back_to_tech_alert', 1); // to avoid annoying the techs

            }            

        <?php
        }    
        ?>
        

	});
</script>

<script src="/inc/js/app.js"></script>

</body>
</html>
<?php
if( isset($start_load_time) ){
	$time_elapsed_secs = microtime(true) - $start_load_time;
	echo "<p style='text-align:center;'>Execution Time: {$time_elapsed_secs}</p>";
}
 ?>