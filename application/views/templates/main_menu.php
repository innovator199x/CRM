<div class="mobile-menu-left-overlay"></div>

	<?php
	$staff_class = $this->system_model->getStaffClassID();
	if($staff_class!=6){ //display menu button/toggle if not tech
	?>
	<nav class="side-menu">
	
	    <ul class="side-menu-list">

	       <li>
		   		<a href="/home">
					<span>
						<i class="font-icon font-icon-home"></i>
						<span class="lbl">Home</span>
					</span>
				</a>
	        </li>
			
			<?php		
			$sa_id = $this->session->staff_id; // Staff ID
			$sc_id = $this->system_model->getStaffClassID(); // Staff Class

			// MENU
			$sel_query = "menu_id, menu_name, icon_class_new";
			$menu_params = array(
				'sel_query' => $sel_query,
				'active' => 1,  
				
				'sort_list' => array(
					array(
						'order_by' => 'sort_index',
						'sort' => 'ASC'
					)
				),
	
				'display_query' => 0
			);
			$menu_sql = $this->menu_model->getMenu($menu_params); 
			$menu_count = $menu_sql->num_rows(); 
			
			if( $menu_count > 0 ){ 

				foreach( $menu_sql->result() as $menu ){ 
				
					$menu_params = array(
						'menu_id' => $menu->menu_id,
						'user' => $sa_id,
						'staff_class' => $sc_id
					);
					if( $this->menu_model->canViewMenu($menu_params) == true  ){

					$menu_link =  null;	
					if( $menu->menu_id == 4 ){ // Reports
						$menu_link = '/reports';
					}
			?>
		
						<li class="purple with-sub menu_parent_li">

							<?php
							if( $menu_link != '' ){
								echo  '<a href="'.$menu_link.'">';
							}
							?>							
								<span class="menu_name">
									<i class="<?php echo $menu->icon_class_new; ?>"></i>
									<span class="lbl" data-menu_id="<?php echo $menu->menu_id; ?>"><?php echo $menu->menu_name; ?></span>
								</span>
							<?php
							if( $menu_link != '' ){
								echo  '</a>';
							}
							?>
							
							<ul>
								<?php								
								// PAGES
								$sel_query = "cp.crm_page_id, cp.page_name, cp.page_url, cp.menu, cp.active";
								$params = array(
									'sel_query' => $sel_query,
									'active' => 1, 
									'menu' => $menu->menu_id,
									
									'sort_list' => array(
										array(
											'order_by' => 'page_name',
											'sort' => 'ASC'
										)
									),

									'display_query' => 0
								);
								$pages_sql = $this->menu_model->getPages($params);
								//print_r($pages_sql);
								//exit();

								foreach( $pages_sql->result() as $page ){
									
									$page_params = array(
										'page_id' => $page->crm_page_id,
										'user' => $sa_id,
										'staff_class' => $sc_id
									);
									if( $this->menu_model->canViewPage($page_params) == true ){

									$links_params = array(
										'menu_id' => $menu->menu_id,
										'page_url' => $page->page_url,
										'user' => $sa_id
									);
									$page_url = $this->menu_model->getDynamicLinks($links_params);

									$page_total = $this->system_model->get_page_total($page_url);

								?>						
									<li>
										<a href="<?php echo $page_url; ?>" class="menu_link">
											<span class="lbl" data-page_id="<?php echo $page->crm_page_id; ?>"><?php echo $page->page_name; ?></span>
											<?php
											if( $page_total > 0 ){ ?>
												<span class="label label-custom label-pill label-danger bubble_count chops">
													<?php echo $page_total;  ?>
												</span>
											<?php
											}
											?>											
										</a>
									</li>			
								<?php
									}
								} 
								?>
							</ul>

						</li>
			<?php
					}

				}?>

				<!-- TEST MENU -->
				<?php
				
				$tester_arr = $this->system_model->tester();
				$tester_appended = $tester_arr;
				
				//$tester_appended[] = 2056; // Robert Bell 
				//$tester_appended[] = 2175; // Thalia Paki 
				//print_r($tester_appended);
				
				if( in_array($this->session->staff_id, $tester_appended) ){ ?>

				<li class="with-sub">

					<span>
						<i class="fa fa-bar-chart"></i>
						<span class="lbl">Test</span>
					</span>
					<ul>							
						<li><a href="/agency/duplicate_users"><span class="lbl">Duplicate Users</span></a></li>								
					</ul>
					<ul>							
						<li><a href="/jobs/future_pendings_v2"><span class="lbl">Future Pendings</span></a></li>								
					</ul>
					<ul>							
						<li><a href="/properties/active_job_properties_v2"><span class="lbl">Active Job Properties V2</span></a></li>								
					</ul>
					<ul>							
						<li><a href="/jobs/on_hold_reasons"><span class="lbl">On Hold Reasons</span></a></li>								
					</ul>
					<ul>							
						<li><a href="/test/renewals_created_jobs"><span class="lbl">Renewals Created Jobs</span></a></li>								
					</ul>
					<ul>							
						<li><a href="/properties/next_service"><span class="lbl">Next Service</span></a></li>								
					</ul>				
					<ul>							
						<li><a href="/reports/no_retest_date"><span class="lbl">No Retest Date</span></a></li>								
					</ul>
					<ul>							
						<li><a href="/properties/duplicate_property_service"><span class="lbl">Duplicate Property Service</span></a></li>								
					</ul>						

				</li>

				<?php			
				}
				
				?>				

				<!-- LOGOUT -->
				<li>
					<a href="/sys/logout">
						<span>
						<i class="fa fa-power-off"></i>
							<span class="lbl">Logout</span>
						</span>
					</a>
				</li>

			<?php	
			}	
			
			?>
			
			
	    </ul>
	
</nav><!--.side-menu-->

<?php  } ?>
