

<style type="text/css">
	.validation_errors p{
		margin-bottom:0;
	}
</style>

<!-- DISPLAY PRELOADER IF CAME FROM CRM START -->
<?php if($this->input->get_post('crm_login')==1){ ?>
	<div id="preloader">
		<div id="status">&nbsp;</div>
	</div>
<?php } ?>
<!-- DISPLAY PRELOADER IF CAME FROM CRM END -->


<?php
if( isset($_GET['rp'])){ ?>
	<div class="alert alert-success mx5">
		Your new password is now updated. You may try your new password now.
	</div>
<?php
}
?>


<?php
if( $this->session->flashdata('initial_setup_relogin') == 1 ){ ?>
	<div class="alert alert-success mx5">
		Please now log back in to the portal to complete the setup. 
		Use the email address that you just entered and the password that you used to log in. 
		You can reset your password at anytime on your profile page.
	</div>
<?php
}
?>


<?php 
// generate CSRF token 
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(24));

$form_attr = array(
	'id' => 'jform',
	'class'=>'sign-box'
);
echo form_open(base_url().'login/index',$form_attr); 
?>
	<div class="text-center">
		<img src="/images/logo_login.png" class="sats_logo" />
	</div>
	
	
	<h1 style="display: none;">
		We are experiencing some issues and will be back online shortly
	</h1>
	
	<div style="display: block;">
	
		<header class="sign-title">Sign In</header>
		
		
		<?php 
		if( validation_errors() ){ ?>
			<div class="alert alert-danger validation_errors">
			<?php echo validation_errors(); ?>
			</div>
		<?php
		}	
		?>
		
		
		<?php 

		// incorrect password
		if( $this->session->flashdata('password_incorrect') == 1 ){ ?>
			<div class="alert alert-danger">
			Password Incorrect
			</div>
		<?php
		}	
		?>
		
		<?php 
		// user doesnt exist
		if( $this->session->flashdata('account_doesnt_exist') == 1 ){ ?>
			<div class="alert alert-danger">
			Account Doesn't Exist
			</div>
		<?php
		}	
		?>

		
		<div class="form-group">
			<input type="text" name="username" class="form-control" placeholder="E-Mail" value="<?php echo $this->input->get_post('user') ?>" />
		</div>
		<div class="form-group">
			<input type="password" name="password" class="form-control" placeholder="Password" 
			data-validation="[NOTEMPTY]"
			/>
		</div>

		<!-- google recaptcha start -->
		<?php if($this->session->userdata('loginFailedCounter')==3){ ?>
			<div class="form-group">
				<div class="g-recaptcha" data-sitekey="6LcChoMUAAAAAOv1oQr4SX8vcS28hIHGSZHKjA0I"></div>
			</div>
		<?php } ?>
		<!-- google recaptcha end -->

		<div class="form-group">
			<div class="float-right reset">
				<a href="/user_accounts/reset_password_form">Reset Password</a>
			</div>
		</div> 
		<div>
			<input type="hidden" name="agency_id" value="<?php echo $this->input->get_post('agency_id') ?>" />
			<input type="hidden" name="crm_login" value="<?php echo $this->input->get_post('crm_login') ?>" />
			<input type="hidden" name="hid_pass" value="<?php echo $this->input->get_post('pass') ?>" />
			<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
			<button type="submit" id="sign_in_btn" class="btn ladda-button" data-style="zoom-out">
				<span class="ladda-label">Sign in</span>
			</button>
		</div>
		<div class="sign-note">
				<div>Your IP Address: <?php echo $this->jcclass->getIPaddress(); ?></div>
		</div>
		
	</div>

</form>

