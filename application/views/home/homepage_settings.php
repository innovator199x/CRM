<style>
.move{
    cursor: move;
 }
</style>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.2.8/css/rowReorder.dataTables.min.css"/>
<script src="https://cdn.datatables.net/rowreorder/1.2.8/js/dataTables.rowReorder.min.js"></script>
<div class="box-typical box-typical-padding">

	<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/home/homepage_settings"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>


	<section>
		<div class="body-typical-body">


			<div class="table-responsivess" style="padding-top:0px;">

                <div style="margin-bottom: 10px;">
                    <a data-fancybox data-type="ajax" href="javascript:;" data-src="/home/ajax_get_goal_count_data" class="btn btn fancybox" id="edit-goal">Update Goals</a>
                    &nbsp;&nbsp;
                    <?php 
                    if(  in_array($this->session->staff_id,$this->config->item('allow_to_edit_user_class_block'))  ){ ?>
                        <a href="/home/assign_user_class_block" class="btn btn-success right" id="popup_box_assigned_class_block">Update/Assign User Class Block</a>
                    <?php } ?>
                </div>
                
                <!-- Table for Small blocks -->
                <table id="row_order_table" class="table table-hover main-table" style="width:100%;">

					<thead>
						<tr>
							<th>Sort</th>
							<th>Block ID</th>
							<th>Small Container Name (Drag to sort order)</th>
							<th>Display</th>
                        </tr>
					</thead>

					<tbody>
                            <?php 

                                $user_block_cnt = $this->users_model->check_home_content_block_users_block($this->session->staff_id);

                                if( $res->num_rows() > 0 ){
                                    $chckCounter=0; 
                                    $cnt = 1;
                                    foreach($res->result_array() as $row){ 

                                        //$data_sort = ($row['sort']=="")? '9999999' : $row['sort'];

                                        if( $user_block_cnt<=0 ){

                                            $attr_checked = 'checked="checked"';
                                            $data_sort = $cnt;

                                        }else{

                                            if( in_array( $row['content_block_id'], $tt_rr)){
                                                $attr_checked = 'checked="checked"';
                                            }else{
                                                $attr_checked = NULL;
                                            }

                                            $data_sort = ($row['sort']=="")? '9999999' : $row['sort'];
                                            
                                        }

                                ?>
                                    <tr data-sorttt="<?php echo $data_sort; ?>" class="tr <?php echo ($row['sort']=='')? NULL : NULL; ?>">
                                        <td><?php echo $data_sort ?></td>
                                        <td><?php echo $row['content_block_id'] ?></td>
                                        <td class="move"><?php echo $row['content_name'] ?></td>
                                        <td>

                                            <div class="checkbox" style="margin:0;">
                                                <input class="job_chk chk_content_block" <?php echo $attr_checked; ?> name="chk_content_block[]" type="checkbox" id="checkbox_ci_<?php echo $chckCounter ?>" value="<?php echo $row['content_block_id']; ?>">
                                                <label for="checkbox_ci_<?php echo $chckCounter ?>">&nbsp;</label>
                                            </div>

                                        </td>
                                    </tr>
                                <?php 
                                    $chckCounter++;
                                    $cnt++;
                                }
                            }else{
                                echo "<tr><td colspan='2'>No blocks assign yet. Please contact admin.</td></tr>";
                            }
                            
                            ?>
					</tbody>

				</table>
                <!-- Table for Small blocks end -->

                <!-- Table for large blocks -->
                <div style="margin-top:40px;">
                    <table id="row_order_table_2" class="table table-hover main-table" style="width:100%;">

                        <thead>
                            <tr>
                                <th>Sort</th>
                                <th>Block ID</th>
                                <th>Large Container Name (Drag to sort order)</th>
                                <th>Display</th>
                            </tr>
                        </thead>

                        <tbody>
                                <?php 

                                    $user_block_cnt = $this->users_model->check_home_content_block_users_block($this->session->staff_id);

                                    if( $res2->num_rows() > 0 ){
                                        $chckCounter2=0; 
                                        $cnt = 1;
                                        foreach($res2->result_array() as $row){ 

                                            //$data_sort = ($row['sort']=="")? '9999999' : $row['sort'];

                                            if( $user_block_cnt<=0 ){

                                                $attr_checked = 'checked="checked"';
                                                $data_sort = $cnt;

                                            }else{

                                                if( in_array( $row['content_block_id'], $tt_rr)){
                                                    $attr_checked = 'checked="checked"';
                                                }else{
                                                    $attr_checked = NULL;
                                                }

                                                $data_sort = ($row['sort']=="")? '9999999' : $row['sort'];
                                                
                                            }

                                    ?>
                                        <tr data-sorttt="<?php echo $data_sort; ?>" class="tr <?php echo ($row['sort']=='')? NULL : NULL; ?>">
                                            <td><?php echo $data_sort ?></td>
                                            <td><?php echo $row['content_block_id'] ?></td>
                                            <td class="move"><?php echo $row['content_name'] ?></td>
                                            <td>

                                                <div class="checkbox" style="margin:0;">
                                                    <input class="job_chk chk_content_block" <?php echo $attr_checked; ?> name="chk_content_block[]" type="checkbox" id="checkbox_ci2_<?php echo $chckCounter2 ?>" value="<?php echo $row['content_block_id']; ?>">
                                                    <label for="checkbox_ci2_<?php echo $chckCounter2 ?>">&nbsp;</label>
                                                </div>

                                            </td>
                                        </tr>
                                    <?php 
                                        $chckCounter2++;
                                        $cnt++;
                                    }
                                }else{
                                    echo "<tr><td colspan='2'>No blocks assign yet. Please contact admin.</td></tr>";
                                }
                                
                                ?>
                        </tbody>

                    </table>
                </div>
                <!-- Table for large blocks end -->

			</div>
		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    Home Page Settings > Edit homepage content block...
	</p>

</div>
<!-- Fancybox END -->
<script type="text/javascript"></script>

<script type="text/javascript">

    function ajax_add_users_block(){

        thisvalchekced = [];
        $("input[name='chk_content_block[]']:checked").each(function (index,el)
        {
            thisvalchekced.push(parseInt($(this).val()));
        });

        jQuery("#load-screen").show();
        jQuery.ajax({
            type: "POST",
            url: "/home/ajax_add_users_block",
            data: { 
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

    jQuery(document).ready(function(){

        $('.chk_content_block').change(function(){

            ajax_add_users_block();

        })

        //Data tables for small blocks -------------------
        var serialsDict = [];
        var dataTable = $('#row_order_table').DataTable({
            'pageLength': 100,
            'bPaginate':false,
            "searching": false,
            rowReorder: {
                       // selector: 'tr:not(.no_sort)'
                        selector: 'tr td:not(:last-of-type)',
                        target: '0'
                     
                    },
            'columnDefs': [
                            {
                                'targets': [1,2,3],
                                'orderable': false
                            },{
                                'targets':[0,1],
                                'visible': false
                            }
                        ]
        });

        dataTable.on('row-reorder', function ( e, diff, edit) {
            
            //insert data to home_content_block_users_block tabe if isn't added yet
            $.post("/home/ajax_check_home_content_block_users_block_count", {
                tt_data: 1
                }, function(data, status) {
                    var cnt = parseInt(data);
                    if(cnt>0){
                        console.log('has data');
                    }else{
                        
                        ajax_add_users_block();

                    }
                })
            //insert data to home_content_block_users_block tabe if isn't added yet end

            for (var i = 0; i < diff.length; i++) {
                var rowData = dataTable.row( diff[i].node ).data();
                serialsDict.push({
                    content_block_id: rowData[1],
                    oldData: rowData[0],
                    newData: diff[i].newData
                });
            }

        });

        dataTable.on('draw', function () {
            if (serialsDict.length) {
                $.post("/home/sort_homepage_settings_block", {
                serialsDict: serialsDict
                }, function(data, status) {
                    serialsDict = [];
                })
            } ;
        });
        //Data tables for small blocks end -------------------


        //Datatable for large blocks -------------------
        var serialsDict2 = [];
        var dataTable2 = $('#row_order_table_2').DataTable({
            'pageLength': 100,
            'bPaginate':false,
            "searching": false,
            rowReorder: {
                       // selector: 'tr:not(.no_sort)'
                        selector: 'tr td:not(:last-of-type)',
                        target: '0'
                     
                    },
            'columnDefs': [
                            {
                                'targets': [1,2,3],
                                'orderable': false
                            },{
                                'targets':[0,1],
                                'visible': false
                            }
                        ]
        });

        dataTable2.on('row-reorder', function ( e, diff, edit) {
            
            //insert data to home_content_block_users_block tabe if isn't added yet
            $.post("/home/ajax_check_home_content_block_users_block_count", {
                tt_data: 1
                }, function(data, status) {
                    var cnt = parseInt(data);
                    if(cnt>0){
                        console.log('has data');
                    }else{
                        
                        ajax_add_users_block();

                    }
                })
            //insert data to home_content_block_users_block tabe if isn't added yet end

            for (var i = 0; i < diff.length; i++) {
                var rowData = dataTable2.row( diff[i].node ).data();
                serialsDict2.push({
                    content_block_id: rowData[1],
                    oldData: rowData[0],
                    newData: diff[i].newData
                });
            }

        });

        dataTable2.on('draw', function () {
            if (serialsDict2.length) {
                $.post("/home/sort_homepage_settings_block", {
                serialsDict: serialsDict2
                }, function(data, status) {
                    serialsDict = [];
                })
            } ;
        });
        //Datatable for large blocks end -------------------


    });


</script>