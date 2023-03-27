<div class="box-typical-body">


    

    <div class="row vad_cta_box form-group text-center">
        <div class="col-md-6 columns text-right">
            <a target="_blank" href="/agency/send_sales_emails/<?php echo $agency_id; ?>" class="btn btn-success">Send Sales Emails</a>
        </div>
        <div class="col-md-6 columns text-left">
            <button class="btn btn-danger btn_update">Update Details</button>
        </div>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function(){

        $('.btn_update').on('click',function(){

            var agency_emails = $('#agency_emails').val();
            var account_emails = $('#account_emails').val();
            
            var error = "";
            var submitcount = 0;
            
            <?php
            // required only on active
            if( $row['status']=='active' ){ ?>
            
                if(agency_emails==""){
                    error += "Agency emails are required\n";
                }
                if(account_emails==""){
                    error += "Account emails is required\n";
                }
               
                //agency email validate white space line
                var agency_email_str_arr = jQuery('#agency_emails').val().split('\n');
                if( jQuery.inArray("", agency_email_str_arr) !== -1 ){
                    error += "Agency emails invalid input\n";
                }

                //agency accounts validaate white space per line
                var account_emails_str_arr = jQuery('#account_emails').val().split('\n');
                if( jQuery.inArray("", account_emails_str_arr) !== -1 ){
                    error += "Account emails invalid input\n";
                }
            
            <?php	
            }
            ?>

            if(error!=""){
                swal('', error, 'error');
                return false;
            }

            if(submitcount==0){
                submitcount++;
                jQuery("#vad_form").submit();
                return false;
            }else{
                swal('','Form submission is in progress','error');
                return false;
            }




        });

    })

</script>