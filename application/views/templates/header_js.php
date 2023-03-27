
<script type="text/javascript">
        
        // phone and mobile input mask
        function phone_mobile_mask(){

            <?php
            if($this->config->item('country')==1){ //AU
            ?>
               // $('.phone-with-code-area-mask-input').mask('00 0000 0000', {placeholder: "__ ____ ____"});
                $('.phone-with-code-area-mask-input').mask('00 0000 0000', {placeholder: "Phone"});

               // $('.tenant_mobile').mask('0000 000 000', {placeholder: "____ ___ ___"});    
                $('.tenant_mobile').mask('0000 000 000', {placeholder: "Mobile"});    

        <?php }else{ //NZ ?>

                //$('.phone-with-code-area-mask-input').mask('00 0000 000', {placeholder: "__ ____ ___"});
                $('.phone-with-code-area-mask-input').mask('00 0000 000', {placeholder: "Phone"});

                //$('.tenant_mobile').mask('0000 000 0000', {placeholder: "____ ___ ____"});    
                $('.tenant_mobile').mask('0000 000 0000', {placeholder: "Mobile"});    

            <?php   } ?>

        }

        function mobile_validation(){
            $('.tenant_mobile').focusout(function(){
                <?php
                    if($this->config->item('country')==1){ ?>
                       var mobile_length = 12;
                   <?php  }else{ ?>
                       var mobile_length = 13;
                    <?php } ?>
            
                if($(this).val().length < 12 && $(this).val().length != 0){
                    $(this).after('<div class="form-tooltip-error" data-error-list="">Format must be 0412 222 222</div>');
                    $(this).parents('.form-group').addClass('error');
                    }else if($(this).val().length <= 12){
                    $(this).next('.form-tooltip-error').remove();
                    }else if($(this).val().length == 0){
                    $(this).next('.form-tooltip-error').remove();
                    }
                });
        }

        function phone_validation(){
            $('.phone-with-code-area-mask-input').focusout(function(){
                if($(this).val().length < 12 && $(this).val().length !=0 ){
                    $(this).after('<div class="form-tooltip-error" data-error-list="">Format must be 02 2222 2222</div>');
                    $(this).parents('.form-group').addClass('error');
                    }else if($(this).val().length >= 12){
                    $(this).next('.form-tooltip-error').remove();
                    }else if($(this).val().length == 0){
                    $(this).next('.form-tooltip-error').remove();
                    }
            });
        }



</script>