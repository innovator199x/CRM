
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .jtable td, .jtable th {
        border-top: none;
        height: auto;
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
    
	<header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open($uri,$form_attr);
            ?>
                <div class="for-groupss row">
                    <div class="col-lg-10 col-md-12 columns">
                        <div class="row">


                            <div class="col-mdd-3">
                                    <label for="date_select">Date:</label>
                                    <input name="date_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo ( $this->input->get_post('date_filter')!= '' )?$this->input->get_post('date_filter'):null; ?>">
                            </div>

                            <div class="col-md-1 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <input class="btn" type="submit" name="btn_search" value="Search">
                            </div>

                            <div class="col-md">
                                <table class="table jtable">
                                    <tr>
                                        <td class="text-right">On Hold - Covid:</td>
                                        <td><?php echo $on_hold_covid_count; ?></td>                                                                                
                                    </tr> 
                                    <tr>                    
                                        <td class="text-right">On Hold:</td>
                                        <td><?php echo $on_hold_count; ?></td>
                                    </tr>   
                                    <tr>        
                                        <td class="text-right">Total jobs:</td>
                                        <td><?php echo $total_job_count; ?></td>
                                    </tr>
                                    <tr>              
                                        <td class="text-right">Percentage of On Hold - COVID:</td>
                                        <td><?php echo number_format((($on_hold_covid_count/$total_job_count)*100), 2, '.', '').'%'; ?></td>
                                    </tr>
                                </table>                                                                                    
                            </div>
                            
                        </div>
                    </div>                                                                   
                </div>
            </form>
        </div>
    </header>
    

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover table-striped main-table">
					<thead>
						<tr>	
                            <th>Job ID</th>          
                            <th>Job Type</th>
                            <th>Job Status</th>	
                            <th>Job Date</th>
                            <th>Property Address</th>	
                            <th>Agency</th>		
						</tr>
					</thead>

					<tbody>
                        <?php
                        
                        if($job_sql->num_rows() > 0){
                            foreach($job_sql->result() as $job_row){
                            $p_address = "{$job_row->p_address_1} {$job_row->p_address_2}, {$job_row->p_address_3}";
                        ?>
                            <tr>    
                                <td>
                                    <a href="<?php echo "{$this->config->item('crm_link')}/view_job_details.php?id={$job_row->jid}"; ?>">
                                        <?php echo $job_row->jid; ?>
                                    </a>                    
                                </td>  
                                <td>
                                    <?php echo $job_row->job_type; ?>
                                </td>
                                <td>
                                    <?php echo $job_row->j_status; ?>
                                </td>
                                <td>
                                    <?php echo ($this->system_model->isDateNotEmpty($job_row->j_date) == true) ? $this->system_model->formatDate($job_row->j_date, 'd/m/Y') : ''; ?>
                                </td> 
                                <td>
                                    <a href="<?php echo "{$this->config->item('crm_link')}/view_property_details.php?id={$job_row->property_id}"; ?>">
                                        <?php echo $p_address; ?>
                                    </a>                    
                                </td> 
                                <td>
                                    <a href="<?php echo "/agency/view_agency_details/{$job_row->agency_id}"; ?>">
                                        <?php echo $job_row->agency_name; ?>
                                    </a>                    
                                </td>                                                                                                  
                            </tr>
                        <?php   
                            }
                        }else{ ?>
                            <tr><td colspan='2'>No Data</td></tr>
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
    This page shows all On Hold jobs, both regular and for COVID-19 reasons                    
	</p>

</div>
<!-- Fancybox END -->


<script>
jQuery(document).ready(function(){
       
    
});
</script>