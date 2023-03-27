<style>
    .message_box {
        border-radius: 5px;
        margin-bottom: 1px;
    }
    .chatbox_div{
        margin-top: 45px;
    }
    .btn_send_div{
        margin-top: 15px;
    }
    .my-box{
        background-color: #00aff0;
        color: white;
    }
    .someones-box{
        background-color: #e4eef2;
        color: black;
    }
    .read_heads{
        margin: 10px 0;
    }
    #message_pic{
        position: absolute;
        bottom: 4px;
        left: 6px;
    }
    #message{
        padding-left: 40px;
    }
</style>
<div class="box-typical box-typical-padding">

	<?php
	// breadcrumbs template
	$bc_items = array(
        array(
			'title' => 'Messages',
			'status' => 'active',
			'link' => '/messages'
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


	<section>
		<div class="body-typical-body">

            <?php
            if( validation_errors() ){ ?>
                <div class="alert alert-danger">
                <?php echo validation_errors(); ?>
                </div>
            <?php
            }
            ?>

            <?php
            $current_talking = null;
            foreach( $msg_sql->result() as $msg ){

                $is_me_talkin = ( $msg->author == $this->session->staff_id )?true:false;

                if( $current_talking != $msg->author ){

                    // remember who is currently talking
                    $current_talking = $msg->author;

                ?>
                    <!-- TIMESTAMP -->
                    <div class="d-flex flex-row<?php echo ( $is_me_talkin == true )?'-reverse':''; ?>">

                        <div class="p-2" data-toggle="tooltip" title="<?php echo date('d/m/Y',strtotime($msg->date)); ?>">


                            <img
                                class='profile_pic_small border border-info'
                                src="/images/<?php echo $this->system_model->getAvatar($msg->profile_pic); ?>"
                                data-toggle="tooltip" title="<?php echo $read_by->FirstName; ?>"
                            />
                            <?php echo date('H:i',strtotime($msg->date)); ?>
                        </div>

                    </div>

                <?php
                }
                ?>

                <!-- MESSAGE -->
                <div class="d-flex flex-row<?php echo ( $is_me_talkin == true )?'-reverse':''; ?>">

                    <div class="p-2 <?php echo ( $is_me_talkin == true )?'my-box':'someones-box'; ?> message_box" data-msg_id="<?php echo $msg->message_id; ?>">
                        <?php echo $msg->message; ?>
                    </div>

                </div>

                <!-- READ HEADS -->
                <?php
                $read_by_str = "
                SELECT
                    mrb.`staff_id`,
                    sa.`profile_pic`,
                    sa.`FirstName`,
                    sa.`LastName`
                FROM `message_read_by` AS mrb
                LEFT JOIN `staff_accounts` AS sa ON mrb.`staff_id` = sa.`StaffID`
                WHERE `message_id` = {$msg->message_id}
                ";
                $read_by_sql = $this->db->query($read_by_str);
                if( $read_by_sql->num_rows() > 0 ){ ?>
                     <div class='d-flex flex-row-reverse read_heads'>
                      <?php
                      foreach( $read_by_sql->result() as $read_by ){ ?>
                            <img
                                class='profile_pic_small border border-info'
                                data-staff_id="<?php echo $read_by->staff_id; ?>"
                                src="/images/<?php echo $this->system_model->getAvatar($read_by->profile_pic); ?>"
                                data-toggle="tooltip" title="<?php echo $read_by->FirstName; ?>"
                            />
                        <?php
                        }
                      ?>
                    </div>
                <?php
                }

            }
            ?>

            <?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open("/messages/convo/?id={$msg->message_header_id}",$form_attr);
		    ?>
            <div class="row chatbox_div">
                <div class="col-sm-11">
                    <!--<input type="text" class="form-control" id="message" name="message" placeholder="Type your message here" />-->

                    <div class="form-control-wrapper form-control-icon-left">
                        <input type="text" class="form-control" id="message" name="message" />
                        <img id="message_pic" src="/images/<?php echo $this->system_model->getAvatar($logged_user_profile_pic); ?>"  class='profile_pic_small border border-info'  />
                    </div>
                </div>
                <div class="col-sm-1">
                    <input type="hidden" class='message_header_id' value="<?php echo $msg->message_header_id; ?>" />
                    <button type="submit" class="btn btn_send">Send</button>
                </div>
            </div>

            <?php
            echo form_close();
            ?>

		</div>
	</section>

</div>

<!-- Fancybox START -->

<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page shows the history of a conversation
	</p>

</div>

<!-- Fancybox END -->
<script>
jQuery(document).ready(function(){

    jQuery("#jform").submit(function(){

        var message = jQuery("#message").val();

        if( message == '' ){

            swal({
                title: "Required!",
                text: "Message is required",
                type: "warning",
                confirmButtonClass: "btn-warning"
            });

            return false;

        }else{

            return true;

        }


    });

});
</script>