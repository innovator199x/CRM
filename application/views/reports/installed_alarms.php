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
        'title' => 'Reports',
        'link' => "/reports"
    ),
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/reports/installed_alarms"
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
        echo form_open('/reports/installed_alarms',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-11 col-md-12 columns">
                    <div class="row">


                        <div class="col-mdd-3">
                            <label for="search">From</label>
                            <input type="text" placeholder="ALL" name="date_from_filter" class="form-control flatpickr" value="<?php echo $date_from_filter ?>" />
                        </div>

                         <div class="col-mdd-3">
                            <label for="search">To</label>
                            <input type="text" placeholder="ALL" name="date_to_filter" class="form-control flatpickr" value="<?php echo $date_to_filter ?>" />
                        </div>
                         <div class="col-mdd-3">
                            <label>Alarm Type</label>
                            <select id="alarm_type_filter" name="alarm_type_filter" class="form-control">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($alarm_power->result_array() as $row){
                                    $selected =  ($this->input->get_post('alarm_type_filter')==$row['alarm_pwr_id'])?'selected':NULL;
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $row['alarm_pwr_id'] ?>"><?php echo $row['alarm_pwr'] ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>

                         <div class="col-mdd-3">
                            <label for="agency_select">Reason</label>
                            <select id="reason_filter" name="reason_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($alarm_reason->result_array() as $row){
                                    $selected =  ($this->input->get_post('reason_filter')==$row['alarm_reason_id'])?'selected':NULL;
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $row['alarm_reason_id'] ?>"><?php echo $row['alarm_reason'] ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-mdd-3">
                            <label for="agency_select">Technician</label>
                            <select id="tech_filter" name="tech_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($tech_filter->result_array() as $row){
                                    $selected =  ($this->input->get_post('tech_filter')==$row['StaffID'])?'selected':NULL;
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $row['StaffID'] ?>"><?php echo "{$row['FirstName']} {$row['LastName']}" ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-mdd-3">
                            <label for="agency_filter">Agency</label>
                            <select id="agency_filter" name="agency_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($agency_filter->result_array() as $row){
                                    $selected =  ($this->input->get_post('agency_filter')==$row['agency_id'])?'selected':NULL;
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $row['agency_id'] ?>"><?php echo "{$row['agency_name']}" ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-mdd-3">
                            <label for="state"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
                            <select id="state_filter" name="state_filter" class="form-control">
                                <option value="">ALL</option>
                                <option value="NSW" <?php echo ( $this->input->get_post('state_filter') == 'NSW' )?'selected="selected"':''; ?>>NSW</option>
                                <option value="VIC" <?php echo ( $this->input->get_post('state_filter') == 'VIC' )?'selected="selected"':''; ?>>VIC</option>
                                <option value="QLD" <?php echo ( $this->input->get_post('state_filter') == 'QLD' )?'selected="selected"':''; ?>>QLD</option>
                                <option value="ACT" <?php echo ( $this->input->get_post('state_filter') == 'ACT' )?'selected="selected"':''; ?>>ACT</option>
                                <option value="TAS" <?php echo ( $this->input->get_post('state_filter') == 'TAS' )?'selected="selected"':''; ?>>TAS</option>
                                <option value="SA" <?php echo ( $this->input->get_post('state_filter') == 'SA' )?'selected="selected"':''; ?>>SA</option>
                                <option value="WA" <?php echo ( $this->input->get_post('state_filter') == 'WA' )?'selected="selected"':''; ?>>WA</option>
                                <option value="NT" <?php echo ( $this->input->get_post('state_filter') == 'NT' )?'selected="selected"':''; ?>>NT</option>
                            </select>
                        </div>

                        <div class="col-mdd-3">
                            <label for="job_type">Job Type</label>
                            <select id="job_type" name="job_type_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($job_type_filter->result_array() as $row){
                                    $selected =  ($this->input->get_post('job_type_filter')==$row['job_type'])?'selected':NULL;
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $row['job_type'] ?>"><?php echo "{$row['job_type']}" ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>

                      

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
                        </div>
                        
                    </div>

                </div>


                <!-- DL ICONS START -->
        
				<div class="col-md-1 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
                                <a href="<?php echo $export_link; ?>" target="blank">
                                    Export
                                </a>						
                            </p>
                        </div>
                    </section>
                </div>
                
             
                                    
                </div>
                </form>
            </div>

        </header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
            <table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Date</th>
							<th>Alarm Type</th>
							<th>Sell (INC GST) Price</th>
							<th>Sell (EX GST) Price</th>
							<th>Buy (INC GST) Price</th>
							<th>Buy (EX GST) Price</th>
                            <th>State</th>
                            <th>Technicians</th>
                            <th>Reason</th>
                            <th>Job</th>
						</tr>
					</thead>

					<tbody>
                        <?php
                            foreach($lists->result_array() as $row){
                                $sell_price = $row['alarm_price']; // from `alarm` table
				                $buy_price = $row['alarm_price_inc']; // from `alarm_pwr` table
				                $ex_sell_price = number_format($row['a_price'] / 1.1, 2); 
				                $ex_buy_price = number_format($row['alarm_price_inc'] / 1.1, 2);
                        ?>
                            <tr>
                                <td data-agencyid="<?php echo $row['agency_id']; ?>"><?php echo date('d/m/Y', strtotime($row['j_date'])); ?></td>
                                <td><?php echo $row['alarm_pwr']; ?></td>
                                <td><?php echo "$".$row['a_price']; ?></td>
                                <td><?php echo "$". $ex_sell_price; ?></td> 
                                <td><?php echo "$".$row['alarm_price_inc']; ?></td>
                                <td><?php echo "$".$ex_buy_price; ?></td>
                                <td><?php echo $row['p_state']; ?></td>
                                <td><?php echo $row['FirstName'].' '.$row['LastName']; ?></td>
                                <td><?php echo $row['alarm_reason']; ?></td>
                                <td><?php echo $this->gherxlib->crmLink('vjd',$row['j_id'],$row['j_id']); ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td><strong>TOTAL</strong></td>
                            <td>&nbsp;</td>
                            <td><strong>$<?php echo number_format($alarm_price_tot,2); ?></strong></td>
                            <td><strong>$<?php echo number_format($alarm_price_tot/1.1,2); ?></strong></td>
                            <td><strong>$<?php echo number_format($alarm_price_inc,2); ?></strong></td>
                            <td><strong>$<?php echo number_format($alarm_price_inc/1.1,2); ?></strong></td>
                            <?php
                            $diff_tot = $alarm_price_tot - $alarm_price_inc;
                            $diff_tot_class = ( $diff_tot>0 )?'text-green':'text-red';						
                            ?>
                            <td class="<?php echo $diff_tot_class; ?>"><strong>$<?php echo number_format($diff_tot,2); ?></strong></td>
                            <td></td>
                            <td></td>
                        </tr>

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
    This page displays all alarms that SATS have installed
	</p>
    <p>Buy Price are exclusive of GST.</p>
    
<pre>
<code><?php echo $query_string ?></code>
</pre>

</div>
<!-- Fancybox END -->
