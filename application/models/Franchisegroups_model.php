<?php

class Franchisegroups_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    /**
     * PULL BUTTON FG FROM DATABASE
     * @param $params | ARRAY | array of data to use for select
     * @return $query | RESULT | result from the database
     * */
    public function getButtonFranchiseGroups($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } elseif ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else {
            $sel_query = '*';
        }
        if ($params['distinct_sql'] != "") {
            $this->db->distinct();
        }
        $this->db->select($sel_query);
        $this->db->from('`franchise_groups` AS fg');
//        $this->db->join('`agency` as a', 'a.franchise_groups_id=fg.franchise_groups_id','LEFT');
        $this->db->where('fg.`franchise_groups_id` > 0');

        // filters
        //$filter_arr = array();

        if ($params['country_id'] != "") {
            $this->db->where('fg.`country_id`', $params['country_id']);
        }
        if ($params['custom_filter'] != '') {
            $this->db->where($params['custom_filter']);
        }

        // search
        if ($params['search'] != '') {
            $this->db->like($params['search']);
        }


        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    //$sort_str_arr[] = $sort_arr['order_by'] . ' ' . $sort_arr['sort'];
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
            $sort_str_imp = implode(", ", $sort_str_arr);
            // $sort_str = "ORDER BY {$sort_str_imp}";
            //$this->db->order_by( $sort_str_imp );
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            //$group_by_str = "GROUP BY {$params['group_by']}";
            $this->db->group_by($params['group_by']);
        }


        // paginate
        if ($params['paginate'] != "") {
            $this->db->limit($params['paginate']['limit'], $params['paginate']['offset']);
        }

        if (isset($params['getCsv'])) {
            $query = $this->db->get()->result_array();
        } else {
            $query = $this->db->get();
        }
        if ($params['echo_query'] == 1) {
            //echo $sql;
            echo $this->db->last_query();
        }

        if ($params['return_count'] == 1) {
            // $j_sql = mysql_query($sql);
            // $row = mysql_fetch_array($j_sql);
            // return $row['jcount'];

            return $query->num_rows();
        } else {
            return $query;
        }
    }

    public function getButtonFranchiseGroupsAgency($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }
        if ($params['distinct_sql'] != "") {
            $this->db->distinct();
        }

        $this->db->select($sel_query);
        $this->db->from('`agency` a');
        $this->db->join('`agency_regions` as ar', 'ar.agency_region_id=a.agency_region_id', 'LEFT');
        $this->db->join('`staff_accounts` as sa', 'a.salesrep = sa.StaffID', 'LEFT');
        $where = "a.`status` = 'active' AND a.`franchise_groups_id` =" . $params['franchise_groups_id'];
        if (isset($params['state']) && $params['state'] != "") {
            $where .= " AND LOWER(a.state) LIKE '%{$params['state']}%' ";
        }

        // sales rep
        if (isset($params['salesrep']) && $params['salesrep'] != "") {
            $where .= " AND (CONCAT_WS(' ',LOWER(sa.FirstName), LOWER(sa.LastName)) LIKE '%{$params['salesrep']}%') ";
        }

        // region
        if (isset($params['region']) && $params['region'] != "") {
            $where .= " AND (LOWER(ar.agency_region_name) LIKE '%{$params['region']}%') ";
        }

        // phrase
        if (isset($params['phrase']) && $params['phrase'] != "") {
            $where .= " AND ( CONCAT_WS( ' ', LOWER(a.agency_name), LOWER(a.contact_first_name), LOWER(a.contact_last_name), LOWER(sa.FirstName), LOWER(sa.LastName), LOWER(a.state), LOWER(ar.agency_region_name) ) LIKE '%{$params['phrase']}%') ";
        }
        $this->db->where($where);



        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    //$sort_str_arr[] = $sort_arr['order_by'] . ' ' . $sort_arr['sort'];
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
            $sort_str_imp = implode(", ", $sort_str_arr);
            // $sort_str = "ORDER BY {$sort_str_imp}";
            //$this->db->order_by( $sort_str_imp );
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            //$group_by_str = "GROUP BY {$params['group_by']}";
            $this->db->group_by($params['group_by']);
        }


        // paginate
        if ($params['paginate'] != "") {
            $this->db->limit($params['paginate']['limit'], $params['paginate']['offset']);
        }
        if (isset($params['getCsv'])) {
            $query = $this->db->get()->result_array();
        } else {
            $query = $this->db->get();
        }
        if ($params['echo_query'] == 1) {
            //echo $sql;
            echo $this->db->last_query();
        }

        if ($params['return_count'] == 1) {
            // $j_sql = mysql_query($sql);
            // $row = mysql_fetch_array($j_sql);
            // return $row['jcount'];

            return $query->num_rows();
        } else {
            return $query;
        }
    }

    public function getSalesRep($params) {
        $this->db->distinct('sa.`StaffID`');
        $this->db->select('sa.`StaffID` , sa.`FirstName` , sa.`LastName`');
        $this->db->from('`agency` AS a');
        $this->db->join('`staff_accounts` AS sa', 'a.`salesrep` = sa.`StaffID`', 'LEFT');
        $this->db->where('a.`franchise_groups_id` =' . $params['franchise_groups_id'] . '
            AND sa.`StaffID` IS NOT NULL
            AND sa.`Deleted` = 0 
            AND sa.`active` = 1 
            ORDER BY sa.`FirstName` ASC;');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getAgencyRegion() {
        $this->db->select('agency_region_name');
        $this->db->from('agency_regions');
        $this->db->where("`agency_region_name` != ''");
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getAlarmJobTypes() {
        $this->db->select('id,type');
        $this->db->from('`alarm_job_type` ajt');
        $this->db->where('ajt.`active`=1');
        $query = $this->db->get();
        return $query;
    }

    public function getAlarmsServicesNumbers($params) {
        $this->db->select('COUNT( * ) AS num_serv');
        $this->db->from('`property_services` ps');
        $this->db->join('`property` p', 'ps.`property_id` = p.`property_id`', 'LEFT');
        $this->db->join('`agency` a', 'p.`agency_id` = a.`agency_id`', 'LEFT');
        $where = "p.`agency_id`={$params['agency_id']}
        AND p.`deleted`=0
        AND ps.`alarm_job_type_id`={$params['alarm_job_type_id']}";
        if (isset($params['service']) && $params['service'] !== "") {
            $where .= " AND ps.`service`= {$params['service']} ";
        }
        $this->db->where($where);

        $query = $this->db->get();
        return $query->row()->num_serv;
    }

    /**
     *  INSERT Franchise Groups TO DB
     *  @param $params | ARRAY | array of data to be used for the update
     */
    public function add_franchise_groups($params) {

        $this->db->insert('franchise_groups', $params);
        return $this->db->insert_id();
    }

    public function check_dup_franchise_groups($params) {
        $this->db->select("COUNT(*) as dups");
        $this->db->from("franchise_groups");
        $this->db->where('name', $params['name']);
        $this->db->where('country_id', $params['country_id']);
        $query = $this->db->get();
        return $query->row()->dups;
    }

    /**
     *  UPDATES THE FG DETAILS
     *  @param $params | ARRAY | array of data to be used for the update
     * */
    public function update_franchise_groups($params) {
        $this->db->where('franchise_groups_id', $params['franchise_groups_id']);
        $this->db->update('franchise_groups', $params['franchise_groups']);
        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }

    /**
     *  DELETES THE FG DETAILS
     *  @param $params | ARRAY | array of data to be used for the update
     * */
    public function remove_franchise_groups($params) {
        $this->db->where('franchise_groups_id', $params['franchise_groups_id']);
        $this->db->delete('franchise_groups');
        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }

}
