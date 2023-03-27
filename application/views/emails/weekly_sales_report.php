<div class="row">
        <div class="col-md-12">
            <section>
                <div class="body-typical-body">
                    <div class="table-responsive">

                        <table class="table table-hover main-table weekly_sales_report_tbl" <?php echo ( $is_email == true )?'style="width:100%"':null; ?>>

                                <tbody>
                                
                                </tbody>
                                
                                <tbody id="weekly_sales_report_tbody">
                                <?php echo $this->reports_model->week_sales_report_table_row($staff_id); ?>
                                </tbody>
                        
      
                        </table>
                        
                    </div>
                    <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
                    <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
                </div>
            </section>
        </div>
    </div>