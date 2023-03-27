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

    <section>
        <div class="box-typical box-typical-padding">
            <div class="row">
                <div class="col-md-12">
                    <?php
                        $form = array(
                            'id' => 'active_properties_without_jobs_form'
                        );
                        echo form_open('/daily/active_properties_without_jobs', $form);
                    ?>

                    <div class="row">
                        <div class="col-md-6 columns">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Agency</label>
                                    <select class="form-control" id="agency_filter" name="agency_filter">
                                        <option value="">All</option>
                                        <?php
                                            foreach ( $agency_filter_result as $row ) {
                                                $agency_sel = ($this->input->post('agency_filter') == $row->agency_id) ? 'selected="true"' : NULL;
                                        ?>
                                                <option <?php echo $agency_sel; ?> value="<?php echo $row->agency_id ?>"><?php echo $row->agency_name?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-sm-12 form-control-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-inline" name="submitFilter">Search</button>
                                </div>
                            </div>                            
                        </div>

                        <div class="col-md-4">&nbsp;</div>

                        <div class="col-md-2 columns">
                            <div class="row">
                                <section class="proj-page-section float-right">
                                    <div class="proj-page-attach">
                                        <i class="fa fa-file-excel-o"></i>
                                        <p class="name"><?php echo $title; ?></p>
                                        <p>
                                            <a href="<?php echo $export_link ?>">
                                                Export
                                            </a>
                                        </p>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>

                    

                    <?php
                        echo form_close();
                    ?>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
                <table class="table main-table">
                    <thead>
                        <tr>                            
                            <th><b>Address</b></th>                            
                            <th><b>Agency</b></th>                            
                        </tr>
                    </thead>
                    <tbody>                
                    <?Php
                    foreach ( $property_sql as $property_row ) {
                    ?>
                    <tr class="body_tr jalign_left">
                        <td><?php echo $this->gherxlib->crmLink('vpd', $property_row->property_id, "{$property_row->address_1} {$property_row->address_2}, {$property_row->address_3} {$property_row->state}"); ?></td>
                        <td><?php echo $this->gherxlib->crmLink('vad', $property_row->agency_id, $property_row->agency_name,'',$property_row->priority); ?></td>
                    </tr>
                    <?php
                    
                    }
                    ?>
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
    <pre>
		<code><?php echo $sql_query; ?></code>
	</pre>

</div>
<!-- Fancybox END -->


