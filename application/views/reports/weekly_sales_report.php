<style>    
.col-mdd-3{
    max-width:15.5%;
}

.main-table tr td:nth-child(1),
.main-table tr th:nth-child(1){
    width: 25%;
    border-right: 1px solid #dee2e6;
}
th.jheading {
    border-bottom: none;
    padding-top: 10px;
    padding-bottom: 9px;
    background: #f6f8fa;
}
#staff_account{
    width: auto;
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
        'link' => $uri
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>
    <div class="row">
        <div class="col-md-12">
            <section>
                <div class="body-typical-body">
                    <div class="table-responsive">

                        <table class="table table-hover main-table">

                                <tbody>
                                <tr>
                                    <th>Select User Data:</th>
                                    <td class="pr-3">
                                        <select id="staff_account" class="form-control">   
                                            <option value="">---</option>                                         
                                            <?php    
                                            $custom_where = "sa.`display_on_wsr` = 1";                                                                                
                                            
                                            $sel_query = '
                                                sa.`StaffID`,
                                                sa.`FirstName`,
                                                sa.`LastName`
                                            ';

                                            // get staff accounts
                                            $params = array( 
                                                'sel_query' => $sel_query,
                                                'custom_where' => $custom_where,                                                
                                                'active' => 1,
                                                'deleted' => 0,

                                                'sort_list' => array(
                                                    array(
                                                        'order_by' => 'sa.`FirstName`',
                                                        'sort' => 'ASC'
                                                    ),
                                                    array(
                                                        'order_by' => 'sa.`LastName`',
                                                        'sort' => 'ASC'
                                                    )
                                                ),

                                                'display_query' => 0
                                            );
                                            
                                            // get user details
                                            $user_account_sql = $this->staff_accounts_model->get_staff_accounts($params);  
                                            foreach( $user_account_sql->result() as $user_account_row ){ ?>
                                                <option value="<?php echo $user_account_row->StaffID ?>"><?php echo "{$user_account_row->FirstName} {$user_account_row->LastName}"; ?></option>
                                            <?php
                                            }                                          
                                            ?>    
                                        </select>
                                    </td>                                    
                                </tr>  
                                </tbody>
                                
                                <tbody id="weekly_sales_report_tbody">
                                </tbody>
                        
      
                        </table>
                        
                    </div>
                    <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
                    <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
                </div>
            </section>
        </div>
    </div>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    When life gives you lemon, make a lemonade :)
	</p>

</div>
<!-- Fancybox END -->


<script type="text/javascript">
jQuery(document).ready(function(){

    
   jQuery("#staff_account").change(function(){

       var sa_id = jQuery(this).val(); 

       if( sa_id > 0 ){

            $('#load-screen').show(); //show loader
            jQuery.ajax({
                type: "POST",
                url: "/reports/get_staff_weekly_sales_report_ajax",                
                data: { 
                    sa_id: sa_id
                }
            }).done(function( ret ) {
            
                $('#load-screen').hide(); //hide loader
                jQuery("#weekly_sales_report_tbody").html(ret);
                

            });

       }else{

        // clear
        jQuery("#weekly_sales_report_tbody").html("");
        
       }        

   });	


});
</script>