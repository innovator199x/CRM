
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => $uri
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

?>
    <!--
	<header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open('/reports/cron_report',$form_attr);
            ?>
                <div class="for-groupss row">
                    <div class="col-lg-10 col-md-12 columns">
                        <div class="row">

                            <div class="col-mdd-3">
                                <label for="a">Cron Type</label>
                                <select id="cron_type_filter" name="cron_type_filter" class="form-control field_g2">
                                    <option value="">ALL</option>
                                    <?php
                                    /* 
                                        foreach($cron_type->result_array() as $row){
                                            $sel = ($this->input->get_post('cron_type_filter')==$row['cron_type_id'])?'selected="true"':NULL;
                                    ?>
                                            <option <?php echo $sel ?> value="<?php echo $row['cron_type_id'] ?>"><?php echo $row['type_name'] ?></option>
                                    <?php
                                        }
                                    */
                                    ?>
                                </select>
                            </div>

                            <div class="col-mdd-3">
                                    <label for="date_select">Date from:</label>
                                    <input name="date_from_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $from ?>">
                            </div>

                            <div class="col-mdd-3">
                                <label for="date_select">to:</label>
                                <input name="date_to_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $to ?>">
                            </div>
                        
                            <div class="col-md-1 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <input class="btn" type="submit" name="btn_search" value="Search">
                            </div>
                            
                        </div>
                    </div>                                                                   
                </div>
            </form>
        </div>
    </header>
    -->

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>	
                            <th>Cron ID</th>          
							<th>Cron Name</th>
							<th>Description</th>
                            <th>Action</th>
						</tr>
					</thead>

					<tbody>
                        <?php
                        
                        if($cron_sql->num_rows() > 0){
                            foreach($cron_sql->result() as $cron_row){
                        ?>
                            <tr>     
                                <td><?php echo $cron_row->cron_type_id; ?></td>                      
                                <td><?php echo $cron_row->type_name; ?></td>
                                <td><?php echo $cron_row->description; ?></td>
                                <td class="action_col">
                                    <button type="button" class="btn run_it_btn">Run it</button>                                    
                                    <input type="hidden" class="ci_link" value="<?php echo $cron_row->ci_link ?>" />
                                    <input type="hidden" class="cron_type_id" value="<?php echo $cron_row->cron_type_id ?>" />
                                </td>
                             </tr>
                        <?php   
                            }
                        }else{ ?>
                            <tr><td colspan='4'>No Data</td></tr>
                        <?php    
                        }                        
                        ?>
                 
					</tbody>

				</table>
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
    This page shows all Crons (Automated scripts) that have been run during the selected time period.
	</p>

</div>
<!-- Fancybox END -->


<script>
jQuery(document).ready(function(){
    
    jQuery(".run_it_btn").click(function(e){
        
        var btn_ob = jQuery(this);
        var parent_td = btn_ob.parents("td.action_col:first");
        var ci_link = parent_td.find(".ci_link").val();

        swal({
            title: "Warning!",
            text: "Are you sure you want to run this cron?",
            type: "warning",						
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Continue",
            cancelButtonClass: "btn-danger",
            cancelButtonText: "No, Cancel!",
            showLoaderOnConfirm: true,
            closeOnConfirm: true,    
            closeOnCancel: true
        },
        function(isConfirm) {

            if (isConfirm) {							  
                window.open(ci_link, '_blank');            				
            }

        });	
        
        
    });
    
});
</script>