
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

                            <div class="col-md-3 columns">
                                <label>Technician</label>
                                <select class="form-control" id="tech" name="tech">
                                    <option value="">ALL</option>         
                                    <?php
                                    foreach( $tech_filter_sql->result() as $tech_filter_row ){ ?>
                                        <option value="<?php echo $tech_filter_row->StaffID; ?>" <?php echo ( $tech_filter_row->StaffID == $this->input->get_post('tech') )?'selected':null; ?>>
                                            <?php echo $this->system_model->formatStaffName($tech_filter_row->FirstName,$tech_filter_row->LastName) ?>    
                                        </option>
                                    <?php
                                    }
                                    ?>                           
                                </select>
                            </div>                 

                            <div class="col-mdd-3">
                                <label for="date_select">Date</label>
                                <input name="tb_start" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" value="<?php echo ( $this->input->get_post('tb_start') != '' )?$this->input->get_post('tb_start'):date('d/m/Y'); ?>" />
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
				<table class="table table-hover table-striped main-table">
					<thead>
						<tr>	
                            <th>Technician</th> 
                            <th>Break Taken</th>  
                            <th>Timestamp</th>                                      
						</tr>
					</thead>

					<tbody>
                    <?php
                    if( $tb_sql->num_rows() > 0  ){
                        foreach( $tb_sql->result() as $tb_row ){ ?>
                            <tr>
                                <td>
                                    <?php echo $this->system_model->formatStaffName($tb_row->FirstName,$tb_row->LastName) ?>
                                </td>
                                <td>
                                    <?php echo ( $tb_row->tech_break_taken == 1 )?'<span class="text-green">Yes</span>':'<span class="text-red">No</span>'; ?>
                                </td>
                                <td>
                                    <?php echo $this->system_model->isDateNotEmpty($tb_row->tech_break_start)?date('d/m/Y H:i',strtotime($tb_row->tech_break_start)):null; ?>
                                </td>
                            </tr>
                        <?php
                        }
                    }else{ ?>
                        <tr>
                            <td colspan="3">Empty</td>
                        </tr>
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
    Shows breaks taken by techs on a given date.         
	</p>
    <pre>
<code>SELECT `sa`.`StaffID`, `sa`.`FirstName`, `sa`.`LastName`, `tb`.`tech_break_id`, `tb`.`tech_id`, `tb`.`tech_break_start`, `tb`.`tech_break_taken`
FROM `tech_breaks` as `tb`
JOIN `staff_accounts` as `sa` ON `tb`.`tech_id` = `sa`.`StaffID`
WHERE `sa`.`ClassID` = 6
AND CAST(tb.tech_break_start AS Date) = '2021-08-10'
ORDER BY `sa`.`FirstName` ASC, `sa`.`LastName` ASC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->