$(document).ready(function(){

    
        //success message 
        jQuery('#notie-alert-outer').delay('4000').animate({top:-99999},5000)

         //mobile and phone custom masking ang error validation for default fields (not event fields)
         phone_mobile_mask(); //init phone and mobile mas for default fields (not event fields)
         mobile_validation(); //init mobile validation
         phone_validation(); //init phone validation

});



function select2Photos (state) {
    if (!state.id) { return state.text; }
    var $state = $(
        '<span class="user-item"><img src="' + state.element.getAttribute('data-photo') + '"/>' + state.text + '</span>'
    );
    return $state;
}

function is_numeric(num){
    if(num.match( /^\d+([\.,]\d+)?$/)==null){
        return false
    }
}

function validate_email(email){
    var atpos = email.indexOf("@");
    var dotpos = email.lastIndexOf(".");
    if ( atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length ){
      return false
    }
}

function formatToDateToYmd(date){
	var date2 = date.split("/");
	var d = date2[0];
	var m = date2[1];
	var y = date2[2];
	return y+'-'+m+'-'+d;
}

function addDays(date, days) {
    var result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
}

function addMonth(date, month) {
	var result = new Date(date);
	result.setMonth(result.getMonth() + month);
	return result;
}

function formatDate(date,format='d/m/y'){
	var d = date.getDate();
	var m = date.getMonth()+1; //January is 0!
	var y = date.getFullYear();
	
	if(d<10){
		d='0'+d;
	} 
	if(m<10){
		m='0'+m;
	} 
	
	switch(format){
		case 'd/m/y':
			format2 = d+'/'+m+'/'+y;
		break;
		case 'y-m-d':
			format2 = y+'-'+m+'-'+d;
		break;
	}
	
	return date = format2;
}



