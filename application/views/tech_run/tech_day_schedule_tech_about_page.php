<h4>Run Sheet</h4>
	<p>
	This page displays your run for the day
	</p>
	<ul class="about_page_li">

		<?php
        $serv_type_sql = $this->db->query("
        SELECT `id`, `type`
        FROM `alarm_job_type`
        WHERE `active` = 1
        ");
		foreach( $serv_type_sql->result() as $serv_type_row ){ ?>
			<li>
				<?php
				// service icons
				$job_icons_params = array(
					'service_type' => $serv_type_row->id
				);
				echo $this->system_model->display_job_icons($job_icons_params);
				?>
				- This job is a <?php echo $serv_type_row->type; ?> service
			</li>
		<?php
		}
		?>
		<li>
			<img src="/images/serv_img/service_garage_icon.png" class="row_icons" data-toggle="tooltip" title="Service Garage" /> - Indicates the garage requires an alarm
		</li>
																									
	</ul>


	<ul class="about_page_li">

		<li><img class="row_icons" title="This job is a first visit" src="/images/first_icon.png"> - This job is a first visit</li>
		<li><img class="row_icons" title="This job is entry via key access" src="/images/key_icon.png"> - This job is entry via key access</li>

		<li><img class="row_icons" title="This job is a Priority" src="/images/caution.png"> - This job is a Priority</li>			
		<li><img class="row_icons" title="This is the ladder required for this job" src="/images/ladder.png"> - This is the ladder required for this job</li>
		<li><img class="row_icons" title="This job has notes to be read before starting" src="/images/notes.png"> - This job has notes to be read before starting</li>
		<li><img class="row_icons" title="Call tenant before the job" src="/images/red_phone2.png"> - Call tenant before the job</li>
		<li><img class="row_icons" title="No Tenants" src="/images/serv_img/upgrade_colored.png"> - This is an upgrade to interconnected job (NEW QLD Legislation)</li>
		<li><img class="row_icons" title="No Tenants" src="/images/240v_colored.png"> - This job requires an electrician</li>
		<li><img class="row_icons" title="No Tenants" src="/images/fr_colored.png"> - This is a Repair job</li>
		<li><img class="row_icons" title="No Tenants" src="/images/expired_alarm.png"> - This job has an expired alarm</li>		
		<li><img class="row_icons" title="No Tenants" src="/images/ppe_icon.png"> - Requires PPE to enter</li>		

	</ul>

	<ul class="about_page_li">	

		<li><span style="background-color: pink;">Pink Highlight</span> - ERROR on Tech sheet</li>	
		<li><span style="background-color: #fffca3;">Yellow Highlight</span> - Unable to Complete</li>	
		<li><span style="background-color: #c2ffa7;">Green Highlight</span> - Completed</li>	
		<li><span style="background-color: #ffff00;">Bright Yellow Highlight</span> - Job is yet to be mapped</li>
																									
	</ul>