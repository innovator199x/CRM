<form method="POST" action="" id="edit-goal-form" class="text-center"> 
    <h3>Update Goals</h3>

	<table class="table table-hover main-table">
        <?php 
        $str_search = array('-','dha','Dha','Nsw','nsw','june');
        $str_replace = array(' ','DHA','DHA','NSW','NSW','June');
        foreach( $result->result_array() as $row ){
            $name = $row['name'];
            if($name == 'upgrade-booked'){
                $label = 'Upgrades (Booked)';
            } else if($name == 'upgrade-completed'){
                $label = 'Upgrades (Completed) '.date('F');
            } else if($name == 'upgrade-to-be-booked'){
                $label = 'Upgrades (To be booked)';
            } else{
                $label = $name;
            }

            $total_goal = $row['total_goal'];
            $label = str_replace($str_search, $str_replace, ucwords($label));
        ?>

            <tr>
                <th><?php echo $label; ?></th>
                <td>
                    <input class="form-control" type="number" name="<?php echo $name; ?>" value="<?php echo $total_goal; ?>" autocomplete="off" required="">
                </td>
            </tr>

        <?php
        } ?>
	</table>

    <button type="submit" class="submitbtnImg btn">Update</button>
  </form>



<script type="text/javascript">


jQuery(document).ready(function(){

    jQuery("#edit-goal-form").submit(function(e){
                e.preventDefault();
                $("#popup-box").show();
                jQuery("#load-screen").show();
                jQuery.ajax({
                    type: "POST",
                    processData: false,
                    contentType: false,
                    cache: false,
                    url: "/home/ajax_save_goal_count_data",
                    data: new FormData(this)
                }).done(function(ret){
                    
                    swal({
                        title: "Success!",
                        text: "Update Success",
                        type: "success",
                        confirmButtonClass: "btn-success",
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    setTimeout(function(){ window.location='/home'; }, <?php echo $this->config->item('timer') ?>);	  

                });
            });

    
});


</script>