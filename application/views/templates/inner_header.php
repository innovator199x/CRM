<?php
$data = $this->templatedatahandler->getData();
extract($data);
?>
<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title><?php echo $title; ?></title>

	<!-- CSS START -->
	<link href="img/favicon.144x144.png" rel="apple-touch-icon" type="image/png" sizes="144x144">
	<link href="img/favicon.114x114.png" rel="apple-touch-icon" type="image/png" sizes="114x114">
	<link href="img/favicon.72x72.png" rel="apple-touch-icon" type="image/png" sizes="72x72">
	<link href="img/favicon.57x57.png" rel="apple-touch-icon" type="image/png">
	<link href="img/favicon.png" rel="icon" type="image/png">
	<link href="img/favicon.ico" rel="shortcut icon">

	<link rel="stylesheet" href="/inc/css/lib/lobipanel/lobipanel.min.css">
	<link rel="stylesheet" href="/inc/css/separate/vendor/lobipanel.min.css">
	<link rel="stylesheet" href="/inc/css/lib/jqueryui/jquery-ui.min.css">
	<link rel="stylesheet" href="/inc/css/separate/pages/widgets.min.css">
	<!--
	<link rel="stylesheet" href="/inc/css/lib/flatpickr/flatpickr.min.css">
	<link rel="stylesheet" href="/inc/css/separate/vendor/flatpickr.min.css">
	-->
	<link rel="stylesheet" href="/inc/css/lib/bootstrap-sweetalert/sweetalert.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.4.2/jquery.fancybox.min.css" />
	<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap.min.css" />

    <link rel="stylesheet" href="/inc/css/lib/font-awesome/font-awesome.min.css">
    <link rel="stylesheet" href="/inc/css/lib/bootstrap/bootstrap.min.css">
	<link rel="stylesheet" href="/inc/css/main.css">
	<link rel="stylesheet" href="/inc/css/custom.css?v=0.1">
	<link rel="stylesheet" type="text/css" href="/inc/loading-bar/loading-bar.css"/>


	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

	<!-- JS START -->
	<script src="/inc/js/lib/jquery/jquery-3.2.1.min.js"></script>
	<script src="/inc/js/lib/popper/popper.min.js"></script>
	<script src="/inc/js/lib/tether/tether.min.js"></script>
	<script src="/inc/js/lib/bootstrap/bootstrap.min.js"></script>
	<script src="/inc/js/plugins.js"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script type="text/javascript" src="/inc/js/lib/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/inc/js/lib/lobipanel/lobipanel.min.js"></script>
	<script type="text/javascript" src="/inc/js/lib/match-height/jquery.matchHeight.min.js"></script>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script src="/inc/js/lib/bootstrap-sweetalert/sweetalert.min.js"></script>
	<script src="/inc/js/lib/html5-form-validation/jquery.validation.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.4.2/jquery.fancybox.min.js"></script>
	<script type="text/javascript" src="/inc/js/lib/bootstrap-select/bootstrap-select.min.js"></script>
	<script type="text/javascript" src="/inc/js/lib/select2/select2.full.min.js"></script>
	<script type="text/javascript" src="/inc/ion_sound/ion.sound.min.js"></script>
	<script type="text/javascript" src="/inc/js/lib/pusher/pusher.min.js"></script>
	<script type="text/javascript" src="/inc/js/lib/jquery-idle-master/jquery.idle.js"></script>
	<script type="text/javascript" src="/inc/js/custom.js"></script>
	<script type="text/javascript" src="/inc/js-cookie/src/js.cookie.js"></script>
	<script type="text/javascript" src="/inc/loading-bar/loading-bar.js"></script>
	<script type="text/javascript" src="/inc/js/jquery.tablednd_0_5.js"></script>
	<script type="text/javascript" src="/inc/js/jsignature/jSignature.min.js"></script>
	<script type="text/javascript" src="/inc/js/input_mask/dist/jquery.inputmask.js"></script>
	<script type="text/javascript" src="https://momentjs.com/downloads/moment.min.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>


	<style type="text/css">
	@media print {
		@page {
			size: auto;
		}
	}
	#load-screen {
		width: 100%;
		height: 100%;
		background: url("/images/preloader2.gif") no-repeat center center #fff;
		position: fixed;
		opacity: 0.7;
		display:none;
		margin-top: -25px;
	}
	/* region filters */
	#region_dp_div{
		padding: 1px 10px 1px 6px;
		position: absolute;
		top: 60px;
		display: none;
		z-index: 10;
		min-width: 129px;
		width: -moz-max-content;
	}
	#region_dp_div .state_div {
		margin: 4px 0;
	}
	#region_dp_div .region_div {
		margin: 13px 0 0 24px;
	}
	#region_dp_div .sub_region_div_chk {
		margin: 13px 0 0 26px;
	}
	#region_dp_div .rf_select{
		font-weight: bold;
	}
	.form-control:disabled, .form-control[readonly] {
		background-color: unset;
		opacity: 1;
	}
	.jtopheader_left .dropdown-toggle::after {
		display: none;
	}
	.about_page_icon{
		margin-right: 5px;
	}
	.fancybox-content {
		max-width: 60%;
	}
	.fb_trigger{
		display: none;
	}
	#about_page_fb ul li span{
		padding: 0 2px;
	}
	#search_fb .row{
		margin-bottom: 10px;
	}
	#search_icon_fb{
		position: relative;
		right: 9px;
		bottom: 1px;
	}
	.top_profile_name{
		float: left;
		margin-right: 19px;
		color: #fff;
		padding: 7px;
		font-size: 15px;
		font-weight:600;
	}
	.logged_user_span{
		margin-right: 3px;
	}
	.site-header .site-header-collapsed{
		margin-right: -500px!important;
	}

	/* z index heirarchy */
	.site-header{
		z-index: 100 !important;
	}
	.fancybox-container{
		z-index: 200 !important;
	}
	#load-screen{
		z-index: 300 !important;
	}

	.pdf_header_div {
		display: inline-block;
		max-width: 100%;
		vertical-align: top;
		position: relative;
		padding: 0 30px 0 60px;
		padding-right: 30px;
		height: 54px;
		margin: 0 0 20px 0;
		font-size: .9375rem;
		line-height: 18px;
	}
	.pdf_header_div .name{
		text-align: left;
	}

	.ldBar{
		width: 15% !important;
		height: 15% !important;
		z-index: 200;
	}
	.ldBar_center {
		margin: 0;
		position: absolute;
		top: 500px;
		left: 50%;
		transform: translate(-50%, -50%);
	}
	.load-screen-bg {
		width: 100%;
		height: 100%;
		position: fixed;
		opacity: 0.5;
		margin-top: -25px;
		z-index: 100;
		background-color: #ECEFF4;
	}
	.loading-bar-div{
		display: none;
	}
	.flags img {
		width: 34px;
		margin-right: 5px;
	}
	.theme-picton-blue .site-header .header-alarm.active::after {
		border-color: red;
    	background-color: red;
	}
	.homepage_setting_box{
		float: left;
		margin-left: 6px;
		margin-top: 6px;
		position: relative;
	}
	.homepage_setting_box a{
		color:#fff;
	}
	</style>

	<?php $this->load->view('templates/header_js.php'); ?>

</head>

<div id="load-screen"></div>

<!-- loading bar START -->
<div class="loading-bar-div">
<div class="load-screen-bg"></div>
<div class="ldBar label-center jlbar ldBar_center" data-preset="circle"></div>
</div>
<!-- loading bar END -->

<body class="with-side-menu theme-picton-blue <?php echo ( $loggedInUser->ClassID == 6 )?'sidebar-hidden':null; ?>">

<div id="preloader">
	<div id="status">&nbsp;</div>
</div>

<?php
 $this->load->view('notifications/pusher_imp.php');

 // get logged user staff class
 $logged_user_class_id = $loggedInUser->ClassID;

 if( $logged_user_class_id == 6 ){ // tech
	$sats_logo_redirect = "/home/index";
 }else{
	$sats_logo_redirect = '/jobs';
 }
?>

	<header class="site-header">
	    <div class="container-fluid">
	        <a href="<?php echo $sats_logo_redirect; ?>" class="site-logo">
	            <img class="hidden-md-down" src="/images/logo.png" alt="SATS">
	            <img class="hidden-lg-down" src="/images/logo.png" alt="SATS">
			</a>
			<?php
			$staff_class = $loggedInUser->ClassID;

			if($staff_class!=6){ //display menu button/toggle if not tech
			?>
			<button id="show-hide-sidebar-toggle" class="show-hide-sidebar">
	            <span>toggle menu</span>
			</button>
			<?php } ?>

	        <button class="hamburger hamburger--htla">
	            <span>toggle menu</span>
	        </button>
	        <div class="site-header-content">
	            <div class="site-header-content-in">


	                <div class="site-header-shown">

						<?php
						$user_account = $loggedInUser;

						?>
						<div class="top_profile_name" >
							<span class="font-icon font-icon-user logged_user_span"></span> <span><?php echo "{$user_account->FirstName} {$user_account->LastName}"; ?></span>
						</div>

						<div class="dropdown dropdown-notification notif">
	                        <a href="javascript:void(0);" class="header-alarm">
	                            <i class="fa fa-search" id="search_icon_fb"></i>
	                        </a>
	                    </div>

						<?php
						// get general notification
						$jparams = array(
							'notf_type' => 1,
							'notify_to' => $this->session->staff_id,
							'read' => 0,
							'return_count' => 1
						);
						$unread_notif_count = $this->system_model->getOverallNotification($jparams);
						?>
	                    <div class="dropdown dropdown-notification notif gen_notif_main_div">
	                        <a href="#"
	                           class="header-alarm general-notif <?php echo ( $unread_notif_count > 0 )?'dropdown-toggle active':null; ?>"
	                           id="dd-notification"
	                           data-toggle="dropdown"
	                           aria-haspopup="true"
	                           aria-expanded="false">
	                            <i class="font-icon-alarm"></i>
	                        </a>
	                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-notif" aria-labelledby="dd-notification">
	                            <div class="dropdown-menu-notif-header">
	                                Notifications
    								<span class="label label-pill label-danger" id="notif_count"></span>
	                            </div>
	                            <div class="dropdown-menu-notif-list" id="notication_div">
                            		<!-- general notification appended here -->
									<div class="main_notf_div">
										<?php
										$jparams = array(
											'notf_type' => 1,
											'notify_to' => $this->session->staff_id,
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
							                            <?php
							                            //$changeRedi = str_replace('href="', 'href="'.$this->config->item("crm_link").'/', $sms_notf_msg);

														if (strpos($sms_notf_msg, '.php') !== false) { // old crm
															$changeRedi = str_replace('href="', 'href="'.$this->config->item("crm_link").'/', $sms_notf_msg);
														}else{ // CI
															$changeRedi = str_replace('href="', 'href="'.$this->config->item("crmci_link").'/', $sms_notf_msg);
														}

														echo $changeRedi;
							                            ?>
							                        </div>
												<?php } else { ?>
							                        <div class="dropdown-menu-notif-item"  style="background-color: #f2f2f2;" data-id="<?=$n['notifications_id']?>">
							                            <div class="photo">
							                                <img src="/images/avatar-2-64.png" alt="">
							                            </div>
							                            <?php
							                            //$changeRedi = str_replace('href="', 'href="'.$this->config->item("crm_link").'/', $sms_notf_msg);

														if (strpos($sms_notf_msg, '.php') !== false) { // old crm
															$changeRedi = str_replace('href="', 'href="'.$this->config->item("crm_link").'/', $sms_notf_msg);
														}else{ // CI
															$changeRedi = str_replace('href="', 'href="'.$this->config->item("crmci_link").'/', $sms_notf_msg);
														}

														echo $changeRedi;
							                            ?>
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
								                       <center>No Notifications</center>
								                    </div>
												</ul>
											</div>
										<?php
										}
										?>
									</div>
	                            </div>
	                            <div class="dropdown-menu-notif-more">
	                                <!-- <a href="#">See more</a> -->
	                            </div>
	                        </div>
	                    </div>

						<?php
						// get SMS notification
						$jparams = array(
							'notf_type' => 2,
							'notify_to' => $this->session->staff_id,
							'read' => 0,
							'return_count' => 1
						);
						$unread_notif_count = $this->system_model->getOverallNotification($jparams);
						?>
	                    <div class="dropdown dropdown-notification notif sms_notif_main_div">
	                        <a href="#"
	                           class="header-alarm sms-notif <?php echo ( $unread_notif_count > 0 )?'dropdown-toggle active':null; ?>"
	                           id="dd-messages"
	                           data-toggle="dropdown"
	                           aria-haspopup="true"
	                           aria-expanded="false">
	                            <i class="font-icon-comments"></i>
	                        </a>
	                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-notif" aria-labelledby="dd-messages">
	                            <div class="dropdown-menu-notif-header">
	                                SMS Notifications
    								<span class="label label-pill label-danger" id="notif_sms_count"></span>
	                            </div>
	                            <div class="dropdown-menu-notif-list" id="notication_sms_div">
                            		<!-- general notification appended here -->
									<div class="main_notf_div">
										<?php
										$jparams = array(
											'notf_type' => 2,
											'notify_to' => $this->session->staff_id,
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
							                            <?php
							                            //$changeRedi = str_replace('href="', 'href="'.$this->config->item("crm_link").'/', $sms_notf_msg);

														if (strpos($sms_notf_msg, '.php') !== false) { // old crm
															$changeRedi = str_replace('href="', 'href="'.$this->config->item("crm_link").'/', $sms_notf_msg);
														}else{ // CI
															$changeRedi = str_replace('href="', 'href="'.$this->config->item("crmci_link").'/', $sms_notf_msg);
														}

														echo $changeRedi;
							                            ?>
							                        </div>
												<?php } else { ?>
							                        <div class="dropdown-menu-notif-item"  style="background-color: #f2f2f2;" data-id="<?=$n['notifications_id']?>">
							                            <div class="photo">
							                                <img src="/images/avatar-2-64.png" alt="">
							                            </div>
							                            <?php
							                            //$changeRedi = str_replace('href="', 'href="'.$this->config->item("crm_link").'/', $sms_notf_msg);

														if (strpos($sms_notf_msg, '.php') !== false) { // old crm
															$changeRedi = str_replace('href="', 'href="'.$this->config->item("crm_link").'/', $sms_notf_msg);
														}else{ // CI
															$changeRedi = str_replace('href="', 'href="'.$this->config->item("crmci_link").'/', $sms_notf_msg);
														}

														echo $changeRedi;
							                            ?>
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
								                       <center>No Notifications</center>
								                    </div>
												</ul>
											</div>
										<?php
										}
										?>
									</div>
	                            </div>
	                            <div class="dropdown-menu-notif-more">
	                                <!-- <a href="#">See more</a> -->
	                            </div>
	                        </div>
	                    </div>
						
						<div class="homepage_setting_box"><a href="/home/homepage_settings"><span class="fa fa-gear"></span></a></div>

						<!--
	                    <div class="dropdown dropdown-lang">
	                        <button class="dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                            <span class="flag-icon flag-icon-us"></span>
	                        </button>
	                        <div class="dropdown-menu dropdown-menu-right">
	                            <div class="dropdown-menu-col">
	                                <a class="dropdown-item" href="#"><span class="flag-icon flag-icon-ru"></span>Русский</a>
	                                <a class="dropdown-item" href="#"><span class="flag-icon flag-icon-de"></span>Deutsch</a>
	                                <a class="dropdown-item" href="#"><span class="flag-icon flag-icon-it"></span>Italiano</a>
	                                <a class="dropdown-item" href="#"><span class="flag-icon flag-icon-es"></span>Español</a>
	                                <a class="dropdown-item" href="#"><span class="flag-icon flag-icon-pl"></span>Polski</a>
	                                <a class="dropdown-item" href="#"><span class="flag-icon flag-icon-li"></span>Lietuviu</a>
	                            </div>
	                            <div class="dropdown-menu-col">
	                                <a class="dropdown-item current" href="#"><span class="flag-icon flag-icon-us"></span>English</a>
	                                <a class="dropdown-item" href="#"><span class="flag-icon flag-icon-fr"></span>Français</a>
	                                <a class="dropdown-item" href="#"><span class="flag-icon flag-icon-by"></span>Беларускi</a>
	                                <a class="dropdown-item" href="#"><span class="flag-icon flag-icon-ua"></span>Українська</a>
	                                <a class="dropdown-item" href="#"><span class="flag-icon flag-icon-cz"></span>Česky</a>
	                                <a class="dropdown-item" href="#"><span class="flag-icon flag-icon-ch"></span>中國</a>
	                            </div>
	                        </div>
	                    </div>

	                    <div class="dropdown user-menu">
	                        <button class="dropdown-toggle" id="dd-user-menu" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                            <img src="/images/avatar-2-64.png" alt="">
	                        </button>
	                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dd-user-menu">
	                            <a class="dropdown-item" href="#"><span class="font-icon glyphicon glyphicon-user"></span>Profile</a>
	                            <a class="dropdown-item" href="#"><span class="font-icon glyphicon glyphicon-cog"></span>Settings</a>
	                            <a class="dropdown-item" href="#"><span class="font-icon glyphicon glyphicon-question-sign"></span>Help</a>
	                            <div class="dropdown-divider"></div>
	                            <a class="dropdown-item" href="/sys/logout"><span class="font-icon glyphicon glyphicon-log-out"></span>Logout</a>
	                        </div>
	                    </div>

	                    <button type="button" class="burger-right">
	                        <i class="font-icon-menu-addl"></i>
	                    </button>
						-->

	                </div>


	                <div class="mobile-menu-right-overlay"></div>
	                <div class="site-header-collapsed jtopheader_left">


						<div class="flags">
							<?php
							$loggedInCountryAccess = $loggedInCountryAccess;
							$countryAccessWithDefaultValues = [
								0 => [
									"country_id" => 1,
									"default" => $this->config->item("country") == 1,
									"status" => $this->config->item("country") == 1,
								],
								1 => [
									"country_id" => 2,
									"default" => $this->config->item("country") == 2,
									"status" => $this->config->item("country") == 2,
								],
							];
							$countryAccessToUse = [];
							$countryIds = array_column($loggedInCountryAccess, "country_id");
							foreach($countryAccessWithDefaultValues as &$ca1) {
								$key = array_search($ca1["country_id"], $countryIds);
								if ($key != false) {
									$countryAccessToUse[] = $loggedInCountryAccess[$key];
								}
								else {
									$countryAccessToUse[] = $ca1;
								}
							}
							?>

							<!-- <?php var_dump($countryAccessToUse); ?> -->
							<?php # if ($countryAccessToUse[0]["status"] == 1): ?>
							<!-- AU -->
							<a href="<?php echo "https://{$this->config->item('crmci')}.sats.com.au"; ?>" target="_blank">
								<img src="/images/flags/<?php echo ( $this->config->item('country') == 1 )?'au':'au_bw'; ?>.png" data-toggle="tooltip" title="AU" />
							</a>
							<?php# endif; ?>

							<?php # if ($countryAccessToUse[1]["status"] == 1): ?>
							<!-- NZ -->
							<a href="<?php echo "https://{$this->config->item('crmci')}.sats.co.nz"; ?>" target="_blank">
								<img src="/images/flags/<?php echo ( $this->config->item('country') == 2 )?'nz':'nz_bw'; ?>.png" data-toggle="tooltip" title="NZ" />
							</a>
							<?php # endif; ?>

						</div>



						<!--
	                    <div class="site-header-collapsed-in">
	                        <div class="dropdown dropdown-typical">
	                            <a class="dropdown-toggle" id="dd-header-sales" data-target="#" href="http://example.com" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                                <span class="font-icon font-icon-wallet"></span>
	                                <span class="lbl">Sales</span>
	                            </a>

	                            <div class="dropdown-menu" aria-labelledby="dd-header-sales">
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-home"></span>Quant and Verbal</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-cart"></span>Real Gmat Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-speed"></span>Prep Official App</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-users"></span>CATprer Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-comments"></span>Third Party Test</a>
	                            </div>
	                        </div>
	                        <div class="dropdown dropdown-typical">
	                            <a class="dropdown-toggle" id="dd-header-marketing" data-target="#" href="http://example.com" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                                <span class="font-icon font-icon-cogwheel"></span>
	                                <span class="lbl">Marketing automation</span>
	                            </a>

	                            <div class="dropdown-menu" aria-labelledby="dd-header-marketing">
	                                <a class="dropdown-item" href="#">Current Search</a>
	                                <a class="dropdown-item" href="#">Search for Issues</a>
	                                <div class="dropdown-divider"></div>
	                                <div class="dropdown-header">Recent issues</div>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-home"></span>Quant and Verbal</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-cart"></span>Real Gmat Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-speed"></span>Prep Official App</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-users"></span>CATprer Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-comments"></span>Third Party Test</a>
	                                <div class="dropdown-more">
	                                    <div class="dropdown-more-caption padding">more...</div>
	                                    <div class="dropdown-more-sub">
	                                        <div class="dropdown-more-sub-in">
	                                            <a class="dropdown-item" href="#"><span class="font-icon font-icon-home"></span>Quant and Verbal</a>
	                                            <a class="dropdown-item" href="#"><span class="font-icon font-icon-cart"></span>Real Gmat Test</a>
	                                            <a class="dropdown-item" href="#"><span class="font-icon font-icon-speed"></span>Prep Official App</a>
	                                            <a class="dropdown-item" href="#"><span class="font-icon font-icon-users"></span>CATprer Test</a>
	                                            <a class="dropdown-item" href="#"><span class="font-icon font-icon-comments"></span>Third Party Test</a>
	                                        </div>
	                                    </div>
	                                </div>
	                                <div class="dropdown-divider"></div>
	                                <a class="dropdown-item" href="#">Import Issues from CSV</a>
	                                <div class="dropdown-divider"></div>
	                                <div class="dropdown-header">Filters</div>
	                                <a class="dropdown-item" href="#">My Open Issues</a>
	                                <a class="dropdown-item" href="#">Reported by Me</a>
	                                <div class="dropdown-divider"></div>
	                                <a class="dropdown-item" href="#">Manage filters</a>
	                                <div class="dropdown-divider"></div>
	                                <div class="dropdown-header">Timesheet</div>
	                                <a class="dropdown-item" href="#">Subscribtions</a>
	                            </div>
	                        </div>
	                        <div class="dropdown dropdown-typical">
	                            <a class="dropdown-toggle" id="dd-header-social" data-target="#" href="http://example.com" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                                <span class="font-icon font-icon-share"></span>
	                                <span class="lbl">Social media</span>
	                            </a>

	                            <div class="dropdown-menu" aria-labelledby="dd-header-social">
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-home"></span>Quant and Verbal</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-cart"></span>Real Gmat Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-speed"></span>Prep Official App</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-users"></span>CATprer Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-comments"></span>Third Party Test</a>
	                            </div>
	                        </div>
	                        <div class="dropdown dropdown-typical">
	                            <a href="#" class="dropdown-toggle no-arr">
	                                <span class="font-icon font-icon-page"></span>
	                                <span class="lbl">Projects</span>
	                                <span class="label label-pill label-danger">35</span>
	                            </a>
	                        </div>
	                        <div class="dropdown dropdown-typical">
	                            <a class="dropdown-toggle" id="dd-header-form-builder" data-target="#" href="http://example.com" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                                <span class="font-icon font-icon-pencil"></span>
	                                <span class="lbl">Form builder</span>
	                            </a>

	                            <div class="dropdown-menu" aria-labelledby="dd-header-form-builder">
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-home"></span>Quant and Verbal</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-cart"></span>Real Gmat Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-speed"></span>Prep Official App</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-users"></span>CATprer Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-comments"></span>Third Party Test</a>
	                            </div>
	                        </div>
	                        <div class="dropdown">
	                            <button class="btn btn-rounded dropdown-toggle" id="dd-header-add" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                                Add
	                            </button>
	                            <div class="dropdown-menu" aria-labelledby="dd-header-add">
	                                <a class="dropdown-item" href="#">Quant and Verbal</a>
	                                <a class="dropdown-item" href="#">Real Gmat Test</a>
	                                <a class="dropdown-item" href="#">Prep Official App</a>
	                                <a class="dropdown-item" href="#">CATprer Test</a>
	                                <a class="dropdown-item" href="#">Third Party Test</a>
	                            </div>
	                        </div>
	                        <div class="help-dropdown">
	                            <button type="button">
	                                <i class="font-icon font-icon-help"></i>
	                            </button>
	                            <div class="help-dropdown-popup">
	                                <div class="help-dropdown-popup-side">
	                                    <ul>
	                                        <li><a href="#">Getting Started</a></li>
	                                        <li><a href="#" class="active">Creating a new project</a></li>
	                                        <li><a href="#">Adding customers</a></li>
	                                        <li><a href="#">Settings</a></li>
	                                        <li><a href="#">Importing data</a></li>
	                                        <li><a href="#">Exporting data</a></li>
	                                    </ul>
	                                </div>
	                                <div class="help-dropdown-popup-cont">
	                                    <div class="help-dropdown-popup-cont-in">
	                                        <div class="jscroll">
	                                            <a href="#" class="help-dropdown-popup-item">
	                                                Lorem Ipsum is simply
	                                                <span class="describe">Lorem Ipsum has been the industry's standard dummy text </span>
	                                            </a>
	                                            <a href="#" class="help-dropdown-popup-item">
	                                                Contrary to popular belief
	                                                <span class="describe">Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC</span>
	                                            </a>
	                                            <a href="#" class="help-dropdown-popup-item">
	                                                The point of using Lorem Ipsum
	                                                <span class="describe">Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text</span>
	                                            </a>
	                                            <a href="#" class="help-dropdown-popup-item">
	                                                Lorem Ipsum
	                                                <span class="describe">There are many variations of passages of Lorem Ipsum available</span>
	                                            </a>
	                                            <a href="#" class="help-dropdown-popup-item">
	                                                Lorem Ipsum is simply
	                                                <span class="describe">Lorem Ipsum has been the industry's standard dummy text </span>
	                                            </a>
	                                            <a href="#" class="help-dropdown-popup-item">
	                                                Contrary to popular belief
	                                                <span class="describe">Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC</span>
	                                            </a>
	                                            <a href="#" class="help-dropdown-popup-item">
	                                                The point of using Lorem Ipsum
	                                                <span class="describe">Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text</span>
	                                            </a>
	                                            <a href="#" class="help-dropdown-popup-item">
	                                                Lorem Ipsum
	                                                <span class="describe">There are many variations of passages of Lorem Ipsum available</span>
	                                            </a>
	                                            <a href="#" class="help-dropdown-popup-item">
	                                                Lorem Ipsum is simply
	                                                <span class="describe">Lorem Ipsum has been the industry's standard dummy text </span>
	                                            </a>
	                                            <a href="#" class="help-dropdown-popup-item">
	                                                Contrary to popular belief
	                                                <span class="describe">Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC</span>
	                                            </a>
	                                            <a href="#" class="help-dropdown-popup-item">
	                                                The point of using Lorem Ipsum
	                                                <span class="describe">Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text</span>
	                                            </a>
	                                            <a href="#" class="help-dropdown-popup-item">
	                                                Lorem Ipsum
	                                                <span class="describe">There are many variations of passages of Lorem Ipsum available</span>
	                                            </a>
	                                        </div>
	                                    </div>
	                                </div>
	                            </div>
	                        </div>
	                        <div class="site-header-search-container">
	                            <form class="site-header-search closed">
	                                <input type="text" placeholder="Search"/>
	                                <button type="submit">
	                                    <span class="font-icon-search"></span>
	                                </button>
	                                <div class="overlay"></div>
	                            </form>
	                        </div>
	                    </div>
					-->
	                </div><!--.site-header-collapsed-->
	            </div><!--site-header-content-in-->
	        </div><!--.site-header-content-->
	    </div><!--.container-fluid-->
	</header><!--.site-header-->






<!-- MAIN LEFT MENU START HERE  -->
<?php $this->load->view('templates/main_menu'); ?>
<!-- MAIN LEFT MENU END HERE -->





	<div class="page-content">
	    <div class="container-fluid">
