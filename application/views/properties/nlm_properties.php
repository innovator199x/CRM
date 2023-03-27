<style>
    .nlm-table tr:hover{
        background: #FFCCCC!important;
    }
</style>
<?php
/* error_reporting(E_ALL); 
ini_set('display_errors', 1);  */
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
            'link' => "/properties/nlm_properties"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <?php
            $form_attr = array(
                'id' => 'nlm_reports',
                'class' => ''
            );
            echo form_open('properties/nlm_properties', $form_attr);
            ?>
            <div class="for-groupss row">
                <div class="col-md-12 columns">
                    <div class="row">
                        <div class="col-md-2">
                            <label>Phrase</label>
                            <input name="phrase_filter" class="form-control" value="<?php echo $phrase_filter; ?>" />
                        </div>
                        <div class="col-md-2">
                            <label>Agency</label>
                            <select class="form-control" id="nlm_sel_agency" name="nlm_sel_agency">
                                <option value="">Select</option>
                                <?php
                                foreach ($select_data['nlm_sel_agency'] as $key => $agency) {
                                    if( $key > 1 ){
                                        $selVal = strval($sel_agency);
                                        $keyVal = strval($key);
                                        $selected = ($keyVal === $selVal) ? 'selected="selected"' : '';
                                        echo '<option value=' . $key . '  ' . $selected . '>' . $agency . '</option>';
                                    }                                    
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Show</label>
                            <select class="form-control" id="nlm_sel_show" name="nlm_sel_show">
                                <option value="">All</option>
                                <?php
                                foreach ($select_data['nlm_sel_show'] as $key =>  $fValue) {
                                    $selVal = strval($sel_show);
                                    $keyVal = strval($key);
                                    $selected = ($keyVal === $selVal) ? 'selected="selected"' : '';
                                    echo '<option value=' . $key . '  ' . $selected . '>' . $fValue . '</option>';
                                } ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <button type="submit" class="btn btn-inline" name="submitFilter">Search</button>
                        </div>

                        <div class="col-md-4">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <button type="button" id="refresh_btn" class="btn btn-inline btn-success float-right" name="submitFilter">Refresh</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            echo form_close();
            ?>

        </div>

    </header>

    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
                <table class="table table-hover_a main-table nlm-table nlm_prop_tbl">
                    <thead>
                        <tr>
                            <?php
                            foreach ($reports_tbl_title as $value) {
                                echo '<th>' . $value . '</th>';
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody class="table-hover">
                        <?php
                        $address = '';
                        $nlm_by = '';
                        $checkedNLM = '';
                        $checkedOwing = '';
                        $checkdWoff = '';
                        $price = '';

                        $properties = $propertiesReturn->result();

                        foreach ($properties as $key => $row) {

                            $row_bg_class = null;

                            $address = $row->address_1 . ' ' . $row->address_2 . ', ' . $row->address_3 . ' ' . $row->state;
                            $agency = $row->agency_name;
                            $del_date = $row->deleted_date;
                            $nlm_timestamp = $row->nlm_timestamp;

                            $nlm_staff = $row->nlm_by_sats_staff;
                            $nlm_agency = $row->nlm_by_agency;
                            $prop_id = $row->property_id;
                            $nlm_display = $row->nlm_display;
                            $nlm_owing = $row->nlm_owing;
                            $write_off = $row->write_off;
                            $rowPrice = floatval($row->price);
                            $agency_id = $row->agency_id;
                            $jID = $jobPropArr[$key]->id;
                            $jDate = $jobPropArr[$key]->date;
                            $jType = $jobPropArr[$key]->job_type;
                            $jPrice = ($jobPropArr[$key]->job_price !== NULL) ? '$' . number_format($this->system_model->price_ex_gst($jobPropArr[$key]->job_price),2) : '';

                            if ($nlm_staff !== NULL) {
                                $nlm_by = 'SATS';
                            } else if ($nlm_agency !== NULL) {
                                $nlm_by = 'Agency';
                            }

                            $no_jobs_completed = ( $jID == '' ) ? 1 : 0;
                            $checkedNLM = ($nlm_display === '1') ? 'checked' : '';
                            $checkedOwing = ($nlm_owing === '1') ? 'checked' : '';
                            $checkdWoff = ($write_off === '1') ? 'checked' : '';
                            $bgRedOwing = ($nlm_owing === '1') ? 'redRowBg' : null;
                            $no_jobs_completed_bg = ( $no_jobs_completed == 1 ) ? 'grey_rgb_bg' : null;

                            // no jobs completed (gray)
                            if( strlen($jID) === 0 ){
                                $row_bg_class = "grey_rgb_bg";
                            }
                                
                            // NLM owing (red)
                            if( $nlm_owing == 1 ){
                                $row_bg_class = "redRowBg";
                            }

                            /**No Jobs completed or LINK */
                            $completed = (strlen($jID) === 0) ? '<i>No Completed Jobs</i>' : $this->gherxlib->crmlink("vjd", $jID, $jType);

                            echo '<tr id=tr_' . $prop_id . ' class=' . $row_bg_class . '>';
                            echo '<td>' . $completed . '</td>';
                            echo '<td>' . dateFormatter($jDate) . '</td>';
                            echo '<td>' . $jPrice . '</td>';
                            echo '<td>' . $jType . '</td>';
                            echo '<td>' . $this->gherxlib->crmlink("vad", $agency_id, $agency,'', $row->priority) . '</td>';
                            echo '<td>' . $this->gherxlib->crmlink("vpd", $prop_id, $address) . '</td>';
                            echo '<td>' . dateFormatter($nlm_timestamp) . '</td>';
                            echo '<td>' . $nlm_by . '</td>';
                            echo '<td>';
                            ?>
                            <div class="checkbox">
                                <input name="verifiedProp[]" type="checkbox" id="check-<?php echo $prop_id; ?>" data-jobid="<?php echo $prop_id ?>" value="<?php echo $prop_id; ?>" <?php echo $checkedNLM; ?> checkboxFor="verified">
                                <label for="check-<?php echo $prop_id; ?>">&nbsp;</label>
                            </div>
                            <?php echo '</td>'; ?>

                            <?php echo '<td>' ?>
                            <div class="checkbox">
                                <input name="owingProp[]" type="checkbox" id="owing-<?php echo $prop_id; ?>" data-jobid="<?php echo $prop_id ?>" value="<?php echo $prop_id; ?>" <?php echo $checkedOwing; ?> checkboxFor="owing">
                                <label for="owing-<?php echo $prop_id; ?>">&nbsp;</label>
                            </div>
                            <?php echo '</td>'; ?>

                            <?php echo '<td>' ?>
                            <div class="checkbox">
                                <input name="write_offProp[]" type="checkbox" id="writeoff-<?php echo $prop_id; ?>" data-jobid="<?php echo $prop_id ?>" value="<?php echo $prop_id; ?>" <?php echo $checkdWoff; ?> checkBoxFor="write_off">
                                <label for="writeoff-<?php echo $prop_id; ?>">&nbsp;</label>
                            </div>
                            <?php echo '</td>';
                            echo '</tr>';
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
<div id="about_page_fb" class="fancybox" style="display:none;">

    <h4>No Longer Managed Report</h4>
    <p>
        This page lists all properties that have been marked No Longer Managed and allows the accounts team to verify if any money is unpaid. Once Verify Paid is unticked they disappear from the page. Jobs that appear on this page will remain visible on the portal but shaded out.
    </p>
    <p>Amount are exclusive of GST.</p>
    <pre>
<code>SELECT DISTINCT(p.`property_id`), `p`.`address_1`, `p`.`address_2`, `p`.`address_3`, `p`.`state`, `p`.`postcode`, `p`.`nlm_by_sats_staff`, `p`.`nlm_by_agency`, `p`.`nlm_display`, `p`.`nlm_owing`, `p`.`write_off`, `p`.`nlm_timestamp`, `a`.`agency_id`, `a`.`agency_name`
FROM `property` AS `p`
LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
LEFT JOIN `agency_user_accounts` AS `aua` ON p.`pm_id_new` = aua.`agency_user_account_id`
WHERE `a`.`country_id` = <?php echo COUNTRY ?> 
AND `nlm_display` = 1
AND COALESCE(write_off,0) =0
ORDER BY `p`.`nlm_timestamp` DESC
LIMIT 100</code>
    </pre>

</div>
<!-- Fancybox END -->

<script>
    $(document).ready(() => {

        // refresh button
        jQuery("#refresh_btn").click(function(){

            location.reload();

        });

        $('.nlm_prop_tbl input[type="checkbox"]').on('click', function(e) {
            const checkID = $(this).attr('id');
            const propID = $(this).attr('data-jobid');
            const propVal = ($(this).prop("checked")) ? 1 : 0;
            const propType = $("#" + checkID).attr("checkboxFor");
            const base_url = "<?php echo base_url() ?>" + "properties/updateNLMProperty";

            //console.log('propID', propID);

            $.ajax({
                type: "post",
                url: base_url,
                dataType: 'json',
                data: {
                    propID: propID,
                    propVal: propVal,
                    propType: propType
                },
                beforeSend: function() {
                    swal.close();
                    $("input, select, button").attr("disabled", true);
                    $("#preloader").show().css("background-color", "rgba(255, 255, 255, 0.2901960784313726)");
                    $("#status").show();
                },
                complete: function() {
                    $("input, select, button").attr("disabled", false);
                    $("#preloader").hide().css("background-color", "#fff");
                    $("#status").hide();
                },
                success: function(response) {
                    //console.log('response', response);
                    if (response) {
                        $('#' + checkID)[0].checked = (propVal === 1) ? true : false;
                        //swal("Success", "Successfully updated item.", "success");
                        if (propType === "owing") {
                            if (propVal === 1) {
                                $('#tr_' + propID).addClass('redRowBg');
                            } else {
                                $('#tr_' + propID).removeClass('redRowBg');
                            }
                        } else {
                            /*
                            $('#tr_' + propID).remove();
                            setTimeout(() => {
                                location.reload();
                            }, 200);
                            */
                        }
                    } else {
                        $('#' + checkID)[0].checked = (propVal === 0) ? true : false;
                        swal("Error", "An error occured while processing the request.\nPlease try again.", "error");
                    }
                },
                error: function(error) {
                    console.log('error', error);
                }
            });
            
        });
    });
</script>