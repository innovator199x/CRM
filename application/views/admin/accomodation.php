
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .action_a, .action_div {
        color: #adb7be!important;
    }
    .edit_acco{
        cursor: pointer;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/admin/accommodation"
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
echo form_open('/admin/accommodation',$form_attr);
?>
    <div class="for-groupss row">
        <div class="col-md-10 columns">
            <div class="row">

                <div class="col-md-2">
                    <label for="phrase_select">Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $this->input->get_post('name'); ?>" />
                </div>

                <div class="col-mdd-3">
                    <label for="service_select">Area</label>
                    <select id="area" name="area" class="form-control field_g2">
                        <option value="">ALL</option>
                        <?php
                        foreach($area_list->result_array() as $row){
                            $selected = ($row['area'] == $this->input->get_post('area')) ? 'selected' : NULL;
                        ?>
                            <option <?php echo $selected; ?> value="<?php echo $row['area'] ?>"> <?php echo $row['area'] ?> </option>
                        <?php
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="phrase_select">Address</label>
                    <input type="text" name="search" class="form-control" placeholder="ALL" value="<?php echo $this->input->get_post('search'); ?>" />
                </div>

                <div class="col-md-1 columns">
                    <label class="col-sm-12 form-control-label">&nbsp;</label>
                    <button type="submit" class="btn btn-inline">Search</button>
                </div>

            </div>

        </div>

           <!-- DL ICONS START -->
			    <div class="col-md-2 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link_params ?>" >
									Export
								</a>
                            </p>
                        </div>
                    </section>
				</div>
				<!-- DL ICONS END -->
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
							<th>Name</th>
							<th>Area</th>
							<th>Street Number</th>
                            <th>Street Name</th>
                            <th>Suburb</th>
                            <th>State</th>
                            <th>Postcode</th>
							<th>Phone</th>
							<th>Email</th>
							<th style="width:80px;">Rate</th>
                            <th>Comment</th>
                            <th>Action</th>
						</tr>
					</thead>

					<tbody>

                        <?php
                            $i = 1;
                            foreach($accomodation_list->result_array() as $row){
                        ?>
                            <tr>
                                <td>
                                    <span class="txt_lbl name"><?php echo $row['name']; ?></span>
                                </td>
                                <td>
                                    <span class="txt_lbl area"><?php echo $row['area']; ?></span>                                    
                                </td>
                                <td>
                                    <span class="txt_lbl street_number"><?php echo $row['street_number']; ?></span>
                                </td>
                                <td>
                                    <span class="txt_lbl street_name"><?php echo $row['street_name']; ?></span>
                                </td>
                                <td>
                                    <span class="txt_lbl suburb"><?php echo $row['suburb']; ?></span>
                                </td>
                                <td>
                                    <span class="txt_lbl state"><?php echo $row['state']; ?></span>
                                </td>
                                <td>
                                    <span class="txt_lbl postcode"><?php echo $row['postcode']; ?></span>
                                </td>
                                <td>
                                    <span class="txt_lbl phone"><?php echo $row['phone']; ?></span>                                    
                                </td>
                                <td>
                                    <span class="txt_lbl email"><?php echo $row['email']; ?></span>                                    
                                </td>
                                <td>$
                                    <span class="txt_lbl rate"><?php echo $row['rate']; ?></span>                                    
                                </td>
                                <td>
                                    <span class="txt_lbl comment"><?php echo $row['comment']; ?></span>                                    
                                </td>
                                <td class="action_div">
                                    <!--<a href="#edit_fancybox_<?php echo $row['accomodation_id'] ?>" data-toggle="tooltip" title="Edit" class="btn_edit action_a fancybox_btn"><i class="font-icon font-icon-pencil"></i></a>-->
                                    
                                    <input type="hidden" class="accomodation_id" value="<?php echo $row['accomodation_id']; ?>" />
                                    <i class="font-icon font-icon-pencil edit_acco"></i>
                                    |
                                    <a data-id="<?php echo $row['accomodation_id'] ?>" data-toggle="tooltip" title="Delete" href="javascript:void(0)" class="btn_delete action_a" ><span class="glyphicon glyphicon-trash"></span></a>
                                    
                                    <!-- EDIT FANCYBOX -->
                                    <!--
                                    <div class="update_btn_div" style="display:none;width:400px;" id="edit_fancybox_<?php echo $row['accomodation_id'] ?>">
                                        <h4>Update Accommodation</h4>

                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" class="name form-control" value="<?php echo $row['name']; ?>" />
                                            <input type="hidden" class="accomodation_id" value="<?php echo $row['accomodation_id']; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label>Area</label>
                                            <input type="text" class="area form-control" value="<?php echo $row['area']; ?>" />
                                        </div>
                                        <div class="form-group">
                                        <label>Address</label>
                                            <?php
                                            if( $this->admin_model->checkIfAccomIsConctdToUser($row['accomodation_id'])==true ){ ?>
                                              <span><?php echo $row['address']; ?></span>
                                            <?php
                                            }else{ ?>
                                                <input type="text" id="address<?php echo $i ?>" class="address form-control" value="<?php echo $row['address']; ?>" />
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="form-group">
                                            <label>Phone</label>
                                            <input type="text" class="phone form-control" value="<?php echo $row['phone']; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="text" class="email form-control" value="<?php echo $row['email']; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label>Rate</label>
                                            <div class="form-control-wrapper form-control-icon-left">
                                                <input type="text" class="rate form-control" value="<?php echo $row['rate']; ?>">
                                                <i class="fa fa-dollar"></i>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Comment</label>
                                            <input type="text" class="comment form-control" value="<?php echo $row['comment']; ?>" />
                                        </div>
                                        <div class="form-group"><button class="btn btn_update">Update</button></div>

                                    </div>
                                    -->
                                    <!-- EDIT FANCYBOX END -->

                               </td>

                            </tr>

                        <?php
                            $i++;
                            }
                        ?>

					</tbody>

				</table>
			</div>
            <!--<a data-fancybox data-src="#add_new_acco_fancybox" href="javascript:;" id="btn_add_new" class="btn">Add New</a>-->

            <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

            <button type="button" id="btn_add_new" class="btn">Add New</button>

            <!-- ADD NEW ACCOMODATION FANCYBOX -->

                <div  id="add_new_acco_fancybox" class="fancybox" style="display:none;width:400px;">
                    <h4 id="add_acco_lb_title">Add New Accommodation</h4>
                    <?php echo form_open('/admin/accomodation_process', 'id=form_accomodation') ?>
                        <div class="form-group">
                            <label class="addlabel" for="title">Name <span class="txt_red">*</span></label>
                            <input type="text" name="name" id="name" class="fname form-control">
                        </div>
                        <div class="form-group">
                            <label class="addlabel" for="title">Area</label>
                            <input type="text" name="area" id="area" class="fname form-control">
                        </div>
                        <div class="form-group">
                            <label class="addlabel" for="title">Address</label>
                            <input type="text" name="address" id="fullAdd" class="fname form-control">
                        </div>
                        <div class="form-group">
                            <label class="addlabel" for="title">Street Number</label>
                            <input type="text" name="street_number" id="address_1" class="fname form-control">
                        </div>
                        <div class="form-group">
                            <label class="addlabel" for="title">Street Name</label>
                            <input type="text" name="street_name" id="address_2" class="fname form-control">
                        </div>
                        <div class="form-group">
                            <label class="addlabel" for="title">Suburb</label>
                            <input type="text" name="suburb" id="address_3" class="fname form-control">
                            <input type="hidden" id="locality" />
                            <input type="hidden" id="sublocality_level_1" />
                        </div>
                        <div class="form-group">
                            <label class="addlabel" for="title">State</label>
                            <!--<input type="text" name="state" id="state" class="fname form-control">-->
                            <select id="state" name="state" class="form-control">  
                                <option value="">----</option>							                  
                                <?php
                                //states dropdown filter
                                $state_query = $this->db->select('state')
                                ->from('states_def')
                                ->where('country_id', $this->config->item('country'))
                                ->order_by('state','ASC')
                                ->get();
                                foreach( $state_query->result() as $state_row ){ ?>
                                    <option value="<?php echo $state_row->state; ?>"><?php echo $state_row->state; ?></option>		
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="addlabel" for="title">Postcode</label>
                            <input type="text" name="postcode" id="postcode" class="fname form-control">
                        </div>
                        <div class="form-group">
                            <label class="addlabel" for="title">Phone</label>
                            <input type="text" name="phone" id="phone" class="fname form-control">
                        </div>
                        <div class="form-group">
                            <label class="addlabel" for="title">Email</label>
                            <input type="text" name="email" id="email" class="fname form-control">
                        </div>
                        <div class="form-group">
                            <label class="addlabel" for="title">Rate</label>
                            <!--<input type="text" name="rate" id="rate" class="fname form-control">-->
                            <div class="form-control-wrapper form-control-icon-left">
                                <input type="text" name="rate" id="rate" class="rate form-control" />
                                <i class="fa fa-dollar"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="addlabel" for="title">Comment</label>
                            <input type="text" name="comment" id="comment" class="fname form-control">
                        </div>
                        <div class="form-group">
                            <input type="hidden" name="accomodation_id" id="accomodation_id" />
                            <input type="submit" name="btn_submit" id="btn_submit" class="btn" style="width: auto;" value="Save" />
                        </div>
                    </form>
                </div>

            <!-- ADD NEW ACCOMODATION FANCYBOX END -->

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page displays all accommodation.
	</p>
    <pre>
<code>SELECT *
FROM `accomodation`
WHERE `country_id` = <?php echo COUNTRY ?> 
ORDER BY `area` ASC</code>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

  jQuery(document).ready(function(){

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


        // // initAutocomplete();

        /*
       $(".fancybox_btn").fancybox({
            hideOnContentClick: false,
            hideOnOverlayClick: false
        });
        */


        // job notes
        jQuery(".edit_acco").click(function(){

            // update header and form redirect 
            jQuery("#add_acco_lb_title").text("Edit Accommodation");
            jQuery("#form_accomodation").attr("action","/admin/ajax_update_accomodation");

            var dom = jQuery(this);
            var parent_row = dom.parents("tr:first");
            var parent_lb_div_dom = jQuery("#add_new_acco_fancybox");

            var accomodation_id = parent_row.find(".accomodation_id").val();
            var name = parent_row.find(".name").text();
            var area = parent_row.find(".area").text();
            var street_number = parent_row.find(".street_number").text();
            var street_name = parent_row.find(".street_name").text();
            var suburb = parent_row.find(".suburb").text();
            var state = parent_row.find(".state").text();
            var postcode = parent_row.find(".postcode").text();
            var full_address = street_number+" "+street_name+" "+suburb+" "+state+" "+postcode;
            var phone = parent_row.find(".phone").text();
            var email = parent_row.find(".email").text();
            var rate = parent_row.find(".rate").text();
            var comment = parent_row.find(".comment").text();

            // clear
            parent_lb_div_dom.find("input[type='text']").val('');

            // fill
            parent_lb_div_dom.find("#accomodation_id").val(accomodation_id);
            parent_lb_div_dom.find("#name").val(name);
            parent_lb_div_dom.find("#area").val(area);
            parent_lb_div_dom.find("#fullAdd").val(full_address);
            parent_lb_div_dom.find("#address_1").val(street_number);
            parent_lb_div_dom.find("#address_2").val(street_name);
            parent_lb_div_dom.find("#address_3").val(suburb);
            parent_lb_div_dom.find("#state").val(state);
            parent_lb_div_dom.find("#postcode").val(postcode);
            parent_lb_div_dom.find("#phone").val(phone);
            parent_lb_div_dom.find("#email").val(email);
            parent_lb_div_dom.find("#rate").val(rate);
            parent_lb_div_dom.find("#comment").val(comment);

            jQuery.fancybox.open({
                src  : '#add_new_acco_fancybox'
            });

        });


        //UPDATE
        /*
        jQuery(".btn_update").click(function(){

            var accomodation_id = jQuery(this).parents(".update_btn_div").find(".accomodation_id").val();
            var name = jQuery(this).parents(".update_btn_div").find(".name").val();
            var area = jQuery(this).parents(".update_btn_div").find(".area").val();
            var address = jQuery(this).parents(".update_btn_div").find(".address").val();
            var phone = jQuery(this).parents(".update_btn_div").find(".phone").val();
            var email = jQuery(this).parents(".update_btn_div").find(".email").val();
            var rate = jQuery(this).parents(".update_btn_div").find(".rate").val();
            var comment = jQuery(this).parents(".update_btn_div").find(".comment").val();
            var error = "";


            if(name==""){
                error += "Accomodation name field is required\n";
            }

            if(email!="" && validate_email(email)==false){
                error += "Email field Invalid\n";
            }

            if(rate!="" && is_numeric(rate)==false){
                error += "Rate field must be numeric\n";
            }


            if(error!=""){
                swal('',error,'error');
            }else{

                jQuery.ajax({
                    type: "POST",
                    url: "/admin/ajax_update_accomodation",
                    data: {
                        accomodation_id: accomodation_id,
                        name: name,
                        area: area,
                        address: address,
                        phone: phone,
                        email: email,
                        rate: rate,
                        comment: comment
                    }
                }).done(function( ret ) {
                    swal({
                        title:"Success!",
                        text: "Update Success",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });

                    var full_url = window.location.href;
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                });

            }

        });
        */

        //DELETE
        jQuery(".btn_delete").on('click',function(){

            var accomodation_id = $(this).attr('data-id');

            swal({
                title: "Warning!",
                text: "Are you sure you want to delete?",
                type: "warning",
                showCancelButton: true,
                cancelButtonText: "Cancel!",
                cancelButtonClass: "btn-danger",
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes",
                closeOnConfirm: false,
            },
            function(isConfirm) {

                if (isConfirm) { // yes

                        $('#load-screen').show(); //show loader
                        jQuery.ajax({
                            type: "POST",
                            url: "/admin/ajax_delete_accomodation",
                            dataType: 'json',
                            data: {
                                accomodation_id: accomodation_id
                            }

                        }).done(function( retval ) {
                            if(retval.status){

                                $('#load-screen').hide(); //hide loader
                                swal({
                                    title:"Success!",
                                    text: "Accomodation Successfully Deleted",
                                    type: "success",
                                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                    timer: <?php echo $this->config->item('timer') ?>
                                });

                                var full_url = window.location.href;
                                setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);


                            }

                        });
                }

            });

        })

        jQuery("#form_accomodation").submit(function(event){

            var name = jQuery("#name").val();
            var email = jQuery("#email").val();
            var rate = jQuery("#rate").val();
            var error = "";

            if(name==""){
                error += "Accomodation name  is required\n";
            }

            /*
            if(email==""){
                error += "Email is required\n";
            }

            if(email!="" && validate_email(email)==false){
                error += "Invalid email\n";
            }
            */

            if(rate!="" && is_numeric(rate)==false){
                error += "Rate must be numeric\n";
            }


            if(error!=""){
                swal('',error,'error');
                return false;
            }else{
                $(this).submit();
            }

        });



  })


    // google map autocomplete
    var placeSearch, autocomplete;

        // google address prefill
        var componentForm2 = {
        route: {
        'type': 'long_name',
        'field': 'address_2'
        },
        locality: {
        'type': 'long_name',
        'field': 'locality'
        },
        sublocality_level_1: {
        'type': 'long_name',
        'field': 'sublocality_level_1'
        },
        administrative_area_level_1: {
        'type': 'short_name',
        'field': 'state'
        },
        postal_code: {
        'type': 'short_name',
        'field': 'postcode'
        }

    };



    function initAutocomplete() {
        // Create the autocomplete object, restricting the search to geographical
        // location types.

        var options = {
            types: ['geocode'],
            componentRestrictions: {country: "<?php echo ($this->config->item('country')==1) ? 'au' : 'nz'  ?>"}
        };

        // singe - add new form
        autocomplete = new google.maps.places.Autocomplete(
            (document.getElementById('fullAdd')),
            options
        );


        /*
        // multi - listings
        var i = 1;
        jQuery(".address").each(function(){

            autocomplete = new google.maps.places.Autocomplete(
            (document.getElementById('address'+i)),
            options
            );

            i++;

        });
        */

        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
        autocomplete.addListener('place_changed', fillInAddress);

    }

    function fillInAddress() {        

        // Get the place details from the autocomplete object.
        var place = autocomplete.getPlace();

        // test
        for (var i = 0; i < place.address_components.length; i++) {

            var addressType = place.address_components[i].types[0];
            if (componentForm2[addressType]) {

                var val = place.address_components[i][componentForm2[addressType].type];
                document.getElementById(componentForm2[addressType].field).value = val;

            }

        }
        
        // street name
        var ac = jQuery("#fullAdd").val();
        var ac2 = ac.split(" ");
        var street_number = ac2[0];
        jQuery("#address_1").val(street_number);

        // get suburb from locality or sublocality
        var sublocality_level_1 = jQuery("#sublocality_level_1").val();
        var locality = jQuery("#locality").val();

        var suburb = ( sublocality_level_1 != '' )?sublocality_level_1:locality;
        jQuery("#address_3").val(suburb);

        // get suburb from google object 'vicinity'
        if( jQuery("#address_3").val() == '' ){
        jQuery("#address_3").val(place.vicinity);
        }        

    }


    // add new accomodation
    jQuery("#btn_add_new").click(function(){

        // update header and form redirect
        jQuery("#add_acco_lb_title").text("Add New Accomodation");
        jQuery("#form_accomodation").attr("action","/admin/accomodation_process");

        var parent_lb_div_dom = jQuery("#add_new_acco_fancybox");

        // clear
        parent_lb_div_dom.find("input[type='text']").val('');
        parent_lb_div_dom.find("input[type='hidden']").val('');

        jQuery.fancybox.open({
            src  : '#add_new_acco_fancybox'
        });

    });


    jQuery("#btn_submit").click(function(){

        var parent_lb_div_dom = jQuery("#add_new_acco_fancybox");

        var accomodation_id = jQuery(this).parents(".update_btn_div").find(".accomodation_id").val();
        var name = jQuery(this).parents(".update_btn_div").find(".name").val();
        var area = jQuery(this).parents(".update_btn_div").find(".area").val();
        var address = jQuery(this).parents(".update_btn_div").find(".address").val();
        var phone = jQuery(this).parents(".update_btn_div").find(".phone").val();
        var email = jQuery(this).parents(".update_btn_div").find(".email").val();
        var rate = jQuery(this).parents(".update_btn_div").find(".rate").val();
        var comment = jQuery(this).parents(".update_btn_div").find(".comment").val();
        var error = "";


        if(name==""){
            error += "Accomodation name field is required\n";
        }

        if(email!="" && validate_email(email)==false){
            error += "Email field Invalid\n";
        }

        if(rate!="" && is_numeric(rate)==false){
            error += "Rate field must be numeric\n";
        }


        if(error!=""){
            swal('',error,'error');
        }else{

            /*
            jQuery.ajax({
                type: "POST",
                url: "/admin/ajax_update_accomodation",
                data: {
                    accomodation_id: accomodation_id,
                    name: name,
                    area: area,
                    address: address,
                    phone: phone,
                    email: email,
                    rate: rate,
                    comment: comment
                }
            }).done(function( ret ) {
                swal({
                    title:"Success!",
                    text: "Update Success",
                    type: "success",
                    showCancelButton: false,
                    confirmButtonText: "OK",
                    closeOnConfirm: false,
                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                    timer: <?php echo $this->config->item('timer') ?>
                });

                var full_url = window.location.href;
                setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
            });
            */

            jQuery("#form_accomodation").submit();

        }

    });


</script>
