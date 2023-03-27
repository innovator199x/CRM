<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gmapproperties extends CI_Controller {


    public function __construct()
	{
		parent::__construct();
        $this->load->database();
        $this->load->model('jobs_model');
        $this->load->model('properties_model');
        $this->load->model('inc/functions_model');
        $this->load->model('sms_model');
        $this->load->library('pagination');
        $this->load->helper('url');
        $this->load->model('daily_model');
	}


    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *      http://example.com/index.php/welcome
     *  - or -
     *      http://example.com/index.php/welcome/index
     *  - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */


    /*

    SELECT
    sa.StaffID,
    sa.ClassID,
    sa.FirstName,
    sa.LastName,

    cc.address,
    cc.lat,
    cc.lng
    FROM staff_accounts AS sa
    LEFT JOIN accomodation AS cc ON cc.accomodation_id = sa.accomodation_id
    WHERE sa.ClassID = 6
    AND sa.Deleted = 0
    AND sa.active = 1
    AND sa.StaffID NOT IN (1,2)
    */

    public function index($propertyid=0)
    {        

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Service Overview";            
                
        $data['address_1']=''; 
        $data['address_2']='';
        $data['address_3']='';
        $data['postcode']='';
        $data['state']='';
        $data['fullAdd']='';
 
 
        // get the latest tablename
        $a="SELECT * FROM active_properties_cron  WHERE cronstatus=1  ORDER BY cronid desc  limit 1";
        $tableres = $this->db->query( $a )->row(); 
        $tablesname=$tableres->crontable;
        $data['selected']= '';
 
        if(isset($_POST['tablesname'])  && $_POST['tablesname']!=''  )
        {
            $tablesname=$_POST['tablesname'];
            $data['selected']= $tablesname;
        }
        $data['tablesname']= $tablesname;

        
        // auto-update property with empty coordinate     
        $active_prop_sql_str = "
        SELECT p.`property_id`
        FROM `{$tablesname}` as pc   
        INNER JOIN `property` as p ON pc.`property_id` = p.`property_id`  
        WHERE ( p.`lat` = '' OR p.`lat` IS NULL ) 
        AND ( p.`lng` = '' OR p.`lng` IS NULL ) 
        ";
        $active_prop_sql = $this->db->query($active_prop_sql_str);        

        
        foreach( $active_prop_sql->result() as $active_prop_row ){

            if( $active_prop_row->property_id > 0 ){
                $this->system_model->update_coordinate_using_gmap($active_prop_row->property_id);
            }            

        }
        
        


        $q=" SELECT p.property_id, p.agency_id,  p.address_1, p.address_2, p.address_3, 
                                        p.state, p.lat, p.lng , a.agency_name 
                                    FROM ". $tablesname ." as pc   
                                    LEFT JOIN property as p  on pc.property_id=p.property_id  
                                    LEFT JOIN agency as a  on a.agency_id=p.agency_id  
                                    WHERE p.lat!=''";

        
        if(isset($_POST['search']) && ($_POST['address_1']!='' || $_POST['address_2']!='' || $_POST['address_3']!='' || $_POST['postcode']!='' || $_POST['state']!=''))
        {


            $q=  " SELECT p.property_id, p.agency_id,  p.address_1, p.address_2, p.address_3, 
                                        p.state, p.lat, p.lng , a.agency_name 
                        FROM ". $tablesname ." as pc   
                            LEFT JOIN property as p  on pc.property_id=p.property_id  
                            LEFT JOIN agency as a  on a.agency_id=p.agency_id  
                        WHERE  p.lat!=''" ;

            $q.= "  ";

        if($_POST['address_1']!='')
            $q.= "   and    p.address_1  = '".$_POST['address_1'] ."'   ";

            if($_POST['address_2']!=''  ){
                $q.= "   and  p.address_2  like '%".$_POST['address_2']."%'   "; 
            }

            if($_POST['address_3']!=''  ) {
                $q.= " and p.address_3    like '%".$_POST['address_3']."%'   "; 
            }
            if($_POST['postcode']!=''  ) {
                $q.= "  and p.postcode    ='".$_POST['postcode']."'    "; 
            }
            if($_POST['state']!=''  ) {
                $q.= "  and p.state    ='".$_POST['state']."'    "; 
            }
                        
    
            $data['fullAdd']=$_POST['fullAdd']; 
            $data['address_1']=$_POST['address_1']; 
            $data['address_2']=$_POST['address_2'];
            $data['address_3']=$_POST['address_3'];
            $data['postcode']=$_POST['postcode'];
            $data['state']=$_POST['state'];
            //print_r($_POST);
        }


        $data['propertyid']=$propertyid;
        if( $propertyid > 0 )
        {

            $q="SELECT p.property_id, p.agency_id,  p.address_1, p.address_2, p.address_3, 
                                        p.state, p.lat, p.lng , a.agency_name 
                                    FROM   property as p    
                                    LEFT JOIN agency as a  on a.agency_id=p.agency_id  


                                    WHERE p.property_id='".$propertyid."'";
            
        

            $data['address_1']=''; 
            

            $sql2 = $this->db->query( $q );
            //echo $this->db->last_query();
            if($sql2->num_rows()>0)
            {
                // find techs near this location
                $lat=$sql2->row()->lat;
                $lng=$sql2->row()->lng;
                $data['center']=array('lat'=>$lat,'lng'=>$lng); 
                $stf="

                SELECT
                    sa.StaffID, sa.ClassID, sa.FirstName, sa.LastName, sa.Deleted, sa.active,
                    cc.address, cc.lat, cc.lng,
                    (
                    6371 * acos (
                    cos ( radians($lat) )  * cos( radians( cc.lat) ) * cos( radians( cc.lng ) - radians($lng) )
                    + sin ( radians($lat) )  * sin( radians( cc.lat) )
                    )
                    ) AS distance
                    FROM staff_accounts AS sa
                    LEFT JOIN accomodation AS cc ON cc.accomodation_id = sa.accomodation_id
                    HAVING sa.ClassID = 6
                        AND sa.Deleted = 0
                        AND sa.active = 1
                        AND sa.StaffID NOT IN (1,2) 
                    
                    ";
                $staffres = $this->db->query( $stf );
                
                //echo $this->db->last_query();
                
                $staff = $staffres->result_array();

                $data['staffmarkerscount']=$staffres->num_rows();
                foreach( $staff as $stfrow) { 
                    $staffmarkers[]=array(   'StaffID'=>$stfrow['StaffID'], 
                                        'coords'=>array(
                                                    'lat'=>"".$stfrow['lat']."" , 
                                                    'lng'=>"".$stfrow['lng'].""
                                                ), 
                                        'content'=> $this->cleanstring($stfrow['FirstName'].' '.$stfrow['LastName']), 
                                        'state'=> '', 
                                        'address_1'=> $this->cleanstring($stfrow['address']), 
                                        'address_2'=> '', 
                                        'address_3'=> '', 
                                        'agency_name'=>'',
                                        'pintype'=>'staff'
                                        //'iconImage'=>'assets/img/places/stparkdublin.png',
                                    );
                    
                    
                }
                $data['staffmarkers']=$staffmarkers;

                    

            }


        }else{
            $stf="

                SELECT
                    sa.StaffID, sa.ClassID, sa.FirstName, sa.LastName, sa.Deleted, sa.active,
                    cc.address, cc.lat, cc.lng
                    FROM staff_accounts AS sa
                    LEFT JOIN accomodation AS cc ON cc.accomodation_id = sa.accomodation_id
                    HAVING sa.ClassID = 6
                        AND sa.Deleted = 0
                        AND sa.active = 1
                        AND sa.StaffID NOT IN (1,2) 
                    
                    ";
                $staffres = $this->db->query( $stf );
            // echo $this->db->last_query();
                $staff = $staffres->result_array();

                $data['staffmarkerscount']=$staffres->num_rows();
                foreach( $staff as $stfrow) { 
                    $staffmarkers[]=array(   'StaffID'=>$stfrow['StaffID'], 
                                        'coords'=>array(
                                                    'lat'=>"".$stfrow['lat']."" , 
                                                    'lng'=>"".$stfrow['lng'].""
                                                ), 
                                        'content'=> $this->cleanstring($stfrow['FirstName'].' '.$stfrow['LastName']), 
                                        'state'=> '', 
                                        'address_1'=> $this->cleanstring($stfrow['address']), 
                                        'address_2'=> '', 
                                        'address_3'=> '', 
                                        'agency_name'=>'',
                                        'pintype'=>'staff'
                                        //'iconImage'=>'assets/img/places/stparkdublin.png',
                                    );
                    
                    
                }
                $data['staffmarkers']=$staffmarkers;

                    

        }

        //$q.= "   limit 0, 80000    "; 

        //echo $q;
        
        $sql = $this->db->query( $q );  
        // echo $this->db->last_query(); 
        //die;
        $result = $sql->result_array();
        $markers=[];
        
        
        $rcount=$sql->num_rows() ;
        $i=0;       
        if ($sql->num_rows() > 0) {
        
            // output data of each row
            foreach( $result as $row) { $i++;        

                $markers[] = array(   
                    'property_id' => $row['property_id'], 
                    'coords' => array(
                        'lat' => $row['lat'], 
                        'lng' => $row['lng']
                    ),         
                    'content' => $row['agency_name'], 
                    'state' => $row['state'], 
                    'address_1' => $row['address_1'], 
                    'address_2' => $row['address_2'], 
                    'address_3' => $row['address_3'], 
                    'agency_name' => $row['agency_name'],
                    'agency_id' => $row['agency_id'],
                    'pintype' => 'property'	
                );

                
            }
            
        }  

        
        //echo $d;
        //$json=json_encode(array('count'=>$rcount,'locations'=>$markers));
        $data['total']=$sql->num_rows();
        $data['markers']=$markers;
        //echo '<script> var datam = \''.json_encode($markers).'\'; let x=JSON.parse(datam); console.log("datam",datam);</script>';             
    

        $data['prodetail']= site_url('gmapproperties/index');

        // get tables
        $ct="SELECT DISTINCT crontitle, crontable FROM active_properties_cron WHERE cronstatus=1  ORDER BY cronid desc";
        $tableres = $this->db->query( $ct )->result(); 
        $data['tableres']=$tableres;
        //$this->load->view('gmapproperties',$data);

        $data['exclude_gmap'] = true;
        $this->load->view('templates/inner_header', $data);
        $this->load->view('gmapproperties', $data);
        $this->load->view('templates/inner_footer', $data);

    }



    public function detail($propertyid=0)
    {
        if($propertyid<=0)
        {
            $this->index();
            return;
        }

        $data;

       // $this->load->library('database');
        error_reporting(-1);
        ini_set('display_errors', 1);
            
       $data['address_1']=''; 
       $data['address_2']='';
       $data['address_3']='';


           $q="SELECT distinct p.property_id, p.agency_id,  p.address_1, p.address_2, p.address_3, 
                                        p.state, p.lat, p.lng , a.agency_name 
                                    FROM   property as p    
                                    INNER JOIN agency as a  on a.agency_id=p.agency_id  


                                    WHERE p.property_id='".$propertyid."'";



        $sql = $this->db->query( $q );  
        // echo $this->db->last_query(); die;
        $result = $sql->result_array();
        $markers=[];
        $d='        
        var data = { "count": '.$sql->num_rows().'';
         
         $rcount=$sql->num_rows() ;
        $i=0;       
        if ($sql->num_rows() > 0) {
            $d.=',"photos": [' ;
          // output data of each row
          foreach( $result as $row) { $i++;
            $markers[]=array(   'agency_id'=>$row['agency_id'], 
                                'coords'=>array(
                                            'lat'=>"".$row['lat']."" , 
                                            'lng'=>"".$row['lng'].""
                                        ), 
                                'content'=> $this->cleanstring($row['agency_name']), 
                                'state'=> $this->cleanstring($row['state']), 
                                'address_1'=> $this->cleanstring($row['address_1']), 
                                'address_2'=> $this->cleanstring($row['address_2']), 
                                'address_3'=> $this->cleanstring($row['address_3']), 
                                'agency_name'=>$this->cleanstring($row['agency_name']),
                                //'iconImage'=>'assets/img/places/stparkdublin.png',
                            );
             $d .=' {"agency_id": '.$row['agency_id'].', "agency_name":\''.$row['agency_name'].'\'}';
             //$d .= "\r\n";
             if($i>0 && $i<$rcount) {
                 $d .= ',';
                 //$d .= "\r\n";
             } 
            
              
          }
            $d.=']';
        }  

        $d.='}';    
        //echo $d;
        //$json=json_encode(array('count'=>$rcount,'locations'=>$markers));
        $data['total']=$sql->num_rows();
        $data['markers']=$markers;
        //echo '<script> var datam = \''.json_encode($markers).'\'; let x=JSON.parse(datam); console.log("datam",datam);</script>';             
        $data['prodetail']= site_url('gmapproperties/detail');
        $this->load->view('gmapproperty',$data);
    }



    private function cleanstring($str)
    {
        return preg_replace("/[^A-Za-z0-9 ]/", '',str_replace('&',' and ',$str));
    }


    function search()
    {
        $data;

       // $this->load->library('database');
        error_reporting(-1);
        ini_set('display_errors', 1);
       $q=  " SELECT distinct p.property_id, p.agency_id,  p.address_1, p.address_2, p.address_3, 
                                        p.state, p.lat, p.lng , a.agency_name 
                        FROM property as p  
                        INNER JOIN agency as a  on a.agency_id=p.agency_id  
                        WHERE" ;
                      /*            
                        if($_POST['address_1']!='')
                            $q.= "( p.lat!='' and p.deleted=0  and    p.address_1  like '%". $_POST['address_1'] ."%')";
                        if($_POST['address_1']!='' && $_POST['address_2'] !='')
                            $q.= "  OR ";
                        if($_POST['address_2']!=''  )
                            $q.= " ( p.lat!='' and p.deleted=0  and  p.address_2  like '%". $_POST['address_2'] ."%') ";
                        if($_POST['address_2']!='' && $_POST['address_3'] !='')
                            $q.= " OR "; 
                        if($_POST['address_3']!=''  ) 
                             $q.= " ( p.lat!='' and p.deleted=0  and p.address_3  like '%". $_POST['address_3'] ."%')  "; 
                        */

                        $q.= "  p.lat!='' and p.deleted=0 AND ( p.is_nlm = 0 OR p.is_nlm IS NULL ) ";

                        if($_POST['address_1']!='')
                            $q.= "    and    p.address_1  like '%".$_POST['address_1'] ."%' ";
                        
                        if($_POST['address_2']!=''  )
                            $q.= "   and  p.address_2  like '%".$_POST['address_2']."%'  ";
                      
                        if($_POST['address_3']!=''  ) 
                             $q.= "   and p.address_3  like '%".$_POST['address_3']."%'   "; 


                              $q.= "   limit 0,100    ";
       

        $sql = $this->db->query( $q );   
          
        //  echo $this->db->last_query(); die;
        $result = $sql->result_array();
        $markers=[];
        $d='        
        var data = { "count": '.$sql->num_rows().'';
         
         $rcount=$sql->num_rows() ;
        $i=0;       
        if ($sql->num_rows() > 0) {
            $d.=',"photos": [' ;
          // output data of each row
          foreach( $result as $row) { $i++;
            $markers[]=array(   'agency_id'=>$row['agency_id'], 
                                'coords'=>array(
                                            'lat'=>"".$row['lat']."" , 
                                            'lng'=>"".$row['lng'].""
                                        ), 
                                'content'=> $this->cleanstring($row['agency_name']), 
                                'state'=> $this->cleanstring($row['state']), 
                                'address_1'=> $this->cleanstring($row['address_1']), 
                                'address_2'=> $this->cleanstring($row['address_2']), 
                                'address_3'=> $this->cleanstring($row['address_3']), 
                                'agency_name'=>$this->cleanstring($row['agency_name']),
                                //'iconImage'=>'assets/img/places/stparkdublin.png',
                            );
             $d .=' {"agency_id": '.$row['agency_id'].', "agency_name":\''.$row['agency_name'].'\'}';
             //$d .= "\r\n";
             if($i>0 && $i<$rcount) {
                 $d .= ',';
                 //$d .= "\r\n";
             } 
            
              
          }
            $d.=']';
        }  

        $d.='}';    
        //echo $d;
        //$json=json_encode(array('count'=>$rcount,'locations'=>$markers));
        
        $data['markers']=$markers;
        //echo '<script> var datam = \''.json_encode($markers).'\'; let x=JSON.parse(datam); console.log("datam",datam);</script>';             
       

        $data['prodetail']= site_url('gmapproperties/detail');
       // echo json_encode($data);
    }




    function cron()
    {
        // createtable
        $cronid=0;
        $this->load->dbforge();

        try{
      
            $fields = array(
                            'cronid' => array(
                                                     'type' => 'INT',
                                                     'constraint' => 9,
                                                     'unsigned' => TRUE,
                                                     'auto_increment' => TRUE
                                              ),
                            'crontitle' => array(
                                                     'type' => 'VARCHAR',
                                                     'constraint' => '200',
                                              ),
                            'crontable' => array(
                                                     'type' => 'VARCHAR',
                                                     'constraint' => '250',
                                              ),
                            'crondate' => array(
                                                     'type' =>'DATETIME',
                                                     'constraint' => '',
                                                     'default' => '0000-00-00 00:00:00',
                                              ),
                            'cronstatus' => array(
                                                     'type' => 'INT',
                                                     'constraint' => 1,
                                                     'default' => '0',
                                              ),
                    );

                $this->dbforge->add_field($fields);
                $this->dbforge->add_key('cronid', TRUE);
                $this->dbforge->create_table('active_properties_cron', TRUE);
            }catch(Exception $e){
                    //echo $e->getMessage();die;


           }
        
           // month and date
           $currentDateTime = new DateTime('UTC');
           $reqDateTime = $currentDateTime->format('Y-m-d h:i:s A');
           $title = $currentDateTime->format('M Y');
           $mdDateTimeString = $currentDateTime->format('md');
           $tblename='active_properties_'.$mdDateTimeString;



           if ($this->db->table_exists($tblename)) {
                log_message('error', $tblename.': already exists and cannot receate');
           }
           else{




               try{
          
                    $fields = array(
                                'pid' => array(
                                                         'type' => 'INT',
                                                         'constraint' => 9,
                                                         'unsigned' => TRUE,
                                                         'auto_increment' => TRUE
                                                  ),
                                'property_id' => array(
                                                         'type' => 'INT',
                                                         'constraint' => '9',
                                                  ),
                                 
                        );

                    $this->dbforge->add_field($fields);
                    $this->dbforge->add_key('pid', TRUE);
                    $this->dbforge->create_table($tblename, TRUE);

                    $this->db->set('crontitle',$title);
                    $this->db->set('crontable',$tblename);
                    $this->db->set('crondate',$reqDateTime);
                    $this->db->set('cronstatus','0');
                    $this->db->insert('active_properties_cron');
                    $cronid=$this->db->insert_id();

                }catch(Exception $e){
                        //echo $e->getMessage();die;
                        log_message('error', ':'.$e->getMessage());die;

               }


               // delete record from table if exists
               $this->db->where('pid >','0');
               $this->db->delete($tblename);

               $this->db->trans_start(); # Starting Transaction

               // select $q
                $q=" SELECT distinct p.property_id   FROM property as p   WHERE p.lat!='' and p.deleted=0 and ( p.is_nlm = 0 OR p.is_nlm IS NULL ) ";


                

                $sql = $this->db->query( $q );  
                // echo $this->db->last_query(); die;
                $result = $sql->result();
                $rcount=$sql->num_rows() ;

                // output data of each row
                foreach( $result as $row) { 
                        $this->db->set('property_id',$row->property_id);
                        $this->db->insert($tblename);
                }


                $this->db->set('cronstatus','1');
                $this->db->where('cronid',$cronid);
                $this->db->update('active_properties_cron');


              $this->db->trans_complete(); # Completing transaction
              if ($this->db->trans_status() === FALSE) {
                    # Something went wrong.
                    $this->db->trans_rollback();
                    log_message('error', 'trans_rollback: Rolled back');
                    return FALSE;
                } 
                else {
                    # Everything is Perfect. 
                    # Committing data to the database.

                    $this->db->trans_commit();
                    log_message('info', 'trans_commit: Commit Success fully');

                    return TRUE;
                }
            }

        }


}
