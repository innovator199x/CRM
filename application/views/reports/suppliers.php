
<style>
.action_tools, .action_tools a{

    color:#adb7be;
    font-size:16px;
}
.pac-container{
    z-index:99999;
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
			'link' => "/stock/suppliers"
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
                       <th style="width:13%;">Company Name</th>
                       <th style="width:12%;">Service Provided</th>
                       <th style="width:30%;">Address</th>
                       <th>Contact Name</th>
                       <th style="width:10%;">Phone</th>
                       <th>Email</th>
                       <th>Website</th>
                       <th>Notes</th>
                       <th>On Map</th>
                       <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                        $counter = 1;
                        foreach($lists->result_array() as $row){
                    ?>
                        <tr>
                            <td><?php echo $row['company_name'] ?></td>
                            <td><?php echo $row['service_provided'] ?></td>
                            <td><?php echo $row['address'] ?></td>
                            <td><?php echo $row['contact_name'] ?></td>
                            <td><?php echo $row['phone'] ?></td>
                            <td><?php echo $row['email'] ?></td>
                            <td><?php echo $row['website'] ?></td>
                            <td><?php echo $row['notes'] ?></td>
                            <td><?php echo ($row['on_map']==1)?'Yes':'No' ?></td>
                            <td>
                                <div class="action_tools" style="width:60px;">
                                        <a data-toggle="tooltip" data-suppid="<?php echo $row['suppliers_id'] ?>" title="Delete" class="btn_delete_supplier action_a" href="#"><span class="glyphicon glyphicon-trash"></span></a>
                                        &nbsp;|&nbsp;
                                        <a data-toggle="tooltip" title="Edit" class="inline_fancybox action_a"  href="#data<?php echo $row['suppliers_id'] ?>"><i class="font-icon font-icon-pencil"></i></a>
                                        


                                         <!--- UPDATE FANCYBOX START -->
                                    <div style="display:none;" class="snapshot_edit_box" id="data<?php echo $row['suppliers_id']; ?>">
                                        <h4>Edit</h4>
                                        

                                        <div style="width:350px;">

                                                    <?php
                                                        $form_attr = array(
                                                            'class' => 'supplier_edit_form',
                                                            'id'=> 'suppliers_form_edit_'.$row['suppliers_id']
                                                        );
                                                        echo form_open('/stock/update_suppliers',$form_attr);
                                                    ?>
                                                        <div class="form-group">
                                                            <label>Company Name <span class="text-red">*</span></label>
                                                            <input type="text" name="company_name_edit" id="company_name_edit" class="company_name_edit form-control" value="<?php echo $row['company_name'] ?>">
                                                            <input type="hidden" name="supp_id" class="supp_id" value="<?php echo $row['suppliers_id']; ?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Service Provided <span class="text-red">*</span></label>
                                                            <input type="text" name="service_provided_edit" id="service_provided_edit" class="service_provided_edit form-control" value="<?php echo $row['service_provided'] ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Address</label>
                                                            <input type="text" name="address_edit" id="address_edit_<?php echo $counter; ?>" class="address_edit form-control" value="<?php echo $row['address'] ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Contact Name</label>
                                                            <input type="text" name="contact_name_edit" id="contact_name_edit" class="contact_name_edit form-control" value="<?php echo $row['contact_name'] ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Phone</label>
                                                            <input type="text" name="phone_edit" id="phone_edit" class="phone_edit form-control" value="<?php echo $row['phone'] ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Email</label>
                                                            <input type="text" name="email_edit" id="email_edit" class="email_edit form-control" value="<?php echo $row['email'] ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Website</label>
                                                            <input type="text" name="website_edit" id="website_edit" class="website_edit form-control" value="<?php echo $row['website'] ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Sales Agreement number</label>
                                                            <input type="text" name="sales_agreement_number" id="sales_agreement_number" class="sales_agreement_number form-control" value="<?php echo $row['sales_agreement_number'] ?>" >
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Notes</label>
                                                            <textarea name="notes_edit" class="form-control"><?php echo $row['notes'] ?></textarea>
                                                        </div>
                                                  
                                                         <div class="form-group">
                                                            <label>On Map</label>
                                                            <select name="on_map_edit" class="form-control">
                                                                <option <?php echo ($row['on_map']==1)?"selected='true'":NULL  ?> value="1">Yes</option>
                                                                <option <?php echo ($row['on_map']==0)?"selected='true'":NULL  ?> value="0">No</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                                <input type="submit" value="Update" class="btn btn_update">
                                                        </div>


                                                    </form>

                                                </div>

                                    </div>
                                    <!--- UPDATE FANCYBOX END -->


                                </div>
                            </td>
                        </tr>
                    <?php
                        $counter++;
                        }
                    ?>
                  
                    
                </tbody>

            </table>
        </div>

    <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
    <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
        
            <div>
                <button type="button" href="#add_new" class="btn inline_fancybox">Add New</button>
            
                                <div style="display:none;" id="add_new" style="width:300px!important;">
                                <h4>Add New</h4>

                                <div style="width:350px;">

                                    <?php
                                            $form_attr = array(
                                                'id' => 'add_stock_form'
                                            );
                                            echo form_open('/stock/add_suppliers',$form_attr);
                                        ?>
                                        
                                        
                                        <div class="form-group">
                                            <label>Company Name <span class="text-red">*</span></label>
                                            <input type="text" name="company_name" id="company_name" class="company_name form-control" >
                                        </div>
                                        <div class="form-group">
                                            <label>Service Provided <span class="text-red">*</span></label>
                                            <input type="text" name="service_provided" id="service_provided" class="service_provided form-control" >
                                        </div>
                                        <div class="form-group">
                                            <label>Address</label>
                                            <input type="text" name="address" id="address" class="address form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Contact Name</label>
                                            <input type="text" name="contact_name" id="contact_name" class="contact_name form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Phone</label>
                                            <input type="text" name="phone" id="phone" class="phone form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="text" name="email" id="email" class="email form-control" >
                                        </div>
                                        <div class="form-group">
                                            <label>Website</label>
                                            <input type="text" name="website" id="website" class="website form-control" >
                                        </div>
                                        <div class="form-group">
                                            <label>Sales Agreement number</label>
                                            <input type="text" name="sales_agreement_number" id="sales_agreement_number" class="sales_agreement_number form-control" >
                                        </div>
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <textarea name="notes" class="form-control"></textarea>
                                        </div>
                                
                                        <div class="form-group">
                                            <input  type="submit" value="Submit" class="btn">
                                        </div>


                                    </form>

                                </div>

                                </div>
            
            </div>
    </div>
    </section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

<h4><?php echo $title; ?></h4>
<p>
This page is a directory of all Suppliers
</p>
<pre>
<code>SELECT `suppliers_id`, `company_name`, `service_provided`, `address`, `contact_name`, `phone`, `email`, `website`, `notes`, `on_map`, `sales_agreement_number`
FROM `suppliers`
WHERE `status` = 1
AND `country_id` = 1
ORDER BY `company_name` ASC</code>
</pre>

</div>
<!-- Fancybox END -->



<script type="text/javascript">
    jQuery(document).ready(function(){

            //success/error message sweel alert pop  start
            <?php if( $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'success' ){?>
                swal({
                    title: "Success!",
                    text: "<?php echo $this->session->flashdata('success_msg') ?>",
                    type: "success",
                    confirmButtonClass: "btn-success",
                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                    timer: <?php echo $this->config->item('timer') ?>
                });
            <?php }else if(  $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'error'  ){ ?>
                swal({
                    title: "Error!",
                    text: "<?php echo $this->session->flashdata('error_msg') ?>",
                    type: "error",
                    confirmButtonClass: "btn-danger"
                });
            <?php } ?>
            //success/error message sweel alert pop  end


            $(".inline_fancybox").fancybox({
                'hideOnContentClick': true,
                'width': 500,
                'height': 'auto',
                'autoSize': false,
                'autoDimensions':false
            });



            //DELETE SUPPLIER
            $('.btn_delete_supplier').click(function(e){
                e.preventDefault();
                
                var supp_id = $(this).attr('data-suppid');
                
                swal(
                    {
                        title: "",
                        text: "Are you sure you want to delete?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){
                            $('#load-screen').show(); //show loader
                            
                            
                            jQuery.ajax({
                                type: "POST",
                                url: "/stock/ajax_delete_supplier",
                                dataType: 'json',
                                data: { 
                                    supp_id: supp_id
                                }
                            }).done(function( ret ) {

                                    //success
                                    if(ret.status){
                                        $('#load-screen').hide(); //show loader
                                        
                                        swal({
                                            title:"Success!",
                                            text: ret.msg,
                                            type: "success",
                                            showCancelButton: false,
                                            confirmButtonText: "OK",
                                            closeOnConfirm: false,

                                        },function(isConfirm2){
                                        if(isConfirm2){ 
                                                location.reload();
                                            }
                                        });
                                    }
                                
                            });		


                        }else{
                            return false;
                        }
                        
                    }
                    
                );


            })


            //ADD NEW SUPPLIER
            $('#add_stock_form').submit(function(){

                var obj = $(this);
                var company_name = obj.find('#company_name').val();
                var service_provided = obj.find('#service_provided').val();
                var email = obj.find('#email').val();
                var error = "";
                var counter = 0;


                if($.trim(company_name).length == 0){
                    error += "Company Name must not be empty\n";
                }
                
                if($.trim(service_provided).length==0){
                    error += "Service Provided name must not be empty\n";
                }

               
                if(email!="" && validate_email(email)==false){
                    error += "Invalid email address\n";
                }
                

                if(error!=""){
                    swal('',error,'error');
                    return false;
                }

                if(counter==0){
                    counter++;
                    job.submit();
                    return false;
                }


            })

            $('.btn_update').click(function(){

                var obj = $(this);
                var form = obj.parents('.supplier_edit_form');
                var company_name = form.find('.company_name_edit').val();
                var service_provided = form.find('.service_provided_edit').val();
                var email = form.find('.email_edit').val();
                var error = "";
                var counter = 0;


                if($.trim(company_name).length==0){
                    error += "Company Name must not be empty\n";
                }
                if($.trim(service_provided).length==0){
                    error += "Service Provided must not be empty\n";
                }

                if(email!="" && validate_email(email)==false){
                        error += "Invalid email address\n";
                }


                if(error!=""){
                    swal('',error,'error');
                    return false;
                }

                if(counter==0){
                    counter++;
                    form.submit();
                    return false;
                }


            })



    })
    //document ready end




        // google map autocomplete


        function initAutocomplete() {
       

            var input = document.getElementById('address');

            <?php if( $this->config->item('country') ==1 ){ ?>
            var cntry = 'au';
            <?php }else{ ?>
                var cntry = 'nz';
            <?php } ?>

            var options = {
                types: ['geocode'],
                componentRestrictions: {
                    country: cntry
                }
            };

            var autocomplete = new google.maps.places.Autocomplete(input, options);


            //for multiple edit list
            var i = 1;
            jQuery(".address_edit").each(function(){

                autocomplete = new google.maps.places.Autocomplete(
                (document.getElementById('address_edit_'+i)),
                options
                );
                
                i++;
                
            });
                

        }


       


</script>



