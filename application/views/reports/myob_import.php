
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .action_div{
        display: none;
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
            'link' => "/reports/overdue_invoices"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">

        <div class="box-typical box-typical-padding">
        
            <div class="for-groupss row">

               
                <div class="col-md-8 columns">

                <?php 
                    $form_attr = array(
                        'id' => 'jform'
                    );
                    echo form_open_multipart('/reports/overdue_invoices',$form_attr);
                ?>

                        <div class="row">

                            <div class="col-md-11"> 
                                <input class="btn btn_file" type="file" name="file">  <input type="submit" name="btn_import_csv" value="Import" style="display:none;" class="btn btn_import_csv">
                            </div>

                            <div class="col-md-1 columns">
                               
                            </div>

                        </div>

                    </form>

                </div>

                

                <!-- DL ICONS START -->
                <div class="col-md-4 columns">
                  
                </div>
                                
            </div>
        
        </div>

    </header>

    <section>

        <div class="body-typical-body">
            <div class="table-responsive">
                <table class="table table-hover main-table">
                    <thead>
                        <th>Job ID</th>
                        <th class="text-center">Amount ($)</th>
                        <th class="text-center">Number of days overdue</th>
                       <!--<th class="text-right">
                            <div class="checkbox" style="margin:0;">
                                <input name="chk_all" type="checkbox" id="check-all">
                                <label for="check-all">&nbsp;</label>
                            </div>
                        </thead>-->
                    </tr>

                    <?php 
                    $i=0;
                    foreach($ttcsv as $row){
                        if($row[8]!=""){
                    ?>
                        <tr>
                                <td><?php echo $this->gherxlib->crmLink('vjd',$row[8],$row[8],"_blank"); ?></td>
                                <td class="text-center"><?php echo $row[9] ?></td>
                                <td class="text-center"><?php echo $row[10] ?></td>
                                <!--<td class="text-right">
                                    <div class="checkbox">
                                        <input class="chk_myob" name="chk_myob[]" type="checkbox" id="check-<?php echo $i ?>" value="<?php echo $row->credit_request_id; ?>">
                                        <label for="check-<?php echo $i ?>">&nbsp;</label>
                                    </div>
                                </td>-->
                            </tr>
                    <?php
                        $i++;
                        }
                    }
                    ?>
                </table>

               <!-- <div id="mbm_box" class="text-right" style="display: none;"><button id="email_myob" type="button" class="btn">Email</button></div> -->
            </div>
        </div>

    </section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>
        Import a CSV of the sales export from MYOB and view overdue jobs with the ID, the amount owing and how many days overdue the job is.
    </p>

</div>
<!-- Fancybox END -->


<script>
    jQuery(document).ready(function () {

        $('.btn_file').on("change", function(){ 
            thisval = $(this);
            if( thisval!="" ){
                $('.btn_import_csv').show();
            }else{
                $('.btn_import_csv').hide();
            }
         });

        $('#check-all').on('change',function(){
			var obj = $(this);
			var isChecked = obj.is(':checked');
			var divbutton = $('#mbm_box');
			if(isChecked){
				divbutton.show();
				$('.chk_myob').prop('checked',true);
			}else{
				divbutton.hide();
				$('.chk_myob').prop('checked',false);
			}
		})

        $('.chk_myob').on('change',function(){
			var obj = $(this);
			var isLength = $('.chk_myob:checked').length;
			var divbutton = $('#mbm_box');
			if(isLength>0){
				divbutton.show();
			}else{
				divbutton.hide();
			}
		})

    });
</script>