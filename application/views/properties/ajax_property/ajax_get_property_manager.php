<?php 
    echo "<option value=''>Please Select Property Manager</option>";
    echo "<option value='0'>No PM assigned</option>";
    foreach($pm->result_array() as $row){

        echo "<option value='{$row['agency_user_account_id']}'>{$row['fname']} {$row['lname']}</option>";

    }

?>