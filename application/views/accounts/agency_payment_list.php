<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table table-striped" id="agency_payments_tbl">
					<thead>
						<tr>
                            <th>Date</th>	                            		
                            <th>Agency</th>		
                            <th>Payment Type</th>					
                            <th>Reference</th>
                            <th>Amount</th>	
                            <th>Allocated</th>                                                   
                            <th>Remaining</th>
                            <th>Bank Deposit</th>                            
                            <th>Remittance</th>

                            <th class="action_col">Action</th>
						</tr>
					</thead>

					<tbody>
                        <?php 
                        $chckCounter = 1;                        
                            if( $agen_pay_sql->num_rows() > 0 ){

                                foreach($agen_pay_sql->result() as $index => $agen_pay_row){		                                                                        
                                ?>

                                    <tr class="body_tr <?php echo $agen_pay_row_higlight; ?>">
                                        <td>
											<?php echo ($this->system_model->isDateNotEmpty($agen_pay_row->date)==true)?$this->system_model->formatDate($agen_pay_row->date,'d/m/Y'):''; ?>
										</td>																				
                                        <td>                                            
                                            <?php    
                                            // get agencies
                                            $sel_query = "
                                            agen_pay_a.agency_payments_id,
                                        
                                            a.`agency_id`,
                                            a.`agency_name`
                                            ";
                                            
                                            $a_params = array(
                                                'sel_query' => $sel_query,                                
                                                'agency_payments_id' => $agen_pay_row->agency_payments_id,    
                                                'join_table' => array('agency_payments_agencies'),     
                                                'sort_list' => array(
                                                    array(
                                                        'order_by' => 'a.`agency_name`',
                                                        'sort' => 'ASC'
                                                    )
                                                ),                          
                                                'display_query' => 0
                                            );
                                            $agency_sql = $this->remittance_model->get_agency_payments($a_params);    
                                            
                                            $agency_arr = [];
                                            $agency_imp = null;

                                            foreach( $agency_sql->result() as $a_row ){ 
                                                $agency_arr[] = "<a href='/agency/view_agency_details/{$a_row->agency_id}'>{$a_row->agency_name}</a>";
                                                $agency_imp = implode(", ",$agency_arr);                    
                                            }

                                            echo $agency_imp;                                                                                
                                            ?>                                                             
                                        </td>
                                        <td>
                                            <?php echo $agen_pay_row->pt_name; ?>
                                        </td>										
										<td>
                                            <?php echo $agen_pay_row->reference; ?>
                                        </td>
                                        <td>
											$<?php echo $amount = number_format($agen_pay_row->amount,2); ?>
											<input type="hidden" class="amount" value="<?php echo $amount; ?>" />
                                        </td>
                                        <td>
                                            $<?php echo $allocated = number_format($agen_pay_row->allocated,2); ?>
											<input type="hidden" class="allocated" value="<?php echo $allocated; ?>" />
                                        </td>                                      
                                        <td class="font-italic">
                                            $<?php echo $remaining = number_format($agen_pay_row->remaining,2); ?>
											<input type="hidden" class="remaining" value="<?php echo $remaining; ?>" />
                                        </td>   
                                        
                                        <td>
                                            <?php //echo ( $agen_pay_row->bank_deposit == 1 )?'<span class="txt_green">Yes</span>':'<span class="txt_red">No</span>'; ?>
                                            <select class="form-control inline_bank_deposit">
                                                <option value="0" <?php echo ( is_numeric($agen_pay_row->bank_deposit) && $agen_pay_row->bank_deposit == 0 )?'selected="selected"':null; ?>>No</option>										
                                                <option value="1" <?php echo ( $agen_pay_row->bank_deposit == 1 )?'selected="selected"':null; ?>>Yes</option>                                               
                                            </select> 
                                        </td>										
										<td>
                                            <?php 
                                            /*
                                            if( $agen_pay_row->remittance == 0 ){
                                                echo '<span class="txt_red">No</span>';
                                            }if( $agen_pay_row->remittance == 1 ){
                                                echo '<span class="txt_green">Yes</span>';
                                            }if( $agen_pay_row->remittance == 2 ){
                                                echo '<span class="txt_red">Not Needed</span>';
                                            }
                                            */

                                            // allocate button
                                            if( $agen_pay_row->bank_deposit == 1 && ( $agen_pay_row->remittance == 1 || $agen_pay_row->remittance == 2 ) ){ // active
                                                $allocate_btn_link = "/accounts/receipting/?agency_payments_id={$agen_pay_row->agency_payments_id}";
                                                $allocate_btn_class = 'btn-primary';
                                                $allocate_btn_disabled = '';                                                
                                            }else{ // disable
                                                $allocate_btn_link = "javascript:void(0);";
                                                $allocate_btn_class = '';
                                                $allocate_btn_disabled = 'disabled';
                                            }
                                            
                                            ?>
                                            <select class="form-control inline_remittance">	
                                            <option value="0" <?php echo ( is_numeric($agen_pay_row->remittance) && $agen_pay_row->remittance == 0 )?'selected="selected"':null; ?>>No</option>										
                                                <option value="1" <?php echo ( $agen_pay_row->remittance == 1 )?'selected="selected"':null; ?>>Yes</option>                                                   
                                                <option value="2" <?php echo ( $agen_pay_row->remittance == 2 )?'selected="selected"':null; ?>>Not Needed</option>
                                            </select>
                                        </td>
                                        
                                        <td class="action_td">                    
                                            <a href="<?php echo $allocate_btn_link; ?>">
                                                <button class="btn <?php echo $allocate_btn_class; ?> allocate_btn" type="button" <?php echo $allocate_btn_disabled; ?>>Allocate</button>
                                            </a>

                                            <a href="javascript:void();">
                                                <button class="btn btn-danger edit_btn" type="button">Edit</button>
                                            </a>

                                            <!-- edit agency payments lightbox -->
                                            <a href="javascript:;" id="agen_pay_det_fb_link<?php echo $index; ?>" class="fb_trigger agen_pay_det_fb_link" data-fancybox data-src="#agen_pay_det_fb_div">Trigger the fancybox</a>							                                            
                                            <input type="hidden" class="main_agen_pay_id" value="<?php echo $agen_pay_row->agency_payments_id; ?>" />
                                            <input type="hidden" class="main_reference" value="<?php echo $agen_pay_row->reference; ?>" />
                                            
										</td>
                                    </tr>

                                <?php       
                                $chckCounter++;
                                } 
                                
                                if( $agen_pay_hide_tol != true ){ ?>

                                    <tr>
                                        <td colspan="4"><b>TOTAL</b></td>
                                        <td>$<?php echo $agency_pay_amnt_sum; ?></td>
                                        <td>$<?php echo $agency_pay_alloc_sum; ?></td>
                                        <td>$<?php echo $agency_pay_rem_sum; ?></td>
                                        <td colspan="3"></td>
                                    </tr>

                                <?php
                                }
                            }else{ ?>
                                <tr>
                                    <td colspan="9">There are no results for the above search</td>
                                </tr>
                            <?php
                            }
                        ?>
					</tbody>

				</table>
            </div>
            

        <?php
        if( $agen_pay_hide_pagi != true ){ ?>
            <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $agency_pay_pagination_link; ?></nav>
            <div id="pagi_count" class="pagi_count text-center"><?php echo $agency_pay_pagination_count; ?></div>
        <?php
        }
        ?>		
			

		</div>
    </section>

    <!-- edit agency payments lightbox content -->
    <div id="agen_pay_det_fb_div" class="agen_pay_det_fb_div fancybox" style="display:none;">
        --- edit agency payments ajax content here ---
    </div>
    
    <link rel="stylesheet" href="/inc/css/edit_agency_payments.css">
    <script src="/inc/js/edit_agency_payments.js"></script>