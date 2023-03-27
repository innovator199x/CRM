<?php if($result_type =='duplicate'){ ?>
<div class="box-typical box-typical-padding">
            <?php echo $upload_error; ?>
            <section class="card card-red mb-3">
				<header class="card-header">
                Duplicate Property Found
					<button type="button" class="modal-close">
						<i class="font-icon-close-2"></i>
					</button>
				</header>
				<div class="card-block">
					<p class="card-text">The following properties exist in the system with the same details:</p>

                    <table class="table table-hover main-table">
                        <tr>
                            <th>Adddress</th>
                            <th>Status</th>
                            <th>Agency</th>
                        </tr>
                        <tr>
                            <td>
                                <?php echo $this->gherxlib->crmLink('vpd',$prop_id, $address) ?>
                            </td>
                            <td>
                                <?php echo $status; ?>
                            </td>
                            <td>
                            <?php echo $this->gherxlib->crmLink('vad',$agency_id, $agency_name) ?>
                            </td>
                        </tr>
                    </table>

                    
				</div>
			</section>

            <a class="btn" href="/properties/add"><span class="fa fa-arrow-left"></span>&nbsp;&nbsp;Back to Add Property Form</a>

</div>
<?php } ?>

<?php if($result_type == "success"){ ?>

<div class="box-typical box-typical-padding">
   <?php echo $upload_error; ?>
   <section class="card card-green mb-3">
       <header class="card-header">
       Property Successfully Added
           <button type="button" class="modal-close">
               <i class="font-icon-close-2"></i>
           </button>
       </header>
       <div class="card-block">
           <p class="card-text">The following property has been successfully added.</p>

           <table class="table table-hover main-table">
               <tr>
                   <th>Property Address</th>
                   <th>&nbsp;</th>
               </tr>
               <tr>
                   <td>
                       <?php echo $this->gherxlib->crmLink('vpd',$res_prop_id, $res_prop_address) ?>
                   </td>
                   <td>
                       <?php echo $this->gherxlib->crmLink('vpd',$res_prop_id, "View Property Details") ?>
                   </td>
               </tr>
           </table>

           
       </div>
   </section>


</div>

<?php } ?>