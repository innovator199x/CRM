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
        'link' => "/reports/key_tracking"
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
        echo form_open('/reports/key_tracking',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">


                        <div class="col-mdd-3">
                            <label for="search">Date</label>
                            <input type="text" placeholder="ALL" name="date_filter" class="form-control flatpickr" value="<?php echo ($this->input->get_post('date_filter')!="")?$this->input->get_post('date_filter'):date('d/m/Y') ?>" />
                        </div>

                         <div class="col-mdd-3">
                            <label>Agency</label>
                            <select id="agency_filter" name="agency_filter" class="form-control">
                                <option value="">ALL</option>
                                <?php
                                    foreach($agency_list->result_array() as $row){
                                    $selected = ($this->input->get_post('agency_filter')==$row['a_id'])?'selected':'';
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $row['a_id'] ?>"><?php echo $row['a_name'] ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>

                         <div class="col-mdd-3">
                            <label >Tech</label>
                            <select id="tech_filter" name="tech_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                                    <?php 
                                        foreach($tech_list->result_array() as $row){
                                        $selected = ($this->input->get_post('tech_filter')==$row['StaffID'])?'selected':'';
                                    ?>
                                    <option <?php echo $selected; ?> value="<?php echo $row['StaffID'] ?>"><?php echo $row['staff_fName']." ".$row['staff_lName'] ?></option>
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
							<th>Agency</th>
							<th>Technician</th>
							<th>Action</th>
                            <th>Time</th>
                            <th>Number of Keys</th>
                            <th>Agency Staff</th>
                            <th>Signature</th>
                            <th>Keys Returned?</th>
						</tr>
					</thead>

					<tbody>
                        <?php
                        if($lists->num_rows()>0){
                        $i= 0;
                        foreach($lists->result_array() as $row){
                        ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($row['tech_date'])) ?></td>
                            <td><?php echo $this->gherxlib->crmlink('vad',$row['a_id'],$row['a_name'], '', $row['priority']) ?></td>
                            <td><?php echo "{$row['staff_fName']} {$row['staff_lName']}" ?></td>
                            <td style="color:<?php echo ($row['action']=="Pick Up")?'green"':'red'; ?>"><?php echo $row['action']; ?></td>
                            <td><?php echo date('H:i', strtotime($row['completed_date'])) ?></td>
                            <td><?php echo $row['number_of_keys'] ?></td>
                            <td><?php echo $row['kr_agency_staff'] ?></td>
                            <td>
								<?php
                                if( $row['refused_sig'] == 1 ){
                                    echo "Refused";
                                }else{

                                    if( $row['signature_svg']!='' ){ ?>
                                        <a data-toggle="tooltip" title="Show/View" href="#fancy_<?php echo $i ?>" class="inline_fancybox"><span class="fa fa-eye" style="font-size:20px;"></span></a>
                                    
                                        <div style="display:none;" id="fancy_<?php echo $i; ?>">
                                            <img  style="width:300px;" src="<?php echo $row['signature_svg'] ?>" />
                                        </div>
                                        
                                    <?php	
                                    }else{
                                       echo "N/A"; 
                                    }

                                }								
								?>								
							</td>
                            <td>
                                <?php
                                    if( $row['action']=='Drop Off' ){

                                        $this->db->select('is_keys_returned, not_returned_notes');
                                        $this->db->from('agency_keys');
                                        $this->db->where('agency_id', $row['a_id']);
                                        $this->db->where('date', $row['tech_date']);
                                        $this->db->where('tech_id', $row['tech_id']);
                                        $q = $this->db->get();

                                       foreach( $q->result_array() as $row2 ){

                                            if( $row2['is_keys_returned'] == 1 ){
                                                $is_keys_returned_text = 'Yes';
                                                $not_returned_notes_text = NULL;
                                            } else{
                                                $is_keys_returned_text = 'No';
                                                $not_returned_notes_text = $row2['not_returned_notes'];
                                            }
                                ?>
                                
                                        <?php  
                                        echo $is_keys_returned_text."<br/>";
                                        echo $not_returned_notes_text;
                                        ?>

                                <?php
                                       }
                                    }
                                ?>
                            </td>
                        </tr>
                        <?php
                        $i++;
                        }
                    }else{
                        echo "<tr><td colspan='8'>No Data</td></tr>";
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
    This page displays all key pick up and drop offs by the technicians
	</p>
    <pre>
<code>SELECT `kr`.`tech_run_keys_id` as `techRun_id`, `kr`.`date` as `tech_date`, `kr`.`action`, `kr`.`completed_date`, `kr`.`number_of_keys`, `kr`.`agency_staff` as `kr_agency_staff`, `kr`.`signature_svg`, `kr`.`refused_sig`, `a`.`agency_id` as `a_id`, `a`.`agency_name` as `a_name`, `sa`.`FirstName` as `staff_fName`, `sa`.`LastName` as `staff_lName`
FROM `tech_run_keys` AS `kr`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `kr`.`agency_id`
LEFT JOIN `staff_accounts` as `sa` ON `sa`.`StaffID` = `kr`.`assigned_tech`
WHERE `kr`.`tech_run_keys_id` > 0
AND `kr`.`date` = '2021-08-10'
AND `kr`.`completed` = 1
AND `a`.`country_id` = 1
ORDER BY `kr`.`date` DESC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script>

     jQuery(document).ready(function(){

         $("a.inline_fancybox").fancybox({});

     });

</script>