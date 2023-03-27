<?php $this->load->view('emails/template/email_header.php') ?>

<!-- CONTENT START HERE -->
<h3>This is sample template only</h3>

<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quam elit, iaculis et nulla ac, suscipit aliquet neque. Quisque malesuada lorem sed nibh porttitor tempus. Proin sed diam ante. Nullam euismod dui felis, sit amet pretium est posuere dictum. Donec ultricies nisl vitae mauris luctus consectetur. Cras auctor purus in magna dictum, id facilisis tellus semper. Aliquam vitae lorem orci. Nullam convallis malesuada euismod. Vivamus sagittis magna nulla, ut dapibus tortor sollicitudin eget. Sed accumsan arcu magna. </p>




<p>Dear test name,</p>
<p>You have added a new property through the SATS Agency portal:</p>
<p><strong>Property Address:</strong></p>
<p> address here</p>
<hr/>
<h3>Tenants:</h3>

    <table style="width:100%; border:1px solid #ccc;text-align: left;">
    <thead>
        <tr>
            <th style="padding:5px;">First Name</th>
            <th style="padding:5px;">Last Name</th>
            <th style="padding:5px;">Mobile</th>
            <th style="padding:5px;">Landline</th>
            <th style="padding:5px;">Email</th>
        </tr>
    </thead>
    <tbody>
       
        <tr>
            <td style="padding:5px;"><?php echo "Fname" ?></td>
            <td style="padding:5px;"><?php echo "Laname" ?></td>
            <td style="padding:5px;"><?php echo "Mobile" ?></td>
            <td style="padding:5px;"><?php echo "Phone" ?></td>
            <td style="padding:5px;"><?php echo "Email" ?></td>
        </tr>
 
    </tbody>
</table>


   <h3>Services:</h3>
    <ul style="margin: 0">
        
                <li>Bundle services 100.00 - SATS</li>
                <li>Bundle services 100.00 - No Response</li>
       
</ul>
    
<h3>Comments:</h3>
Job Comments: <?php echo "comments" ?>

<h3>Landlord:</h3>
<table style="width:100%; border:1px solid #ccc;text-align: left;">
    <thead>
       <th style="padding:5px;">Full Name</th>
        <th style="padding:5px;">Mobile</th>
       <th style="padding:5px;">Landline</th>
       <th style="padding:5px;">Email</th>
    </thead>
    <tbody>
    <tr>
    <td>a</td>
    <td>a</td>
    <td>a</td>
    <td>a</td>
    </tr>
    
    </tbody>
</table>

    
<p>&nbsp;</p>
<p>Kind Regards<br/>
    Smoke Alarm Testing Services.
    </p>

<!-- CONTENT END HERE -->


<?php $this->load->view('emails/template/email_footer.php') ?>