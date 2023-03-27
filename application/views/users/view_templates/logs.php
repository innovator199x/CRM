<div role="tabpanel" class="tab-pane fade active show" id="licencing-tab">
    <div class="row">
        <div class="col-sm-12">
            <h3 class="h3">User Logs</h3>
            <form id="form-user_logs" method="post" action="/users/add_log/<?= $user['StaffID'] ?>">
                <table class="table border-horizontal border-bottom">
                    <thead>
                        <tr>
                            <th style="width: 125px;">Date</th>
                            <th class="w-75">Details</th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="input-column">
                                <div class="form-group mb-0">
                                    <input type="text"
                                        name="date"
                                        class="form-control flatpickr flatpickr-input"
                                        style="width: 125px;"
                                        required
                                        data-validation="[NOTEMPTY]"
                                    />
                                </div>
                            </td>
                            <td class="w-75 input-column">
                                <div class="form-group mb-0">
                                    <input type="text"
                                        name="details"
                                        class='form-control'
                                        required
                                        data-validation="[NOTEMPTY]"
                                    />
                                </div>
                            </td>
                            <td>
                                <button type="submit" class="btn btn-primary">
                                    Add Log
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>

            <table class="table table-hover border-horizontal border-bottom mt-2">
                <thead>
                    <tr>
                        <th style="width: 136px;">Date</th>
                        <th class="w-25">Who</th>
                        <th class="w-auto">Details</th>
                        <th style="width: 100px;">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userLogs as $userLog): ?>
                    <tr>
                        <td style="width: 136px;">
                            <?= $this->customlib->formatYmdhisToDmy($userLog['date'], true) ?>
                        </td>
                        <td class="w-25">
                            <?= $this->system_model->formatStaffName($userLog['FirstName'], $userLog['LastName']) ?>
                        </td>
                        <td class="w-auto">
                            <?= $userLog['details'] ?>
                        </td>
                        <td style="width: 100px;" class="text-center">
                            <button class="btn btn-danger delete_user_log" data-log_id="<?= $userLog['user_log_id'] ?>">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($userLogs)): ?>
                    <tr>
                        <td colspan="4" class="text-center">
                            -- No Entries --
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($userLogsCount > 10): ?>
                    <tr>
                        <td colspan="4" class="text-center">
                            <a href="/users/user_logs/<?= $user['StaffID'] ?>">View All &gt;&gt;</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h4 class="mt-4">System Logs</h4>
            <div class="table-responsive">
                <table class="table table-hover border-horizontal border-bottom ">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Staff</th>
                            <th>Title</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $systemLogs  as $index => $row ): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i',strtotime($row->created_date)); ?></td>
                            <td>
                                <img
                                    class="profile_pic_small border border-info mr-1"
                                    src="/images/<?= $this->system_model->getAvatar($row->photo) ?>"
                                    data-toggle="tooltip" title="<?= $row->FirstName ?>"
                                />
                                <?= $this->system_model->formatStaffName($row->FirstName, $row->LastName) ?>
                            </td>
                            <td>
                                <?php echo $row->title_name; ?>
                            </td>
                            <td>
                                <?php
                                //echo $this->jcclass->parseDynamicLink2($row);
                                
                                $dlink_params = array(
                                   'log_id' => $row->log_id
                                );
                                echo $this->system_model->parseDynamicLink($dlink_params);                                
                                ?>
                                <input type="hidden" class="log_id" value="<?php echo $row->log_id; ?>" />
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($systemLogs)): ?>
                        <tr>
                            <td colspan="4" class="text-center">
                                -- No Entries --
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($systemLogsCount > 10): ?>
                        <tr>
                            <td colspan="4" class="text-center">
                                <a href="/users/system_logs/<?= $user['StaffID'] ?>">View All &gt;&gt;</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <a href="/users/view/<?= $user['StaffID'] ?>/logs/?logs_inv_this_user=1">
                    <button type="button" class="btn mt-3">Load logs involving this user</button>
                </a>

            </div>
        </div>
    </div>
</div>