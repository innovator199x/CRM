<div class="box-typical-body mt-3">
    <table class="table table-bordered table-striped table-hover mt-2" id="logs_table" style="width:100%;">
        <thead>
            <tr>
                <th>Details</th>
                <th>Staff</th>
                <th>Date</th>
            </tr>
        </thead>
    </table>
</div>

<script>

$('#logs_table').DataTable({
        "order": [[ 2, "desc" ]],
        "pageLength": 50,
        "processing": true,
        "serverSide": true,
        "deferRender": true,
        "ajax": {
            "url": "<?php echo base_url('email/datatable_email_logs'); ?>",
            "dataType": "json",
            "type": "POST"
        },
        "columns": [{
                "data": "details"
            },
            {
                "data": "name"
            },
            {
                "data": "created_date"
            }
        ]
    });
</script>