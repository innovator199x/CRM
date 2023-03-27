
<style>
.first_col{
    width: 50%;
}
.second_col{
    border-left: 1px solid #dee2e6;
}
</style>

<div class="box-typical box-typical-padding">

    <?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => $uri
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

?>
    
    <!--
	<header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open($uri,$form_attr);
            ?>
                <div class="for-groupss row">
                    <div class="col-lg-10 col-md-12 columns">
                        <div class="row">

                            <div class="col-md-4 columns">
                                <label>Agency <span class="color-red">*</span></label>
                                <select class="form-control" id="agency_filter" name="agency_filter">
                                    <option value="">---</option>
                                </select>
                            </div>

                            <div class="col-md-2 columns">
                                <label>Expiry Year <span class="color-red">*</span></label>
                                <select class="form-control" id="alarm_expiry" name="alarm_expiry">
                                    <option value="">---</option>                                   
                                </select>
                            </div>

                            <div class="col-md-1 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <input class="btn" type="submit" name="btn_search" value="Search">
                            </div>
                            
                        </div>
                    </div>                                                                   
                </div>
            </form>
        </div>
    </header>
    -->
    

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">

				<table class="table table-hover table-striped main-table">

					<thead>
						<tr>	
                            <th>Property</th> 
                            <th>Agency</th>
                            <th>Property Service</th>     
                            <th>Duplicate Count</th>                                    
						</tr>
                    </thead>

                    <tbody>
                    <?php                    
                    if( $list_sql->num_rows() > 0 ){                           
                        foreach( $list_sql->result() as $row ){ ?>
                            <tr>
                                <td>
                                    <a href="<?php echo "{$this->config->item('crm_link')}/view_property_details.php?id={$row->property_id}"; ?>">
                                        <?php echo "{$row->p_street_num} {$row->p_street_name}  {$row->p_suburb}  {$row->p_state}  {$row->p_postcode}"; ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo "/agency/view_agency_details/{$row->agency_id}"; ?>">
                                        <?php echo $row->agency_name; ?>
                                    </a>
                                </td> 
                                <td><?php echo $row->service_type; ?></td>
                                <td><?php echo $row->duplicate_count; ?></td>                                   
                            </tr>
                        <?php
                        // add total
                        $al_qty_total += $row->al_qty;                            
                        } ?>                        
                    <?php
                    }else{ ?>
                        <tr><td colspan='2'>Empty</td></tr>
                    <?php
                    }                   
                    ?>     
                    </tbody>

                </table>

            </div>

        <button class="btn" type="button" id="fix_dup_prop_serv">Fix Duplicate Property Service</button>
        
		<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
        

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    
	<p></p>

</div>
<!-- Fancybox END -->
<script>
jQuery(document).ready(function(){

    jQuery("#fix_dup_prop_serv").click(function(){

        swal({
            title: "Warning!",
            text: "This will fix duplicate property services issues on database, do you want to continue?",
            type: "warning",						
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Continue",
            cancelButtonClass: "btn-danger",
            cancelButtonText: "No, Cancel!",
            closeOnConfirm: true,
            showLoaderOnConfirm: true,
            closeOnCancel: true
        },
        function(isConfirm) {

            if (isConfirm) {							  
                
                $('#load-screen').show();
                jQuery.ajax({
                    type: "POST",
                    url: "/properties/fix_dup_prop_serv"
                }).done(function( ret ){

                    $('#load-screen').hide();	
                    window.location.reload();

                });	

            }

        });

    });

});
</script>
