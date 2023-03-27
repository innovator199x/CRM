
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .action_div{
        display: none;
    }
    .being-removed{
        background: #721c24;
    }
    .txl_lbl img, .txt_hid img{
        max-width: 100%;
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
            'link' => "/reports/view_franchise_groups"
        ),
    );
    if ($this->uri->segment(3)) {
        $bc_items[] = [
            'title' => "Franchise Groups",
            'status' => 'active',
            'link' => "/reports/view_franchise_groups/" . $this->uri->segment(3)
        ];
    }
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="for-groupss row">
                <div class="col-md-10">
                    <form method=POST action="/reports/view_franchise_groups/<?php echo $fg_id; ?>" class="form-row">


                        <div class="form-group col-md-2">
                            <label>State:</label>
                            <?php //$allstates = $user->getAllStates(); ?>
                            <select name="state" class="form-control">
                                <option value="">----</option>
                                <option <?php echo $state == 'NSW' ? 'selected="selected"' : ''; ?> value='NSW'>NSW</option>
                                <option <?php echo $state == 'VIC' ? 'selected="selected"' : ''; ?> value='VIC'>VIC</option>
                                <option <?php echo $state == 'QLD' ? 'selected="selected"' : ''; ?> value='QLD'>QLD</option>
                                <option <?php echo $state == 'ACT' ? 'selected="selected"' : ''; ?> value='ACT'>ACT</option>
                                <option <?php echo $state == 'TAS' ? 'selected="selected"' : ''; ?> value='TAS'>TAS</option>
                                <option <?php echo $state == 'SA' ? 'selected="selected"' : ''; ?> value='SA'>SA</option>
                                <option <?php echo $state == 'WA' ? 'selected="selected"' : ''; ?> value='WA'>WA</option>
                                <option <?php echo $state == 'NT' ? 'selected="selected"' : ''; ?> value='NT'>NT</option>
                                <?php //foreach($allstates as $states){ ?>
                                        <!--<option value="<? //=$states['name'];        ?>" ><? //=$states['name'];        ?></option>-->
                                <?php //} ?>

                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label>Sales Rep:</label>
                            <select name="salesrep" class="form-control">
                                <option value="">----</option>
                                <?php
                                foreach ($sales_rep as $sr) {
                                    $sales_rep_name = $sr['FirstName'] . " " . $sr['LastName'];
                                    ?>
                                    <option value="<?php echo $sales_rep_name; ?>" <?php echo ($sales_rep_name == $salesrep) ? 'selected="selected"' : ''; ?>><?php echo $sales_rep_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label>Region:</label>
                            <select name="region" class="form-control">
                                <option value="">----</option>
                                <?php foreach ($result_region as $regions) { ?>
                                    <option <?php echo $region == $regions['agency_region_name'] ? 'selected="selected"' : ''; ?> value="<?= $regions['agency_region_name']; ?>" ><?= $regions['agency_region_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>


                        <div class="form-group col-md-2">
                            <label>Phrase:</label>
                            <input type="text" value="<?Php echo ($phrase !== null) ? $phrase : "" ?>" size="10" name="phrase" class="addinput form-control">
                        </div>

                        <div class="form-group col-md-2">
                            <input style="margin-top: 15px;" class="searchstyle submitbtnImg btn" type="submit" value="Search">
                        </div>
                    </form> 






                </div>
                <div class="form-group col-md-2">
                    <form method=POST action="/reports/view_franchise_groups/<?php echo $fg_id; ?>" class="form-row">
                        <input type="hidden" name="isExport" value="1" />
                        <input type="hidden" name="sort" value="<?Php echo $sort ?>" />
                        <input type="hidden" name="order_by" value="<?php echo $order_by; ?>" />
                        <input type="hidden" name="state" value="<?php echo $state; ?>" />
                        <input type="hidden" name="salesrep" value="<?php echo $salesrep; ?>" />
                        <input type="hidden" name="region" value="<?php echo $region; ?>" />
                        <input type="hidden" name="phrase" value="<?php echo $phrase; ?>" />
                        <input style="margin-top: 15px;float:right;" type="submit" class="submitbtnImg export btn btn-danger" value="Export" />
                        <!--                            <a style="margin-top: 15px;float:right;" class="submitbtnImg export btn btn-danger" 
                                                       href="https://crmdev.sats.com.au/export_franchise_group_agencies.php?fg_id=<?php
                        echo $fg_id;
                        ?>&sort=<?php echo $sort; ?>&order_by=<?php echo $order_by; ?>&state=<?php echo $state; ?>&salesrep=<?php echo $salesrep; ?>&region=<?php echo $region; ?>&phrase=<?php echo $phrase; ?>">Export</a>-->
                    </form>

                </div>

            </div>
        </div>
    </header>

    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
                <table class="table table-hover main-table">
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>State</th>
                            <th>Properties</th>
                            <?Php
                            foreach ($alarm_job_types as $ajt) {
                                switch ($ajt['id']) {
                                    case 2:
                                        $serv_color = 'b4151b';
                                        $serv_icon = 'smoke_colored.png';
                                        break;
                                    case 5:
                                        $serv_color = 'f15a22';
                                        $serv_icon = 'safety_colored.png';
                                        break;
                                    case 6:
                                        $serv_color = '00ae4d';
                                        $serv_icon = 'corded_colored.png';
                                        break;
                                    case 7:
                                        $serv_color = '00aeef';
                                        $serv_icon = 'pool_colored.png';
                                        break;
                                    case 8:
                                        $serv_color = '9b30ff';
                                        $serv_icon = 'sa_ss_colored.png';
                                        break;
                                    case 9:
                                        $serv_color = '9b30ff';
                                        $serv_icon = 'sa_cw_ss_colored.png';
                                        break;
                                }

                                if ($this->config->item('country') == 2) {
                                    if ($ajt['id'] == 2) {
                                        ?>
                                        <!--
                                        <th><img src="/images/serv_img/<?php echo $serv_icon; ?>" /></th>
                                        -->

                                         <th class="text-center"><img src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($ajt['id']); ?>" /></th>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <!--
                                    <th data-aw="<?php echo $ajt['id'] ?>"><img src="/images/serv_img/<?php echo $serv_icon; ?>" /></th>
                                    -->
                                    <th class="text-center" data-aw="<?php echo $ajt['id'] ?>"><img src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($ajt['id']); ?>" /></th>
                                    <?php
                                }
                            }
                            ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if (count($franchise_groups_agency) > 0) {
                            foreach ($franchise_groups_agency as $row) {
                                ?>
                                <tr>
                                    <td>
                                        <span class="txt_lbl">
                                            <?Php echo $this->gherxlib->crmLink('vad', $row['agency_id'], $row['agency_name']) ?>
        <!--                                            <a href="<?Php echo $this->gherxlib->crmLink('vad', $row['agency_id'], $row['agency_name']) ?>"><?php echo $row['agency_name']; ?>
                                            </a>-->
                                        </span>
                                    </td>
                                    <td>
                                        <span class="txt_lbl"><?php echo $row['state']; ?></span>
                                    </td>
                                    <td>
                                        <span class="txt_lbl"><?php echo $row['tot_properties']; ?></span>
                                    </td>	
                                    <?Php
                                    $ajt_cnt = 0;
                                    foreach ($row as $ajt => $val) {

                                        if (strpos($ajt, 'AJT_') === false) {
                                            continue;
                                        }

                                        if ($this->config->item('country') == 2) {
                                            if ($ajt_cnt == 0) {
                                                ?>
                                                <td class="text-center">
                                                    <span class="txt_lbl"><?php echo $val; ?></span>
                                                </td>
                                                <?Php
                                            }
                                        } else {
                                            ?>
                                            <td class="text-center">
                                                <span class="txt_lbl"><?php echo $val; ?></span>
                                            </td>
                                            <?php
                                        }

                                        $ajt_cnt++;
                                    }
                                    ?>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='11'>No Data</td></tr>";
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

<!--Fancybox Start--> 
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>
        This page shows displays total number of offices attached to a particular group
    </p>

</div>
<!-- Fancybox END 


<script>
    jQuery(document).ready(function () {

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

        // update
        jQuery(".btn_update").click(function () {

            var $this = jQuery(this);
            var name = jQuery(this).parents("tr:first").find(".name").val();
            jQuery(this).parents("form.post-submit").find('.name').val(name);
            jQuery(this).parents("form.post-submit").submit();


            return;

            var error = "";

            if (description == "") {
                error += "Description is required";
            }

            if (page == "") {
                error += "Page is required";
            }

            if (error != "") {
                alert(error);
            } else {

                jQuery.ajax({
                    type: "POST",
                    url: "/reports/update_icon",
                    data: {
                        icon_id: icon_id,
                        description: description,
                        page: page
                    }
                }).done(function (ret) {
                    window.location = "/icons.php?update_success=1";
                    var $resp = jQuery.parseJSON(ret);
                    swal({
                        title: $resp.title,
                        text: $resp.message,
                        type: $resp.type
                    });
                    if ($resp.status) {
                        $this.parents("tr:first").find('.lbl_desc').html($this.parents("tr:first").find(".txt_hid.description").val());
                        $this.parents("tr:first").find('.lbl_page').html($this.parents("tr:first").find(".txt_hid.page").val());
                        $this.parents("tr:first").find(".action_div").hide();
                        $this.parents("tr:first").find(".btn_edit").show();
                        $this.parents("tr:first").find("input.txt_hid").removeClass('form-control').hide();
                        $this.parents("tr:first").find("span.txt_hid").hide();
                        $this.parents("tr:first").find(".txt_lbl").show();
                    }

                });

            }

        });


        // delete script
        jQuery(".btn_delete").click(function () {

            var $this = jQuery(this);
            var franchise_groups_id = jQuery(this).parents("tr:first").find(".franchise_groups_id").val();
            swal({
                title: "Trying to delete.",
                text: "Are you sure you want to delete?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                closeOnConfirm: false,
                closeOnCancel: true
            }, function (isConfirm) {
                if (isConfirm) {
                    jQuery.ajax({
                        type: "POST",
                        url: "/reports/delete_franchise_groups_action_ajax",
                        data: {
                            'franchise_groups_id': franchise_groups_id
                        }
                    }).done(function (ret) {
                        //window.location = "/view_vehicles.php";
                        var $resp = jQuery.parseJSON(ret);
                        if ($resp.status === true) {
                            $this.parents('tr').addClass('being-removed');
                            setTimeout(() => {
                                $this.parents('tr').removeClass('being-removed').remove();
                            }, 500);
                        }


                        swal({
                            title: $resp.title,
                            text: $resp.message,
                            type: $resp.type,
                            showConfirmButton: false
                        });
                        setTimeout(() => {
                            swal.close();
                        }, 2000);
                    });
                }
            });
            // if(confirm("Are you sure you want to delete")){



            // }
        });

        // inline edit
        jQuery(".btn_edit").click(function () {

            var btn_txt = jQuery(this).html();

            jQuery(this).hide();

            if (btn_txt == 'Edit') {
                jQuery(this).parents("tr:first").find(".action_div").show();
                jQuery(this).parents("tr:first").find("input.txt_hid").addClass('form-control').show();
                jQuery(this).parents("tr:first").find("span.txt_hid").show();
                jQuery(this).parents("tr:first").find(".txt_lbl").hide();
            } else {
                jQuery(this).parents("tr:first").find(".action_div").hide();
            }


        });


        // cancel script
        jQuery(".btn_cancel").click(function () {
            jQuery(this).parents("tr:first").find(".action_div").hide();
            jQuery(this).parents("tr:first").find(".btn_edit").show();
            jQuery(this).parents("tr:first").find("input.txt_hid").removeClass('form-control').hide();
            jQuery(this).parents("tr:first").find("span.txt_hid").hide();
            jQuery(this).parents("tr:first").find(".txt_lbl").show();
        });


        // add icons show/hide script
        jQuery("#add_icon_btn").click(function () {

            jQuery("#add_icon_div").show();

        });

        // add header validation
        jQuery("#add_icon_form").submit(function () {

            var icon = jQuery("#icon").val();
            var description = jQuery("#description").val();
            var error = "";

            if (icon == "") {
                error += "Icon is required\n";
            }

            if (description == "") {
                error += "Description is required\n";
            }

            if (error != "") {
                alert(error);
                return false
            } else {
                return true;
            }

        });


    });
</script>