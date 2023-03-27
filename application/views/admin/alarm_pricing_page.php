<style>
    .col-mdd-3 {
        max-width: 15.5%;
    }

    .action_a,
    .action_div {
        color: #adb7be !important;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/admin/alarm_pricing_page"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

?>



    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
                <table class="table table-hover main-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Make</th>
                            <th>Model</th>
                            <th class="text-center">Alarm Power Source</th>
                            <th class="text-center">Battery Type</th>
                            <th class="text-center">Is Battery Replaceable</th>
                            <th>Is Lithium</th>
                            <th>Expiry</th>
                            <th>Buy Price EX GST</th>
                            <th>Buy Price INC GST</th>
                            <th>Active</th>
                            <th>Edit</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach($lists->result_array() as $alarm){ ?>
                        <tr>

                            <td>
                                <span class="txt_lbl"><?php echo $alarm['alarm_pwr']; ?></span>

                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $alarm['alarm_make']; ?></span>

                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $alarm['alarm_model']; ?></span>
                            </td>
                            <td class="text-center">
                                <span class="txt_lbl"><?php echo $alarm['alarm_pwr_source']; ?></span>
                            </td>
                            <td class="text-center"> <span class="txt_lbl"><?php echo $alarm['battery_type']; ?></span>
                            </td>
                            <td class="text-center">
                                <span class="txt_lbl">
                                    <?php 
                                                if($alarm['is_replaceable']!=NULL){
                                                    if($alarm['is_replaceable']==1){
                                                        echo "Yes";
                                                    }else{
                                                        echo "No";
                                                    }
                                                }else{
                                                    echo "&nbsp;";
                                                }
                                            ?>
                                </span>
                            </td>
                            <td>
                                <?php echo ($alarm['is_li']==1)? "Yes" : "No"; ?>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $alarm['alarm_expiry']; ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl">$<?php echo $alarm['alarm_price_ex']; ?></span>

                            </td>
                            <td>
                                <span class="txt_lbl">$<?php echo $alarm['alarm_price_inc']; ?></span>

                            </td>
                            <td>
                                <span
                                    class="txt_lbl"><?php echo ($alarm['active']==1)?'<span class="text-green">Yes</span>':'<span class="text-red">No</span>'; ?></span>
                            </td>
                            <td class="action_div">
                                <a href="#edit_fancybox_<?php echo $alarm['alarm_pwr_id'] ?>" data-toggle="tooltip"
                                    title="Edit" class="btn_edit action_a fancybox_btn"><i
                                        class="font-icon font-icon-pencil"></i></a>

                                <!-- EDIT FANCYBOX -->
                                <div class="update_btn_div" id="edit_fancybox_<?php echo $alarm['alarm_pwr_id'] ?>"
                                    style="display:none;width:900px;">
                                    <h4>Edit Alarm</h4>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Name</label>
                                                <input type="text" name="alarm_pwr" class="form-control alarm_pwr" value="<?php echo $alarm['alarm_pwr']; ?>" />
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Make</label>
                                                <input type="text" name="alarm_make" class="form-control alarm_make" value="<?php echo $alarm['alarm_make']; ?>" />
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Model</label>
                                                <input type="text" name="alarm_model" class="form-control alarm_model" value="<?php echo $alarm['alarm_model']; ?>" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Alarm Power Source</label>
                                                <select name="alarm_pwr_source" class="form-control alarm_pwr_source">
                                                    <option value="">Please Select</option>
                                                    <option <?php echo ($alarm['alarm_pwr_source']=='3v') ? 'selected="selected"' :NULL; ?> value="3v">3v</option>
                                                    <option <?php echo ($alarm['alarm_pwr_source']=='3vLi') ? 'selected="selected"' :NULL; ?> value="3vLi">3vLi</option>
                                                    <option <?php echo ($alarm['alarm_pwr_source']=='6vLi') ? 'selected="selected"' :NULL; ?> value="6vLi">6vLi</option>
                                                    <option <?php echo ($alarm['alarm_pwr_source']=='9v') ? 'selected="selected"' :NULL; ?> value="9v">9v</option>
                                                    <option <?php echo ($alarm['alarm_pwr_source']=='9vLi') ? 'selected="selected"' :NULL; ?> value="9vLi">9vLi</option>
                                                    <option <?php echo ($alarm['alarm_pwr_source']=='240v') ? 'selected="selected"' :NULL; ?> value="240v">240v</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Alarm Type</label>
                                                <select name="alarm_type" class="form-control alarm_type">
                                                    <option value="">Please Select</option>
                                                    <?php foreach($alarm_types->result_array() as $alarm_type){ ?>
                                                    <option value="<?php echo $alarm_type['alarm_type_id'] ?>"
                                                        <?php echo ($alarm['alarm_type']==$alarm_type['alarm_type_id']) ? 'selected' :NULL; ?>>
                                                        <?php echo $alarm_type['alarm_type']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Battery Type</label>
                                                <select name="battery_type" class="form-control battery_type">
                                                    <option value="">Please Select</option>
                                                    <option
                                                        <?php echo ($alarm['battery_type']=='3v') ? 'selected="selected"' :NULL; ?>
                                                        value="3v">3v</option>
                                                    <option
                                                        <?php echo ($alarm['battery_type']=='3vLi') ? 'selected="selected"' :NULL; ?>
                                                        value="3vLi">3vLi</option>
                                                    <option
                                                        <?php echo ($alarm['battery_type']=='6vLi') ? 'selected="selected"' :NULL; ?>
                                                        value="6vLi">6vLi</option>
                                                    <option
                                                        <?php echo ($alarm['battery_type']=='9v') ? 'selected="selected"' :NULL; ?>
                                                        value="9v">9v</option>
                                                    <option
                                                        <?php echo ($alarm['battery_type']=='9vLi') ? 'selected="selected"' :NULL; ?>
                                                        value="9vLi">9vLi</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Is Battery Replaceable</label>
                                                <select name="is_replaceable" class="form-control is_replaceable">
                                                    <option value="">Please Select</option>
                                                    <option
                                                        <?php echo ($alarm['is_replaceable']=="1") ? 'selected="selected"' :NULL; ?>
                                                        value="1">Yes</option>
                                                    <option
                                                        <?php echo ($alarm['is_replaceable']=="0") ? 'selected="selected"' :NULL; ?>
                                                        value="0">No</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Expiry</label>
                                                <input type="text" name="alarm_expiry" class="form-control alarm_expiry"
                                                    value="<?php echo $alarm['alarm_expiry']; ?>" />
                                            </div>
                                        </div>


                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Buy Price EX GST</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">$</span>
                                                    </div>
                                                    <input type="text" name="alarm_price_ex"
                                                        class="form-control alarm_price_ex"
                                                        value="<?php echo $alarm['alarm_price_ex']; ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Buy Price INC GST</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">$</span>
                                                    </div>
                                                    <input type="text" name="alarm_price_inc"
                                                        class="form-control alarm_price_inc"
                                                        value="<?php echo $alarm['alarm_price_inc']; ?>" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Active</label>
                                                <select class="form-control active" name="active" id="active"
                                                    style="width: 100% !important;">
                                                    <option value="">--Select--</option>
                                                    <option value="1"
                                                        <?php echo ($alarm['active']==1)?'selected="selected"':''; ?>>
                                                        Active</option>
                                                    <option value="0"
                                                        <?php echo ($alarm['active']==0)?'selected="selected"':''; ?>>
                                                        Inactive</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group mt-3">
                                                <input type="hidden" name="alarm_pwr_id" class="alarm_pwr_id"
                                                    value="<?php echo $alarm['alarm_pwr_id']; ?>" />
                                                <button class="btn btn_update btn-block">Update</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- EDIT FANCYBOX END -->

                            </td>

                        </tr>
                        <?php
                            }
                        ?>

                    </tbody>

                </table>

                <div>
                    <a data-fancybox data-src="#add_alarm_fancybox" class="btn" id="btn_add_alarm_txt"
                        href="javascript:;">Add Alarm</a>

                    <!-- ADD ALARM FANCYBOX -->
                    <div class="add_alarm_fancybox" id="add_alarm_fancybox" style="display:none;width:400px;">
                        <h4>Add New Alarm</h4>
                        <?php echo form_open('/admin/add_alarm_pricing', 'id=add_alarm_pricing_form') ?>

                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" id="name" class="form-control alarm_pwr" />
                        </div>

                        <div class="form-group">
                            <label>Make</label>
                            <input type="text" name="make" id="make" class="form-control alarm_make" />
                        </div>

                        <div class="form-group">
                            <label>Model</label>
                            <input type="text" name="model" id="model" class="form-control alarm_model" />
                        </div>

                        <div class="form-group">
                            <label>Alarm Power Source</label>
                            <select name="alarm_pwr_source" class="form-control">
                                <option value="">Please Select</option>
                                <option value="3v">3v</option>
                                <option value="3vLi">3vLi</option>
                                <option value="6vLi">6vLi</option>
                                <option value="9v">9v</option>
                                <option value="9vLi">9vLi</option>
                                <option value="240v">240v</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Battery Type</label>
                            <select name="battery_type" class="form-control">
                                <option value="">Please Select</option>
                                <option value="3v">3v</option>
                                <option value="3vLi">3vLi</option>
                                <option value="6vLi">6vLi</option>
                                <option value="9v">9v</option>
                                <option value="9vLi">9vLi</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Is Battery Replaceable</label>
                            <select name="is_replaceable" class="form-control">
                                <option value="">Please Select</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Expiry</label>
                            <input type="text" name="expiry" id="expiry" class="form-control alarm_expiry"
                                placeholder="eg. 2020" />
                        </div>

                        <div class="form-group">
                            <label>Buy Price EX GST</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" name="price_ex_gst" id="price_ex_gst"
                                    class="form-control alarm_price_ex" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Buy Price INC GST</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" name="price_inc_gst" id="price_inc_gst"
                                    class="form-control alarm_price_inc" />
                            </div>
                        </div>

                        <div class="form-group">
                            <button class="btn btn_add" type="submit">Submit</button>
                        </div>

                        </form>
                    </div>
                    <!-- ADD ALARM FANCYBOX END -->

                </div>

            </div>
            <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center">
                <?php echo $pagination; ?></nav>
            <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
        </div>
    </section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the
    fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;">

    <h4><?php echo $title; ?></h4>
    <p><strong>IMPORTANT</strong></p>
    <p>
        The Alarms entered into this page are the alarms that are available on the Tech Sheet for the Technicians to
        install. The pricing on this page is used to control the pricing on the Installed Alarms Report. The prices on
        this page will not update the alarm prices on the purchase order page. They will need to be adjusted from the
        Stock Items page.
    </p>
    <pre>
<code>SELECT *
FROM `alarm_pwr` as `ap`
WHERE `ap`.`alarm_pwr_id`>0
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">
    function getExclusiveGST(inclusiveGST) {
        let exclusiveGST = (inclusiveGST / 1.10).toFixed(2);
        return exclusiveGST>0 ? exclusiveGST : "";
    }

    function getInclusiveGST(exclusiveGST) {
        let inclusiveGST = (exclusiveGST * 1.10).toFixed(2);
        return inclusiveGST>0 ? inclusiveGST : "";
    }

    function setInclusiveGST(inputName, val) {
        $(`input[name="${inputName}"]`).val("");

        if (parseFloat(val)>0) {
            $(`input[name="${inputName}"]`).val(getInclusiveGST(val));
        }
    }

    function setExclusiveGST(inputName, val) {
        $(`input[name="${inputName}"]`).val("");

        if (parseFloat(val)>0) {
            $(`input[name="${inputName}"]`).val(getExclusiveGST(val));
        }
    }

    jQuery(document).ready(function () {
        <?php if($this->session->flashdata('status') && $this->session->flashdata('status') =='success') { ?>
            swal({
                title: "Success!",
                text: "<?php echo $this->session->flashdata('success_msg') ?>",
                type: "success",
                confirmButtonClass: "btn-success",
                showConfirmButton: < ? php echo $this->config ->item('showConfirmButton') ?>,
                timer: < ? php echo $this->config ->item('timer') ? >
            }); <?php
        } else if ($this->session->flashdata('status') && $this->session->flashdata('status') =='error') { ?>
            swal({
                title: "Error!",
                text: "<?php echo $this->session->flashdata('error_msg') ?>",
                type: "error",
                confirmButtonClass: "btn-danger"
            }); 
        <?php } ?>

        $(".fancybox_btn").fancybox({
            hideOnContentClick: false,
            hideOnOverlayClick: false
        });

        //Update
        jQuery(".btn_update").click(function () {

            var alarm_pwr_id = jQuery(this).parents(".update_btn_div").find(".alarm_pwr_id").val();
            var alarm_pwr = jQuery(this).parents(".update_btn_div").find(".alarm_pwr").val();
            var alarm_make = jQuery(this).parents(".update_btn_div").find(".alarm_make").val();
            var alarm_model = jQuery(this).parents(".update_btn_div").find(".alarm_model").val();
            var alarm_expiry = jQuery(this).parents(".update_btn_div").find(".alarm_expiry").val();
            var alarm_price_ex = jQuery(this).parents(".update_btn_div").find(".alarm_price_ex").val();
            var alarm_price_inc = jQuery(this).parents(".update_btn_div").find(".alarm_price_inc").val();
            var battery_type = jQuery(this).parents(".update_btn_div").find(".battery_type").val();
            var is_replaceable = jQuery(this).parents(".update_btn_div").find(".is_replaceable").val();
            var alarm_pwr_source = jQuery(this).parents(".update_btn_div").find(".alarm_pwr_source").val();
            var alarm_type = jQuery(this).parents(".update_btn_div").find(".alarm_type").val();
            var active = jQuery(this).parents(".update_btn_div").find(".active").val();

            var error = "";

            if (alarm_pwr_id == "") {
                error += "Number is required";
            }

            if (alarm_pwr == "") {
                error += "Name is required";
            }

            if (error != "") {
                swal('', error, 'error');
            } else {

                jQuery.ajax({
                    type: "POST",
                    url: "/admin/ajax_update_alarm_pricing",
                    dataType: 'json',
                    data: {
                        alarm_pwr_id: alarm_pwr_id,
                        alarm_pwr: alarm_pwr,
                        alarm_make: alarm_make,
                        alarm_model: alarm_model,
                        alarm_expiry: alarm_expiry,
                        alarm_price_ex: alarm_price_ex,
                        alarm_price_inc: alarm_price_inc,
                        active: active,
                        battery_type: battery_type,
                        is_replaceable: is_replaceable,
                        alarm_pwr_source: alarm_pwr_source,
                        alarm_type: alarm_type,
                    }
                }).done(function (ret) {
                    if (ret.status) {
                        swal({
                            title: "Success!",
                            text: "Update Success",
                            type: "success",
                            showCancelButton: false,
                            confirmButtonText: "OK",
                            closeOnConfirm: false,
                            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                            timer: <?php echo $this->config ->item('timer') ?>
                        });

                        var full_url = window.location.href;
                        setTimeout(function () {
                            window.location = full_url
                        }, <?php echo $this->config->item('timer') ?>);

                    } else {
                        swal('', 'Server error: Please contact admin!', 'error');
                    }
                });

            }

        });

        //ADD ALARM PRICING
        jQuery("#add_alarm_pricing_form").submit(function () {

            var name = jQuery("#add_alarm_pricing_form #name").val();
            var expiry = jQuery("#add_alarm_pricing_form #expiry").val();
            var error = "";

            if (name == "") {
                error += "Alarm Name is Required\n";
            }
            if (expiry != "") {
                if (!$.isNumeric(expiry)) {
                    error += "Expiry must be numeric\n";
                }
            }

            if (error != '') {
                swal('', error, 'error');
                return false;
            } else {
                return true;
            }

        });

        // add alarm
        $('input[name="price_inc_gst"]').on('keyup', function () {
            setExclusiveGST("price_ex_gst", $(this).val());
        });

        $('input[name="price_ex_gst"]').on('keyup', function () {
            setInclusiveGST("price_inc_gst", $(this).val());
        });


        // edit alarm
        $('input[name="alarm_price_inc"]').on('keyup', function () {
            setExclusiveGST("alarm_price_ex", $(this).val());
        });

        $('input[name="alarm_price_ex"]').on('keyup', function () {
            setInclusiveGST("alarm_price_inc", $(this).val());
        });
    });
</script>