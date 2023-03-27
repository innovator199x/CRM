          
<style>
    .jalign_left{
        text-align:left;
    }
    .txt_hid, .btn_update{
        display:none;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Daily',
            'link' => "/daily/"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/daily/incorrectly_upgraded_properties"
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
                            <th><b>Address</b></th>
                            <!-- <th><b>IC Job</b></th> -->
                            <th><b>Service</b></th>
                            <th><b>Agency</b></th>
                        </tr>
                    </thead>

                    <tbody>    
                        <?php
                            if($lists->num_rows()>0){
                         foreach($lists->result_array() as $row) { 
                        ?>            
                            <tr>
                                <td>
                                    <?php 
                                        $property_address = "{$row['p_address_1']} {$row['p_address_2']} {$row['p_address_3']}, {$row['p_state']}";
                                        echo $this->gherxlib->crmlink('vpd', $row['p_property_id'], $property_address); 
                                    ?>
                                </td>
                                <!-- <td><?php echo $this->gherxlib->crmlink('vjd',$row['j_id'],$row['j_id'])  ?></td> -->
                                <td>
                                    <img data-toggle="tooltip" title="<?php echo $row['ajt_type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($row['j_service']); ?>" />
                                </td>
                                <td><?php echo $this->gherxlib->crmlink('vad', $row['agency_id'], $row['agency_name'],'',$row['priority']) ?></td>
                            </tr>
                        <?php }}else{
                        ?>
                            <tr><td colspan="4">There are no results</td></tr>
                        <?php
                        } ?>
                    </tbody>

                </table>
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
    This shows all incorrectly upgraded properties:<br/>
    If there is a completed IC Upgrade Job and required QLD alarm number has not been cleared<br/>
Or<br/>
Properties with completed IC Upgrade Jobs but not an active IC Service
    </p>
<pre><code><?php echo $sql_query; ?></code></pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

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
    });


</script>
