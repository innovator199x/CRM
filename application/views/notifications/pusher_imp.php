
<script>
Pusher.logToConsole = true;
var pusherKey = "<?=$this->config->item('PUSHER_KEY');?>";
var pusherClu = "<?=$this->config->item('PUSHER_CLUSTER');?>";
var pusher = new Pusher(pusherKey, {
  cluster: pusherClu,
  forceTLS: true
});
var ch = "ch<?=$this->session->staff_id?>";
var ev = "ev01";

var channel = pusher.subscribe(ch);
channel.bind(ev, function(data) {
	if (data.notif_type === 1) {
		$('.general-notif').addClass("dropdown-toggle active")
	}else {
		$('.sms-notif').addClass("dropdown-toggle active")
	}
  	getNotification(data.notif_type);
    getNotifCount(data.notif_type);
	playSoundNotification();
});

$(document).ready(function() {
	/*
  	getNotification(1); 
    getNotifCount(1); 
  	getNotification(2); 
    getNotifCount(2); 
	*/
    $(".general-notif").on('click', function(event){

		let dom = jQuery(this);
		let parent = dom.parents(".gen_notif_main_div");

    	if ( !parent.hasClass( "show" ) ) {

			var notifIds = [];
			parent.find(".new_notification").each(function(){
				var notifId = $(this).attr('data-id')
				notifIds.push(notifId);
			});

			if (notifIds.length > 0) {
				jQuery.ajax({
					type: "POST",
					url: "/sys/ajax_message_mark_as_read",
					data: {
						notifIds: notifIds
					}
				}).done(function( ret ) {
					$('.general-notif').removeClass("dropdown-toggle")
					$('.general-notif').removeClass("active")
				});
			}

    	}
	});

    $(".sms-notif").on('click', function(event){

		let dom = jQuery(this);
		let parent = dom.parents(".sms_notif_main_div");

    	if ( !parent.hasClass( "show" ) ) {

			var notifIds = [];
			parent.find(".new_notification").each(function(){
				var notifId = $(this).attr('data-id')
				notifIds.push(notifId);
			});

			if (notifIds.length > 0) {
				jQuery.ajax({
					type: "POST",
					url: "/sys/ajax_message_mark_as_read",
					data: {
						notifIds: notifIds
					}
				}).done(function( ret ) {
					$('.sms-notif').removeClass("dropdown-toggle")
					$('.sms-notif').removeClass("active")
				});
			}

    	}
	});
})

	// ajax call get notifications
	function getNotification(notifType){ 
		// update notication
		jQuery.ajax({
			type: "POST",
			url: "/sys/ajax_get_notifications",
			data: {
				notifType : notifType
			}
		}).done(function( ret ) {
			switch(notifType) {
			  case 1:
					jQuery("#notication_div").html(ret);
			    break;
			  case 2:
					jQuery("#notication_sms_div").html(ret);
			    break;
			  default:
			    // code block
			}
		});	
	}

	function playSoundNotification(){
		jQuery.ajax({
			type: "POST",
			url: "/sys/ajax_update_sound_notification"
		}).done(function( ret ) {
			playIonSoundNotification();
		});	
	}

	function playIonSoundNotification(){
		// init bunch of sounds
		ion.sound({
			sounds: [
				{name: "door_bell"}
			],

			// main config
			path: "/inc/ion_sound/sounds/",
			preload: true,
			multiplay: true,
			volume: 0.9
		});
		
		// play sound
		ion.sound.play("door_bell");
	}

	function getNotifCount(notifType) {
		jQuery.ajax({
			type: "POST",
			url: "/sys/ajax_get_notif_count",
			data: {
				notifType : notifType
			}
		}).done(function( ret ) {
			switch(notifType) {
			  case 1:
					jQuery("#notif_count").html(ret);
					if (parseInt(ret) > 0) { 
						$('.general-notif').addClass("dropdown-toggle active") 
					}
			    break;
			  case 2:
					jQuery("#notif_sms_count").html(ret);
					if (parseInt(ret) > 0) { 
						$('.sms-notif').addClass("dropdown-toggle active") 
					}
			    break;
			  default:
			    // code block
			}
		});	
	}


</script>