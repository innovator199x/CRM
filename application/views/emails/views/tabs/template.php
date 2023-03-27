<?php
$edit_permission = false;
if($class_id == 2 || $class_id == 3 || $class_id == 9 || $class_id == 10){
    $edit_permission = true;
}
?>
<div class="box-typical-body mt-3">
    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="for-groupss row">
                <div class="col-md-12 columns">
                    <div class="row">
                        <div class="col-md-4">
                        <?php if($edit_permission == true){ ?>
                            <label>&nbsp;</label>
                            <a href="/email/view_add_template"><button type="button" class="btn">Add New</button></a>
                            <?php } ?>
                        </div>	
                        <div class="col-md-4 offset-4">
                            <form id="form_search" method="post" >
                                <div class="fl-left" style="float: left;">
                                    <label>Display: </label>
                                    <select name="active" class="form-control">
                                        <option value="-1" <?php echo ( $active == '' ) ? 'selected="selected"' : ''; ?>>ALL</option>	
                                        <option value="1" <?php echo ( $active == 1 ) ? 'selected="selected"' : ''; ?>>Active</option>
                                        <option value="0" <?php echo ( is_numeric($active) && $active == 0) ? 'selected="selected"' : ''; ?>>Inactive</option>						
                                    </select>
                                </div>
                                <div class="fl-left" style="float:left; margin-left: 10px;">				
                                    <label>&nbsp;</label>
                                    <button class="submitbtnImg btn" id="btn_submit" type="submit">
                                        Search
                                    </button>				
                                </div>
                            </form>
                        </div>	
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="body-typical-body">
        <div class="table-responsive">
            <table class="table table-hover main-table">
                <thead>
                    <tr class="toprow jalign_left">				
                        <th>Template Name</th>
                        <th>Subject</th>	
                        <th>Type</th>
                        <th>Call Centre</th>
                        <th>Active</th>	
                    </tr>
                </thead>
                <tbody>                
                    <?php
                    if (count($templates) > 0) {
                        foreach ($templates as $et) {
                            ?>
                            <tr class="body_tr jalign_left">						
                                <td><a href="/email/view_email_template_detail/?id=<?php echo $et['email_templates_id']; ?>"><?php echo $et['template_name']; ?></a></td>
                                <td><?php echo $et['subject']; ?></td>
                                <td><?php echo $et['ett_name'] ?></td>
                                <td class="<?php echo ($et['show_to_call_centre'] == 1) ? 'colorItGreen' : 'colorItRed'; ?>"><?php echo ($et['show_to_call_centre'] == 1) ? 'Yes' : 'No'; ?></td>
                                <td class="<?php echo ($et['et_active'] == 1) ? 'colorItGreen' : 'colorItRed'; ?>"><?php echo ($et['et_active'] == 1) ? 'Yes' : 'No'; ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    <?php } else { ?>
                        <tr><td colspan="100%" align="left">Empty</td></tr>
                        <?php } ?>			
                </tbody>
            </table>
        </div>
        <nav aria-label="Page navigation example" style="text-align:center">
            <?php echo $pagination; ?>
        </nav>
        <div class="pagi_count text-center">
            <?php echo $pagi_count; ?>
        </div>
    </div>
</div>