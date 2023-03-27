<style>
    .preferences_list_box table td{
        padding:11px 30px 10px 7px;
    }
    .preferences_list_box .radio{
        margin-bottom: 0px;
    }
    .colorItGreen{
        color: #5dca73;
    }
    .colorItRed{
        color: #fb6067;
    }
    .jshowIt{
        display:block;
    }
    .jhideIt {
        display: none;
    }
</style>
<div class="text-left preferences_div">

    <p><strong>Agency Preferences</strong></p>

    <div class="preferences_list_box agency_pref_tab_content">
        <table class="table_no_border">
            <tr>
                <td>Attach invoices to emails?</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="send_emails" id="email_invoice_cert_1" value="1" <?php echo $row['send_emails'] == 1 ? 'checked' : ''; ?>>
                        <label for="email_invoice_cert_1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="send_emails" id="email_invoice_cert_2" value="0" <?php echo $row['send_emails'] == 0 ? 'checked' : ''; ?>>
                        <label for="email_invoice_cert_2">NO </label>
                    </div>
                    <input type="hidden" name="og_send_emails" value="<?php echo $row['send_emails']; ?>">
                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['send_emails']==1)?'jshowIt':'jhideIt'; ?>">Agency emails will include an attached PDF invoice and a link</div>
                    <div class="colorItRed <?php echo ($row['send_emails']==0)?'jshowIt':'jhideIt'; ?>">Agency emails will only include a hyperlink to the invoice</div>
                </td>
            </tr>

             <tr>
                <td>Send Combined Certificate and Invoice</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="send_combined_invoice" id="send_combined_invoice1" value="1" <?php echo $row['send_combined_invoice'] == 1 ? 'checked' : ''; ?>>
                        <label for="send_combined_invoice1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="send_combined_invoice" id="send_combined_invoice2" value="0" <?php echo $row['send_combined_invoice'] == 0 ? 'checked' : ''; ?>>
                        <label for="send_combined_invoice2">NO </label>
                    </div>
                    <input type="hidden" name="og_send_combined_invoice" value="<?php echo $row['send_combined_invoice']; ?>">
                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['send_combined_invoice']==1)?'jshowIt':'jhideIt'; ?>">Agency Receives a Combined Invoice and Certificate</div>
					<div class="colorItRed <?php echo ($row['send_combined_invoice']==0)?'jshowIt':'jhideIt'; ?>">Agency Receives a separate Invoice and Certificate</div>
                </td>
            </tr>

             <tr>
                <td>Entry Notice issued by Email</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="send_entry_notice" id="send_entry_notice1" value="1" <?php echo $row['send_entry_notice'] == 1 ? 'checked' : ''; ?>>
                        <label for="send_entry_notice1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="send_entry_notice" id="send_entry_notice2" value="0" <?php echo $row['send_entry_notice'] == 0 ? 'checked' : ''; ?>>
                        <label for="send_entry_notice2">NO </label>
                    </div>
                    <input type="hidden" name="og_send_entry_notice" value="<?php echo $row['send_entry_notice']; ?>">
                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['send_entry_notice']==1)?'jshowIt':'jhideIt'; ?>">Entry Notices by Email allowed</div>
					<div class="colorItRed <?php echo ($row['send_entry_notice']==0)?'jshowIt':'jhideIt'; ?>">Entry Notices MUST be Posted</div>
                </td>
            </tr>

              <tr>
                <td>Work Order Required For All Jobs?</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="work_order_required" id="work_order_required1" value="1" <?php echo $row['require_work_order'] == 1 ? 'checked' : ''; ?>>
                        <label for="work_order_required1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="work_order_required" id="work_order_required2" value="0" <?php echo $row['require_work_order'] == 0 ? 'checked' : ''; ?>>
                        <label for="work_order_required2">NO </label>
                    </div>
                    <input type="hidden" name="og_work_order_required" value="<?php echo $row['require_work_order']; ?>">
                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['require_work_order']==1)?'jshowIt':'jhideIt'; ?>">Work order number required for all jobs</div>
					<div class="colorItRed <?php echo ($row['require_work_order']==0)?'jshowIt':'jhideIt'; ?>">NO work order number required</div>
                </td>
            </tr>

              <tr>
                <td>Individual Property Managers Receive Certificate & Invoice?</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="allow_indiv_pm_email_cc" id="allow_indiv_pm_email_cc1" value="1" <?php echo $row['allow_indiv_pm_email_cc'] == 1 ? 'checked' : ''; ?>>
                        <label for="allow_indiv_pm_email_cc1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="allow_indiv_pm_email_cc" id="allow_indiv_pm_email_cc2" value="0" <?php echo $row['allow_indiv_pm_email_cc'] == 0 ? 'checked' : ''; ?>>
                        <label for="allow_indiv_pm_email_cc2">NO </label>
                    </div>
                    <input type="hidden" name="og_allow_indiv_pm_email_cc" value="<?php echo $row['allow_indiv_pm_email_cc']; ?>">
                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['allow_indiv_pm_email_cc']==1)?'jshowIt':'jhideIt'; ?>">PMs get additional copy of Invoice and Certificate</div>
					<div class="colorItRed <?php echo ($row['allow_indiv_pm_email_cc']==0)?'jshowIt':'jhideIt'; ?>">NO additional Certificate and Invoice sent</div>
                </td>
            </tr>

              <tr>
                <td>Auto Renew Yearly Maintenance Properties</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="auto_renew" id="auto_renew1" value="1" <?php echo $row['auto_renew'] == 1 ? 'checked' : ''; ?>>
                        <label for="auto_renew1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="auto_renew" id="auto_renew2" value="0" <?php echo $row['auto_renew'] == 0 ? 'checked' : ''; ?>>
                        <label for="auto_renew2">NO </label>
                    </div>
                    <input type="hidden" name="og_auto_renew" value="<?php echo $row['auto_renew']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['auto_renew']==1)?'jshowIt':'jhideIt'; ?>">Agency Allows Auto Renew</div>
					<div class="colorItRed <?php echo ($row['auto_renew']==0)?'jshowIt':'jhideIt'; ?>">Agency DOESN'T allow Auto Renew</div>
                </td>
            </tr>

              <tr>
                <td>Key Access Allowed?</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="key_allowed" id="key_allowed1" value="1" <?php echo $row['key_allowed'] == 1 ? 'checked' : ''; ?>>
                        <label for="key_allowed1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="key_allowed" id="key_allowed2" value="0" <?php echo $row['key_allowed'] == 0 ? 'checked' : ''; ?>>
                        <label for="key_allowed2">NO </label>
                    </div>
                    <input type="hidden" name="og_key_allowed" value="<?php echo $row['key_allowed']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['key_allowed']==1)?'jshowIt':'jhideIt'; ?>">Key access allowed</div>
					<div class="colorItRed <?php echo ($row['key_allowed']==0)?'jshowIt':'jhideIt'; ?>">Key access NOT allowed</div>
                </td>
            </tr>

              <tr>
                <td>Tenant Key Email Required?</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="key_email_req" id="key_email_req1" value="1" <?php echo $row['key_email_req'] == 1 ? 'checked' : ''; ?>>
                        <label for="key_email_req1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="key_email_req" id="key_email_req2" value="0" <?php echo $row['key_email_req'] == 0 ? 'checked' : ''; ?>>
                        <label for="key_email_req2">NO </label>
                    </div>
                    <input type="hidden" name="og_key_email_req" value="<?php echo $row['key_email_req']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['key_email_req']==1)?'jshowIt':'jhideIt'; ?>">Agency wants email from Tenant to approve keys</div>
					<div class="colorItRed <?php echo ($row['key_email_req']==0)?'jshowIt':'jhideIt'; ?>">No email from Tenant required</div>
                </td>
            </tr>

              <tr>
                <td>Allow Doorknocks?</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="allow_dk" id="allow_dk1" value="1" <?php echo $row['allow_dk'] == 1 ? 'checked' : ''; ?>>
                        <label for="allow_dk1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="allow_dk" id="allow_dk2" value="0" <?php echo $row['allow_dk'] == 0 ? 'checked' : ''; ?>>
                        <label for="allow_dk2">NO </label>
                    </div>
                    <input type="hidden" name="og_allow_dk" value="<?php echo $row['allow_dk']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['allow_dk']==1)?'jshowIt':'jhideIt'; ?>">Door Knocks allowed</div>
					<div class="colorItRed <?php echo ($row['allow_dk']==0)?'jshowIt':'jhideIt'; ?>">NO Door Knocks allowed</div>
                </td>
            </tr>

              <tr>
                <td>Allow Entry Notice?</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="allow_en" id="allow_en1" value="1" <?php echo $row['allow_en'] == 1 ? 'checked' : ''; ?>>
                        <label for="allow_en1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="allow_en" id="allow_en2" value="0" <?php echo $row['allow_en'] == 0 ? 'checked' : ''; ?>>
                        <label for="allow_en2">NO </label>
                        &nbsp;&nbsp;
                        <input type='radio' id='allow_en3' name='allow_en' value='-1' <?php echo $row['allow_en'] == -1 ? 'checked' : ''; ?>>
                        <label for="allow_en3">NR </label>
                        &nbsp;&nbsp;
                        <input type='radio' id='allow_en4' name='allow_en' value='2' <?php echo $row['allow_en'] == 2 ? 'checked' : ''; ?>>
                        <label for="allow_en4">ONLY</label>
                    </div>
                    <input type="hidden" name="og_allow_en" value="<?php echo $row['allow_en']; ?>">
                    
                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['allow_en']==1)?'jshowIt':'jhideIt'; ?>">Entry Notices are Allowed</div>
                    <div class="colorItRed <?php echo ($row['allow_en']==0)?'jshowIt':'jhideIt'; ?>">NO Entry Notices are Allowed</div>
                    <div class="colorItRed pref_nr <?php echo ($row['allow_en']==-1)?'jshowIt':'jhideIt'; ?>">No Response in regards to Entry Notice</div>
                    <div class="colorItGreen pref_only <?php echo ($row['allow_en']==2)?'jshowIt':'jhideIt'; ?>">Every job marked 'Booked' on VJD will be sent an entry notice for each update.</div>
                </td>
            </tr>

              <tr>
                <td>All New Jobs Emailed to Agency?</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="new_job_email_to_agent" id="new_job_email_to_agent1" value="1" <?php echo $row['new_job_email_to_agent'] == 1 ? 'checked' : ''; ?>>
                        <label for="new_job_email_to_agent1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="new_job_email_to_agent" id="new_job_email_to_agent2" value="0" <?php echo $row['new_job_email_to_agent'] == 0 ? 'checked' : ''; ?>>
                        <label for="new_job_email_to_agent2">NO </label>
                    </div>
                    <input type="hidden" name="og_new_job_email_to_agent" value="<?php echo $row['new_job_email_to_agent']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['new_job_email_to_agent']==1)?'jshowIt':'jhideIt'; ?>">Agency Receives email for ALL new properties</div>
                    <div class="colorItRed <?php echo ($row['new_job_email_to_agent']==0)?'jshowIt':'jhideIt'; ?>">Agency DOESN'T Receive email for ALL new properties</div>
                </td>
            </tr>

              <tr>
                <td>Display BPAY on Invoices?</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="display_bpay" id="display_bpay1" value="1" <?php echo $row['display_bpay'] == 1 ? 'checked' : ''; ?>>
                        <label for="display_bpay1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="display_bpay" id="display_bpay2" value="0" <?php echo $row['display_bpay'] == 0 ? 'checked' : ''; ?>>
                        <label for="display_bpay2">NO </label>
                    </div>
                    <input type="hidden" name="og_display_bpay" value="<?php echo $row['display_bpay']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['display_bpay']==1)?'jshowIt':'jhideIt'; ?>">BPAY displayed on invoices</div>
					<div class="colorItRed <?php echo ($row['display_bpay']==0)?'jshowIt':'jhideIt'; ?>">BPAY not displayed on invoices</div>
                </td>
            </tr>

              <tr>
                <td>Subscription Billing?</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="allow_upfront_billing" id="allow_upfront_billing1" value="1" <?php echo $row['allow_upfront_billing'] == 1 ? 'checked' : ''; ?>>
                        <label for="allow_upfront_billing1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="allow_upfront_billing" id="allow_upfront_billing2" value="0" <?php echo $row['allow_upfront_billing'] == 0 ? 'checked' : ''; ?>>
                        <label for="allow_upfront_billing2">NO </label>
                    </div>
                    <input type="hidden" name="og_allow_upfront_billing" value="<?php echo $row['allow_upfront_billing']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['allow_upfront_billing']==1)?'jshowIt':'jhideIt'; ?>">Agency Allows up front billing</div>
					<div class="colorItRed <?php echo ($row['allow_upfront_billing']==0)?'jshowIt':'jhideIt'; ?>">Agency Does not Allow up front billing</div>
                </td>
            </tr>

              <tr>
                <td>Invoice PM'S Only?</td>
                <td>
                    <div class="radio">
                        <input disabled="true" type="radio" name="invoice_pm_only" id="invoice_pm_only1" value="1" <?php echo $row['invoice_pm_only'] == 1 ? 'checked' : ''; ?>>
                        <label for="invoice_pm_only1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="invoice_pm_only" id="invoice_pm_only2" value="0" <?php echo $row['invoice_pm_only'] == 0 ? 'checked' : ''; ?>>
                        <label for="invoice_pm_only2">NO </label>
                    </div>
                    <input type="hidden" name="og_invoice_pm_only" value="<?php echo $row['invoice_pm_only']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['invoice_pm_only']==1)?'jshowIt':'jhideIt'; ?>">Invoice issued only to individual PM not to agency accounts email</div>
					<div class="colorItRed <?php echo ($row['invoice_pm_only']==0)?'jshowIt':'jhideIt'; ?>">Agency Does not Allow Invoice PM'S Only</div>
                </td>
            </tr>

              <tr>
                <td>Electrician Only</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="electrician_only" id="electrician_only1" value="1" <?php echo $row['electrician_only'] == 1 ? 'checked' : ''; ?>>
                        <label for="electrician_only1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="electrician_only" id="electrician_only2" value="0" <?php echo $row['electrician_only'] == 0 ? 'checked' : ''; ?>>
                        <label for="electrician_only2">NO </label>
                    </div>
                    <input type="hidden" name="og_electrician_only" value="<?php echo $row['electrician_only']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['electrician_only']==1)?'jshowIt':'jhideIt'; ?>">Electricians ONLY to attend</div>
					<div class="colorItRed <?php echo ($row['electrician_only']==0)?'jshowIt':'jhideIt'; ?>">Both Techs and Electricians can attend</div>
                </td>
            </tr>

              <tr>
                <td>Send copy of EN to Agency</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="send_en_to_agency" id="send_en_to_agency1" value="1" <?php echo $row['send_en_to_agency'] == 1 ? 'checked' : ''; ?>>
                        <label for="send_en_to_agency1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="send_en_to_agency" id="send_en_to_agency2" value="0" <?php echo $row['send_en_to_agency'] == 0 ? 'checked' : ''; ?>>
                        <label for="send_en_to_agency2">NO </label>
                    </div>
                    <input type="hidden" name="og_send_en_to_agency" value="<?php echo $row['send_en_to_agency']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['send_en_to_agency']==1)?'jshowIt':'jhideIt'; ?>">Agency receives a copy of Entry Notices</div>
					<div class="colorItRed <?php echo ($row['send_en_to_agency']==0)?'jshowIt':'jhideIt'; ?>">Agency does not receive a copy of Entry Notices</div>
                </td>
            </tr>

            <tr>
                <td>Individual Property Managers Receive EN?</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="en_to_pm" id="en_to_pm1" value="1" <?php echo $row['en_to_pm'] == 1 ? 'checked' : ''; ?>>
                        <label for="en_to_pm1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="en_to_pm" id="en_to_pm2" value="0" <?php echo $row['en_to_pm'] == 0 ? 'checked' : ''; ?>>
                        <label for="en_to_pm2">NO </label>
                    </div>
                    <input type="hidden" name="og_en_to_pm" value="<?php echo $row['en_to_pm']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['en_to_pm']==1)?'jshowIt':'jhideIt'; ?>">EN sent to PM only</div>
					<div class="colorItRed <?php echo ($row['en_to_pm']==0)?'jshowIt':'jhideIt'; ?>">EN sent to Agency only</div>
                </td>
            </tr>

            <tr>
                <td>Show accounts reports?</td>
                <td>
                    <div class="radio">
                        <input disabled="disabled" type="radio" name="accounts_reports" id="accounts_reports1" value="1" <?php echo $row['accounts_reports'] == 1 ? 'checked' : ''; ?>>
                        <label for="accounts_reports1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="accounts_reports" id="accounts_reports2" value="0" <?php echo $row['accounts_reports'] == 0 ? 'checked' : ''; ?>>
                        <label for="accounts_reports2">NO </label>
                    </div>
                    <input type="hidden" name="og_accounts_reports" value="<?php echo $row['accounts_reports']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['accounts_reports']==1)?'jshowIt':'jhideIt'; ?>">Accounts reports, including statements will be visible</div>
					<div class="colorItRed <?php echo ($row['accounts_reports']==0)?'jshowIt':'jhideIt'; ?>">No accounting information will be visible to the agency</div>
                </td>
            </tr>

            <tr>
                <td>Exclude $0 invoices</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="exclude_free_invoices" id="exclude_free_invoices1" value="1" <?php echo $row['exclude_free_invoices'] == 1 ? 'checked' : ''; ?>>
                        <label for="exclude_free_invoices1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="exclude_free_invoices" id="exclude_free_invoices2" value="0" <?php echo $row['exclude_free_invoices'] == 0 ? 'checked' : ''; ?>>
                        <label for="exclude_free_invoices2">NO </label>
                    </div>
                    <input type="hidden" name="og_exclude_free_invoices" value="<?php echo $row['exclude_free_invoices']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['exclude_free_invoices']==1)?'jshowIt':'jhideIt'; ?>">This agency will only receive invoices with a positive balance.</div>
					<div class="colorItRed <?php echo ($row['exclude_free_invoices']==0)?'jshowIt':'jhideIt'; ?>">This agency will receive all invoices.</div>
                </td>
            </tr>

            <tr>
                <td>Send 48 hour key email</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="send_48_hr_key" id="send_48_hr_key1" value="1" <?php echo $row['send_48_hr_key'] == 1 ? 'checked' : ''; ?>>
                        <label for="send_48_hr_key1">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="send_48_hr_key" id="send_48_hr_key2" value="0" <?php echo $row['send_48_hr_key'] == 0 ? 'checked' : ''; ?>>
                        <label for="send_48_hr_key2">NO </label>
                    </div>
                    <input type="hidden" name="og_send_48_hr_key" value="<?php echo $row['send_48_hr_key']; ?>">

                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['send_48_hr_key']==1)?'jshowIt':'jhideIt'; ?>">This agency will be notified both 24 and 48 hours in advance of any keys required.</div>
					<div class="colorItRed <?php echo ($row['send_48_hr_key']==0)?'jshowIt':'jhideIt'; ?>">This agency will only be notified 24 hours in advance of any keys required.</div>
                </td>
            </tr>
            <tr>
                <td>Address Invoice to Agency?</td>
                <td>
                    <div class="radio">
                        <input type="radio" name="add_inv_to_agen" id="add_inv_to_agen_yes" value="1" <?php echo $row['add_inv_to_agen'] == 1 ? 'checked' : ''; ?>>
                        <label for="add_inv_to_agen_yes">YES </label>
                        &nbsp;&nbsp;
                        <input type="radio" name="add_inv_to_agen" id="add_inv_to_agen_no" value="0" <?php echo ( is_numeric($row['add_inv_to_agen']) && $row['add_inv_to_agen'] == 0 ) ? 'checked' : ''; ?>>
                        <label for="add_inv_to_agen_no">NO </label>
                        &nbsp;&nbsp;
                        <input type='radio' name='add_inv_to_agen' id='add_inv_to_agen_nr'  value='' <?php echo $row['add_inv_to_agen'] == '' ? 'checked' : ''; ?>>
                        <label for="add_inv_to_agen_nr">NR </label>
                    </div>                                                
                </td>
                <td>
                    <div class="colorItGreen <?php echo ($row['add_inv_to_agen']==1)?'jshowIt':'jhideIt'; ?>">The invoice will show 'ATTN: [agency_name]'</div>
                    <div class="colorItRed <?php echo ( is_numeric($row['add_inv_to_agen']) && $row['add_inv_to_agen']==0 )?'jshowIt':'jhideIt'; ?>">The invoice will show 'ATTN: [landlord_firstname landlord_lastname]'</div>
                    <div class="colorItRed pref_nr <?php echo ($row['add_inv_to_agen']=='')?'jshowIt':'jhideIt'; ?>">The invoice will show 'ATTN: CARE OF THE OWNER'</div>
                </td>
            </tr>
            <?php            
            // display preference using new table
            // get agency preference
            $agency_pref_sql = $this->db->query("
            SELECT *
            FROM `agency_preference`
            WHERE `active` = 1
            ");

            foreach( $agency_pref_sql->result() as $agency_pref_row ){ 
                
                // get selected preference
                $agency_pref_sel_sql = $this->db->query("
                SELECT *
                FROM `agency_preference_selected`
                WHERE `agency_id` = {$row['agency_id']}
                AND `agency_pref_id` = {$agency_pref_row->id}
                ");
                $agency_pref_sel_row = $agency_pref_sel_sql->row();

                ?>

                <tr>
                    <td><?php echo $agency_pref_row->pref_text; ?></td>
                    <td>
                        <div class="radio">
                            <input type="radio" name="agency_pref[<?php echo $agency_pref_row->id; ?>]" id="agency_pref_<?php echo $agency_pref_row->id; ?>_yes" value="1" <?php echo ( $agency_pref_sel_row->sel_pref_val == 1 )?'checked':null; ?>>
                            <label for="agency_pref_<?php echo $agency_pref_row->id; ?>_yes">YES </label>
                            &nbsp;&nbsp;
                            <input type="radio" name="agency_pref[<?php echo $agency_pref_row->id; ?>]" id="agency_pref_<?php echo $agency_pref_row->id; ?>_no" value="0" <?php echo ( $agency_pref_sel_row->sel_pref_val == 0 && is_numeric($agency_pref_sel_row->sel_pref_val) )?'checked':null; ?>>
                            <label for="agency_pref_<?php echo $agency_pref_row->id; ?>_no">NO </label>
                        </div>                        
                    </td>
                    <td>
                        <div class="colorItGreen <?php echo ( $agency_pref_sel_row->sel_pref_val == 1 )?'jshowIt':'jhideIt'; ?>"><?php echo $agency_pref_row->yes_txt; ?></div>
                        <div class="colorItRed <?php echo ( $agency_pref_sel_row->sel_pref_val == 0 && is_numeric($agency_pref_sel_row->sel_pref_val) )?'jshowIt':'jhideIt'; ?>"><?php echo $agency_pref_row->no_txt; ?></div>
                    </td>
                </tr>

            <?php
            }
            ?>       
            
            <?php 
                if( COUNTRY == 1 && $row['state']=='QLD' ){ ## Show AU and QLD agency only 

                    // get marker id
                    $query_marker = $this->db->query("
                    SELECT *
                    FROM `agency_markers`
                    WHERE `agency_id` = {$row['agency_id']} AND marker_id = 1
                    ");
                    $data_marker = $query_marker->row();
                    $marker_id = $data_marker->marker_id;

                    // get the marker definition
                    $query_definition = $this->db->query("
                    SELECT *
                    FROM `agency_marker_id_definition`
                    WHERE id = 1
                    ");
                    $data_definition = $query_definition->row();
                    $marker_definition = $data_definition->marker_definition;
                    $yes = $data_definition->yes;
                    $no = $data_definition->no;

                    if(  $marker_id!="" && $marker_id == 1 ){ # do not hide (Hide 'Not 2022 Compliant' for short term rentals?)
                        // if(  $row['marker_id']!="" && $row['marker_id'] == 1 ){ # do not hide (Hide 'Not 2022 Compliant' for short term rentals?)
                        $marker_id_stat = 1;
                    }else{
                        $marker_id_stat = 0; ##hide
                    }
            ?>

                <tr data-markerid="<?php echo $marker_id ?>" data-markerIDStat="<?php echo $marker_id_stat; ?>">
                    <td><?=$marker_definition?></td>
                    <td>
                        <div class="radio">
                            <input type="radio" name="hide_2022_compliant" id="hide_2022_compliant_1" value="1" <?php echo $marker_id_stat<=0 ? 'checked' : ''; ?>>
                            <label for="hide_2022_compliant_1">YES </label>
                            &nbsp;&nbsp;
                            <input type="radio" name="hide_2022_compliant" id="hide_2022_compliant_2" value="0" <?php echo $marker_id_stat==1 ? 'checked' : ''; ?>>
                            <label for="hide_2022_compliant_2">NO </label>
                        </div>
                        <input type="hidden" name="og_hide_2022_compliant" value="<?php echo ($marker_id=="")?1:0; ?>">

                    </td>
                    <td>
                        <div class="colorItGreen <?php echo ($marker_id_stat<=0)?'jshowIt':'jhideIt'; ?>"><?=$yes?></div>
                        <div class="colorItRed <?php echo ($marker_id_stat==1)?'jshowIt':'jhideIt'; ?>"><?=$no; ?></div>
                    </td>
                </tr>

            <?php } ?>


            <?php
            if( $row['state'] != 'QLD' ){

            // get renewal interval and start date offset
            // also update cron_model, create_renewals function, if you update these logic

            $renewal_interval_default = 12; // months
            $renewal_start_offset_default = $this->config->item('renewal_start_offset_default'); // days 
            $hume_house_agency_id = 1598; // Hume Housing

            if( $this->config->item('country') == 1 ){ // AU only

                if( $row['agency_id'] == $hume_house_agency_id ){ // if Hume Agency, renewal interval in 9 months

                    $renewal_interval_default = 9; 

                }else if( $row['state'] == 'NSW' ){ 

                    // if NSW, start 15 days earlier from regular cron(15 day earlier), so add more 15 days to the offset so it now becomes 30
                    $renewal_start_offset_default =  $this->config->item('renewal_start_offset_nsw'); 

                }

            }
            
            // overrides default if values are set
            $renewal_interval = ( $row['renewal_interval'] > 0 )?$row['renewal_interval']:$renewal_interval_default;  
            $renewal_start_offset = ( is_numeric($row['renewal_start_offset']) )?$row['renewal_start_offset']:$renewal_start_offset_default;
            ?>
            <tr>
                <td>Renewal Interval (in Months)</td>
                <td>
                    <input 
                        type="text" 
                        name="renewal_interval" 
                        id='renewal_interval' 
                        class="form-control renewal_interval" 
                        value="<?php echo $row['renewal_interval']; ?>" 
                        placeholder="Default is <?php echo $renewal_interval_default; ?>" 
                        readonly
                    />
                </td>
                <td>
                    <div class="colorItGreen">This interval means that CRM will get YMs completed <?php echo $renewal_interval; ?> months ago, and create a new YM for each property discovered.</div>
                </td>
            </tr>

            <?php
            // if logged user is global
            $staff_sql_str = "
            SELECT COUNT(`StaffID`) AS sa_count
            FROM `staff_accounts`
            WHERE `StaffID` = {$this->session->staff_id}
            AND `ClassID` = 2
            ";
            $staff_sql = $this->db->query($staff_sql_str);
            $staff_row = $staff_sql->row();

            $logged_user_is_global = ( $staff_row->sa_count > 0 )?true:false;
            ?>
            <tr>
                <td>Renewal Start Offset (in days)</td>
                <td>
                    <input 
                        type="text" 
                        name="renewal_start_offset" 
                        id='renewal_start_offset' 
                        class="form-control renewal_start_offset" 
                        value="<?php echo $row['renewal_start_offset']; ?>" 
                        placeholder="Default is <?php echo $renewal_start_offset_default; ?>"
                        <?php echo ( $logged_user_is_global == false )?'readonly':null; // only g   lobal user can edit this ?>
                    />
                </td>
                <td>
                    <div class="colorItGreen">The start date for the created YM will be <?php echo $renewal_start_offset; ?> days prior to last year's completed YM for each job.</div>
                </td>
            </tr>          
            
            <?php 
            } 
            ?>
            
        </table>

        <div class="vad_cta_box form-group text-right">
            <input type="hidden" name="agency_state" value="<?php echo $row['state']; ?>" />
            <button class="btn">Update Details</button>
        </div>
    </div>

</div>


<script>
$(document).ready(function(){

        // preferences text script
	    jQuery(".agency_pref_tab_content input[type='radio']").click(function(){

            var obj = jQuery(this);
            var pref_radio = obj.val();

            //console.log(pref_radio);

            if( pref_radio == 1 ){
                obj.parents("tr:first").find(".colorItGreen").show();
                obj.parents("tr:first").find(".colorItRed").hide();
                obj.parents("tr:first").find(".pref_nr").hide();
                obj.parents("tr:first").find(".pref_only").hide();
            }else if( parseInt(pref_radio) == 0 ){
                obj.parents("tr:first").find(".colorItGreen").hide();
                obj.parents("tr:first").find(".colorItRed").show();
                obj.parents("tr:first").find(".pref_nr").hide();
                obj.parents("tr:first").find(".pref_only").hide();
            }else if( pref_radio == -1 || pref_radio == '' ){
                obj.parents("tr:first").find(".colorItGreen").hide();
                obj.parents("tr:first").find(".colorItRed").hide();
                obj.parents("tr:first").find(".pref_nr").show();
                obj.parents("tr:first").find(".pref_only").hide();
            }else if( pref_radio == 2 ){
                obj.parents("tr:first").find(".colorItGreen").hide();
                obj.parents("tr:first").find(".colorItRed").hide();
                obj.parents("tr:first").find(".pref_nr").hide();
                obj.parents("tr:first").find(".pref_only").show();
            } 

        });

})
</script>