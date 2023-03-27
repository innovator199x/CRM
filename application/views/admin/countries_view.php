<div class="box-typical box-typical-padding">

    <?php
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Countries',
            'status' => 'active',
            'link' => "/admin/countries"
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
                            <th>Name</th>
                            <th>Code</th>
                            <th>Agent Number</th>
                            <th>Tenant Number</th>
                            <th>Trading Name</th>
                            <th>Outgoing Email</th>
                            <th>Bank</th>
                            <th>BSB</th>
                            <th>AC Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($countries as $item): ?>
                        <tr>
                            <td>
                                <span class="txt_lbl"><a href="<?php echo base_url();?>admin/country_details/<?php echo $item->country_id; ?>"><?php echo $item->country; ?></a></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $item->iso; ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $item->agent_number; ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $item->tenant_number; ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $item->ac_name; ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $item->outgoing_email; ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $item->bank; ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $item->bsb; ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $item->ac_number; ?></span>
                            </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;">

    <h4><?php echo $title; ?></h4>
    <p>
    This page displays all countries.
    </p>
    <pre>
<code>SELECT `countries`.`country_id`, `countries`.`country`, `countries`.`iso`, `countries`.`agent_number`, `countries`.`tenant_number`, `countries`.`ac_name`, `countries`.`outgoing_email`, `countries`.`bank`, `countries`.`bsb`, `countries`.`ac_number`
FROM `countries`</code>
    </pre>

</div>
<!-- Fancybox END -->
