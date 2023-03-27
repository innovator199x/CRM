<?php $this->load->view('emails/template/email_header.php') ?>


<!-- CONTENT START HERE -->


<!-- Agency Details -->
<table style="width:100%; border: 1px solid #efefef;margin-bottom:15px;">
    <thead>
        <tr>
            <td colspan="2"style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Agency Details</b></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Agency Name:</td>
            <td><?php echo $agency_name; ?></td>
        </tr>
        <tr style="background-color:#efefef;">
            <td>Legal Name:</td>
            <td><?php echo $legal_name; ?></td>
        </tr>
        <tr>
            <td>ABN Number:</td>
            <td><?php echo $abn_number; ?></td>
        </tr>
        <tr style="background-color:#efefef;">
            <td>Street Number:</td>
            <td><?php echo $street_number; ?></td>
        </tr>
        <tr>
            <td>Street Name:</td>
            <td><?php echo $street_name; ?></td>
        </tr>
        <tr style="background-color:#efefef;">
            <td>Suburb:</td>
            <td><?php echo $suburb; ?></td>
        </tr>
        <tr>
            <td>State:</td>
            <td><?php echo $state; ?></td>
        </tr>
        <tr style="background-color:#efefef;">
            <td>Postcode:</td>
            <td><?php echo $postcode; ?></td>
        </tr>
        <tr>
            <td>Region:</td>
            <td><?php echo $region; ?></td>
        </tr>
        <tr style="background-color:#efefef;">
            <td>Phone:</td>
            <td><?php echo $phone; ?></td>
        </tr>
        <tr>
            <td>Total Properties:</td>
            <td><?php echo $tot_properties; ?></td>
        </tr>
    </tbody>
</table>


<!-- Agency Contact -->
<table style="width:100%; border: 1px solid #efefef;margin-bottom:15px;">
    <thead>
        <tr>
            <td colspan="2"style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Agency Contact</b></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>First Name:</td>
            <td><?php echo $ac_fname; ?></td>
        </tr>
        <tr style="background-color:#efefef;">
            <td>Last Name:</td>
            <td><?php echo $ac_lname; ?></td>
        </tr>
        <tr>
            <td>Phone:</td>
            <td><?php echo $ac_phone; ?></td>
        </tr>
        <tr style="background-color:#efefef;">
            <td>Email:</td>
            <td><?php echo $ac_email; ?></td>
        </tr>
        <tr>
            <td>Accounts Name:</td>
            <td><?php echo $acc_name; ?></td>
        </tr>
        <tr style="background-color:#efefef;">
            <td>Accounts Phone:</td>
            <td><?php echo $acc_phone; ?></td>
        </tr>
    </tbody>
</table>


<!-- Agency Emails -->
<table style="width:100%; border: 1px solid #efefef;margin-bottom:15px;">
    <thead>
        <tr>
            <td colspan="2"style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Agency Emails</b></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Agency Emails:</td>
            <td><?php echo $agency_emails; ?></td>
        </tr>
        <tr style="background-color:#efefef;">
            <td>Accounts Emails:</td>
            <td><?php echo $account_emails; ?></td>
        </tr>
    </tbody>
</table>


<!-- Sales Rep -->
<table style="width:100%; border: 1px solid #efefef;margin-bottom:15px;">
    <thead>
        <tr>
            <td colspan="2"style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Sales Rep</b></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Name:</td>
            <td><?php echo $salesrep; ?></td>
        </tr>
    </tbody>
</table>

<p>Agency added by <?php echo $staff; ?></p>

<p>Kind Regards,<br />SATS Team</p>

<!-- CONTENT END HERE -->


<?php $this->load->view('emails/template/email_footer.php') ?>