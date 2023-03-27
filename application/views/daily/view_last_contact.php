          
<style>
    .jalign_left{
        text-align:left;
    }
    .txt_hid, .btn_update{
        display:none;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Daily',
            'link' => "/daily/"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/daily/view_last_contact"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);

    $export_links_params_arr = array(
        'state' => $this->input->get_post('state'),
        'agency_filter' => $this->input->get_post('agency_filter'),
        'agency_priority_filter' => $this->input->get_post('agency_priority_filter')
    );
    $export_link_params = '/daily/view_last_contact?export=1&'.http_build_query($export_links_params_arr);
    ?>
    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="form-row">
                <form method="POST" class="form-row" style="min-width: 100%">
                    <div class="col-md-2">                        
                        <label>
                            <?php
                            $str_state = 'State';
                            if ($this->config->item('country') === 2) {
                                $str_state = 'Region';
                            }
                            echo $str_state;
                            ?>:
                        </label>
                        <?php if(isset($total_jobs)): ?>
                            <select id="state" name="state" class="form-control">
                                <option value="">Any</option> 			
                                <?php
                                $state_arr = array();
                                foreach ($total_jobs as $hasState) {
                                    if (!in_array($hasState['p_state'], $state_arr)) {
                                        $state_arr[] = $hasState['p_state'];
                                    }
                                }
                                foreach ($state_arr as $state_val) {
                                    ?>
                                    <option value="<?php echo $state_val; ?>" <?php echo ($state_val == $state) ? 'selected="selected"' : ''; ?>><?php echo $state_val; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-2">
                        Agency
                        <select id="agency_filter" name="agency_filter" class="form-control">
                            <option value="">---</option>
                            <?php if(isset($dist_agency_sql)):  ?>
                            <?php foreach( $dist_agency_sql as $dist_agency_row ): ?>
                                <option value="<?php echo $dist_agency_row->agency_id; ?>" <?php echo ( $dist_agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected':null; ?>>
                                    <?php echo $dist_agency_row->agency_name; ?>
                                </option>
                            <?php endforeach; ?>
                            <?php endif; ?>			                            
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="ht_select">Agency Priority</label><span>
                        <select id="agency_priority_filter" name="agency_priority_filter" class="form-control field_g2">
                            <option value="" <?php echo ($this->input->get_post('agency_priority_filter') == "") ? "selected" : ""; ?>>ALL</option>
                            <option value="0" <?php echo ($this->input->get_post('agency_priority_filter') === "0") ? "selected" : ""; ?>>Regular</option>
                            <option value="1" <?php echo ($this->input->get_post('agency_priority_filter') === "1") ? "selected" : ""; ?>>HT</option>
                            <option value="2" <?php echo ($this->input->get_post('agency_priority_filter') === "2") ? "selected" : ""; ?>>VIP</option>
                            <option value="3" <?php echo ($this->input->get_post('agency_priority_filter') === "3") ? "selected" : ""; ?>>HWC</option>
                        </select>
                        <div class="mini_loader"></div>
                    </div>

                    <div class="col-md-4">
                        <label>&nbsp;</label>
                        <input type='submit' class='submitbtnImg btn' value='Search' />
                    </div>

                    <div class="col-md-2 columns">
                        <section class="proj-page-section float-right">
                            <div class="proj-page-attach">
                                <i class="fa fa-file-excel-o"></i>
                                <p class="name"><?php echo $title; ?></p>
                                <p>
                                    <a href="<?php echo $export_link_params ?>" target="blank">
                                        Export
                                    </a>
                                </p>
                            </div>
                        </section>
                    </div>

                    
                    
                </form>
            </div>
        </div>
    </header>
    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
                <table class="table table-hover main-table">
                    <thead>
                        <tr>
                            <th>Created</th>
                            <th>Last Contact</th>
                            <th>Days</th>
                            <th>Job Type</th>
                            <th>
                                <div class="tbl-tp-name colorwhite bold">Address</div>
                                <a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=p.address_3&order_by=<?php echo ($_REQUEST['order_by'] == 'ASC') ? 'DESC' : 'ASC'; ?>&state=<?Php echo $state ?>"> 
                                    <div class="arw-std-<?php echo ( $order_by == 'ASC' ) ? 'up' : 'dwn'; ?> arrow-<?php echo ( $order_by == 'ASC' ) ? 'up' : 'dwn'; ?>-<?php echo ($sort == 'p.address_3') ? 'active' : ''; ?>"></div>
                                </a>
                            </th>

                            <th><?php
                                    $str_state = 'State';
                                    if ($this->config->item('country') === 2) {
                                        $str_state = 'Region';
                                    }
                                    echo $str_state;
                                    ?></th>
                            <th>Agency</th>
                            <th style="width:20%">Job Notes</th>
                            <th>Comments</th>
                            <th>Assigned Tech</th>
                            <th>Job #</th>                        
                            <th><div class="tbl-tp-name colorwhite bold"><input type="checkbox" id="maps_check_all" /></div></th>
                        </tr>
                    </thead>

                    <tbody>                


                        <?php
                            
                        if (!empty($jobs)) {
                            foreach ($jobs as $row) {
                                ?>
                                <tr class="body_tr jalign_left">

                                    <td><?php echo ($row['jcreated'] != "" && $row['jcreated'] != "0000-00-00") ? date("d/m/Y", strtotime($row['jcreated'])) : ''; ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($row['last_contact_v2'])); ?></td>
                                    <td>
                                        <?php
                                        $now = time(); // or your date as well
                                        $your_date = strtotime($row['last_contact_v2']);
                                        $datediff = $now - $your_date;
                                        echo floor($datediff / (60 * 60 * 24));
                                        ?>
                                    </td>
                                    <td><?php echo $this->gherxlib->getJobTypeAbbrv($row['job_type']); ?></td>
                                    <td><?Php echo $this->gherxlib->crmLink('vpd', $row['property_id'], "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"); ?></td>

                                    <td><?php echo $row['p_state']; ?></td>
                                    <td class="<?php echo ( $row['priority'] > 0 )?'j_bold':null; ?>">
                                        <?php echo $row['agency_name']." ".( ( $row['priority'] > 0 )?' ('.$row['abbreviation'].')':null ); ?>
                                    </td>
                                    <td><?php echo $row['comments']; ?></td>
                                    <td>
                                        <textarea class="addinput form-control comments" id="comments"><?php echo $row['lcc_comments']; ?></textarea>
                                    </td>
                                    <td><?php echo "{$row['FirstName']} {$row['LastName']} "; ?></td>
                                    <td>
                                        <?Php echo $this->gherxlib->crmLink('vjd', $row['jid'], $row['jid']); ?>
                                    </td>							                                
                                    <td>
                                        <input type="checkbox" class="maps_chk_box" value="<?php echo $row['jid']; ?>" />
                                        <input type="hidden" class="hid_job_id" value="<?php echo $row['jid']; ?>" />
                                        <input type="hidden" class="p_id<?php echo $row['jid']; ?>" value="<?php echo $row['property_id']; ?>" />
                                    </td>

                                </tr>

                                <?php
                                $i++;
                            }
                        } else {
                            echo "ram";
                            echo "<tr><td colspan='8'>No Data</td></tr>";
                        }
                        ?>
                    </tbody>

                </table>
                <div class="text-right" style="float: right;display: none;" id="map_div">
                    <select class="form-control select_action float-left mr-2">
                        <option value="">----</option>
                        <option value="1">Assign Tech</option>	
                        <option value="2">Snooze</option>	
                    </select>
                </div>
                <div class="col-md-5 offset-3" id="tech_div" style="display: none">
                    <div class="row">
                        <div class="col-md-5">
                            <label>Tech:</label>
                            <select id="maps_tech" class="form-control">
                                <option value="">-- select --</option>
                                <?Php
                                foreach ($sa_tech as $tech) {
                                    ?>

                                    <option value="<?php echo $tech['StaffID']; ?>">
                                        <?php
                                        echo $this->system_model->formatStaffName($tech['FirstName'], $tech['LastName']) . ( ( $tech['is_electrician'] == 1 ) ? ' [E]' : null );
                                        ?>
                                    </option>

                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label>Date:</label>
                            <input autocomplete="off"  type="label" id="maps_date" class="flatpickr form-control flatpickr-input">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="button" id="btn_assign" class="blue-btn submitbtnImg btn">Assign</button>
                        </div>
                    </div>
                    <br><br>
                </div>
                <div class="col-md-5 offset-3" id="snooze_div" style="display: none;">
                    <div class="row">
                        <div class="col-md-10">
                            <input name="snooze_reason" class="form-control"  id="snooze_reason" type="text" placeholder="Snooze reason*" >
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="execute" class="blue-btn submitbtnImg btn">Execute</button>
                        </div>
                    </div>
                    <br><br>
                </div>
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
        This page focuses on jobs that have not been contacted in anyway for an extended period.
    </p>
    <pre>
        <code><?php echo $sql_query; ?></code>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

    jQuery(document).ready(function () {

        jQuery(".select_action").change(function(){

        var node = jQuery(this);
        var select_action = node.val();
        // alert(select_action);
        if (select_action == 1) {
            jQuery("#tech_div").show();
            jQuery("#snooze_div").hide();
        } else if(select_action == 2){
            jQuery("#snooze_div").show();
            jQuery("#tech_div").hide();
        } else {
            jQuery("#tech_div").hide();
            jQuery("#snooze_div").hide();
        }

        });

        // check all toggle
        jQuery("#maps_check_all").click(function () {

            if (jQuery(this).prop("checked") == true) {
                jQuery(".maps_chk_box:visible").prop("checked", true);
                jQuery("#map_div").show();
            } else {
                jQuery(".maps_chk_box:visible").prop("checked", false);
                jQuery("#map_div").hide();
            }

        });

        // toggle hide/show remove button
        jQuery(".maps_chk_box").click(function () {

            var chked = jQuery(".maps_chk_box:checked").length;

            if (chked > 0) {
                jQuery("#map_div").show();
            } else {
                jQuery("#map_div").hide();
            }

        });

        // move to maps 
        jQuery("#btn_assign").click(function () {

            var job_id = new Array();
            var tech_id = jQuery("#maps_tech").val();
            var date = jQuery("#maps_date").val();

            jQuery(".maps_chk_box:checked").each(function () {
                job_id.push(jQuery(this).val());
            });
            jQuery.ajax({
                type: "POST",
                url: "/daily/assign_tech_to_jobs_action_ajax",
                data: {
                    job_id: job_id,
                    tech_id: tech_id,
                    date: date
                }
            }).done(function (ret) {
                //window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
                location.reload();
            });

        });

        jQuery("#execute").click(function () {

            var maps_chk = $('.maps_chk_box:checked');
            var snooze_reason = jQuery("#snooze_reason").val();

            if( snooze_reason == '' ){
				swal('','Snooze reason is required');
				return false;
			}

            var values = [];
            for (var i = 0; i < maps_chk.length; i++) {
                maps_chk[i].checked = true;
                values.push(maps_chk[i].value);
            }

            for (let i = 0; i < values.length; i++) {
                var chck = values[i];
                var p_id = $('.p_id'+chck).val();
                jQuery.ajax({
                    type: "POST",
                    url: "/daily/snooze_property",
                    data: {
                        property_id: p_id,
                        snooze_reason: snooze_reason
                    }
                }).done(function (ret) {
                    //window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
                    
                });
            }
            swal({
                title:"Success!",
                type: "success",
                showCancelButton: false,
                confirmButtonText: "OK",
                closeOnConfirm: false,
                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                timer: <?php echo $this->config->item('timer') ?>

            });
            setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);

        });

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
    });


    jQuery(".comments").change(function(){

        var dom = jQuery(this);
        var parents_tr = dom.parents("tr:first");

        var job_id = parents_tr.find(".hid_job_id").val();
        var comments = dom.val();        

        if( job_id > 0 && comments != '' ){

            jQuery('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/daily/save_last_contact_comments",
                data: { 		
                    job_id: job_id,			
                    comments: comments
                }
            }).done(function( ret ){
                jQuery('#load-screen').hide();
                    
                /*
                jQuery('#load-screen').hide();
                swal({
                    title: "Success!",
                    text: "Payment Success",
                    type: "success",
                    confirmButtonClass: "btn-success",
                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                    timer: <?php echo $this->config->item('timer') ?>
                });
                setTimeout(function(){ window.location='/accounts/receipting'; }, <?php echo $this->config->item('timer') ?>);	
                */

            });

        }        


    });
    


</script>
