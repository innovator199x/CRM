<?php

class Admin_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    /**
     * get checkIfAccomIsConctdToUser by accomodation id
     * return boolean
     */
    public function checkIfAccomIsConctdToUser($accomodation_id) {
        $this->db->select('*');
        $this->db->from('staff_accounts');
        $this->db->where('accomodation_id', $accomodation_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function get_agency_site_maintenance_mode() {

        $this->db->select('mode');
        $this->db->from('agency_site_maintenance_mode');
        $query = $this->db->get();
        return $query;
    }

    public function get_internal_doc_header($country_id) {
        $this->db->select("DISTINCT(tdh.`admin_doc_header_id`), tdh.name");
        $this->db->from("`admin_doc_header` AS tdh");
        $this->db->join("`admin_documents` AS td", "tdh.`admin_doc_header_id` = td.`admin_doc_header_id`", "LEFT");
        $this->db->where("tdh.`country_id`=$country_id");
        $this->db->order_by("tdh.`name`", "ASC");
        $query = $this->db->get();
        return $query;
    }

    public function get_internal_docs_by_header($admin_doc_header_id, $country_id) {
        $this->db->select("filename,path,title,date,url,type,admin_documents_id");
        $this->db->from("`admin_documents` AS td");
        $this->db->join("`admin_doc_header` AS tdh", "tdh.`admin_doc_header_id` = td.`admin_doc_header_id`", "LEFT");
        $this->db->where("tdh.`country_id`=$country_id");
        $this->db->where("td.`admin_doc_header_id`=$admin_doc_header_id");
        $this->db->order_by("td.`date`", "DESC");
        $query = $this->db->get();
        return $query;
    }

    public function add_internal_doc($params) {

        $this->db->insert('admin_documents', $params);
        return $this->db->insert_id();
    }

    public function edit_internal_doc_header($params, $header_id, $country_id) {
        $this->db->where('admin_doc_header_id', $header_id);
        $this->db->where("country_id", $country_id);
        $this->db->update('admin_doc_header', $params);
        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }

    public function add_internal_doc_header($params) {

        $this->db->insert('admin_doc_header', $params);
        return $this->db->insert_id();
    }

    public function remove_internal_doc_header($header_id) {
        $this->db->where('admin_doc_header_id', $header_id);
        $this->db->delete('admin_doc_header');
        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }

    public function remove_internal_doc($doc_id) {
        $this->db->where('admin_documents_id', $doc_id);
        $this->db->delete('admin_documents');
        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }

    public function get_resources_header(){
        
        /*$this->db->distinct('rh.resources_header_id');
        $this->db->select('rh.resources_header_id, rh.name');
        $this->db->from('resources as r');
        $this->db->join('resources_header as rh','rh.resources_header_id = r.resources_header_id','left');
        $this->db->where('r.country_id', $this->config->item('country'));
        $this->db->where('rh.status', 1);
        $this->db->order_by('rh.name','asc');
        return $this->db->get();*/
        $this->db->select('*');
        $this->db->from('resources_header');
        $this->db->where('country_id', $this->config->item('country'));
        $this->db->where('status', 1);
        $this->db->order_by('name','asc');
        return $this->db->get();

    }

    /**
     *  Get Resources List
     *  @params header_id, state
     */
    public function jgetResourceList($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`resources` AS r');
        $this->db->join('resources_header as rh', 'rh.resources_header_id = r.resources_header_id', 'left');
        $this->db->where('rh.resources_header_id', $params['header_id']);
        $this->db->where('r.country_id', $this->config->item('country'));
        $this->db->order_by('r.states','asc');

        if(!empty($params['state_filter'])){
            $this->db->like('r.states', $params['state_filter']);
        }

        return $this->db->get();

    }

    public function ifCountryHasState(){
        $sql = $this->db->query("
            SELECT *
            FROM `countries`
            WHERE `country_id` = {$this->config->item('country')}
        ");
        $row = $sql->row_array();
        if($row['states']==1){
           return true;
        }else{
           return false;
        }
    }

    public function insert_resources($data){
        $this->db->insert('resources', $data);
        $this->db->limit(1);
        return $this->db->insert_id();
    }

    public function getSmokeAlarms($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('smoke_alarms');
        $this->db->where('smoke_alarm_id >',0);
        $this->db->where('country_id', $this->config->item('country'));

        if ($params['smoke_alarm_id'] != "") {
            $this->db->where('smoke_alarm_id', $params['smoke_alarm_id']);
        }

        if(!empty($params['search'])){
            $search_where = "CONCAT_WS(' ', LOWER(make), LOWER(model))";
            $this->db->like($search_where, $params['search']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // sort
        // custom filter
        if (isset($params['custom_sort'])) {
            $this->db->order_by($params['custom_sort']);
        }

        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // limit
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;

    }

    public function insert_new_alarm($data){
        $this->db->insert('smoke_alarms', $data);
        $this->db->limit(1);
        return $this->db->insert_id();
    }

    public function update_alarm_image_path($smoke_alarm_id,$data){
        $this->db->where('smoke_alarm_id', $smoke_alarm_id);
        $this->db->update('smoke_alarms',$data);
        $this->db->limit(1);
    }

    public function update_smoke_alarms($sa_id, $data){
        
        $this->db->where('smoke_alarm_id', $sa_id);
        $this->db->update('smoke_alarms', $data);
        $this->db->limit(1);

    }

    function deleteFile($path_to_file) {
        $del_file = "{$path_to_file}";

        if ($del_file != "") {
            // delete file
            unlink($del_file);
        }
    }

    //GET data from DB
    public function getCountries() {
        return $this->db->select(
            'countries.country_id,
             countries.country,
             countries.iso,
             countries.agent_number,
             countries.tenant_number,
             countries.ac_name,
             countries.outgoing_email,
             countries.bank,
             countries.bsb,
             countries.ac_number
            ')
        ->from('countries')
        ->get()->result_object();
        $this->db->get('countries');
    }//endfct

    //GET Country By ID from DB
    public function getCountryById($id) {
        return $this->db->select('*')
        ->from('countries')
        ->where('country_id', $id)
        ->get()->result_object();
        $this->db->get('countries');
    }//endfct

    //UPDATE Country By ID from DB
    public function updateCountry($country_id, $update_data) {
      $this->db->where('country_id', $country_id);
      $this->db->update('countries', $update_data);

      if($this->db->affected_rows()>0){
          return true;
      }
      else{
          return false;
      }
    }//endfct

    //COUNT Distinct Region State from DB -- USED!!
    public function countDistinctState($country_id) {
        $this->db->select('region_state');
        $this->db->distinct();
        $this->db->where('country_id', $country_id);
        $query = $this->db->get('regions');
        return count($query->result()); 
    }//endfct

    //SEARCH keyword if STATE
    public function searchRegionCheckByState($keyword){
        return $this->db->select('region_state')
        ->from('regions')
        ->where('region_state', $keyword)
        ->where('status', 1)
        ->get()->result_object();
        $this->db->get('regions');
    }//endfct

    //SEARCH keyword if REGION
    public function searchRegionCheckByRegion($keyword,$country_id){
        return $this->db->select('region_name')
        ->from('regions')
        ->like('region_name', $keyword)
        //->where('region_name', $keyword)
        ->where('country_id', $country_id)
        ->where('status', 1)
        ->get()->result_object();
        $this->db->get('regions');
    }//endfct

    //SEARCH keyword if REGION
    public function searchRegionCheckBySubregion($keyword){
        return $this->db->select('subregion_name')
        ->from('sub_regions')
        ->like('subregion_name', $keyword)
        ->where('active', 1)
        ->get()->result_object();
        $this->db->get('sub_regions');
    }//endfct

    //SEARCH regions By Postcode from DB
    public function searchRegionsByPostcode($postcode) {
        return $this->db->select('DISTINCT `r`.`region_state`', FALSE)
        ->from('`postcode` AS p')
        ->join('sub_regions as sr', 'p.sub_region_id = sr.sub_region_id', 'left')
        ->join('regions as r', 'sr.region_id = r.regions_id', 'left')
        ->like('postcode', $postcode)
        ->where('p.deleted', 0)
        ->order_by('r.region_state','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //SEARCH regions By State from DB
    /*public function searchRegionsByState($state, $region, $subregion) {
        return $this->db->select('DISTINCT `r`.`region_state`', FALSE)
        ->from('`postcode` AS p')
        ->join('sub_regions as sr', 'p.sub_region_id = sr.sub_region_id', 'left')
        ->join('regions as r', 'sr.region_id = r.regions_id', 'left')
        ->like('region_state', $state)
        ->where('p.deleted', 0)
        ->order_by('r.region_name','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }*///endfct

    //SEARCH regions By State from DB
    public function searchRegionsByState($state) {
        return $this->db->select('DISTINCT `r`.`region_state`', FALSE)
        ->from('`postcode` AS p')
        ->join('sub_regions as sr', 'p.sub_region_id = sr.sub_region_id', 'left')
        ->join('regions as r', 'sr.region_id = r.regions_id', 'left')
        ->where('r.region_state', $state)
        ->where('p.deleted', 0)
        ->order_by('r.region_name','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //SEARCH regions By Region from DB
    public function searchRegionsByRegion($region) {
        return $this->db->select('DISTINCT `r`.`region_state`', FALSE)
        ->from('`postcode` AS p')
        ->join('sub_regions as sr', 'p.sub_region_id = sr.sub_region_id', 'left')
        ->join('regions as r', 'sr.region_id = r.regions_id', 'left')
        ->where('r.region_name', $region)
        ->where('p.deleted', 0)
        ->order_by('r.region_name','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //SEARCH regions By Subregion from DB
    public function searchRegionsBySubregion($subregion) {
        return $this->db->select('DISTINCT `r`.`region_state`', FALSE)
        ->from('`postcode` AS p')
        ->join('sub_regions as sr', 'p.sub_region_id = sr.sub_region_id', 'left')
        ->join('regions as r', 'sr.region_id = r.regions_id', 'left')
        ->where('sr.subregion_name', $subregion)
        ->where('p.deleted', 0)
        ->order_by('r.region_name','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //CHECK duplicate postcode from DB -- USED!!!
    public function searchDuplicatePostcode($postcode) {
        return $this->db->select('postcode,sub_region_id')
        ->from('postcode')
        ->where('postcode', $postcode)
        ->where('deleted', 0)
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //CHECK duplicate subregion name from DB -- USED!!!
    public function searchDuplicateSubregion($subregion_name) {
        return $this->db->select('subregion_name')
        ->from('sub_regions')
        ->where('subregion_name', $subregion_name)
        ->where('active', 1)
        ->get()->result_object();
        $this->db->get('sub_regions');
    }//endfct

    //CHECK duplicate region name from DB -- USED!!!
    public function searchDuplicateRegion($region_name) {
        return $this->db->select('region_name')
        ->from('regions')
        ->where('region_name', $region_name)
        ->where('status', 1)
        ->get()->result_object();
        $this->db->get('regions');
    }//endfct


    //GET Search Postcode By Subregion from DB -- USED!!!
    public function getSeachPostcodeBySubregion($subregion_id, $postcode) {
        return $this->db->select('postcode')
        ->from('postcode')
        ->like('postcode', $postcode)
        ->where('sub_region_id', $subregion_id)
        ->where('deleted', 0)
        ->order_by('sub_region_id','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct
    

    //GET region By ID from DB
    public function getSearchRegionsByDistinctState($region_state,$postcode) {
        return $this->db->select('DISTINCT `r`.`region_name`,`sr`.`region_id`', FALSE)
        ->from('`postcode` AS p')
        ->join('sub_regions as sr', 'p.sub_region_id = sr.sub_region_id', 'left')
        ->join('regions as r', 'sr.region_id = r.regions_id', 'left')
        ->like('postcode', $postcode)
        ->where('r.region_state', $region_state)
        ->where('p.deleted', 0)
        ->order_by('r.region_name','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //GET Region By Distinct Region from DB
    public function getSearchRegionsByDistinctRegion($region_state,$regkeyword) {
        return $this->db->select('DISTINCT `r`.`region_name`,`sr`.`region_id`', FALSE)
        ->from('`postcode` AS p')
        ->join('sub_regions as sr', 'p.sub_region_id = sr.sub_region_id', 'left')
        ->join('regions as r', 'sr.region_id = r.regions_id', 'left')
        ->like('r.region_name', $regkeyword)
        ->where('r.region_state', $region_state)
        //->where('r.region_name', $regkeyword)
        ->where('p.deleted', 0)
        ->order_by('r.region_name','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //GET Region By Distinct Subreg from DB
    public function getSearchRegionsByDistinctSubreg($region_state,$subkeyword) {
        return $this->db->select('DISTINCT `r`.`region_name`,`sr`.`region_id`', FALSE)
        ->from('`postcode` AS p')
        ->join('sub_regions as sr', 'p.sub_region_id = sr.sub_region_id', 'left')
        ->join('regions as r', 'sr.region_id = r.regions_id', 'left')
        ->like('sr.subregion_name', $subkeyword)
        ->where('r.region_state', $region_state)
        //->where('sr.subregion_name', $subkeyword)
        ->where('p.deleted', 0)
        ->order_by('r.region_name','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //GET region By ID from DB
    public function getSearchRegionsBySubregion($region_name,$postcode) {
        return $this->db->select('*')
        ->from('`postcode` AS p')
        ->join('regions as r', 'p.region_id = r.regions_id', 'left')
        ->like('postcode', $postcode)
        ->where('r.subregion_name', $subregion_name)
        ->where('p.deleted', 0)
        ->order_by('r.region_name','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //GET region By ID from DB
    
    public function getSearchRegionsByPostcode($region_id,$postcode) {
        return $this->db->select('DISTINCT `sr`.`subregion_name`, `p`.`sub_region_id`', FALSE)
        ->from('`postcode` AS p')
        ->join('sub_regions as sr', 'p.sub_region_id = sr.sub_region_id', 'left')
        ->join('regions as r', 'sr.region_id = r.regions_id', 'left')
        ->like('postcode', $postcode)
        ->where('r.regions_id', $region_id)
        ->where('p.deleted', 0)
        ->order_by('sr.subregion_name','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct 
    
    public function getSearchRegionsByDistinctSubregion($region_id,$postcode) {
        return $this->db->select('DISTINCT `sr`.`subregion_name` , `sr`.`sub_region_id`', FALSE)
        ->from('`sub_regions` AS sr')
        ->join('regions AS r', 'sr.region_id = r.regions_id', 'left')
        ->where('sr.region_id', $region_id)
        ->order_by('sr.subregion_name','asc')
        ->get()->result_object();
        $this->db->get('sub_regions');
    }//endfct

    public function getSearchRegionsByDistinctSubkeyword($region_id,$subkeyword) {
        return $this->db->select('DISTINCT `sr`.`subregion_name` , `sr`.`sub_region_id`', FALSE)
        ->from('`sub_regions` AS sr')
        ->join('regions AS r', 'sr.region_id = r.regions_id', 'left')
        ->like('sr.subregion_name', $subkeyword)
        ->where('sr.region_id', $region_id)
        //->where('sr.subregion_name', $subkeyword)
        ->order_by('sr.subregion_name','asc')
        ->get()->result_object();
        $this->db->get('sub_regions');
    }//endfct

    //GET All Region State Distinct
    public function getAllStateByDistinct($limit, $start, $country_id) {
        return $this->db->select('DISTINCT `region_state`', FALSE)
        ->from('regions')
        ->where('status', 1)
        ->where('country_id', $country_id)
        ->limit($limit, $start)
        ->order_by('region_state','asc')
        ->get()->result_object();
        $this->db->get('regions');
    }//endfct

    //GET Postcodes By ID and Subregion Name -- USED!!
    public function getPostcodesBySubregion($subregion_id) {
        return $this->db->select('postcode')
        ->from('postcode')
        ->where('sub_region_id', $subregion_id)
        ->where('deleted', 0)
        ->order_by('sub_region_id','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //GET Postcodes By ID and Subregion Name -- USED!!
    public function getPostcodeIds($subregion_name, $region_id) {
        return $this->db->select('id')
        ->from('postcode')
        ->where('region_id', $region_id)
        ->where('subregion_name', $subregion_name)
        ->where('deleted', 0)
        ->order_by('subregion_name','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //GET region By ID from DB
    public function getRegionsById($region_id) {
        return $this->db->select('*')
        ->from('regions')
        ->where('status', 1)
        ->where('regions_id', $region_id)
        ->get()->result_object();
        $this->db->get('regions');
    }//endfct

    //GET region By State from DB
    public function getRegionsByState($region_state,$c_id) {
        return $this->db->select('*')
        ->from('regions')
        ->where('status', 1)
        ->where('region_state', $region_state)
        ->where('country_id', $c_id)
        ->get()->result_object();
        $this->db->get('regions');
    }//endfct

    //GET all Regions from DB
    public function getAllRegions($country_id) {
        return $this->db->select('*')
        ->from('regions')
        ->where('country_id', $country_id)
        ->where('status', 1)
        ->order_by('region_name','asc')
        ->get()->result_object();
        $this->db->get('regions');
    }//endfct

    //GET all Subregions from DB
    public function getAllSubregions() {
        return $this->db->select('subregion_name,sub_region_id')
        ->from('sub_regions')
        ->where('active', 1)
        ->order_by('subregion_name','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //GET all States from DB
    public function getAllStates($country_id) {
        return $this->db->select('DISTINCT `region_state`', FALSE)
        ->from('regions')
        ->where('country_id', $country_id)
        ->where('status', 1)
        ->order_by('region_state','asc')
        ->get()->result_object();
        $this->db->get('regions');
    }//endfct

    //GET Subregion By ID from DB -- USED!!!
    public function getSubregionsById($region_id,$c_id) {
        return $this->db->select('DISTINCT `sr`.`subregion_name`,`sr`.`sub_region_id`', FALSE)
        ->from('`sub_regions` AS sr')
        ->join('regions AS r', 'sr.region_id = r.regions_id', 'left')
        ->where('sr.region_id', $region_id)
        ->where('r.country_id', $c_id)
        ->order_by('sr.subregion_name','asc')
        ->get()->result_object();
        $this->db->get('sub_regions');
    }//endfct

    //GET Postcode By Region ID from DB
    public function getPostcodeById($region_id) {
        return $this->db->select('DISTINCT `postcode`', FALSE)
        ->from('postcode')
        ->where('deleted', 0)
        ->where('region_id', $region_id)
        ->order_by('postcode','asc')
        ->get()->result_object();
        $this->db->get('postcode');
    }//endfct

    //GET Subregion ID By Subregion Name from DB
    public function getSubregionidBySubregionname($subregion_name) {
        return $this->db->select('sub_region_id')
        ->from('sub_regions')
        ->where('active', 1)
        ->where('subregion_name', $subregion_name)
        ->order_by('subregion_name','asc')
        ->get()->result_object();
        $this->db->get('sub_regions');
    }//endfct

    //UPDATE Region By ID from DB
    public function updateRegionById($region_id, $update_data) {
        $this->db->where('regions_id', $region_id);
        $this->db->update('regions', $update_data);
  
        if($this->db->affected_rows()>0){
            return true;
        }
        else{
            return false;
        }
    }//endfct

    //UPDATE Region By ID from DB
    public function updateSubRegionByName($subregion_id, $update_data) {
        $this->db->where('sub_region_id', $subregion_id);
        $this->db->update('sub_regions', $update_data);
  
        if($this->db->affected_rows()>0){
            return true;
        }
        else{
            return false;
        }
    }//endfct

    //UPDATE Subregion By ID from DB
    public function updateSubregionById($subregion_id, $update_data) {
        $this->db->where('id', $subregion_id);
        $this->db->update('postcode', $update_data);
  
        if($this->db->affected_rows()>0){
            return true;
        }
        else{
            return false;
        }
    }//endfct

    //DELETE region By ID from DB
    public function deleteRegionById($region_id) {
        $this->db->delete('regions', array('regions_id' => $region_id));
  
        if($this->db->affected_rows()>0){
            return true;
        }
        else{
            return false;
        }
    }//endfct

    //DELETE Subregion By ID from DB
    public function deleteSubregionById($subregion_id) {
        $this->db->delete('postcode', array('sub_region_id' => $subregion_id));
        $this->db->delete('sub_regions', array('sub_region_id' => $subregion_id));
        
        if($this->db->affected_rows()>0){
            return true;
        }
        else{
            return false;
        }
    }//endfct

    //ADD New Region
    public function addNewRegion($params) {
        $this->db->insert('regions', $params);
        return $this->db->insert_id();
    }//endfct

    //ADD New Region
    public function addNewSubRegion($params) {
        $this->db->insert('sub_regions', $params);
        return $this->db->insert_id();
    }//endfct

    //ADD New Postcode
    public function addNewPostcode($params) {
        $this->db->insert('postcode', $params);
        return $this->db->insert_id();
    }//endfct

    //ADD New Account
    public function addNewAccount($params) {
        $this->db->insert('site_accounts', $params);
        return $this->db->insert_id();
    }//endfct

    //DELETE Postcode By Region Name
    public function deletePostcodeBySubregionName($subregion_id) {
        $this->db->where('sub_region_id', $subregion_id);
        $this->db->delete('postcode');
  
        if($this->db->affected_rows()>0){
            return true;
        }
        else{
            return false;
        }
    }//endfct

    //GET All accounts from DB
    public function getAllAccounts($country_id,$status) {
        $accounts = $this->db->select('*')
            ->from('site_accounts')
            ->where('country_id', $country_id);

        if($status == 'all'){
            $accounts->where_in('status', [0,1]);
        } else if($status == 'active' || $status == 1){
            $accounts->where('status', 1);
        } else if($status == 'inactive'){
            $accounts->where('status', 0);
        } 

        return $accounts->get()->result();
    }//endfct

    //GET Account BY ID from DB
    public function getAccountsById($password_id) {
        return $this->db->select('*')
        ->from('site_accounts')
        ->where('site_accounts_id', $password_id)
        ->get()->result_object();
        $this->db->get('site_accounts');
    }//endfct

    //UPDATE Password By ID from DB
    public function updatePasswordById($password_id, $update_data) {
        $this->db->where('site_accounts_id', $password_id);
        $this->db->update('site_accounts', $update_data);
  
        if($this->db->affected_rows()>0){
            return true;
        }
        else{
            return false;
        }
    }//endfct

    //DELETE Password By ID from DB
    public function deletePasswordById($password_id) {
        $this->db->where('site_accounts_id', $password_id);
        $this->db->delete('site_accounts');
  
        if($this->db->affected_rows()>0){
            return true;
        }
        else{
            return false;
        }
    }//endfct

}
