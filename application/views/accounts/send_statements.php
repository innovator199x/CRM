<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .jtimestamp {
        color: #00D1E5;
        font-style: italic;
    }

</style>

<?php
  $export_links_params_arr = array(
	'date_from_filter' => $this->input->get_post('date_from_filter'),
	'date_to_filter' => $this->input->get_post('date_to_filter'),
	'reason_filter' => $this->input->get_post('reason_filter'),
    'state_filter' =>  $this->input->get_post('state_filter')
);
$export_link_params = '/reports/discarded_alarms/?export=1&'.http_build_query($export_links_params_arr);
?>
<div class="box-typical box-typical-padding">

	<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => 'Reports',
        'link' => "/reports"
    ),
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/accounts/send_statements"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

	<header class="box-typical-header">

        <div class="box-typical box-typical-padding">
        <div class="for-groupss row">
                <div class="col-md-10 columns">
                    <?php
                    $form_attr = array(
                        'id' => 'jform'
                    );
                    echo form_open('/accounts/send_statements',$form_attr);
                    ?>
                    <div class="row">

                         <div class="col-mdd-3">
                            <label for="agency_select">Agency</label>
                            <select id="agency" name="agency" class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($agency_filter->result_array() as $agency_filter_row){
                                ?>
                                        <option <?php echo ($this->input->get_post('agency')==$agency_filter_row['agency_id']) ? 'selected' : ''; ?> value="<?php echo $agency_filter_row['agency_id'] ?>"><?php echo $agency_filter_row['agency_name'] ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                        <label for="search">Phrase</label>
                        <input type="text" name="search" class="form-control" placeholder="Text" value="<?php echo ($this->input->get_post('search')) ? $this->input->get_post('search') :'' ?>">
                        </div>

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
                        </div>
                    </div>
                    </form>
                </div> 
                </div>
            </div>
        </header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table" id="invoice_table">
					<thead>
						<tr>
						    <th>Agency Name</th>
						    <th>Agency Comments</th>
						    <th>Total Amount Due</th>
                            <th>Email Address</th>
                            <th>Last Sent</th>
                            <th>
                                <div class="checkbox" style="margin:0;">
									<input class="chk_all" name="chk_all" type="checkbox" id="chk_all">
									<label for="chk_all">&nbsp;</label>
								</div>
                            </th>
						</tr>
					</thead>

					<tbody>
                        <?php 
                            $counter = 1;
                            foreach($plist->result_array() as $row){
                            $invoice_balance = $row['invoice_balance_tot'];

							$acc_em_arr = $this->gherxlib->convertEmailToArray($row['account_emails']); 
                            $accounts_email = implode(",",$acc_em_arr);
							
                        ?>

                             <tr class="body_tr">
                                <td><?php echo $this->gherxlib->crmlink('vad', $row['agency_id'], $row['agency_name']) ?></td>
                                <td>
                                    <?php 
                                    $statements_agency_comments = $row['statements_agency_comments'];
                                    $statement_comments_link = ($statements_agency_comments!="")?'more...':'Add notes';
                                    echo substr($statements_agency_comments,0,20). " <small><a data-fancybox href='javascript:;' data-src='#statements_agency_comments_fancybox_{$counter}'>{$statement_comments_link}</a></small>";
                                    ?>
                                    
                                    <!-- FANCYBOX COMMENT UPDATE START -->
                                    <div style="display:none;" id="statements_agency_comments_fancybox_<?php echo $counter ?>">
                                       <div class="statements_agency_comments_fancybox_wrapper" >
                                            <h4>Update Statement Agency Comments</h4>
                                            <div class="form-group">
                                                <label>Statement Agency Comments</label>
                                                <textarea  rows="5" class="form-control statement_comments"><?php echo $row['statements_agency_comments'] ?></textarea>
                                            </div>
                                            <div class="form-group">
                                                <input type="hidden" class="agency_id" value="<?php echo $row['agency_id'] ?>">
                                                <button type="button" class="btn btn_update_statement_comments">Update</button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- FANCYBOX COMMENT UPDATE END -->

                                </td>
							    <td>$<?php echo number_format($invoice_balance,2); ?></td>
                                <td><input type="text" class="accounts_email form-control" value="<?php echo $accounts_email; ?>" /></td>
                                <td class="jtimestamp"><?php echo ( $this->system_model->isDateNotEmpty($row['send_statement_email_ts']) )?date('d/m/Y H:i',strtotime($row['send_statement_email_ts'])):''; ?></td>
							    <td>
                                    <div class="checkbox" style="margin:0;">
                                        <input class="chk_agency" name="chk_agency[]" type="checkbox" id="chk_agency_<?php echo $row['agency_id'] ?>" value="<?php echo $row['agency_id']; ?>">
                                        <label for="chk_agency_<?php echo $row['agency_id'] ?>">&nbsp;</label>
                                    </div>    
                                </td>
                            </tr>

                        <?php
                            $invoice_balance_tot += $invoice_balance;
                            $counter++;
                            }
                        ?>
                        <thead>
                            <tr class="jalign_left jtblfooter">
                                
                                <th>TOTAL</th>	
                                <th></th>
                                <th>$<?php echo number_format($invoice_balance_tot,2); ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            
                            </tr>
                        </thead>
					</tbody>

				</table>
            </div>

            <div class="text-right save_div" id="mbm_box" style="display:none;">
              	<button type='submit' class='btn' id="btn_email">
                    Email
                </button>
            </div>

		 <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			
		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page displays send statements data.
	</p>
    <pre>
<code>SELECT SUM(j.`invoice_balance`) AS invoice_balance_tot, `a`.`agency_name`, `a`.`agency_id`, `a`.`account_emails`, `a`.`agency_emails`, `a`.`send_statement_email_ts`, `a`.`statements_agency_comments`
FROM `jobs` as `j`
LEFT JOIN `property` as `p` ON `p`.`property_id` = `j`.`property_id`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `p`.`agency_id`
WHERE `j`.`id` > 0
AND 
`j`.`invoice_balance` >0
AND `j`.`status` = 'Completed'
AND `a`.`status` != 'target'
AND (
j.`date` >= ' <?php echo $this->config->item('accounts_financial_year') ?> ' OR
j.`unpaid` = 1
)
GROUP BY `a`.`agency_id`
ORDER BY `a`.`agency_name` ASC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script>




jQuery(document).ready(function(){

    
   //check all toggle tweak
	$('#chk_all').on('change',function(){
		var obj = $(this);
		var isChecked = obj.is(':checked');
		var divbutton = $('#mbm_box');
		if(isChecked){
			divbutton.show();
			$('.chk_agency').prop('checked',true);
		}else{
			divbutton.hide();
			$('.chk_agency').prop('checked',false);
		}
	})

    //check sing checkbox toggle tweak
	$('.chk_agency').on('change',function(){
		var obj = $(this);
		var isLength = $('.chk_agency:checked').length;
		var divbutton = $('#mbm_box');
		if(isLength>0){
			divbutton.show();
		}else{
            $('#chk_all').prop('checked',false);
			divbutton.hide();
		}
	})

    jQuery("#btn_email").click(function(){
		
		var agency_id_arr = [];
		jQuery(".chk_agency:checked").each(function(){
			
			var agency_id = jQuery(this).val();
			var accounts_email = jQuery(this).parents("tr:first").find(".accounts_email").val();
			
			json_data = { 
				'agency_id': agency_id, 
				'accounts_email': accounts_email 
			}
			var json_str = JSON.stringify(json_data);
			
			agency_id_arr.push(json_str);
			
		});
		
		
		jQuery.ajax({
			type: "POST",
			url: "/accounts/ajax_email_agency_statements",
			data: { 
				agency_id_arr: agency_id_arr,
				
			}
		}).done(function( ret ) {
			swal({
                title: "Success!",
                text: "Email Sent",
                type: "success",
                confirmButtonClass: "btn-success",
                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                timer: <?php echo $this->config->item('timer') ?>
            });
            setTimeout(function(){ window.location='/accounts/send_statements'; }, <?php echo $this->config->item('timer') ?>);	
		});	
		
		
	});

    $('.btn_update_statement_comments').click(function(){

        var statement_comments = $(this).parents('.statements_agency_comments_fancybox_wrapper').find('.statement_comments').val();
        var agency_id = $(this).parents('.statements_agency_comments_fancybox_wrapper').find('.agency_id').val();
        var error = "";
        var submitCount = 0;
        
        if(statement_comments==""){
            error += "Statement Agency Comments must not be empty\n";
        }
        if(error!=""){
            swal('',error,'error');
            return false;
        }

        if(submitCount==0){
            submitCount++;
            
            $('#load-screen').show();
            //ajax submit
            jQuery.ajax({
                type: "POST",
                url: "/accounts/ajax_update_statement_agency_comments",
                data: {
                    agency_id: agency_id,
                    statement_comments: statement_comments
                }
            }).done(function( ret ){	
                $('#load-screen').hide();
                swal({
                    title: "Success!",
                    text: "Update Success",
                    type: "success",
                    confirmButtonClass: "btn-success",
                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                    timer: <?php echo $this->config->item('timer') ?>
                });
                setTimeout(function(){ window.location='/accounts/send_statements'; }, <?php echo $this->config->item('timer') ?>);	

            });	

            return false;
        }else{
            swal('','Form submission is in progress.');
            return false;
        }

    })


})


</script>
