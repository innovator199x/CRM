<section class="card card-blue-fill">
    <header class="card-header">Portal Users</header>
    <div class="card-block">

        <table class="table table-hover main-table portal_user_table table-no-border" id="pm_table">
            <thead>
                <tr>
                    <th>User Type</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Position</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Login</th>
                    <th>Invite</th>
                    <th>2FA</th>
                    <th>Reset Password</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($agency_pm_sql as $pm){ ?>
                    <tr>
                        <td>
                            <?php 
                                $paramsa = array('sel_query'=>"auat.user_type_name",'aua_id'=>$pm['agency_user_account_id']);
                                $user_type_q = $this->system_model->get_user_accounts($paramsa)->row_array();
                                echo $user_type_q['user_type_name'];
                            ?>
                        </td>
                        <td>														
                        <?php echo $pm['fname']; ?>
                           
                        </td>
                        <td>
                        <?php echo $pm['lname']; ?>
                           
                        </td>											
                        <td>
                        <?php echo $pm['job_title']; ?>
                           
                        </td>
                        <td>
                        <?php echo $pm['phone']; ?>
                           
                        </td>
                        <td>
                        <?php echo $pm['email']; ?>
                            
                        </td>	
                        <td>
                            <?php
                            // login
                            if( $pm['active'] == 1 ){
                                if( $pm['password'] != '' ){ ?>
                                    <a href="<?php echo $this->config->item('agencyci_link'); ?>?user=<?php echo $pm['email']; ?>&agency_id=<?php echo $agency_id; ?>&pass=<?php echo $pm['password'] ?>&crm_login=1" target="__blank">
                                        <span class="fa fa-paper-plane"></span>
                                    </a>
                                <?php	
                                }else{
                                    echo "Password not set";
                                }
                            }
                            ?>												
                        </td>
                        <td>
                            <?php
                            // invite
                            if( $pm['active'] == 1 ){
                                if(  $pm['password'] == '' ){ ?>
                                    <a class="invite_email_link" href="<?php echo $this->config->item('agencyci_link'); ?>/sys/send_invite_email?aua_id=<?php echo $pm['agency_user_account_id']; ?>" target="__blank">
                                    <span class="fa fa-envelope-o"></span>
                                    </a>
                                <?php	
                                }else{
                                    echo "Password already set";
                                }
                            }
                            ?>												
                        </td>
                        <td><?php echo ( $pm['au_2fa_id'] > 0 && $pm['au_2fa_active'] == 1 )?'<span class="text-green fa fa-check"></span>':null; ?></td>
                        <td>
                            <?php
                            // reset password
                            if( $pm['active'] == 1 ){ ?>
                                <a class="reset_pass_email_link" href="<?php echo $this->config->item('agencyci_link'); ?>/sys/send_reset_password_email?aua_id=<?php echo $pm['agency_user_account_id']; ?>" target="__blank">
                                    <span class="fa fa-envelope-o"></span>
                                </a>	
                            <?php
                            }
                            ?>																					
                        </td>
                        <td class="cta action_div">		

                            <?php
                            if( $pm['active'] == 1 ){ ?>	

                                <!--<button class="btn btn-danger submitbtnImg eagdtbt status_toggle_btn" type="button" data-status="0">
                                    <span class="fa fa-close"></span>
                                    Deactivate
                                </button> -->

                                <a data-fancybox data-original-title="Edit User" data-src="#edit_user_fancybox_<?php echo $pm['agency_user_account_id'] ?>" data-toggle="tooltip" href="#"><span class="font-icon font-icon-pencil"></span></a> |

                                <a class="status_toggle_btn" data-status="0" data-original-title="Deactivate User" data-toggle="tooltip" href="#"><span class="glyphicon glyphicon-trash"></span></a>
                            <?php	
                            }else{ ?>
                                <a class="status_toggle_btn" data-status="1" data-original-title="Restore User" data-toggle="tooltip" href="#"><span class="font-icon font-icon-refresh"></span></a>
                            <?php	
                            }
                            ?>
                          
                                <!-- EDIT PORTAL USER FANCYBOX-->
                                <div style="display:none;" class="fancybox_div" id="edit_user_fancybox_<?php echo $pm['agency_user_account_id'] ?>">
                                    <section class="card card-blue-fill">
                                        <header class="card-header">Edit <?php echo $pm['fname']; ?></header>
                                        <div class="card-block">
                                            <?php 
                                                echo form_open("/agency/update_agency/{$agency_id}/{$tab}","class=vad_form"); 
                                                
                                                $hidden_input_data_agency_id = array(
                                                    'type'  => 'hidden',
                                                    'name'  => 'agency_id',
                                                    'id'    => 'agency_id',
                                                    'value' => $agency_id,
                                                    'class' => 'agency_id'
                                                );
                                                echo form_input($hidden_input_data_agency_id);
                                            ?>
                                        
                                                <div class="form-group">
                                                    <label class="form-label">User Type</label>
                                                    <select name="pm_user_type"  class="form-control pm_user_type">											
                                                        <option value="">---</option> 	
                                                        <?php	
                                                        foreach( $agency_user_account_types_sql->result_array() as $aua_t ){ ?>
                                                            <option value="<?php echo $aua_t['agency_user_account_type_id']; ?>" <?php echo ($aua_t['agency_user_account_type_id'] == $pm['user_type'])?'selected="selected"':'' ?>><?php echo $aua_t['user_type_name']; ?></option> 	
                                                        <?php	
                                                        }
                                                        ?>
                                                    </select>
                                                    <input type="hidden" name="og_pm_user_type" value="<?php echo $pm['user_type']; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label">First Name</label>
                                                    <input type="text" name="pm_fname" class="form-control pm_fname" value="<?php echo $pm['fname']; ?>" />
                                                    <input type="hidden" name="og_pm_fname" class="form-control og_pm_fname" value="<?php echo $pm['fname']; ?>" />
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label">Last Name</label>
                                                    <input type="text" name="pm_lname" class="form-control pm_lname" value="<?php echo $pm['lname']; ?>" />
                                                    <input type="hidden" name="og_pm_lname" class="form-control og_pm_lname" value="<?php echo $pm['lname']; ?>" />
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label">Position</label>
                                                    <input type="text" name="pm_job_title" class="form-control pm_job_title" value="<?php echo $pm['job_title']; ?>" />
                                                    <input type="hidden" name="og_pm_job_title" class="form-control og_pm_job_title" value="<?php echo $pm['job_title']; ?>" />
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label">Phone</label>
                                                    <input type="text" name="pm_phone" class="form-control pm_phone" value="<?php echo $pm['phone']; ?>"  />
                                                    <input type="hidden" name="og_pm_phone" class="form-control og_pm_phone" value="<?php echo $pm['phone']; ?>"  />
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label">Email</label>
                                                    <input type="text" name="pm_email" class="form-control pm_email" value="<?php echo $pm['email']; ?>" />
                                                    <input type="hidden" name="og_pm_email" class="form-control og_pm_email" value="<?php echo $pm['email']; ?>" />
                                                </div>

                                                <div class="form-group text-right"><button class="btn btn-update-user">Update Details</button></div>
                                                <input type="hidden" name="pm_id" class="pm_id" value="<?php echo $pm['agency_user_account_id']; ?>" />
                                            </form>
                                        </div>
                                    </section>
                                </div>
                               
                                <!-- EDIT PORTAL USER FANCYBOX END-->
                           
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>
</section>


<div class="vad_cta_box form-group text-left">
    <?php
        if($this->input->get_post('show_all')==1){
            $show_all_text = "Show Only Active";
            $show_all_link = "/agency/view_agency_details/{$agency_id}/{$tab}";
        }else{
            $show_all_text = "Show All Users";
            $show_all_link = $_SERVER['REQUEST_URI'].'?show_all=1';
        }
       
    ?>
    <button data-fancybox data-src="#add_new_user_fancybox" id="btn_add_pm" class="btn" type="button">Add User</button> &nbsp; 
    <a href='<?php echo $show_all_link; ?>' class="btn"><?php echo $show_all_text; ?></a> &nbsp; 
</div>

<div style="display:none;" id="add_new_user_fancybox">

<section class="card card-blue-fill">
    <header class="card-header">Add New User</header>
        <div class="card-block">

            <?php 
                echo form_open("/agency/update_agency/{$agency_id}/{$tab}","class=vad_form_add_user"); 
                
                $hidden_input_data_agency_id = array(
                    'type'  => 'hidden',
                    'name'  => 'agency_id',
                    'id'    => 'agency_id',
                    'value' => $agency_id,
                    'class' => 'agency_id'
                );
                echo form_input($hidden_input_data_agency_id);
            ?>

                <div class="form-group">
                    <label class="form-label">User Type</label>
                    <select name="pm_user_type"  class="form-control pm_user_type">											
                        <option value="">---</option> 	
                        <?php	
                        foreach( $agency_user_account_types_sql->result_array() as $aua_t ){ ?>
                            <option value="<?php echo $aua_t['agency_user_account_type_id']; ?>" ><?php echo $aua_t['user_type_name']; ?></option> 
                        <?php	
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="pm_fname" class="form-control pm_fname" />
                </div>

                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="pm_lname" class="form-control pm_lname"/>
                </div>

                <div class="form-group">
                    <label class="form-label">Position</label>
                    <input type="text" name="pm_job_title" class="form-control pm_job_title"/>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="pm_phone" class="form-control pm_phone" />
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="text" name="pm_email" class="form-control pm_email" />
                </div>

                <div class="form-group text-right"><button class="btn btn-update-user">Add</button></div>   
            </form>
        </div>
    </section>
</div>

<script type="text/javascript">

        jQuery(document).ready(function(){


            // agency portal status deactivate/restore
            jQuery(document).on("click",".status_toggle_btn",function(){

                var parent_row = jQuery(this).parents("tr:first");
                var pm_id = parseInt(parent_row.find(".pm_id").val());
                var status = jQuery(this).attr("data-status");
                var agency_id = <?php echo $agency_id; ?>;
              
                console.log("pm_id: "+pm_id);
                console.log("status: "+status);
                    
                    if(pm_id>0){
                        var confirm_txt;
                    
                        if( status == 1 ){ // activate
                            confirm_txt = 'Are you sure you want to restore this user?';
                        }else{ // deactivate
                            confirm_txt = 'Are you sure you want to deactivate this user?';
                        }
                        
                        //alert('PM id present');
                        swal({
                            title: "Warning!",
                            text: confirm_txt,
                            type: "warning",
                            showCancelButton: true,
                            cancelButtonText: "Cancel!",
                            cancelButtonClass: "btn-danger",
                            confirmButtonClass: "btn-success",
                            confirmButtonText: "Yes",
                            closeOnConfirm: true,
                        },
                        function(isConfirm) {
                            if (isConfirm) { // yes
                                
                                $('#load-screen').show();
                                jQuery.ajax({
                                    type: "POST",
                                    url: "/agency/ajax_activate_deactivate_portal_users",
                                    dataType: 'json',
                                    data: { 
                                        pm_id: pm_id,
                                        status: status,
                                        agency_id: agency_id
                                    }
                                }).done(function( ret ){
                                    
                                    $('#load-screen').hide();   
                                    if(ret.status==false){
                                        swal('','There are properties attached to this PM','error');
                                    }else{
                                        swal({
                                            title:"Success!",
                                            text: ret.msg,
                                            type: "success",
                                            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                            timer: <?php echo $this->config->item('timer') ?>
                                        });
                                        setTimeout(function(){ location.reload() }, <?php echo $this->config->item('timer') ?>);
                                        }
                                    
                                });	

                            }
                        });
                    }
                    
            });

            jQuery(document).on("click",".btn_remove_user_row",function(){
		
                jQuery(this).parents("tr:first").remove();
                
            });

            $('.btn-update-user').on('click', function(){

                var err = ""
                var submitCount = 0;
                var obj = $(this);
                var userType = obj.parents('.fancybox_div').find(".pm_user_type").val();
                var fname = obj.parents('.fancybox_div').find(".pm_fname").val();

                if(userType==""){
                    err +="User Type is required \n";
                }
                if(fname==""){
                    err +="Name is required \n";
                }

                if(err!=""){
                    swal('',err,'error');
                    return false;
                }

                if(submitCount==0){
                    submitCount++;
                    return;
                }else{
                    swal('','Form submission is in progress.');
                    return false;
                }


            })


        })

</script>