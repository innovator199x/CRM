<?php 
// adjust email body here, default is 700px
$email_body_width_fin = ( $email_body_width != '' )?$email_body_width:'700px'; 
?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <title><?php echo $title; ?></title>
    <!--[if !mso]><!-- -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
    body{
        font-family:arial;
        font-size:14px;
    }
    hr{
        display: block;
        height: 1px;
        border: 0;
        border-top: 1px solid #ccc;
        margin: 1em 0;
        padding: 0; 
    }
    p{
        font-weight:normal;
        font-size:14px;
    }
    h3{margin-bottom:5px;margin-top:24px;}
        #outlook a {
            padding: 0;
        }

        .ReadMsgBody {
            width: 100%;
        }

        .ExternalClass {
            width: 100%;
        }

        .ExternalClass * {
            line-height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            font-size:14px;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        p {
            display: block;
            margin: 13px 0;
        }
    </style>
    <!--[if !mso]><!-->
    <style type="text/css">
        @media only screen and (max-width:480px) {
            @-ms-viewport {
                width: 320px;
            }
            @viewport {
                width: 320px;
            }
        }
    </style>
    <!--<![endif]-->
    <!--[if mso]>
<xml>
  <o:OfficeDocumentSettings>
    <o:AllowPNG/>
    <o:PixelsPerInch>96</o:PixelsPerInch>
  </o:OfficeDocumentSettings>
</xml>
<![endif]-->
    <!--[if lte mso 11]>
<style type="text/css">
  .outlook-group-fix {
    width:100% !important;
  }
</style>
<![endif]-->
    <style type="text/css">
        @media only screen and (min-width:480px) {
            .mj-column-per-100 {
                width: 100% !important;
            }
        }
    </style>
</head>

<body style="background: #eceff4;">

    <div class="mj-container" style="background-color:#eceff4;">
        <!--[if mso | IE]>
      <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="700" align="center" style="width:700px;">
        <tr>
          <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->
        <div style="margin:0px auto;max-width:<?php echo $email_body_width_fin; ?>;">
            <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;" align="center" border="0">
                <tbody>
                    <tr>
                        <td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:20px 0px;padding-bottom:24px;padding-top:0px;"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--[if mso | IE]>
      </td></tr></table>
      <![endif]-->
        <!--[if mso | IE]>
      <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="700" align="center" style="width:700px;">
        <tr>
          <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->
        <div style="margin:0px auto;max-width:<?php echo $email_body_width_fin; ?>;background:#d8e2e7;">
            <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;background:#d8e2e7;" align="center" border="0">
                <tbody>
                    <tr>
                        <td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:1px;">
                            <!--[if mso | IE]>
      <table role="presentation" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td style="vertical-align:top;width:700px;">
      <![endif]-->
                            <div class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:13px;text-align:left;width:100%;">
                                <table role="presentation" cellpadding="0" cellspacing="0" style="background:white;" width="100%" border="0">
                                    <tbody>
                                        <tr>
                                            <td style="padding:2px;">
                                                <table style="width: 100%;">
                                                    <tr>
                                                        <td style="padding: 25px 25px;text-align:left;background:#fff;">
                                                        <?php 
                                                        if ($this->config->item('country') == 1) {
                                                            $link = 'http://sats.com.au/';
                                                        } else {
                                                            $link = 'https://sats.co.nz/';
                                                        }
                                                        ?>
                                                            <a href="<?php echo $link;?>">  
																<img  style="width: 230px;" src="<?php echo base_url('/images/logo_login.png'); ?>" alt="Smoke Alarm Testing Services">
															</a>
                                                        </td>
                                                        <td style="padding: 25px 25px;text-align:right;background:#fff;">
                                                           
														<strong style="font-size:22px; color:#b4151b;">
                                                            <?php
																// get country data
																$ctry_sql = $this->gherxlib->get_country_data(); 
																$ctry_row = $ctry_sql->row();
																//echo $ctry_row->agent_number;

                                                                if($show_tenant_number==true){
                                                                    echo $tenant_number;
                                                                }else{
                                                                    echo $ctry_row->agent_number;
                                                                }						
                                                                ?>
														</strong>
                                                     
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>


                                        
                                        <tr>
                                             <td style="word-wrap:break-word;padding:10px 30px 15px;" align="left">