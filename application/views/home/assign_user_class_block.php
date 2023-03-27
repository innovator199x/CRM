<style>
 .class_block_table ul{}
 .class_block_table ul li{
    display: flex;
 }
</style>
<div class="box-typical box-typical-padding">

	<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/home/assign_user_class_block"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>



	<section>
		<div class="body-typical-body">


			<div class="row" style="padding-top:25px;">

                <?php  
                    $cnt_tt = 0; foreach( $staff_class_list->result_array() as $row ){ 

                        $this->db->select('content_block_id,user_class');
                            $this->db->from('home_content_block_class_access');
                            $this->db->where('user_class', $row['ClassID']);
                            $home_content_block_class_access_q = $this->db->get();
                            $ttr_tt = $home_content_block_class_access_q->result_array();
                            $ttr_arr = [];
                            foreach( $ttr_tt as $ttr_arr_row ){
                                $ttr_arr[] = $ttr_arr_row['content_block_id'];
                            }

                ?>
                    <div class="col-lg-4">
                        <table class="table table-hover main-table class_block_table">

                            <thead>
                                <tr>
                                    <th>
                                        <?php echo $row['ClassName'] ?>
                                        <input type="hidden" class="class_id" value="<?php echo $row['ClassID'] ?>">
                                    </th>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>
                                        <ul>

                                        <?php 
                                            $cnt = 0; foreach( $home_content_block_list->result_array() as $row2 ){

                                            if( in_array( $row2['content_block_id'], $ttr_arr) && !empty($ttr_arr) ){
                                                $attr_checked = 'checked="checked"';
                                            }else{
                                                $attr_checked = NULL;
                                            }
                                        ?>
                                            <li>
                                            <div style="margin:0;" class="checkbox left">
                                                <input <?php echo $attr_checked; ?> class="class_chk" name="class_chk[]" type="checkbox" id="chk_<?php echo $cnt_tt."_".$cnt; ?>" value="<?php echo $row2['content_block_id'] ?>">
                                                <label for="chk_<?php echo $cnt_tt."_".$cnt;; ?>" style="margin-right: 28px;"><?php echo $row2['content_name'] ?></label>
                                            </div>
                                            </li>
                                        <?php
                                        $cnt++;
                                        } ?>

                                        </ul>
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>
                <?php $cnt_tt++; } ?>
                

			</div>
		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    Assign user class blocks.
	</p>

</div>
<!-- Fancybox END -->

<script type="text/javascript">


    jQuery(document).ready(function(){

        $('.class_chk').change(function(){

            var a = $(this).val();
            var b = $(this).parents('.class_block_table').find('.class_id').val(); //class_id
            thisvalchekced = [];

            $( $(this).parents('.class_block_table').find("input[name='class_chk[]']:checked") ).each(function ()
            {
                thisvalchekced.push(parseInt($(this).val()));
            });

            if( a!="" && b!="" ){
                 
                jQuery("#load-screen").show();
                jQuery.ajax({
                    type: "POST",
                    url: "/home/ajax_assign_user_class_block",
                    data: { 
                        class_id: b,
                        block_id: thisvalchekced
                    },
                    dataType: "json"
                }).done(function( ret ) {		
                    jQuery("#load-screen").hide();

                    if(ret.status){
                        console.log('Update Success');
                        return false;
                    }else{
                        console.log('Error please contact admin');
                        return false;
                    }
                    
                });

            }

        })
        
    });


</script>