
<style>
    .col-mdd-3{
        max-width:25.5%;
    }
    .api_li li{
        padding: 3px 0;
    }
    .txt_green{
        color: #46c35f;
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
        'link' => $uri
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

?>
    <?php
        if(!empty($agency)){
            $ag = $agency;
        }
        else{
            $ag = "null";
        }
        if(!empty($tas_filter)){
            $tas = $tas_filter;
        }
        else{
            $tas = "null";
        }
        $export_link_params = "/agency/export_account/".$ag."/".$tas;
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
                <div class="col-md-8 columns">
                    <div class="row">

                        <div class="col-mdd-3">
                            <label for="agency_select">Agency</label>
                            <input type="text" name="agency_filter" class="form-control" value="<?php echo $this->input->get_post('agency_filter') ?>">
                        </div>

                         <div class="col-mdd-3">
                           <label>Trust Account Software</label>
                            <select id="tas_filter" name="tas_filter" class="form-control">
                                <option value="">ALL</option>
                                <?php                                 
                                foreach($tas_sql->result() as $tas_row){
                                ?>
                                    <option value="<?php echo $tas_row->trust_account_software_id; ?>" <?php echo (  $tas_row->trust_account_software_id == $this->input->get_post('tas_filter') )?'selected="selected"':null; ?>><?php echo $tas_row->tsa_name ?></option>
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

                <div class="col-md-3 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
                                <a href="<?php echo $export_link_params ?>">
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
							<th>Agency</th>
							<th>Trust Account Software</th>
							<th>Available to Connect</th>
							<th>API Connected</th>
                            <th>Marker ID</th>
						</tr>
					</thead>

					<tbody>
                        <?php 
                        
                            foreach($lists->result() as $row){
                        ?>
                                 <tr>
                                    <td><?php echo $this->gherxlib->crmlink('vad', $row->agency_id, $row->agency_name,'', $row->priority); ?></td>
                                    <td><?php echo $row->tsa_name; ?></td>
                                    <td>
                                        <?php
                                        // enabled API on agency
                                        $sel_query = "
                                            agen_api_int.`api_integration_id`,
                                            agen_api_int.`connected_service`,

                                            agen_api.`api_name`
                                        ";
                                        $api_integ_params = array(
                                            'sel_query' => $sel_query,
                                            'active' => 1,
                                            'agency_id' =>$row->agency_id,
                                            'display_query' => 0
                                        );
                                        $api_integ_sql = $this->api_model->get_agency_api_integration($api_integ_params); 
                                        //echo $this->db->last_query();
                                        ?>
                                        <ul class="api_li">
                                            <?php
                                            foreach( $api_integ_sql->result() as $api_integ_row ){ ?>
                                            <li>
                                                <?php echo $api_integ_row->api_name; ?>
                                            </li>
                                            <?php
                                            }                                     
                                            ?>                                            
                                        </ul>
                                    </td>
                                    <td>
                                        <?php
                                        // connected API on agency
                                        $sel_query = "
                                            agen_api_tok.`agency_api_token_id`, 
                                            agen_api_tok.`agency_id`, 
                                            agen_api_tok.`api_id`,

                                            agen_api.`api_name`
                                        ";
                                        $api_token_params = array(
                                            'sel_query' => $sel_query,
                                            'active' => 1,
                                            'agency_id' => $row->agency_id,
                                            'group_by' => 'agen_api_tok.`agency_id`',
                                            'display_query' => 0			
                                        );
                                        $api_token_sql = $this->api_model->get_agency_api_tokens($api_token_params);
                                        //echo $this->db->last_query();
                                        ?>
                                        <ul class="api_li">
                                            <?php
                                            foreach( $api_token_sql->result() as $api_row ){ ?>
                                            <li class="txt_green">
                                                <?php echo $api_row->api_name; ?>
                                            </li>
                                            <?php
                                            }                                     
                                            ?>                                            
                                        </ul>
                                    </td>
            
                                    <td>
                                    <?php
                                        // connected API on agency
                                        $sel_query = " 
                                            agen_api_tok.`api_id`
                                        ";
                                        $api_token_params = array(
                                            'sel_query' => $sel_query,
                                            'active' => 1,
                                            'agency_id' => $row->agency_id,
                                            'group_by' => 'agen_api_tok.`agency_id`',
                                            'display_query' => 0			
                                        );
                                        $api_token_sql = $this->api_model->get_agency_api_tokens($api_token_params)->result_array();
                                        if(empty($api_token_sql)){
                                            echo "NONE";
                                        }
                                        else{
                                            $api_id = $api_token_sql[0]['api_id'];
                                            
                                            if($api_id == 1){
                                                $sel_query = " 
                                                    `pme_supplier_id`
                                                ";
                                                $api_marker_params = array(
                                                    'sel_query' => $sel_query,
                                                    'agency_id' => $row->agency_id			
                                                );
                                                $api_marker_sql = $this->api_model->get_agency_api_marker($api_marker_params)->result_array();
                                                $marker_id = $api_marker_sql[0]['pme_supplier_id'];
                                                echo strtoupper($marker_id);
                                                //print_r($api_marker_sql);
                                            }
                                            else if($api_id == 4){
                                                $sel_query = " 
                                                    `palace_diary_id`
                                                ";
                                                $api_marker_params = array(
                                                    'sel_query' => $sel_query,
                                                    'agency_id' => $row->agency_id			
                                                );
                                                $api_marker_sql = $this->api_model->get_agency_api_marker($api_marker_params)->result_array();
                                                $marker_id = $api_marker_sql[0]['palace_diary_id'];
                                                echo strtoupper($marker_id);
                                                //print_r($api_marker_sql);
                                            }
                                        }
                                    ?>
                                    </td>
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
        This page shows all the trust account software used by Agencies
	</p>
    <pre>
<code>SELECT `a`.`agency_id`, `a`.`agency_name`, `tas`.`trust_account_software_id`, `tas`.`tsa_name`
FROM `agency` AS `a`
LEFT JOIN `trust_account_software` AS `tas` ON `a`.`trust_account_software` = `tas`.`trust_account_software_id`
WHERE `a`.`status` = 'active'
AND `a`.`country_id` = <?php echo COUNTRY ?> 
AND `a`.`trust_account_software` >0
ORDER BY `a`.`agency_name` ASC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

  

</script>