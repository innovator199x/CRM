
 <!-- BOOKINGS RESULT TABLE -->
 <div id="search_result">
                    <div class="table_top_head">Bookings</div>
                    <table class="table table-hover main-table table_border">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Date</th>
                                <th>Booked</th>
                                <th>DKs</th>
                                <th>Completed</th>
                                <th>Techs</th>
                                <th>Average</th>
                                <th>Estimated Income</th>
                            </tr>
                        </thead>

                        <tbody>
                                <?php
                                    $exclude_dha = "a.franchise_groups_id!=14";

                                    for($i=0;$i<=4;$i++){ 
                                    // date
                                    $date = date('Y-m-d',strtotime("+{$i} day")); 
                                    $day = date('l',strtotime($date));	
                                ?>

                                <tr>
                                    <td><?php echo $day; ?></td>
                                    <td><?php  echo date('d/m/Y',strtotime($date)); ?></td>
                                    <td>

                                        <?php
                                        //get booked
                                        $custom_where = " j.`door_knock` = 0";
                                        $params_booked = array(
                                            'sel_query' => " COUNT(DISTINCT(j.`id`)) AS booked_count",
                                            'p_deleted' => 0,
                                            'a_status' => 'active',
                                            'del_job' => 0,
                                            'country_id' => $this->config->item('country'),
                                            'job_status' => 'Booked',
                                            'date' => $date,
                                            'custom_where_arr' => array($custom_where,$exclude_dha)
                                        );
                                        $get_booked = $this->jobs_model->get_jobs($params_booked);
                                        echo ($get_booked->row()->booked_count>0)?$get_booked->row()->booked_count:NULL;
                                        ?>
                                    
                                    </td>
                                    <td>
                                        <?php 
                                        //get DKs
                                        $custom_where = " j.`door_knock` = 1 ";
                                        $custom_where2 = "( j.`status` = 'Booked' OR j.`status` = 'Completed' )";
                                        $params_dks = array(
                                            'sel_query' => " COUNT(DISTINCT(j.`id`)) AS dks_count",
                                            'p_deleted' => 0,
                                            'a_status' => 'active',
                                            'del_job' => 0,
                                            'country_id' => $this->config->item('country'),
                                            'date' => $date,
                                            'custom_where_arr' => array($custom_where, $custom_where2, $exclude_dha)
                                        );
                                        $get_dks = $this->jobs_model->get_jobs($params_dks);
                                        echo ($get_dks->row()->dks_count>0)?$get_dks->row()->dks_count:NULL;
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            //Get Completed
                                            $custom_where = " j.`ts_completed` = 1 ";
                                            $params_completed = array(
                                                'sel_query' => " COUNT(DISTINCT(j.`id`)) AS completed_count",
                                                'p_deleted' => 0,
                                                'a_status' => 'active',
                                                'del_job' => 0,
                                                'country_id' => $this->config->item('country'),
                                                'date' => $date,
                                                'custom_where_arr' => array($custom_where,$exclude_dha)
                                            );
                                            $get_completed = $this->jobs_model->get_jobs($params_completed);
                                            echo ($get_completed->row()->completed_count>0)?$get_completed->row()->completed_count:NULL;
                                        ?>
                                    </td>
                                    <td>
                                            <?php
                                            //Get Techs
                                            $custom_where = " j.`assigned_tech` NOT IN(1,2) ";  //exclude_tech_other_supplier 
                                            $custom_where2 = "( j.`status` = 'Booked' OR j.`status` = 'Completed' )"; //status_booked_or_completed
                                            $params_tech = array(
                                                'sel_query' => " COUNT(DISTINCT(j.`assigned_tech`)) AS tech_count",
                                                'p_deleted' => 0,
                                                'a_status' => 'active',
                                                'del_job' => 0,
                                                'country_id' => $this->config->item('country'),
                                                'date' => $date,
                                                'custom_where_arr' => array($custom_where, $custom_where2, $exclude_dha)
                                            );
                                            $get_tech = $this->jobs_model->get_jobs($params_tech);
                                            echo ($get_tech->row()->tech_count>0)?$get_tech->row()->tech_count:NULL;
                                            ?>
                                    </td>
                                    <td>

                                    <?php 
                                        // average
                                        // booked and completed
                                        $custom_where = " j.`assigned_tech` NOT IN(1,2) ";  //exclude_tech_other_supplier 
                                        $custom_where2 = "( j.`status` = 'Booked' OR j.`status` = 'Completed' )"; //status_booked_or_completed
                                        $params_average = array(
                                            'sel_query' => " COUNT(DISTINCT(j.`id`)) AS j_count",
                                            'p_deleted' => 0,
                                            'a_status' => 'active',
                                            'del_job' => 0,
                                            'country_id' => $this->config->item('country'),
                                            'date' => $date,
                                            'custom_where_arr' => array($custom_where, $custom_where2,$exclude_dha)
                                        );
                                        $booked_and_completed_job_count = $this->jobs_model->get_jobs($params_average);
                                        $jobcount_ave = floor($booked_and_completed_job_count->row()->j_count/$get_tech->row()->tech_count); 
                                        echo ($jobcount_ave>0)?$jobcount_ave:NULL;
                                    ?>

                                    </td>
                                    <td>
                                        <?php 
                                            //Get Estimated Income
                                            $sel_query = "SUM( j.`job_price` ) AS j_price "; // sum_job_price
                                            $custom_where = "( ( j.`status` = 'Booked' AND j.`door_knock` !=1 ) OR j.`status` = 'Completed'  OR j.`status` = 'Merged Certificates' )" ;   //query_for_estimated_income
                                            $params_income = array(
                                                'sel_query' => $sel_query,
                                                'p_deleted' => 0,
                                                'a_status' => 'active',
                                                'del_job' => 0,
                                                'country_id' => $this->config->item('country'),
                                                'date' => $date,
                                                'custom_where_arr' => array($custom_where,$exclude_dha)
                                            );
                                            $get_income = $this->jobs_model->get_jobs($params_income);
                                            $job_price = $get_income->row()->j_price;

                                            //Alarm Price
                                            $alrm_custom_where = " alrm.`ts_discarded` = 0 ";
                                            $alarm_params = array(
                                                'sel_query' => " SUM( alrm.`alarm_price` ) AS a_price ",
                                                'join_table' => array('property','agency'),
                                                'p_deleted' => 0,
                                                'a_status' => 'active',
                                                'del_job' => 0,

                                                'new_alarm'=> 1,
                                                'query_for_estimated_income' => 1,
                                                'country_id' => $this->config->item('country'),
                                                'date' => $date,
                                                'custom_where' => $alrm_custom_where
                                            );
                                            $ap_query = $this->system_model->getAlarms($alarm_params);
                                            $alarm_price = $ap_query->row()->a_price;
                                            $job_tot = $job_price+$alarm_price;
                
                                            /*
                                            if($job_tot>0){
                                                echo '$'.number_format($job_tot, 2);
                                            }else{
                                                echo '';
                                            }
                                            */
                                            
                                            echo ( $job_tot > 0 )?'$'.number_format($this->system_model->price_ex_gst($job_tot),2):null;

                                        ?>
                                    </td>
                                </tr>

                                <?php
                                    }
                                ?>

                        </tbody>

                    </table>
                </div>
                 <!-- BOOKINGS RESULT TABLE END -->