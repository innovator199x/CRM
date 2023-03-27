<?php

class Messages extends MY_ApiController {

    public function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->model('messages_model');
        $this->load->model('staff_accounts_model');
    }

    public function message_threads() {
        $techId = $this->api->getJWTItem('staff_id');
        $unreadOnly = $this->input->get('unread-only', true) ?? 0;

        $readFilter = "";
        if ($unreadOnly == 1) {
            $readFilter = "AND mrb.`read` IS NULL";
        }

        $threadCount = $this->db->query("
            SELECT
                COUNT(mh.message_header_id) AS total_messages
            FROM `message` AS m
            INNER JOIN(
                SELECT
                    m2.`message_header_id`,
                    MAX(m2.`date`) as latest_date,
                    mrb2.message_id
                FROM `message` AS m2
                LEFT JOIN `message_read_by` AS mrb2 ON ( m2.`message_id` = mrb2.`message_id` AND mrb2.`staff_id` = {$techId} )
                INNER JOIN `message_header` AS mh2 ON m2.`message_header_id` = mh2.`message_header_id`
                INNER JOIN `message_group` AS mg2 ON ( mh2.`message_header_id` = mg2.`message_header_id` )
                WHERE mg2.`staff_id` = {$techId}
                GROUP BY m2.`message_header_id`
            ) AS m4 ON ( m.message_header_id = m4.message_header_id AND m.date = m4.latest_date )
            LEFT JOIN `message_read_by` AS mrb ON ( m.`message_id` = mrb.`message_id` AND mrb.`staff_id` = {$techId} )
            INNER JOIN `message_header` AS mh ON m.`message_header_id` = mh.`message_header_id`
            INNER JOIN `message_group` AS mg ON ( mh.`message_header_id` = mg.`message_header_id` )
            WHERE mg.`staff_id` = {$techId}
            {$readFilter}
        ")->row()->total_messages;

        $page = $this->input->get('page', true) ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * 20;

        $threads = $this->db->query("
            SELECT
                mh.message_header_id AS mh_id,
                mh.subject AS mh_subject,
                mh.read AS mh_read,
                mh.status AS mh_status,
                mh.date AS mh_date,
                m.message AS m_message,
                m.author AS m_author,
                m.read AS m_read,
                m.date AS m_date,
                mrb.read AS mrb_read,
                m4.message_id AS m_last_message_id
            FROM `message` AS m
            INNER JOIN(
                SELECT
                    m2.`message_header_id`,
                    MAX(m2.`date`) as latest_date,
                    mrb2.message_id
                FROM `message` AS m2
                LEFT JOIN `message_read_by` AS mrb2 ON ( m2.`message_id` = mrb2.`message_id` AND mrb2.`staff_id` = {$techId} )
                INNER JOIN `message_header` AS mh2 ON m2.`message_header_id` = mh2.`message_header_id`
                INNER JOIN `message_group` AS mg2 ON ( mh2.`message_header_id` = mg2.`message_header_id` )
                WHERE mg2.`staff_id` = {$techId}
                GROUP BY m2.`message_header_id`
            ) AS m4 ON ( m.message_header_id = m4.message_header_id AND m.date = m4.latest_date )
            LEFT JOIN `message_read_by` AS mrb ON ( m.`message_id` = mrb.`message_id` AND mrb.`staff_id` = {$techId} )
            INNER JOIN `message_header` AS mh ON m.`message_header_id` = mh.`message_header_id`
            INNER JOIN `message_group` AS mg ON ( mh.`message_header_id` = mg.`message_header_id` )
            WHERE mg.`staff_id` = {$techId}
            {$readFilter}
            ORDER by m.date DESC
            LIMIT {$offset}, {$perPage}
        ")->result_array();

        if (!empty($threads)) {
            $threadsAssoc = [];
            for ($x = 0; $x < count($threads); $x++) {
                $thread = &$threads[$x];
                $thread['members'] = [];
                $thread['unread_messages'] = 0;

                $threadsAssoc[$thread['mh_id']] = &$thread;
            }

            $threadIds = array_keys($threadsAssoc);

            $threadIdsString = implode(',', $threadIds);

            $members = $this->db->query("
                SELECT
                    mg.message_header_id AS mg_mh_id,
                    sa.StaffID AS staff_id,
                    sa.FirstName AS sa_first_name,
                    sa.LastName AS sa_last_name,
                    sa.profile_pic AS sa_profile_pic
                FROM `message_group` AS mg
                INNER JOIN `staff_accounts` AS sa ON (sa.StaffID = mg.staff_id)
                WHERE
                    mg.staff_id != {$techId} AND
                    mg.message_header_id IN ({$threadIdsString})
                ORDER BY mg_mh_id DESC, sa_first_name ASC
            ")->result_array();

            foreach ($members as $member) {
                $threadsAssoc[$member['mg_mh_id']]['members'][] = $member;
            }

            $unreadMessages = $this->db->query("
                SELECT
                    COUNT(m.message_id) AS new_count,
                    m.message_header_id AS mh_id
                FROM message AS m
                INNER JOIN (

                    SELECT
                        IFNULL(mrb.message_id, 0) AS message_id,
                        m.message_header_id
                    FROM message AS m
                    LEFT JOIN message_read_by AS mrb ON ( m.message_id = mrb.message_id AND mrb.staff_id = {$techId} )
                    WHERE m.message_header_id IN ({$threadIdsString})
                    GROUP BY m.message_header_id
                    ORDER BY mrb.message_id DESC

                ) AS mx ON ( m.message_id > mx.message_id AND m.message_header_id = mx.message_header_id )
                WHERE m.message_header_id IN ({$threadIdsString})
                GROUP BY m.message_header_id
            ")->result_array();

            foreach($unreadMessages as $entry) {
                $threadsAssoc[$entry['mh_id']]['unread_messages'] = $entry['new_count'];
            }
        }

        $this->api->setSuccess(true);
        $this->api->putData('threads', $threads);
        $this->api->putData('thread_count', $threadCount);
        $this->api->putData('current_page', $page);
        $this->api->putData('max_pages', ceil($threadCount / $perPage));
    }

    public function thread($messageHeaderId) {
        $techId = $this->api->getJWTItem('staff_id');

        $threadCount = $this->db->query("
            SELECT
                COUNT(mh.message_header_id) AS total_messages
            FROM `message` AS m
            INNER JOIN(
                SELECT
                    m2.`message_header_id`,
                    MAX(m2.`date`) as latest_date,
                    mrb2.message_id
                FROM `message` AS m2
                LEFT JOIN `message_read_by` AS mrb2 ON ( m2.`message_id` = mrb2.`message_id` AND mrb2.`staff_id` = {$techId} )
                INNER JOIN `message_header` AS mh2 ON m2.`message_header_id` = mh2.`message_header_id`
                INNER JOIN `message_group` AS mg2 ON ( mh2.`message_header_id` = mg2.`message_header_id` )
                WHERE mg2.`staff_id` = {$techId}
                GROUP BY m2.`message_header_id`
            ) AS m4 ON ( m.message_header_id = m4.message_header_id AND m.date = m4.latest_date )
            LEFT JOIN `message_read_by` AS mrb ON ( m.`message_id` = mrb.`message_id` AND mrb.`staff_id` = {$techId} )
            INNER JOIN `message_header` AS mh ON m.`message_header_id` = mh.`message_header_id`
            INNER JOIN `message_group` AS mg ON ( mh.`message_header_id` = mg.`message_header_id` )
            WHERE mg.`staff_id` = {$techId}
        ")->row()->total_messages;

        $thread = $this->db->query("
            SELECT
                mh.message_header_id AS mh_id,
                mh.subject AS mh_subject,
                mh.read AS mh_read,
                mh.status AS mh_status,
                mh.date AS mh_date,
                m.message AS m_message,
                m.author AS m_author,
                m.read AS m_read,
                m.date AS m_date,
                mrb.read AS mrb_read,
                m4.message_id AS m_last_message_id
            FROM `message` AS m
            INNER JOIN(
                SELECT
                    m2.`message_header_id`,
                    MAX(m2.`date`) as latest_date,
                    mrb2.message_id
                FROM `message` AS m2
                LEFT JOIN `message_read_by` AS mrb2 ON ( m2.`message_id` = mrb2.`message_id` AND mrb2.`staff_id` = {$techId} )
                INNER JOIN `message_header` AS mh2 ON m2.`message_header_id` = mh2.`message_header_id`
                INNER JOIN `message_group` AS mg2 ON ( mh2.`message_header_id` = mg2.`message_header_id` )
                WHERE mg2.`staff_id` = {$techId}
                GROUP BY m2.`message_header_id`
            ) AS m4 ON ( m.message_header_id = m4.message_header_id AND m.date = m4.latest_date )
            LEFT JOIN `message_read_by` AS mrb ON ( m.`message_id` = mrb.`message_id` AND mrb.`staff_id` = {$techId} )
            INNER JOIN `message_header` AS mh ON m.`message_header_id` = mh.`message_header_id`
            INNER JOIN `message_group` AS mg ON ( mh.`message_header_id` = mg.`message_header_id` )
            WHERE mg.`staff_id` = {$techId}
            AND mh.message_header_id = {$messageHeaderId}
            ORDER by m.date DESC
            LIMIT 1
        ")->row_array();

        if ($thread != null) {

            $members = $this->db->query("
                SELECT
                    mg.message_header_id AS mg_mh_id,
                    sa.StaffID AS staff_id,
                    sa.FirstName AS sa_first_name,
                    sa.LastName AS sa_last_name,
                    sa.profile_pic AS sa_profile_pic
                FROM `message_group` AS mg
                INNER JOIN `staff_accounts` AS sa ON (sa.StaffID = mg.staff_id)
                WHERE
                    mg.staff_id != {$techId} AND
                    mg.message_header_id = {$messageHeaderId}
                ORDER BY mg_mh_id DESC, sa_first_name ASC
            ")->result_array();

            $thread['members'] = [];

            foreach ($members as $member) {
                $thread['members'][] = $member;
            }

            $thread['unread_messages'] = 0;
            $unreadMessage = $this->db->query("
                SELECT
                    COUNT(m.message_id) AS new_count
                FROM message AS m
                INNER JOIN (

                    SELECT
                        IFNULL(mrb.message_id, 0) AS message_id,
                        m.message_header_id
                    FROM message AS m
                    LEFT JOIN message_read_by AS mrb ON ( m.message_id = mrb.message_id AND mrb.staff_id = {$techId} )
                    WHERE m.message_header_id = {$messageHeaderId}
                    GROUP BY m.message_header_id

                ) AS mx ON ( m.message_id > mx.message_id AND m.message_header_id = mx.message_header_id )
                WHERE m.message_header_id = {$messageHeaderId}
                GROUP BY m.message_header_id
            ")->row_array();

            $perPage = 20;

            $this->api->setSuccess(true);
            $this->api->putData('thread', $thread);
            $this->api->putData('max_pages', ceil($threadCount / $perPage));
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage("Thread does not exist.");
        }

    }

    // mark all unread message as read
    public function mark_all_as_read(){
        $staffId = $this->api->getJWTItem('staff_id');

        $results = $this->messages_model->markAllAsRead($staffId);

        $sender = $this->staff_accounts_model->get_staff_accounts([
            'sel_query' => "
                sa.`StaffID`,
                sa.`FirstName`,
                sa.`LastName`,
                sa.`profile_pic`
            ",
            'staff_id' => $staffId,
            'active' => 1,
            'deleted' => 0,
            'display_query' => 0,
        ])->row_array();

        $data = [
            'read' => 1,
            'message_id' => 'all',
            'message_header_id' => 'all',
            'staff_id' => $staffId,
            'date' => date("Y-m-d H:i:s"),
            'FirstName' => $sender['FirstName'],
            'LastName' => $sender['LastName'],
            'profile_pic' => $sender['profile_pic'],
        ];

        $pusherCluster = $this->config->item('PUSHER_CLUSTER');
        $pusherKey = $this->config->item('PUSHER_KEY');
        $pusherSecret = $this->config->item('PUSHER_SECRET');
        $pusherAppId = $this->config->item('PUSHER_APP_ID');

        $options = [
            'cluster' => $pusherCluster,
            'useTLS' => true,
        ];
        $pusher = new Pusher\Pusher(
            $pusherKey,
            $pusherSecret,
            $pusherAppId,
            $options
        );

        $pusher->trigger('mark_all_read', 'mark_all_read', [
            'data' => $data,
        ]);

        $this->api->setSuccess(true);
        $this->api->putData('message_count', $results['message_count']);
        $this->api->putData('delete_count', $results['delete_count']);
        $this->api->putData('insert_count', $results['insert_count']);
    }

    public function mark_read($messageHeaderId) {
        $staffId = $this->api->getJWTItem('staff_id');

        $this->db->query("
            DELETE mrb
            FROM `message_read_by` AS mrb
            LEFT JOIN `message` AS m ON mrb.`message_id`  = m.`message_id`
            WHERE mrb.`staff_id` = {$staffId}
            AND m.`message_header_id` = {$messageHeaderId}
        ");

        $lastMessage = $this->db->query("
            SELECT m.`message_id`
            FROM `message` AS m
            WHERE m.`message_header_id` = {$messageHeaderId}
            ORDER BY m.`date` DESC
            LIMIT 1
        ")->row_array();

        $readByData = [
            'read' => 1,
            'message_id' => $lastMessage['message_id'],
            'staff_id' => $staffId,
            'date' => date("Y-m-d H:i:s"),
        ];
        $this->db->insert('message_read_by', $readByData);
        $readByData['message_read_by_id'] = $this->db->insert_id();
        $readByData['FirstName'] = $sender['FirstName'];
        $readByData['LastName'] = $sender['LastName'];
        $readByData['profile_pic'] = $sender['profile_pic'];

        $pusherCluster = $this->config->item('PUSHER_CLUSTER');
        $pusherKey = $this->config->item('PUSHER_KEY');
        $pusherSecret = $this->config->item('PUSHER_SECRET');
        $pusherAppId = $this->config->item('PUSHER_APP_ID');

        $options = [
            'cluster' => $pusherCluster,
            'useTLS' => true,
        ];
        $pusher = new Pusher\Pusher(
            $pusherKey,
            $pusherSecret,
            $pusherAppId,
            $options
        );

        $otherMembers = $this->db->query("
            SELECT
                mg.`message_group_id`,
                mg.`staff_id`

            FROM `message_group` AS mg
            WHERE mg.`message_header_id` = {$messageHeaderId}
            AND mg.`staff_id` != {$staffId}
        ")->result_array();

        $pusherData = [
            'read_by' => $readByData,
        ];
        $ev = "mark_read";
        foreach($otherMembers as $member) {

            $ch = "ch{$member['staff_id']}";

            $pusher->trigger($ch, $ev, $pusherData);
        }

        $this->api->setSuccess(true);
        $this->api->putData('read_by', $readByData);
    }

    public function unread_message_count() {
        $staffId = $this->api->getJWTItem('staff_id');

        $unreadCount = $this->db->query("
            SELECT
                COUNT(mh.message_header_id) AS unread
            FROM `message` AS m
            INNER JOIN(
                SELECT
                    m2.`message_header_id`,
                    MAX(m2.`date`) as latest_date,
                    mrb2.message_id
                FROM `message` AS m2
                LEFT JOIN `message_read_by` AS mrb2 ON ( m2.`message_id` = mrb2.`message_id` AND mrb2.`staff_id` = {$staffId} )
                INNER JOIN `message_header` AS mh2 ON m2.`message_header_id` = mh2.`message_header_id`
                INNER JOIN `message_group` AS mg2 ON ( mh2.`message_header_id` = mg2.`message_header_id` )
                WHERE mg2.`staff_id` = {$staffId}
                GROUP BY m2.`message_header_id`
            ) AS m4 ON ( m.message_header_id = m4.message_header_id AND m.date = m4.latest_date )
            LEFT JOIN `message_read_by` AS mrb ON ( m.`message_id` = mrb.`message_id` AND mrb.`staff_id` = {$staffId} )
            INNER JOIN `message_header` AS mh ON m.`message_header_id` = mh.`message_header_id`
            INNER JOIN `message_group` AS mg ON ( mh.`message_header_id` = mg.`message_header_id` )
            WHERE mg.`staff_id` = {$staffId} AND mrb.`read` IS NULL
        ")->row()->unread;

        $this->api->setSuccess(true);
        $this->api->putData('unread_count', $unreadCount);
    }

    public function conversation($messageHeaderId) {
        $countryId = $this->config->item('country');
        $staffId = $this->api->getJWTItem('staff_id');

        $this->db->query("
            DELETE mrb
            FROM `message_read_by` AS mrb
            LEFT JOIN `message` AS m ON mrb.`message_id`  = m.`message_id`
            WHERE mrb.`staff_id` = {$staffId}
            AND m.`message_header_id` = {$messageHeaderId}
        ");

        $lastMessage = $this->db->query("
            SELECT m.`message_id`
            FROM `message` AS m
            WHERE m.`message_header_id` = {$messageHeaderId}
            ORDER BY m.`date` DESC
            LIMIT 1
        ")->row_array();

        $sender = $this->staff_accounts_model->get_staff_accounts([
            'sel_query' => "
                sa.`StaffID`,
                sa.`FirstName`,
                sa.`LastName`,
                sa.`profile_pic`
            ",
            'staff_id' => $staffId,
            'active' => 1,
            'deleted' => 0,
            'display_query' => 0,
        ])->row_array();

        $readByData = [
            'read' => 1,
            'message_id' => $lastMessage['message_id'],
            'staff_id' => $staffId,
            'date' => date("Y-m-d H:i:s"),
        ];
        $this->db->insert('message_read_by', $readByData);
        $readByData['message_read_by_id'] = $this->db->insert_id();
        $readByData['FirstName'] = $sender['FirstName'];
        $readByData['LastName'] = $sender['LastName'];
        $readByData['profile_pic'] = $sender['profile_pic'];

        $pusherCluster = $this->config->item('PUSHER_CLUSTER');
        $pusherKey = $this->config->item('PUSHER_KEY');
        $pusherSecret = $this->config->item('PUSHER_SECRET');
        $pusherAppId = $this->config->item('PUSHER_APP_ID');

        $options = [
            'cluster' => $pusherCluster,
            'useTLS' => true,
        ];
        $pusher = new Pusher\Pusher(
            $pusherKey,
            $pusherSecret,
            $pusherAppId,
            $options
        );

        $otherMembers = $this->db->query("
            SELECT
                mg.`message_group_id`,
                mg.`staff_id`

            FROM `message_group` AS mg
            WHERE mg.`message_header_id` = {$messageHeaderId}
            AND mg.`staff_id` != {$staffId}
        ")->result_array();

        $pusherData = [
            'read_by' => $readByData,
        ];
        $ev = "mark_read";
        foreach($otherMembers as $member) {

            $ch = "ch{$member['staff_id']}";

            $pusher->trigger($ch, $ev, $pusherData);
        }

        $this->api->putData('other_members', $otherMembers);

        $messages = $this->messages_model->get_messages([
            'sel_query' => "
                m.`message_id`,
                m.`message_header_id`,
                m.`date`,
                m.`message`,
                m.`author`,

                sa.`FirstName`,
                sa.`LastName`,
                sa.`profile_pic`
            ",
            'message_header_id' => $messageHeaderId,
            'join_table' => ['staff_accounts'],
            'sort_list' => [
                [
                    'order_by' => 'm.date',
                    'sort' => 'DESC',
                ],
            ],
        ])->result_array();

        $messagesAssoc = [];

        for($x = 0; $x < count($messages); $x++) {
            $message = &$messages[$x];
            $message['read_bys'] = [];

            $messagesAssoc[$message['message_id']] = &$message;
        }

        $messageIds = array_keys($messagesAssoc);
        $messageIdsString = implode(',', $messageIds);

        $readBys = $this->db->query("
            SELECT
                mrb.`message_read_by_id`,
                mrb.`message_id`,
                mrb.`staff_id`,
                sa.`profile_pic`,
                sa.`FirstName`,
                sa.`LastName`
            FROM `message_read_by` AS mrb
            LEFT JOIN `staff_accounts` AS sa ON mrb.`staff_id` = sa.`StaffID`
            WHERE `message_id` IN ({$messageIdsString})
        ")->result_array();

        foreach($readBys as $readBy) {
            $messagesAssoc[$readBy['message_id']]['read_bys'][] = $readBy;
        }

        $this->api->setSuccess(true);
        $this->api->putData('messages', $messages);
    }

    public function send_message() {
        $this->api->assertMethod('post');

        $staffId = $this->api->getJWTItem('staff_id');
        $messageHeaderId = $this->api->getPostData('message_header_id');
        $message = $this->api->getPostData('message');

        $messageData = [
            'message_header_id' => $messageHeaderId,
            'author' => $staffId,
            'message' => $message,
            'date' => date("Y-m-d H:i:s"),
        ];

        $this->db->insert('message', $messageData);
        $messageId = $this->db->insert_id();
        $messageData['message_id'] = $messageId;
        $messageData['read'] = 0;

        $sender = $this->staff_accounts_model->get_staff_accounts([
            'sel_query' => "
                sa.`StaffID`,
                sa.`FirstName`,
                sa.`LastName`,
                sa.`profile_pic`
            ",
            'staff_id' => $staffId,
            'active' => 1,
            'deleted' => 0,
            'display_query' => 0,
        ])->row_array();

        $messageData['FirstName'] = $sender['FirstName'];
        $messageData['LastName'] = $sender['LastName'];
        $messageData['profile_pic'] = $sender['profile_pic'];

        $senderName = $this->system_model->formatStaffName($sender->FirstName, $sender->LastName);

        $this->db->query("
            DELETE mrb
            FROM `message_read_by` AS mrb
            LEFT JOIN `message` AS m ON mrb.`message_id`  = m.`message_id`
            WHERE mrb.`staff_id` = {$staffId}
            AND m.`message_header_id` = {$messageHeaderId}
        ");

        $readBy = [
            'read' => 1,
            'message_id' => $messageId,
            'staff_id' => $staffId,
            'date' => date("Y-m-d H:i:s"),
        ];
        $this->db->insert('message_read_by', $readBy);
        $readBy['message_read_by_id'] = $this->db->insert_id();
        $readBy['staff_id'] = $staffId;
        $readBy['FirstName'] = $sender['FirstName'];
        $readBy['LastName'] = $sender['LastName'];
        $readBy['profile_pic'] = $sender['profile_pic'];

        $messageData['read_bys'] = [$readBy];

        $otherMembers = $this->db->query("
            SELECT
                mg.`message_group_id`,
                mg.`staff_id`

            FROM `message_group` AS mg
            WHERE mg.`message_header_id` = {$messageHeaderId}
            AND mg.`staff_id` != {$staffId}
        ")->result_array();

        $countryId = $this->config->item('country');
        $pusherCluster = $this->config->item('PUSHER_CLUSTER');
        $pusherKey = $this->config->item('PUSHER_KEY');
        $pusherSecret = $this->config->item('PUSHER_SECRET');
        $pusherAppId = $this->config->item('PUSHER_APP_ID');

        $options = [
            'cluster' => $pusherCluster,
            'useTLS' => true,
        ];
        $pusher = new Pusher\Pusher(
            $pusherKey,
            $pusherSecret,
            $pusherAppId,
            $options
        );

        $notificationType = 1;
        $notificationMessage = "New <a href='".BASEURL."messages/convo/?id={$messageHeaderId}'>Message</a> From {$senderName}";
        foreach($otherMembers as $member) {
            $params = [
                'notf_type' => $notificationType,
                'staff_id' => $member->staff_id,
                'country_id' => $countryId,
                'notf_msg' => $notificationMessage,
            ];

            $this->gherxlib->insertNewNotification($params);

            $pusherData = [
                'notif_type' => $notificationType,
                'message' => $messageData,
            ];

            $ch = "ch{$member->staff_id}";
            $ev = "ev01";

            $pusher->trigger($ch, $ev, $pusherData);
        }

        $this->api->setSuccess(true);
        $this->api->putData('message', $messageData);
    }

    public function search_users() {
        $this->api->assertMethod('post');

        $staffId = $this->api->getJWTItem('staff_id');
        $search = $this->api->getPostData('search');

        $conditions = "sa.`StaffID` != {$staffId}";
        if ($search != '') {
            $conditions .= " AND CONCAT(sa.`FirstName`, ' ', sa.`LastName`) LIKE '%{$search}%'";
        }


        $params = array(
            'sel_query' => "
                sa.`StaffID`,
                sa.`FirstName`,
                sa.`LastName`,
                sa.`profile_pic`,
            ",
            'active' => 1,
            'deleted' => 0,
            'sort_list' => [
                [
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC',
				],
				[
                    'order_by' => 'sa.`LastName`',
                    'sort' => 'ASC',
                ],
            ],
            'custom_where' => $conditions,
            'display_query' => 0
        );

        $users = $this->staff_accounts_model->get_staff_accounts($params)->result_array();

        $this->api->setSuccess(true);
        $this->api->putData('search', $search);
        $this->api->putData('users', $users);
    }

    public function create_message() {
        $this->api->assertMethod('post');

        $staffId = $this->api->getJWTItem('staff_id');
        $recipientIds = $this->api->getPostData('recipient_ids');
        $message = $this->api->getPostData('message');

        $threadData = [
            'from' => $staffId,
            'date' => date("Y-m-d H:i:s"),
        ];

        $this->db->insert('message_header', $threadData);
        $messageHeaderId = $this->db->insert_id();
        $threadData['mh_id'] = $messageHeaderId;

        $messageData = [
            'message_header_id' => $messageHeaderId,
            'author' => $staffId,
            'message' => $message,
            'date' => date("Y-m-d H:i:s"),
        ];

        $this->db->insert('message', $messageData);
        $messageId = $this->db->insert_id();
        $messageData['message_id'] = $messageId;
        $messageData['read'] = 0;

        $this->db->insert('message_group', [
            'message_header_id' => $messageHeaderId,
            'staff_id' => $staffId,
        ]);

        $this->db->insert('message_read_by', [
            'read' => 1,
            'message_id' => $messageId,
            'staff_id' => $staffId,
            'date ' => $today
        ]);

        $sender = $this->staff_accounts_model->get_staff_accounts([
            'sel_query' => "
                sa.`StaffID`,
                sa.`FirstName`,
                sa.`LastName`,
                sa.`profile_pic`
            ",
            'staff_id' => $staffId,
            'active' => 1,
            'deleted' => 0,
            'display_query' => 0,
        ])->row_array();

        $messageData['FirstName'] = $sender['FirstName'];
        $messageData['LastName'] = $sender['LastName'];
        $messageData['profile_pic'] = $sender['profile_pic'];

        $senderName = $this->system_model->formatStaffName($sender['FirstName'], $sender['LastName']);

        $messageGroupInserts = [];
        foreach($recipientIds as $recipientId) {
            $messageGroupInserts[] = [
                'message_header_id' => $messageHeaderId,
                'staff_id' => $recipientId,
            ];
        }

        $this->db->insert_batch('message_group', $messageGroupInserts);

        $countryId = $this->config->item('country');
        $pusherCluster = $this->config->item('PUSHER_CLUSTER');
        $pusherKey = $this->config->item('PUSHER_KEY');
        $pusherSecret = $this->config->item('PUSHER_SECRET');
        $pusherAppId = $this->config->item('PUSHER_APP_ID');

        $notificationType = 1;
        $notificationMessage = "New <a href='".BASEURL."messages/convo/?id={$messageHeaderId}'>Message</a> From {$senderName}";
        foreach($recipientIds as $recipientId) {

            $params = [
                'notf_type' => $notificationType,
                'staff_id' => $recipientId,
                'country_id' => $countryId,
                'notf_msg' => $notificationMessage,
            ];

            $this->gherxlib->insertNewNotification($params);

            $options = [
                'cluster' => $pusherCluster,
                'useTLS' => true,
            ];

            $pusher = new Pusher\Pusher(
                $pusherKey,
                $pusherSecret,
                $pusherAppId,
                $options
            );

            $pusherData = [
                'notif_type' => $notificationType,
                'message_data' => $messageData
            ];

            $ch = "ch{$recipientId}";
            $ev = "ev01";

            $pusher->trigger($ch, $ev, $pusherData);
        }

        $this->api->setSuccess(true);
        $this->api->putData('message', $messageData);
        $this->api->putData('thread', $threadData);
    }

}
?>