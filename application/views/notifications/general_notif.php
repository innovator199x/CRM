
<?php         
	                            
								$notf_arr = [];
							
								$notf_arr = array(
									array(
										'notf_type' => $notif_type,
										'noft_bubble' => 'noft_bubble_gen',
										'button_name' => 'notifications-button',
										'notf_title' => 'General Notification'
									)
								);       
								foreach( $notf_arr as $notf_data ){ ?>
							
									<div class="main_notf_div">
										<?php
										// get  notification
										$jparams = array(
											'notf_type' => $notf_data['notf_type'],
											'notify_to' => $staff_id,			
											'sort_list' => array(
												array(
													'order_by' => 'n.`date_created`',
													'sort' => 'DESC'
												)
											),
											'paginate' => array(
												'offset' => 0,
												'limit' => 15
											)
										);
										$n_sql = $this->system_model->getOverallNotification($jparams);
										$n_num = count($n_sql);
										
										if( $n_num >0 ){ 
										?>
										
											<!-- NOTIFICATION BOX -->
											<div class="notification_box" data-notf_type="<?php echo $notf_data['notf_type']; ?>">
												<ul>
													<?php
													foreach ($n_sql as $n) {
														$sms_notf_msg = $n['notification_message']; 
														  if ($n['read']==0 ) {
													?>			
														<div class="dropdown-menu-notif-item new_notification" data-id="<?=$n['notifications_id']?>">
															<div class="photo">
																<img src="/images/avatar-2-64.png" alt="">
															</div>
															   <!-- <div class="dot"></div> -->
															<?php 
															//$changeRedi = str_replace('href="', 'href="'.$this->config->item("crm_link").'/', $sms_notf_msg);
															
															if (strpos($sms_notf_msg, '.php') !== false) { // old crm		
																$changeRedi = str_replace('href="', 'href="'.$this->config->item("crm_link").'/', $sms_notf_msg);
															}else{ // CI
																$changeRedi = str_replace('href="', 'href="'.$this->config->item("crmci_link").'/', $sms_notf_msg);
															}

															echo $changeRedi; 
															?>
															<!-- <div class="color-blue-grey-lighter">
																<?php 
																	$timestamp = strtotime($n['date_created']);
																	$time = $timestamp - (3 * 60 * 60);
																	$time = $time - (2 * 60);
																	$datetime = date("Y-m-d H:i:s", $time);
																?>
																<span class="timeago" title="<?=$datetime?>"></span>
															</div> -->
														</div>
													<?php } else { ?>
							
														<div class="dropdown-menu-notif-item"  style="background-color: #f2f2f2;" data-id="<?=$n['notifications_id']?>">
															<div class="photo">
																<img src="/images/avatar-2-64.png" alt="">
															</div>
															   <!-- <div class="dot"></div> -->
															<?php 
															//$changeRedi = str_replace('href="', 'href="'.$this->config->item("crm_link").'/', $sms_notf_msg);
															
															if (strpos($sms_notf_msg, '.php') !== false) { // old crm		
																$changeRedi = str_replace('href="', 'href="'.$this->config->item("crm_link").'/', $sms_notf_msg);
															}else{ // CI
																$changeRedi = str_replace('href="', 'href="'.$this->config->item("crmci_link").'/', $sms_notf_msg);
															}
															
															echo $changeRedi; 
															?>
															<!-- <div class="color-blue-grey-lighter">
																<?php 
																	$timestamp = strtotime($n['date_created']);
																	$time = $timestamp - (3 * 60 * 60);
																	$time = $time - (2 * 60);
																	$datetime = date("Y-m-d H:i:s", $time);
																?>
																<span class="timeago" title="<?=$datetime?>"></span>
															</div> -->
														</div>
													<?php } ?>
													<?php	
													}
													?>
												</ul>
											</div>
							
										<?php
										} else { ?>
											<!-- NOTIFICATION BOX -->
											<div class="notification_box" data-notf_type="<?php echo $notf_data['notf_type']; ?>">
												<ul>
													<div class="dropdown-menu-notif-item"  style="background-color: #f2f2f2;" data-id="<?=$n['notifications_id']?>">
													   <center> No Notification</center>
													</div>
												</ul>
											</div>
										<?php 
										}
										?>
									</div>
							
							<?php
							}
							?>
							<!-- <script type="text/javascript">
								jQuery(document).ready(function() {
									 $("span.timeago").timeago();
								});
							</script> -->