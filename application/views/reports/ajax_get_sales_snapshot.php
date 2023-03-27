<style>
.action_tools, .action_tools a{

    color:#adb7be;
    font-size:16px;
}
.action_tools a{
    display:inline-block;
}
</style>


<table class="table table-hover main-table">
                        <thead>
                                <tr>
                                <th width="7%">Date</th>
                                <th width="18%">Agency</th>
                                <th width="5%">Properties</th>
                                <th width="10%"><?php echo $this->customlib->getDynamicRegionViaCountry($this->config->item('country'));  ?></th>
                                <th width="8%">Status</th>
                                <th width="47%">Details</th>
                                <th width="8%">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                                $params = array(
                                    'sel_query' => 'ss.sales_snapshot_sales_rep_id, ss_s.name as status_name, ss.sales_snapshot_id, ss.sales_snapshot_status_id as ss_status_id, ss.details, ss.date, ss.properties, a.agency_id, a.agency_name, sr.sub_region_id as postcode_region_id, sr.subregion_name as postcode_region_name, sa.FirstName AS first_name, sa.LastName AS last_name, aght.priority ',
                                    'sales_snapshot_sales_rep_id_where' => $sales_snapshot_sales_rep_id
                                );
                                $snapshot_list = $this->reports_model->getSnapshot($params);
                            ?>
                            

                            <?php

                                    $total = 0;
                                    foreach($snapshot_list->result() as $row){
                            ?>

                                        <tr class="tr_row_<?php echo $row->sales_snapshot_id ?>" data-rowid=<?php echo $row->sales_snapshot_id  ?>>
                                            <td class="snap_date">
                                                <?php echo ( $this->system_model->isDateNotEmpty($row->date) )? date('d/m/Y', strtotime($row->date)) : null ?>
                                            </td>
                                            <td class="snap_agency">
                                               <?php 
                                                    echo $this->gherxlib->crmLink('vad',$row->agency_id,$row->agency_name,'',$row->priority);
                                               ?>
                                            </td>
                                            <td class="snap_property">
                                                <?php
                                                    echo $row->properties;
                                                ?>
                                                <?php $total += $row->properties; ?>
                                            </td>
                                            <td class="snap_region">
                                                <?php 
                                                    echo ($row->postcode_region_id!="") ? $row->postcode_region_name : NULL;
                                                ?>
                                            </td>
                                            <td class="snap_status">
                                                <?php 
                                                    echo $row->status_name;
                                                ?>
                                            </td>
                                            <td class="snap_details">
                                                <?php
                                                    echo $row->details;
                                                ?>
                                            </td>
                                            <td>
                                            <div class="action_tools">
                                                <a data-toggle="tooltip" data-snapid="<?php echo $row->sales_snapshot_id ?>" title="Delete" class="btn_delete_snapshot" href="#"><span class="glyphicon glyphicon-trash"></span></a>
                                                &nbsp;|&nbsp;
                                                <a data-toggle="tooltip" title="Edit" class="inline_fancybox"  href="#data<?php echo $row->sales_snapshot_id; ?>"><i class="font-icon font-icon-pencil"></i></a>
                                                </div>
                                                <?php 

                                                
                                                
                                                ?>

                                                    <!--- UPDATE FANCYBOX START -->
                                                    <div style="display:none;" class="snapshot_edit_box" id="data<?php echo $row->sales_snapshot_id; ?>">
                                                            <h4><?php echo "{$row->first_name} {$row->last_name}" ?></h4>
                                                        
                                                       

                                                        <div class="row">
                                                                <div class="col-md-6 columns">

                                                                    <div class="form-group">
                                                                        <label>Salesrep</label>
                                                                        <select class="form-control snap_sales_rep" name="snap_sales_rep<?php echo $row->sales_snapshot_id ?>">
                                                                        <option value="">----</option>

                                                                        <?php
                                                                            foreach($snapshot_reps->result() as $snapshot_reps_row){
                                                                                
                                                                            $sales_reps_selected = ($sales_snapshot_sales_rep_id == $snapshot_reps_row->sales_snapshot_sales_rep_id) ? 'selected="selected"' : NULL;
                                                                        ?>
                                                                            <option <?php echo $sales_reps_selected ?>  value="<?php echo $snapshot_reps_row->sales_snapshot_sales_rep_id ?>"><?php echo "{$snapshot_reps_row->first_name} {$snapshot_reps_row->last_name}" ?></option>
                                                                        <?php
                                                                            }
                                                                        ?>

                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 columns">
                                                                    <div class="form-group">
                                                                        <label>Date</label>
                                                                        <input name="snapshot_date<?php echo $row->sales_snapshot_id ?>" readonly="readonly" class="form-control" value="<?php echo ( $this->system_model->isDateNotEmpty($row->date) )? date('d/m/Y', strtotime($row->date)) : null ?>">
                                                                    </div>
                                                                </div>
                                                        </div>

                                                        <div class="row">
                                                                <div class="col-md-6 columns">
                                                                    <div class="form-group">
                                                                        <label>Agency</label>
                                                                        <select class="form-control snap_agency" name="snap_agency<?php echo $row->sales_snapshot_id ?>">
                                                                            <option value="">----</option>
                                                                          <?php 
                                                                            foreach($agency_list->result() as $tt){
                                                                                $agency_list_selected = ($tt->agency_id == $row->agency_id) ? 'selected="selected"' : NULL;
                                                                                echo "<option {$agency_list_selected} value='{$tt->agency_id}'>{$tt->a_name} ({$tt->status})</option>";
                                                                            }
                                                                          ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 columns">
                                                                    <div class="form-group">
                                                                        <label>Properties</label>
                                                                        <input type="text" class="form-control snap_properties" name="snap_properties<?php echo $row->sales_snapshot_id ?>" value="<?php echo $row->properties; ?>">
                                                                    </div>
                                                                </div>
                                                        </div>
                                                        <div class="row">
                                                                <div class="col-md-6 columns">
                                                                    <div class="form-group">
                                                                        <label>Region</label>
                                                                        <input type="text" class="form-control" readonly="readonly" name="snap_region<?php echo $row->sales_snapshot_id ?>" value="<?php  echo ($row->postcode_region_id!="") ? $row->postcode_region_name : NULL; ?>">
                                                                </div>
                                                                </div>
                                                                <div class="col-md-6 columns">
                                                                    <div class="form-group">
                                                                        <label>Status</label>
                                                                        <select class="form-control snap_status" name="snap_status<?php echo $row->sales_snapshot_id ?>" >
                                                                        <option value="">----</option>
                                                                            <?php 
                                                                                foreach($sales_snapshot_status_list->result() as $sales_snapshot_status_list_row){
                                                                                $sales_snapshot_status_list_selected = ($sales_snapshot_status_list_row->sales_snapshot_status_id == $row->ss_status_id) ? 'selected="selected"' : NULL;
                                                                            ?>

                                                                                    <option <?php echo $sales_snapshot_status_list_selected; ?> value="<?php echo $sales_snapshot_status_list_row->sales_snapshot_status_id; ?>"><?php echo $sales_snapshot_status_list_row->name; ?></option>
                                                                            <?php
                                                                                }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                        </div>
                                                        <div class="row">
                                                                <div class="col-md-12 columns">
                                                                    <div class="form-group">
                                                                        <label>Details</label>
                                                                        <textarea class="form-control snap_details" name="snap_details<?php echo $row->sales_snapshot_id ?>"><?php  echo $row->details; ?></textarea>
                                                                    </div>
                                                                </div>
                                                        </div>
                                                        <div class="row">
                                                                <div class="col-md-12 columns">
                                                                    <div class="form-group">
                                                                    <input type="hidden" class="input_sales_snapshot_id" value="<?php echo $row->sales_snapshot_id ?>">
                                                                      <button class="btn btn_update_snapshot">Update</button>  <button class="btn btn-danger btn-cancel-fancybox">Cancel</button>
                                                                    </div>
                                                                </div>
                                                        </div>
                                                            
                                                    </div>
                                                    <!--- UPDATE FANCYBOX END -->
                                            
                                            
                                            </td>

                                         
                                        </tr>

                            <?php
                                     }
                                
                            ?>
                            <tr>
                            <td><strong>Total</strong></td>
                            <td>&nbsp;</td>
                            <td colspan="5"><?php echo $total; ?></td>
                            </tr>
                            
                        </tbody>

                    </table>
                

                <script type="text/javascript">

                    jQuery(document).ready(function(){


                        $("a.inline_fancybox").fancybox({
                            'hideOnContentClick': true,
                            'width': 700,
                            'height': 520,
                            'autoSize': false,
                            'autoDimensions':false
                        });

                        $('.btn-cancel-fancybox').on('click',function(){
                            $.fancybox.close();
                        })

                        //UPDATE SNAPSHOT
                        $('.btn_update_snapshot').on('click',function(){

                        var obj = $(this);
                        var sales_snapshot_id = obj.parents(".snapshot_edit_box").find(".input_sales_snapshot_id").val();
                        var sales_rep = obj.parents(".snapshot_edit_box").find(".snap_sales_rep").val();
                        var agency_id = obj.parents(".snapshot_edit_box").find(".snap_agency").val();
                        var properties = obj.parents(".snapshot_edit_box").find(".snap_properties").val();
                        var status = obj.parents(".snapshot_edit_box").find(".snap_status").val();		
                        var details = obj.parents(".snapshot_edit_box").find(".snap_details").val();

                        var sales_snapshot_sales_rep_id = <?php echo $sales_snapshot_sales_rep_id; ?>


                        //validation
                        var error = "";
                        if($.trim(sales_rep).length == 0){
                            error += "Salesrep must not be empty\n";
                        }
                        if($.trim(agency_id).length == 0){
                            error += "Agency must not be empty\n";
                        }
                        if($.trim(status).length == 0){
                            error += "Status must not be empty\n";
                        }

                        //if error return false
                        if(error!=""){
                            swal('',error,'error');
                            return false;
                        }

                        swal(
                                {
                                    title: "",
                                    text: "Are you sure you want to update?",
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
                                        $.fancybox.close();
                                        $('#load-screen').show(); //show loader
                                        $('.sweet-alert, .sweet-overlay').css('z-index', 10);
                                        
                                        jQuery.ajax({
                                            type: "POST",
                                            url: "/reports/ajax_update_sales_snapshot",
                                            dataType: 'json',
                                            data: { 
                                                sales_snapshot_id: sales_snapshot_id,
                                                sales_rep: sales_rep,
                                                agency_id: agency_id,
                                                properties: properties,
                                                status: status,
                                                details: details,
                                                sales_snapshot_sales_rep_id: sales_snapshot_sales_rep_id
                                            }
                                        }).done(function( ret ) {

                                                //success
                                                if(ret.status){
                                                    $('#load-screen').hide(); //show loader
                                                    $('.sweet-alert, .sweet-overlay').css('z-index', 99999);


                                                    //populate
                                                    $('.tr_row_'+sales_snapshot_id).children('.snap_date').text(ret.date);
                                                    $('.tr_row_'+sales_snapshot_id).children('.snap_agency').html(ret.agency);
                                                    $('.tr_row_'+sales_snapshot_id).children('.snap_property').text(ret.properties);
                                                    $('.tr_row_'+sales_snapshot_id).children('.snap_region').text(ret.region);
                                                    $('.tr_row_'+sales_snapshot_id).children('.snap_status').text(ret.snap_status);
                                                    $('.tr_row_'+sales_snapshot_id).children('.snap_details').text(ret.details);


                                                    swal({
                                                        title:"Success!",
                                                        text: "Update Successfull",
                                                        type: "success",
                                                        showCancelButton: false,
                                                        confirmButtonText: "OK",
                                                        closeOnConfirm: false,

                                                    },function(isConfirm2){
                                                    if(isConfirm2){ 
                                                            //location.reload();
                                                            swal.close();
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


                        $('.btn_delete_snapshot').on('click',function(e){
                            e.preventDefault();
                            
                            var snapid = $(this).data('snapid');
                            var tr_row = $(this).parents('.tr_row_'+snapid);   
                            
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
                                       // $('.sweet-alert, .sweet-overlay').css('z-index', 10);
                                        
                                        jQuery.ajax({
                                            type: "POST",
                                            url: "/reports/ajax_delete_snapshot",
                                            dataType: 'json',
                                            data: { 
                                                sales_snapshot_id: snapid
                                            }
                                        }).done(function( ret ) {

                                                //success
                                                if(ret.status){
                                                    $('#load-screen').hide(); //show loader
                                                    

                                                   //delete row via event
                                                   tr_row.remove();


                                                    swal({
                                                        title:"Success!",
                                                        text: ret.msg,
                                                        type: "success",
                                                        showCancelButton: false,
                                                        confirmButtonText: "OK",
                                                        closeOnConfirm: false,

                                                    },function(isConfirm2){
                                                    if(isConfirm2){ 
                                                            //location.reload();
                                                            swal.close();
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





                    })
                
                </script>

               
              