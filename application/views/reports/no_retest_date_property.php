
<style>
.btn_agency_table_join{
    margin-top:8px;
}
.btn_show_job_details{
    margin-top:15px;
}
.atay_2{padding:0!important;margin:0!important;}
table.awo tr td{border:0px;}
th.check_all_td{width:50px;}
</style>
<div class="box-typical box-typical-padding">

<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/reports/no_retest_date_property"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

<section>
    <div class="body-typical-body">
        <div class="table-responsive">
            <table class="table table-hover main-table table-striped">
                <thead>
                    <tr>
                       <!-- <th>Deadline</th> -->
                        <th>Address</th>
                        <th class="check_all_td">
								<div class="checkbox" style="margin:0;">
									<input name="chk_all" type="checkbox" id="check-all">
									<label for="check-all">&nbsp;</label>
								</div>
							</th>
                      <!--  <th style="width:200px;">Job Type</th>
                        <th style="width:150px;">Job Age</th>-->
                      <!--  <th>Job ID</th>
                        <th>Job Date</th> -->
                    </tr>
                </thead>

                <tbody>
                    <?php
                        if($lists->num_rows()>0){
                        foreach($lists->result_array() as $u){

                        // $now = time();
                        // $your_date = strtotime($u['test_date']);
                        // $datediff = $now - $your_date;

                        // $deadline = 365-(round($datediff / (60 * 60 * 24)));
                        // if ($deadline > 15) {
                        //     continue;
                        // }
                    ?>
                            <tr class="body_tr list-main-tr jalign_left aw_row" data-prowid="<?php echo $u['property_id'] ?>" <?php echo $bg_color; ?>>
								<td>
									<span class="txt_lbl">
                                        <?php 
                                        $prop_address = $u['p_address1']." ".$u['p_address2'].", ".$u['p_address3']." ".$u['p_state']." ".$u['p_postcode'];
                                        echo $this->gherxlib->crmLink('vpd',$u['property_id'],$prop_address);
                                        ?>
									</span>
                                </td>
                                <td>
                                    <div class="checkbox sd_checkbox">
                                        <input value="<?php echo $u['property_id']; ?>" data-propid="<?php echo $u['property_id']; ?>" type="checkbox" class="chkbox" name="chkbox[]" id="check-<?php echo $u['property_id']; ?>"> 
                                        <label for="check-<?php echo $u['property_id']; ?>">&nbsp;</label>
                                    </div>
                                </td>
                               <!-- <td class="atay_2" colspan="2"></td>-->
                               <!-- <td>
                                    <span class="txt_lbl">
                                        <?php 
                                        echo $this->gherxlib->crmLink('vjd',$u['id'],$u['id']);
                                        ?>
                                    </span>
                                </td>
                                <td><?=$u['status']?></td>
                                <td><?=$this->system_model->formatDate($u['job_date'],'d/m/Y')?></td> -->
                            </tr>
                    <?php
                        }}else{
                    ?>
                            <tr><td colspan="2">No results found</td></tr>
                    <?php
                        }
                    ?>
                    <tr class="sd_buttons" style="display:none;">
                        <td colspan="2">
                        <div class="sd_buttons_sss text-right">
                            <button style="margin-top:10px;" type="button" class="btn btn-inline btn-danger" id="btn_open_new_tab">Open in new tab</button>
                        </div>
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>

        <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

    </div>
</section>

</div>


<style>
.main-table {
    border-left: 1px solid #dee2e6;
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 20px;
}

.col-mdd-3 {
    -webkit-box-flex: 0;
    -ms-flex: 0 0 15.2%;
    flex: 0 0 15.2%;
    max-width: 15.2%;

    position: relative;
    width: 100%;
    min-height: 1px;
    padding-right: 15px;
    padding-left: 15px;
}
</style>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

<h4><?php echo $title; ?></h4>
<?php $last_90_days = date('Y-m-d', strtotime(date('Y-m-d').' -90 days')); ?>
<p>This page finds properties without a retest date, so we prevent missing compliance deadlines.<br/><br/>
</p>
<pre>
<code>SELECT DISTINCT(p.property_id) as property_id, `p`.`address_1` as `p_address1`, `p`.`address_2` as `p_address2`, `p`.`address_3` as `p_address3`, `p`.`state` as `p_state`, `p`.`postcode` as `p_postcode`
FROM `property` as `p`
LEFT JOIN `property_services` as `ps` ON `ps`.`property_id` = `p`.`property_id`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `p`.`agency_id`
WHERE `a`.`country_id` = <?php echo COUNTRY ?> 
AND `p`.`deleted` = 0
AND `ps`.`service` = 1
AND `p`.`retest_date` IS NULL AND CAST(p.`created` AS DATE ) < '<?php echo $last_90_days; ?>'
AND `is_nlm` != 1
LIMIT 50</code>
</pre>

</div>
<!-- Fancybox END -->

<script type="text/javascript">

    jQuery(document).ready(function(){

        //click all tick box
        $('#check-all').on('change',function(){
            var obj = $(this);
            var isChecked = obj.is(':checked');
            var divbutton = $('.sd_buttons');
            if(isChecked){
                divbutton.show();
                $('.chkbox').prop('checked',true);
                $('.list-main-tr').addClass('selected');
            }else{
                divbutton.hide();
                $('.chkbox').prop('checked',false);
                $('.list-main-tr').removeClass('selected');
            }
        })

        //SERVICE DUE CHECKBOX TWEAK
        $('.chkbox').on('change',function(){

            var obj = $(this);

            if(obj.is(':checked')){
                obj.parents('.list-main-tr').addClass('selected');
                obj.parents('.list-main-tr').next('.abudakar').find('.sd_prop_vacant_more_info').slideDown();
                $('.sd_buttons').show();
            }else{
                obj.parents('.list-main-tr').removeClass('selected');
                obj.parents('.list-main-tr').next('.abudakar').find('.sd_prop_vacant_more_info').slideUp();

                if($('[name="chkbox[]"]:checked').length==0){
                    $('.sd_buttons').hide();
                }
            }

        });

        $('#btn_open_new_tab').on('click',function(){

            var obj = $(this);
			var ischecked = $('.chkbox:checked');
            var base_url = "<?php echo $this->config->item('crm_link') ?>";
           

            if(ischecked.length>0){
                jQuery(".chkbox:checked").each(function(){
                    var prop_id = $(this).val();
                    window.open(base_url+'/view_property_details.php?id='+prop_id, '_blank')
                });
            }else{
                swal('','No Items Selected','error');
            }

        })
        
    })

</script>