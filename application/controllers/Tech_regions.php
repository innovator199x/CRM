<?php

class Tech_regions extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->library('pagination');
        $this->load->helper('url');
    }

    public function index() {
        $data = [];

        $techRegions = $this->db->select("
            tr.id,
            tr.name,
            tr.breakpoint,
            COUNT(trr.tech_region_id) AS count
            ")
            ->from("tech_regions AS tr")
            ->join("tech_regions_regions AS trr", "trr.tech_region_id = tr.id", "left")
            ->group_by("tr.id")
            ->order_by("tr.name ASC")
            ->get()->result_array();

        $regions = $this->db->select("
            trr.tech_region_id,
            r.regions_id,
            r.region_name
            ")
            ->from("tech_regions_regions AS trr")
            ->join("regions AS r", "r.regions_id = trr.region_id", "left")
            ->get()->result_array();

        foreach ($techRegions as &$techRegion) {
            $techRegion["regions"] = array_filter($regions, function($region) use ($techRegion) {
                return $region["tech_region_id"] == $techRegion["id"];
            });
        }

        $techRegionTechnicians = $this->db->select("
            tr.id,
            COUNT(tech.accomodation_id) AS technician_count
            ")
            ->from("tech_regions AS tr")
            ->join("accomodation AS tech", "tr.id = tech.assigned_region", "left")
            ->join("staff_accounts AS sa", "sa.accomodation_id = tech.accomodation_id", "left")
            ->join("country_access AS ca", "ca.staff_accounts_id = sa.StaffID", "left")
            ->where("ca.country_id", $this->config->item("country"))
            ->where("ca.default", 1)
            ->where("sa.active", 1)
            ->group_by("tr.id")
            ->get()->result_array();

        foreach ($techRegions as &$techRegion) {
            $techRegion["technician_count"] = 0;

            foreach ($techRegionTechnicians AS $technician) {
                if ($technician["id"] == $techRegion["id"]) {
                    $techRegion["technician_count"] = $technician["technician_count"];
                    break;
                }
            }
        }

        $unassignedTechniciansQuery = $this->db->select("
                sa.StaffID,
                sa.FirstName,
                sa.LastName
            ")
            ->from("staff_accounts AS sa")
            ->join("accomodation AS a", "a.accomodation_id = sa.accomodation_id", "left")
            ->join("country_access AS ca", "ca.staff_accounts_id = sa.StaffID", "left")
            ->where("sa.ClassID", 6)
            ->where("sa.active", 1)
            ->where("sa.Deleted", 0)
            ->group_start()
            ->where("a.assigned_region IS NULL")
            ->or_where("a.assigned_region", "")
            ->group_end()
            ->where_not_in("sa.StaffID", [1, 2])
            ->where("ca.country_id", $this->config->item("country"))
            ->where("ca.default", 1);
        if ($this->config->item("country") == 2) {
            $unassignedTechniciansQuery->where("sa.StaffID !=", 2188);
        }
        $unassignedTechnicians = $this->db->get()->result_array();

        $unassignedRegions = $this->db->select("
                r.regions_id,
                r.region_name,
                r.region_state
            ")
            ->from("regions AS r")
            ->join("tech_regions_regions AS trr", "trr.region_id = r.regions_id", "left")
            ->where("r.country_id", $this->config->item("country"))
            ->where("r.status", 1)
            ->where("trr.tech_region_id IS NULL")
            ->group_by("r.regions_id")
            ->order_by("r.region_state asc, region_name asc")
            ->get()->result_array();

        $data = [
            "title" => "Tech Regions",
            "techRegions" => $techRegions,
            "unassignedTechnicians" => $unassignedTechnicians,
            "unassignedRegions" => $unassignedRegions,
        ];

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('tech_regions/index', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function add() {

        $data = [];

        $data["title"] = "Add Tech Region";

        if ($this->input->method() == "post") {
            $postData = $this->input->post();

            $this->db->trans_start();

            $inserted = $this->db->insert("tech_regions", $postData["tech_region"]);

            if ($inserted) {
                $techRegionId = $this->db->insert_id();

                $includedRegions = $postData["tech_regions_regions"];
                foreach ($includedRegions as &$includedRegion) {
                    $includedRegion["tech_region_id"] = $techRegionId;
                }

                $this->db->insert_batch("tech_regions_regions", $includedRegions);
            }

            $this->db->trans_complete();

            $success = $this->db->trans_status();

            if ($success) {
                $this->session->set_flashdata([
                    "success" => true,
                    "new_tech_region" => array_merge(
                        [
                            "tech_region_id" => $techRegionId,
                        ],
                        $postData["tech_region"]
                    ),
                ]);
            }
            else {
                $this->session->set_flashdata([
                    "success" => false,
                ]);
            }
        }

        if ($this->config->item("country") == 1) {
            $states = $this->db->select("
                StateID,
                state
            ")
            ->from("states_def")
            ->where("country_id", $this->config->item("country"))
            ->order_by("state_full_name", "asc")
            ->get()->result_array();
        }
        else {
            $states = $this->db
                ->distinct(("r.region_state"))
                ->select("
                    r.region_state,
                    r.region_state AS state
                ")
                ->from("regions AS r")
                ->where("r.country_id", $this->config->item("country"))
                ->where("r.status", 1)
                ->order_by("r.region_state ASC, r.region_name ASC")
                ->get()->result_array();

            foreach($states as $index => &$state) {
                $state["StateID"] = $index;
            }
        }

        $regions = $this->db->select("
                r.regions_id,
                r.region_name,
                r.region_state,
                tr.id AS tech_region_id,
                tr.name AS tech_region_name
            ")
            ->from("regions AS r")
            ->join("tech_regions_regions AS trr", "trr.region_id = r.regions_id", "left")
            ->join("tech_regions AS tr", "tr.id = trr.tech_region_id", "left")
            ->where("r.country_id", $this->config->item("country"))
            ->where("r.status", 1)
            ->group_by("r.regions_id")
            ->order_by("r.region_state asc, region_name asc")
            ->get()->result_array();


        foreach($states as &$state) {
            $state["regions"] = [];

            foreach($regions as $region) {
                if ($state["state"] == $region["region_state"]) {
                    $state["regions"][] = $region;
                }
            }
        }

        $data["states"] = $states;

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('tech_regions/add', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function edit($techRegionId) {

        $data = [];

        $techRegion = $this->db->select("
                id,
                name,
                breakpoint
            ")
            ->from("tech_regions")
            ->where("id", $techRegionId)
            ->get()->row_array();

        $includedRegions = $this->db->select("
                trr.tech_region_id,
                trr.region_id,
                r.regions_id,
                r.region_name,
                r.region_state,
            ")
            ->from("tech_regions_regions AS trr")
            ->join("regions AS r", "r.regions_id = trr.region_id", "inner")
            ->where("trr.tech_region_id", $techRegionId)
            ->get()->result_array();

        $techRegion["regions"] = $includedRegions;

        $data["techRegion"] = $techRegion;

        $data["title"] = $techRegion["name"];

        if ($this->config->item("country") == 1) {
            $states = $this->db->select("
                StateID,
                state
            ")
            ->from("states_def")
            ->where("country_id", $this->config->item("country"))
            ->order_by("state_full_name", "asc")
            ->get()->result_array();
        }
        else {
            $states = $this->db
                ->distinct(("r.region_state"))
                ->select("
                    r.region_state,
                    r.region_state AS state
                ")
                ->from("regions AS r")
                ->where("r.country_id", $this->config->item("country"))
                ->where("r.status", 1)
                ->order_by("r.region_state ASC, r.region_name ASC")
                ->get()->result_array();

            foreach($states as $index => &$state) {
                $state["StateID"] = $index;
            }
        }

        $regions = $this->db->select("
                r.regions_id,
                r.region_name,
                r.region_state,
                tr.id AS tech_region_id,
                tr.name AS tech_region_name
            ")
            ->from("regions AS r")
            ->join("tech_regions_regions AS trr", "trr.region_id = r.regions_id", "left")
            ->join("tech_regions AS tr", "tr.id = trr.tech_region_id", "left")
            ->where("r.country_id", $this->config->item("country"))
            ->where("r.status", 1)
            ->group_by("r.regions_id")
            ->order_by("r.region_state asc, region_name asc")
            ->get()->result_array();


        foreach($states as &$state) {
            $state["regions"] = [];

            foreach($regions as $region) {
                if ($state["state"] == $region["region_state"]) {
                    $state["regions"][] = $region;
                }
            }
        }

        $data["states"] = $states;

        $assignedTechnicians = $this->db->select("
                sa.StaffID,
                sa.FirstName,
                sa.LastName,
                acc.accomodation_id,
                acc.assigned_region
            ")
            ->from("staff_accounts AS sa")
            ->join("accomodation AS acc", "acc.accomodation_id = sa.accomodation_id", "inner")
            ->where("sa.ClassID", 6)
            ->where("acc.assigned_region", $techRegionId)
            ->where("sa.active", 1)
            ->get()->result_array();

        $data["assignedTechnicians"] = $assignedTechnicians;

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('tech_regions/edit', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_update_fields() {
        $postData = $this->input->post();

        $this->db->trans_start();

        $affectedRows = 0;
        foreach($postData as $table => $tableData) {
            $idField = $tableData["_idField"];
            $idValue = $tableData["_idValue"];

            $this->db->set($tableData["fields"])
                ->where($idField, $idValue)
                ->update($table);

            $affectedRows += $this->db->affected_rows();
        }

        $this->db->trans_complete();

        $success = $this->db->trans_status();

        $jsonData = [];
        $jsonData["success"] = $success;

        if ($success) {
            $jsonData["message"] = "Update successful.";
        }
        else {
            $jsonData["message"] = "Update failed.";
        }

        echo json_encode($jsonData);
        return;
    }

    public function update_regions() {

        $postData = $this->input->post();

        $techRegionId = $postData["tech_region_id"];

        $this->db->trans_start();

        $includedRegions = $postData["tech_regions_regions"];
        foreach ($includedRegions as &$includedRegion) {
            $includedRegion["tech_region_id"] = $techRegionId;
        }

        $this->db->delete("tech_regions_regions", "tech_region_id = {$techRegionId}");
        $this->db->insert_batch("tech_regions_regions", $includedRegions);

        $this->db->trans_complete();

        $success = $this->db->trans_status();

        if ($success) {
            $this->session->set_flashdata([
                "success" => true,
                "message" => "Included regions updated.",
            ]);
        }
        else {
            $this->session->set_flashdata([
                "success" => false,
            ]);
        }

        $this->load->library('user_agent');

        $referrer = $this->agent->referrer() != '' ? $this->agent->referrer() : base_url("/tech_regions/index");
        redirect($referrer);
    }

    public function reports() {

        $data = [
            "title" => "Tech Region Numbers",
        ];

        $country_id = $this->config->item('country');

        $states = $this->db->select("regions.region_state as name")
            ->from("tech_regions_regions")
            ->join("regions", "tech_regions_regions.region_id = regions.regions_id", "inner")
            ->where('regions.country_id', $country_id)
            ->order_by("regions.region_state", "asc")
            ->group_by('regions.region_state')
            ->get()->result_array();
        $data["states"] = $states;

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('tech_regions/reports', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_reports_data() {

        $state = $this->input->get_post('state_filter');
        $this->db->select("tr.id,tr.name,tr.breakpoint,tr.techs_needed")
            ->from("tech_regions AS tr")
            ->join("tech_regions_regions as trr", "tr.id = trr.tech_region_id")
            ->join("regions as r", "trr.region_id = r.regions_id");

        if ($state !='' ) {
            $this->db->where('r.region_state', $state);
        }
        $techRegions = $this->db->group_by('tr.id')->get()->result_array();

        $techRegionsById = [];

        for ($x = 0; $x < count($techRegions); $x++) {
            $tr =& $techRegions[$x];
            $tr["property_count"] = 0;
            $tr["technician_count"] = 0;
            $tr["job_count"] = 0;

            $techRegionsById[$tr["id"]] =& $tr;
        }

        $this->db->distinct("ps.property_id")
            ->select("ps.property_id")
            ->from("property_services AS ps")
            ->join("property AS p2", "ps.property_id = p2.property_id", "inner")
            ->join("agency AS a2", "p2.agency_id = a2.agency_id", "inner")
            ->where("ps.service", 1)
            ->where("p2.deleted", 0)
            ->where("a2.status", "active")
            ->where("a2.country_id", $this->config->item("country"));
        $selectQuery = $this->db->get_compiled_select();

        $techRegionProperties = $this->db->select("
            tr.id,
            COUNT(p.property_id) AS property_count
            ")
            ->from("tech_regions AS tr")
            ->join("tech_regions_regions AS trr", "tr.id = trr.tech_region_id", "left")
            ->join("regions AS r", "trr.region_id = r.regions_id", "left")
            ->join("sub_regions AS sr", "r.regions_id = sr.region_id", "left")
            ->join("postcode AS pc", "sr.sub_region_id = pc.sub_region_id", "left")
            ->join("property AS p", "pc.postcode = p.postcode", "left")
            ->join("({$selectQuery}) AS ps", "p.property_id = ps.property_id", "inner")
            ->join("agency AS a", "p.agency_id = a.agency_id", "left")
            ->where("a.franchise_groups_id !=", 14)
            ->where("a.status", 'active')
            ->where("a.country_id", $this->config->item("country"))
            ->where("p.deleted", 0)
            ->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )")
            // ->where("p.property_id IN ({$selectQuery})")
            ->group_by("tr.id")
            ->get()->result_array();

        foreach ($techRegionProperties as $trp) {
            $techRegionsById[$trp["id"]]["property_count"] = intval($trp["property_count"]);
        }

        $techRegionTechnicians = $this->db->select("
            tr.id,
            COUNT(tech.accomodation_id) AS technician_count
            ")
            ->from("tech_regions AS tr")
            ->join("accomodation AS tech", "tr.id = tech.assigned_region", "left")
            ->join("staff_accounts AS sa", "sa.accomodation_id = tech.accomodation_id", "left")
            ->join("country_access AS ca", "ca.staff_accounts_id = sa.StaffID", "left")
            ->where("ca.country_id", $this->config->item("country"))
            ->where("ca.default", 1)
            ->where("sa.active", 1)
            ->group_by("tr.id")
            ->get()->result_array();

        foreach ($techRegionTechnicians as $trt) {
            $techRegionsById[$trt["id"]]["technician_count"] = intval($trt["technician_count"]);
        }

        $techRegionIncompleteJobs = $this->db->select("
            tr.id,
            COUNT(j.id) AS job_count
            ")
            ->from("tech_regions AS tr")
            ->join("tech_regions_regions AS trr", "tr.id = trr.tech_region_id", "left")
            ->join("regions AS r", "trr.region_id = r.regions_id", "left")
            ->join("sub_regions AS sr", "r.regions_id = sr.region_id", "left")
            ->join("postcode AS pc", "sr.sub_region_id = pc.sub_region_id", "left")
            ->join("property AS p", "pc.postcode = p.postcode", "left")
            ->join("agency AS a", "p.agency_id = a.agency_id", "left")
            ->join("jobs AS j", "p.property_id = j.property_id", "left")
            ->where("a.franchise_groups_id !=", 14)
            ->where("a.status", 'active')
            ->where("p.deleted", 0)
            ->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )")
            ->where("a.country_id", $this->config->item("country"))
            ->not_group_start()
            ->where_in("j.status", ["Completed", "Merged", "Service Due", "Cancelled"])
            ->group_end()
            ->group_by("tr.id")
            ->get()->result_array();

        foreach ($techRegionIncompleteJobs as $trj) {
            $techRegionsById[$trj["id"]]["job_count"] = intval($trj["job_count"]);
        }

        $this->output
            ->set_status_header(200)
            ->set_content_type("application/json")
            ->set_output(json_encode([
                "tech_regions" => $techRegions,
            ]));
    }

    public function find_assignable_regions() {
        $query = $this->input->get("term");

        $regions = $this->db->select("
            id,
            name
            ")
            ->from("tech_regions")
            ->where("name LIKE", "{$query}%")
            ->limit(10)
            ->get()->result_array();

        echo json_encode($regions);
    }

    public function ajax_update_techs_needed() {
        $postData = $this->input->post();

        $updated = $this->db->set([
                "techs_needed" => $postData["techs_needed"],
            ])
            ->where("id", $postData["tech_region_id"])
            ->update("tech_regions");

        if ($updated) {
            $this->output->set_status_header(201);
        }
        else {
            $this->output->set_status_header(400);
        }
    }

    public function ajax_find_assignable_technician() {
        $query = $this->input->get("term");
        $techRegionId = $this->input->get("tech_region_id");

        $assignableTechnicians = $this->db->select("
                sa.StaffID,
                sa.FirstName,
                sa.LastName,
                acc.accomodation_id,
                acc.assigned_region
            ")
            ->from("staff_accounts AS sa")
            ->join("accomodation AS acc", "acc.accomodation_id = sa.accomodation_id", "inner")
            ->join("country_access AS ca", "ca.staff_accounts_id = sa.StaffID", "left")
            ->where("sa.ClassID", 6)
            ->where("sa.Deleted", 0)
            ->where("sa.active", 1)
            // ->group_start()
            // Remove current assigned_region to get the current region
            // ->where("acc.assigned_region !=", $techRegionId)
            // ->or_where("acc.assigned_region IS NULL")
            // ->group_end()
            ->group_start()
            ->where("sa.FirstName LIKE", "{$query}%")
            ->or_where("sa.LastName LIKE", "{$query}%")
            ->or_where("CONCAT(sa.FirstName, ' ', sa.LastName) LIKE", "{$query}%")
            ->group_end()
            ->where_not_in("sa.StaffID", [1, 2])
            ->where("ca.country_id", $this->config->item("country"))
            ->where("ca.default", 1)
            ->get()->result_array();

        foreach($assignableTechnicians as &$technician) {
            $technician["name"] = "{$technician["FirstName"]} {$technician["LastName"]}";
            if ($technician["assigned_region"] == $techRegionId) {
                $technician["name"] .= " (Assigned to this Region)";
            } elseif (!is_null($technician["assigned_region"])) {
                $technician["name"] .= " (Assigned)";
            } else {
                $technician["name"] .= " (Unassigned)";
            }
            $technician["short_name"] = $this->system_model->formatStaffName($technician["FirstName"], $technician["LastName"]);
        }

        echo json_encode($assignableTechnicians);
    }

    public function ajax_assign_technician() {
        $postData = $this->input->post();

        $staffId = $postData["staff_id"];
        $accommodationId = $postData["accommodation_id"];
        $techRegionId = $postData["tech_region_id"];

        if (!is_null($accommodationId)) {
            $success = $this->db->set([
                    "assigned_region" => $techRegionId,
                ])
                ->where("accomodation_id", $accommodationId)
                ->update("accomodation");
        }
        else {
            $techInfo = $this->db->select("
                sa.StaffID,
                sa.FirstName,
                sa.LastName,
                sa.address
                ")
                ->from("staff_accounts AS sa")
                ->where("sa.StaffID", $staffId)
                ->limit(1)
                ->get()->row_array();

            $this->db->trans_start();
            $this->db->insert("accomodation", [
                    "name" => "{$techInfo["FirstName"]} {$techInfo["LastName"]}",
                    "address" => $techInfo["address"],
                    "lat" => 0,
                    "lng" => 0,
                    "assigned_region" => $techRegionId,
                ]);

            $accommodationId = $this->db->insert_id();
            $this->db->update('staff_accounts', [
                'accomodation_id' => $accommodationId,
            ], "StaffID = {$staffId}");
            $this->db->trans_complete();

            $success = $this->db->trans_status();
        }

        if ($success) {
            $this->output
                ->set_status_header(200)
                ->set_content_type("application/json", "utf-8")
                ->set_output(json_encode([
                    "accommodation_id" => $accommodationId,
                ]));
        }
        else {
            $this->output->set_status_header(400);
        }
    }

    public function ajax_unassign_technician() {
        $postData = $this->input->post();

        $accommodationId = $postData["accommodation_id"];

        $success = $this->db->update("accomodation", [
                "assigned_region" => null,
        ], "accomodation_id = {$accommodationId}");

        $this->output->set_status_header($success != false ? 200 : 400);
    }

}