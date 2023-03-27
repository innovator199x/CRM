<?php $this->load->view('emails/template/email_header.php') ?>


<!-- CONTENT START HERE -->

    <?php 
      $is_agency_name_updated = false;
      $is_legal_name_updated = false;
      $is_abn_updated = false;
    ?>

    <p><?php echo $orig_agency_name; ?> had updated their details</p>

    <?php 

       

            // agency name
            if($agency_name!=""){
                if( $orig_agency_name != $agency_name ){

                    $message .= "<p>Agency Name has been updated in the CRM:</p> 
                    
                    From <br /><strong>{$orig_agency_name}</strong> <br />
                    To <br /><strong>{$agency_name}</strong> <br/><br />";

                    $is_agency_name_updated = true;

                }
            }

            // legal name
            if( $orig_legal_name != $legal_name ){

                $message .= "<p>Legal Name has been updated in the CRM:</p> 
                
                From <br /><strong>{$orig_legal_name}</strong> <br />
                To <br /><strong>{$legal_name}</strong> <br/><br />";

                $is_legal_name_updated = true;

            }

            // ABN number
            if( $orig_abn != $abn ){

                $message .= "<p>ABN number has been updated in the CRM:</p> 
                
                From <br /><strong>{$orig_abn}</strong> <br />
                To <br /><strong>{$abn}</strong> <br/><br />";

                $is_abn_updated = true;

            }

            if( $is_agency_name_updated == true || $is_legal_name_updated == true || $is_abn_updated == true  ){
                $message .= "Please update the records in MYOB<br /><br />";
            }

            // salesrep
            if( $orig_salesrep != $salesrep ){ 
                $message .= "<p>Salesrep has been updated in the CRM:</p> 
                From: <strong>{$orig_salesrep_name}</strong> <br />
                To: <strong>{$new_salesrep_name}</strong> <br/><br />";
            }


            if( $orig_agency_emails != $agency_emails ){
		
                $message .= '<p>Agency Emails has been updated:</p>
                From: <br />';
                $orig_agency_emails_split = explode("\n",trim($orig_agency_emails));
                $message .= '<ul>';
                foreach( $orig_agency_emails_split as $email ){
                    $message .= '<li>'.$email.'</li>';
                }
                $message .= '</ul>';
                $message .= '<br />To: <br />';
                $agency_emails_split = explode("\n",trim($agency_emails));
                $message .= '<ul>';
                foreach( $agency_emails_split as $email ){
                    $message .= '<li>'.$email.'</li>';
                }
                $message .= '</ul>';
            }
          
            if( $orig_account_emails != $account_emails ){
            
                $message .= '<p>Account Emails has been updated:</p>
                From: <br />';
                $orig_account_emails_split = explode("\n",trim($orig_account_emails));
                $message .= '<ul>';
                foreach( $orig_account_emails_split as $email ){
                    $message .= '<li>'.$email.'</li>';
                }     
                $message .= '</ul>';
                $message .= '<br />To: <br />';
                $account_emails_split = explode("\n",trim($account_emails));
                $message .= '<ul>';
                foreach( $account_emails_split as $email ){
                    $message .= '<li>'.$email.'</li>';
                }
                $message .= '</ul>';
            }

        echo $message;
    ?>

<!-- CONTENT END HERE -->


<?php $this->load->view('emails/template/email_footer.php') ?>