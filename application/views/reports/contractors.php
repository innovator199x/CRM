

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
        'link' => "/reports/contractors"
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
							<th>Area</th>
							<th>Address</th>
							<th>Phone</th>
							<th>Email</th>
							<th>Rate</th>
							<th>Comment</th>
							<th>Action</th>
						</tr>
					</thead>

					<tbody>
                       <?php
                        foreach($lists->result_array() as $row){
                        ?>

                            <tr>
                                <td><?php echo $row['name'] ?>
                                </td>
                                <td><?php echo $row['area'] ?></td>
                                <td><?php echo $row['address'] ?></td>
                                <td><?php echo $row['phone'] ?></td>
                                <td><?php echo $row['email'] ?></td>
                                <td><?php echo "$".$row['rate'] ?></td>
                                <td><?php echo $row['comment'] ?></td>
                                <td class="action_div">
                                <input type="hidden" class="del_contractors_id" value="<?php echo $row['contractors_id']; ?>" />
                                <a  data-toggle="tooltip" title="Edit" href="#edit_fancybox_<?php echo $row['contractors_id'] ?>" class="btn_edit fancybox_btn action_a"><i class="font-icon font-icon-pencil"></i></a> | 
                                <a  data-toggle="tooltip" title="Delete" href="javascript:void(0)" class="btn_delete action_a"><span class="glyphicon glyphicon-trash"></span></a>
                                
                                
                                <!-- EDIT FANCY BOX -->
                                <div class="update_btn_div" style="display:none;" id="edit_fancybox_<?php echo $row['contractors_id'] ?>">

                                    <h4>Update Contractor</h4>
                                   

                                     <div class="form-group">
                                        <label>Name <span class="text-red">*</span></label>
                                        <input type="text" class="form-control name" value="<?php echo $row['name']; ?>" style="min-width:400px;" />
                                    </div>

                                     <div class="form-group">
                                        <label>Area</label>
                                        <input type="text" class="form-control area" value="<?php echo $row['area']; ?>" style="min-width:400px;" />
                                    </div>

                                     <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" class="form-control address" value="<?php echo $row['address']; ?>" style="min-width:400px;" />
                                    </div>

                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" class="form-control phone" value="<?php echo $row['phone']; ?>" style="min-width:400px;" />
                                    </div>

                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="text" class="form-control email" value="<?php echo $row['email']; ?>" style="min-width:400px;" />
                                    </div>

                                    <div class="form-group">
                                        <label>Rate <span class="text-red">*</span></label>
                                        <input type="text" class="form-control rate" value="<?php echo $row['rate']; ?>" style="min-width:400px;" />
                                    </div>

                                       <div class="form-group">
                                        <label>Comment</label>
                                        <textarea style="min-width:400px;" class="form-control comment"><?php echo $row['comment']; ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <input type="hidden" class="contractors_id" value="<?php echo $row['contractors_id']; ?>" />
                                        <button class="btn btn_update">Update</button>
                                    </div>

                                </div>
                                <!-- EDIT FANCY BOX END -->

                                
                                </td>
                            </tr>

                        <?php
                        }
                       ?>
                       
					</tbody>

				</table>
			</div>
            <div>
                <a href="#add_fancybox" class="btn fancybox_btn btn_add_new_contractors">Add New</a>

                <!-- ADD CONTRACTOR FANCY BOX -->
                <div class="add_btn_div" style="display:none;" id="add_fancybox">
                        <?php echo form_open('/reports/add_contractor','id=add_contractor_form'); ?>
                            <h4>Add New Contractor</h4>

                            <div class="form-group">
                                <label>Name <span class="text-red">*</span></label>
                                <input type="text" class="form-control name" name="name"  style="min-width:400px;" />
                            </div>

                            <div class="form-group">
                                <label>Area</label>
                                <input type="text" class="form-control area" name="area"  style="min-width:400px;" />
                            </div>

                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" class="form-control address" name="address"  style="min-width:400px;" />
                            </div>

                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" class="form-control phone" name="phone"  style="min-width:400px;" />
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" class="form-control email" name="email"  style="min-width:400px;" />
                            </div>

                            <div class="form-group">
                                <label>Rate <span class="text-red">*</span></label>
                                <input type="text" class="form-control rate" name="rate"  style="min-width:400px;" />
                            </div>

                                <div class="form-group">
                                <label>Comment</label>
                                <textarea style="min-width:400px;" name="comment" class="form-control comment"></textarea>
                            </div>
                            <div class="form-group">
                                <button class="btn btn_add">Update</button>
                            </div>
                        </form>
                </div>
                <!-- ADD CONTRACTOR FANCY BOX END -->

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
    This page displays all contractors
	</p>
<pre><code>SELECT `c`.`contractors_id`, `c`.`name`, `c`.`area`, `c`.`address`, `c`.`phone`, `c`.`email`, `c`.`rate`, `c`.`comment`
FROM `contractors` as `c`
WHERE `country_id` = 1
ORDER BY `area` ASC</code></pre>

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



         $(".fancybox_btn").fancybox({
            hideOnContentClick: false,
            hideOnOverlayClick: false
        });
       
        $('.btn_update').click(function(){

            var obj = $(this);

            var contractors_id = obj.parents('.update_btn_div').find('.contractors_id').val();
            var name = jQuery(this).parents(".update_btn_div").find(".name").val();
            var area = jQuery(this).parents(".update_btn_div").find(".area").val();
            var address = jQuery(this).parents(".update_btn_div").find(".address").val();
            var phone = jQuery(this).parents(".update_btn_div").find(".phone").val();		
            var email = jQuery(this).parents(".update_btn_div").find(".email").val();
            var rate = jQuery(this).parents(".update_btn_div").find(".rate").val();
            var comment = jQuery(this).parents(".update_btn_div").find(".comment").val();
            var error = "";

            if(name==""){
                error += "Update Accomodation name must not be empty\n";
            }
            if(email!="" && validate_email(email)==false){
			error += "Update Email field Invalid\n";
            }
            
            if(rate!="" && is_numeric(rate)==false){
                error += "Update Rate field must be numeric\n";
            }

            if(error!=""){
                swal('',error,'error');
                return false;
            }

            swal(
                    {
                        title: "",
                        text: "Update Details?",
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
							//swal.close();

							jQuery.ajax({
                                type: "POST",
                                url: "<?php echo base_url('/reports/ajax_update_contractors') ?>",
                                dataType: 'json',
                                data: { 
                                    contractors_id: contractors_id,
                                    name: name,
                                    area: area,
                                    address: address,
                                    phone: phone,
                                    email: email,
                                    rate: rate,
                                    comment: comment
                                }
							}).done(function(res){
								if(res.status){
                                    
									$('#load-screen').hide(); //hide loader

									swal({
										title:"Success!",
										text: res.msg,
										type: "success",
										showCancelButton: false,
										confirmButtonText: "OK",
										closeOnConfirm: false,
                                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                        timer: <?php echo $this->config->item('timer') ?>
									});
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 3000);

								}else{
									swal.close();
									location.reload();
								}

							});

                        }else{
                            return false;
                            $('#load-screen').hide(); //hide loader
                        }
                        
                    }
            	);	

        })


        //DELETE CONTRACTOR
        $('.btn_delete').on('click',function(){

            var obj = $(this);
            var contractors_id = obj.parents('.action_div').find('.del_contractors_id').val();
            
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
							//swal.close();

							jQuery.ajax({
                                type: "POST",
                                url: "<?php echo base_url('/reports/ajax_delete_contractors') ?>",
                                dataType: 'json',
                                data: { 
                                    contractors_id: contractors_id
                                }
							}).done(function(res){
								if(res.status){
                                    
									$('#load-screen').hide(); //hide loader

									swal({
										title:"Success!",
										text: res.msg,
										type: "success",
										showCancelButton: false,
										confirmButtonText: "OK",
										closeOnConfirm: false,
                                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                        timer: <?php echo $this->config->item('timer') ?>
									});
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 3000);
								}else{
									swal.close();
									location.reload();
								}

							});

                        }else{
                          
                            $('#load-screen').hide(); //hide loader
                        }
                        
                    }
            	);	
            
        })


        $('#add_contractor_form').submit(function(){
            var submitCount = 0;
            var obj = $(this);

            var contractors_id = obj.parents('.add_btn_div').find('.contractors_id').val();
            var name = obj.parents(".add_btn_div").find(".name").val();
            var area = obj.parents(".add_btn_div").find(".area").val();
            var address = obj.parents(".add_btn_div").find(".address").val();
            var phone = obj.parents(".add_btn_div").find(".phone").val();		
            var email = obj.parents(".add_btn_div").find(".email").val();
            var rate = obj.parents(".add_btn_div").find(".rate").val();
            var comment = obj.parents(".add_btn_div").find(".comment").val();
            var error = "";

            if(name==""){
                error += "Accomodation name must not be empty\n";
            }


            if(email!="" && validate_email(email)==false){
			error += "Email field Invalid\n";
            }
            
            if(rate==""){
                error += "Rate must no be empty\n";
            }

            if(rate!="" && is_numeric(rate)==false){
                error += "Rate field must be numeric\n";
            }

            if(error!=""){
                swal('',error,'error');
                return false;
            }

            if(submitCount==0){
                submitCount++;
                obj.submit();
            }else{
                swal('','','error');
                return false;
            }

        })



    });



</script>