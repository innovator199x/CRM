<link rel="stylesheet" href="/inc/css/separate/vendor/select2.min.css">
<style>
 .vad_add_event_div{margin-top:30px;margin-bottom:30px;}
    #ss_status{width:165px;display:none;}
    .add_to_snapshot_label{float:left;}
    .select2-container--arrow .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice, .select2-container--white .select2-selection--multiple .select2-selection__choice{
		color: #fff;
		background: #919fa9;
		border: none;
		font-weight: 600;
		font-size: 1rem;
		padding: 0 2rem 0 .5rem;
		height: 26px;
		line-height: 26px;
		position: relative;
	}
	.select2-container--arrow .select2-results__option--highlighted[aria-selected], .select2-container--default .select2-results__option--highlighted[aria-selected], .select2-container--white .select2-results__option--highlighted[aria-selected]{
		color:#00a8ff;
	}
	.select2-container--arrow .select2-selection--multiple, .select2-container--default .select2-selection--multiple, .select2-container--white .select2-selection--multiple{
		border-color: #d8e2e7;
		min-height: 38px;
	}
	.select2-container--default .select2-selection--multiple .select2-selection__rendered{
		box-sizing: border-box;
		list-style: none;
		margin: 0;
		padding: 0 5px;
		width: 100%;
	}
	.select2-container--default.select2-container--focus .select2-selection--multiple{
		border-color:#c5d6de!important;
	}
</style>



<div class="vad_add_event_div text-left">
        <?php echo form_open('/agency/add_event_agency_logs','id=form_agency_logs') ?>
        <input type="hidden" name="agency_id" value="<?php echo $agency_id; ?>">
            <div class="row">
                <div class="col-md-1 columns">
                    <label class="form-label" for="eventdate">Date</label>
                    <input type="text" id="eventdate" name="eventdate" class="flatpickr_event_log flatpickr-input form-control agency_logs_input" value="<?php echo date("d/m/Y"); ?>">
                </div>
                <div class="col-md-2 columns">
                    <label class="form-label"> 	Contact Type</label>
                    <?php 
                        if($row['status']=='active'){ //active agency
                    ?>
                        <select name="contact_type" class="fselect agency_logs_input form-control">
                          <?php foreach($active_agency_contact_type->result_array() as $row){
                            ?>
                                <option value="<?php echo $row['main_log_type_id'] ?>"><?php echo $row['contact_type'] ?></option> 
                            <?php
                          } ?>
                        </select>
                    <?php
                        }else{ //target agency / inactive
                    ?>
                        <select name="contact_type" class="form-control">											
                            <?php foreach($in_active_agency_contact_type->result_array() as $row){
                            ?>
                                <option value="<?php echo $row['main_log_type_id'] ?>"><?php echo $row['contact_type'] ?></option> 
                            <?php
                          } ?>
                        </select>
                    <?php
                        }
                    ?>
                </div>
                <div class="col-md-4 columns">
                <label class="form-label">Comments</label>
                <textarea name="comments" lengthcut="true" class="form-control vpr-adev-txt comments"></textarea>
                </div>
                <div class="col-md-1 columns">
                <label class="form-label">Next Contact</label>
                <input type="text" name="next_contact" class="flatpickr flatpickr-input form-control agency_logs_input" />	
                </div>

                <div class="col-md-2 columns">
                    <label class="form-label">Add to Snapshot</label>
                    <div class="checkbox">
                        <input type="checkbox" value="1" name="add_to_snapshot" id="add_to_snapshot" />
                        <label class="add_to_snapshot_label" for="add_to_snapshot">&nbsp;</label>
                        <select name="ss_status" id="ss_status" class="form-control agency_logs_input">
                            <option value="">----</option>
                          <?php 
                            foreach($sales_snapshot_status as $ss_s){
                            ?>
                                <option value="<?php echo $ss_s['sales_snapshot_status_id']; ?>"><?php echo $ss_s['name']; ?></option>
                            <?php
                            }
                          ?>
                        </select>
                        <input type="hidden" name="total_prop" value="<?php echo $row['tot_properties']; ?>" />
                    </div>
                </div>
                <div class="col-md-1 columns">
                    <div class="vad_cta_box form-group text-left">
                    <button class="btn btn_add_log_event">Add Event</button>
                    </div>
                </div>
            </div>

        </form>
    </div>

<div class="log_listing_old text-left">
    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <?php
        $form_attr = array(
            'id' => 'jform'
        );
        echo form_open("/agency/view_agency_details/{$agency_id}/7",$form_attr);

        ?>
            <div class="for-groupss row">
                <div class="col-md-9 columns">
                    <div class="row">					
                        <div class="col-md-5">
                            <label for="contact_type">Contact Type</label>
                            <select class="select2 form-control" multiple="multiple" id="contact_type" name="contact_type[]">
								<?php
                                    foreach($main_log_type_q as $main_log_type_q_row){
                                ?>
                                    <option value="<?php echo $main_log_type_q_row['main_log_type_id'] ?>"><?php echo $main_log_type_q_row['contact_type'] ?></option>
                                <?php
                                    }
                                ?>
							</select>
                        </div>
                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <button type="submit" class="btn btn-inline">Search</button>
                        </div>
                    </div>
                </div>
                
            </div>
            </form>
        </div>
    </header>
                            <!--<h4>New Logs</h4>-->
                            <table class="table table-hover main-table table_log_listing_new table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Title</th>
                                        <th>Who</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        if(count($new_log)!=0){
                                            foreach($new_log as $new_log_row){
                                    ?>
                                                <tr>
                                                    <td>
                                                        <?php echo date('d/m/Y',strtotime($new_log_row['created_date'])); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo date('H:i',strtotime($new_log_row['created_date'])); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $new_log_row['title_name']; ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if( $new_log_row['StaffID'] != '' ){ // sats staff
                                                            echo "{$new_log_row['FirstName']} {$new_log_row['LastName']}";
                                                        }else{ // agency portal users
                                                            echo "{$new_log_row['fname']} {$new_log_row['lname']}";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $params = array(
                                                            'log_details' => $new_log_row['details'],
                                                            'log_id' => $new_log_row['log_id']
                                                        );								
                                                         echo $this->agency_model->parseDynamicLink_to_crm($params);
                                                        ?>
                                                    </td>
                                                </tr>
                                    <?php
                                            }
                                        }else{
                                            echo "<tr><td colspan='5'>No Data</td></tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>

                           	<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
                            <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
                            <p>&nbsp;</p>
                        </div>

</div>

<script type="text/javascript">

    jQuery(document).ready(function(){

        <?php if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'success') { ?>
            swal({
                title: "Success!",
                text: "<?php echo $this->session->flashdata('success_msg') ?>",
                type: "success",
                confirmButtonClass: "btn-success",
                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                timer: <?php echo $this->config->item('timer') ?>
            });
        <?php } else if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'error') { ?>
                    swal({
                        title: "Error!",
                        text: "<?php echo $this->session->flashdata('error_msg') ?>",
                        type: "error",
                        confirmButtonClass: "btn-danger"
                    });
        <?php } ?>
        

        // sales snapshot log script
        jQuery("#add_to_snapshot").change(function(){
            
            var checked = jQuery(this).prop("checked");
            if( checked == true ){
                jQuery("#ss_status").show();
            }else{
                jQuery("#ss_status").hide();
            }	
            
        });

        $('.btn_add_log_event').on('click',function(){

            var comments = $('.comments').val();
            var add_to_snapshot = $('#add_to_snapshot').prop('checked');
            var ss_status = $('#ss_status').val();
            
            var error = "";
            var submitcount=0;

            if(comments==""){
                error += "Comment is required\n";
            }
            if(add_to_snapshot==true){
                if(ss_status==""){
                    error += "Snapshot status is required\n";
                }
            }

            if(error!=""){
                swal({
                    title: "",                    
                    text: error,
                    type: "error"
                });
                return false;
            }

            if(submitcount==0){
                submitcount++;
                jQuery("#form_agency_logs").submit();
                return false;
            }else{
                swal('','Form submission is in progress','error');
                return false;
            }

        })

       //init datepicker
		jQuery('.flatpickr_event_log').flatpickr({
			dateFormat: "d/m/Y",
            maxDate: "today",
			locale: {
				firstDayOfWeek: 1
			}
		});

    });


</script>