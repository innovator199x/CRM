function typeInTextarea(el, newText) {

	// starting text highlight position
	var start = el.prop("selectionStart");
	//console.log("selection start: "+start+" \n");
	// end text highlight position
	var end = el.prop("selectionEnd");
	//console.log("selection start: "+end+" \n");
	// text area original text
	var text = el.val();
	// before text of the inserted location
	var before = text.substring(0, start);
	// after text of the inserted location
	var after  = text.substring(end, text.length);
	// combine texts
	el.val(before + newText + after);
	// put text cursor at the end of the insertd tag
	el[0].selectionStart = el[0].selectionEnd = start + newText.length;
	// displat text cursor
    el.focus();
    
}

// also apply changes on model system_model.php - getStreetAbrvFullName
function getStreetAbrvFullName(street){

	var result = street;

	var find_arr = [				
		'Ally','Arc','Ave','Bvd','Bypa','Cct','Cl','Crn','Ct','Cir','Cres','Cds','Dr','Esp',
		'Grn','Gr','Hwy','Jnc','Pde','Pl','Rdge','Rd','Sq','St','Tce','Accs','Awlk','Ally','Alwy',
		'Ambl','App','Arc','Artl','Arty','Av','Ba','Bch','Bwlk','Br','Bran','Brk','Bret','Bdge','Brdwlk',
		'Bdwy','Bswy','Bypa','Bywy','Cswy','Ctr','Cnwy','Ch','Clt','Crcs','Clr','Clde','Cmmn','Cmmns',
		'Cncd','Con','Cntn','Cps','Cso','Crse','Ctyd','Crst','Crf','Crk','Crss','Crsg','Cuwy','Cutt',
		'De','Dstr','Div','Dom','Dwns','Dvwy','Esmt','Elb','Ent','Est','Exp','Extn','Fawy',
		'Fbrk','Flne','Ftrk','Fitr','Flts','Folw','Ftwy','Fshr','Form','Fwy','Frnt','Frtg','Gdn',
		'Gdns','Gte','Gwy','Glde','Gra','Grn','Gr','Gly','Hrbr','Hvn','Hth','Hts','Hird','Hllw','Inlt',
		'Intg','Id','Jnc','Knol','Ladr','Ldg','Lnwy','Ledr','Lkt','Manr','Mndr','Mtwy','Nth','Pwy',
		'Psge','Pway','Psla','Piaz','Plza','Pkt','Pnt','Prec','Prom','Prst','Qdrt','Qy','Qys','Rmbl',
		'Rnge','Rch','Res','Rtt','Rtn','Rdge','Rofw','Rsng','Rvr','Rds','Rdwy','Rty','Rnd','Rte','Svwy',
		'Skln','Slpe','Sth','Sq','Stps','Strt','Stai','Stra','Strp','Sbwy','Tce','Thfr','Thru','Tlwy',
		'Trk','Trl','Tmwy','Tvse','Tkwy','Tunl','Upas','Vlly','Viad','Vws','Vlla','Vlge','Vlls','Vsta','Wkwy',
		'Wtrs','Wtwy','Whrf','Wd','Wds'
	];
	var replace_arr = [
		'Alley','Arcade','Avenue','Boulevard','Bypass','Circuit','Close','Corner','Court','Circle','Crescent','Cul-de-sac','Drive','Esplanade',
		'Green','Grove','Highway','Junction','Parade','Place','Ridge','Road','Square','Street','Terrace','Access','Airwalk','Alley','Alleyway',
		'Amble','Approach','Arcade','Arterial','Artery','Avenue','Banan','Beach','Boardwalk','Brace','Branch','Break','Brett','Bridge','Broadwalk',
		'Broadway','Busway','Bypass','Byway','Causeway','Centre','Centreway','Chase','Circlet','Circus','Cluster','Colonnade','Common','Commons',
		'Concord','Concourse','Connection','Copse','Corso','Course','Courtyard','Crest','Crief','Crook','Cross','Crossing','Cruiseway','Cutting',
		'Deviation','Distributor','Divide','Domain','Downs','Driveway','Easement','Elbow','Entrance','Estate','Expressway','Extension','Fairway',
		'Firebreak','Fireline','Firetrack','Firetrail','Flats','Follow','Footway','Foreshore','Formation','Freeway','Front','Frontage','Garden',
		'Gardens','Gate','Gateway','Glade','Grange','Green','Grove','Gully','Harbour','Haven','Heath','Heights','Highroad','Hollow','Inlet',
		'Interchange','Island','Junction','Knoll','Ladder','Landing','Laneway','Leader','Lookout','Manor','Meander','Motorway','North','Parkway',
		'Passage','Pathway','Peninsula','Piazza','Plaza','Pocket','Point','Precinct','Promenade','Pursuit','Quadrant','Quay','Quays','Ramble',
		'Range','Reach','Reserve','Retreat','Return','Ridge','Right Of Way','Rising','River','Roads','Roadway','Rotary','Round','Route','Serviceway',
		'Skyline','Slope','South','Square','Steps','Straight','Strait','Strand','Strip','Subway','Terrace','Thoroughfare','Throughway','Tollway',
		'Track','Trail','Tramway','Traverse','Trunkway','Tunnel','Underpass','Valley','Viaduct','Views','Villa','Village','Villas','Vista','Walkway',
		'Waters','Waterway','Wharf','Wood','Woods'
	];

	for( var i = 0; i < find_arr.length; i++ ){
		
		var patt = "\\b"+find_arr[i]+"\\b";
		var regx = new RegExp(patt,"i");
		var search_res = regx.test(street);
		
		if ( search_res == true ){

			replace = replace_arr[i];
			result = street.replace(regx, replace, street);

		}	
		
	}

	return result;

}


// also apply changes on model system_model.php - getStreetAbrvFullName
function getStreetAbbrv(street){

	var result = street;

	var find_arr = [
		'Alley','Arcade','Avenue','Boulevard','Bypass','Circuit','Close','Corner','Court','Circle','Crescent','Cul-de-sac','Drive','Esplanade',
		'Green','Grove','Highway','Junction','Parade','Place','Ridge','Road','Square','Street','Terrace','Access','Airwalk','Alley','Alleyway',
		'Amble','Approach','Arcade','Arterial','Artery','Avenue','Banan','Beach','Boardwalk','Brace','Branch','Break','Brett','Bridge','Broadwalk',
		'Broadway','Busway','Bypass','Byway','Causeway','Centre','Centreway','Chase','Circlet','Circus','Cluster','Colonnade','Common','Commons',
		'Concord','Concourse','Connection','Copse','Corso','Course','Courtyard','Crest','Crief','Crook','Cross','Crossing','Cruiseway','Cutting',
		'Deviation','Distributor','Divide','Domain','Downs','Driveway','Easement','Elbow','Entrance','Estate','Expressway','Extension','Fairway',
		'Firebreak','Fireline','Firetrack','Firetrail','Flats','Follow','Footway','Foreshore','Formation','Freeway','Front','Frontage','Garden',
		'Gardens','Gate','Gateway','Glade','Grange','Green','Grove','Gully','Harbour','Haven','Heath','Heights','Highroad','Hollow','Inlet',
		'Interchange','Island','Junction','Knoll','Ladder','Landing','Laneway','Leader','Lookout','Manor','Meander','Motorway','North','Parkway',
		'Passage','Pathway','Peninsula','Piazza','Plaza','Pocket','Point','Precinct','Promenade','Pursuit','Quadrant','Quay','Quays','Ramble',
		'Range','Reach','Reserve','Retreat','Return','Ridge','Right Of Way','Rising','River','Roads','Roadway','Rotary','Round','Route','Serviceway',
		'Skyline','Slope','South','Square','Steps','Straight','Strait','Strand','Strip','Subway','Terrace','Thoroughfare','Throughway','Tollway',
		'Track','Trail','Tramway','Traverse','Trunkway','Tunnel','Underpass','Valley','Viaduct','Views','Villa','Village','Villas','Vista','Walkway',
		'Waters','Waterway','Wharf','Wood','Woods'
	];
	var replace_arr = [
		'Ally','Arc','Ave','Bvd','Bypa','Cct','Cl','Crn','Ct','Cir','Cres','Cds','Dr','Esp',
		'Grn','Gr','Hwy','Jnc','Pde','Pl','Rdge','Rd','Sq','St','Tce','Accs','Awlk','Ally','Alwy',
		'Ambl','App','Arc','Artl','Arty','Av','Ba','Bch','Bwlk','Br','Bran','Brk','Bret','Bdge','Brdwlk',
		'Bdwy','Bswy','Bypa','Bywy','Cswy','Ctr','Cnwy','Ch','Clt','Crcs','Clr','Clde','Cmmn','Cmmns',
		'Cncd','Con','Cntn','Cps','Cso','Crse','Ctyd','Crst','Crf','Crk','Crss','Crsg','Cuwy','Cutt',
		'De','Dstr','Div','Dom','Dwns','Dvwy','Esmt','Elb','Ent','Est','Exp','Extn','Fawy',
		'Fbrk','Flne','Ftrk','Fitr','Flts','Folw','Ftwy','Fshr','Form','Fwy','Frnt','Frtg','Gdn',
		'Gdns','Gte','Gwy','Glde','Gra','Grn','Gr','Gly','Hrbr','Hvn','Hth','Hts','Hird','Hllw','Inlt',
		'Intg','Id','Jnc','Knol','Ladr','Ldg','Lnwy','Ledr','Lkt','Manr','Mndr','Mtwy','Nth','Pwy',
		'Psge','Pway','Psla','Piaz','Plza','Pkt','Pnt','Prec','Prom','Prst','Qdrt','Qy','Qys','Rmbl',
		'Rnge','Rch','Res','Rtt','Rtn','Rdge','Rofw','Rsng','Rvr','Rds','Rdwy','Rty','Rnd','Rte','Svwy',
		'Skln','Slpe','Sth','Sq','Stps','Strt','Stai','Stra','Strp','Sbwy','Tce','Thfr','Thru','Tlwy',
		'Trk','Trl','Tmwy','Tvse','Tkwy','Tunl','Upas','Vlly','Viad','Vws','Vlla','Vlge','Vlls','Vsta','Wkwy',
		'Wtrs','Wtwy','Whrf','Wd','Wds'
	];

	for( var i = 0; i < find_arr.length; i++ ){
		
		var patt = "\\b"+find_arr[i]+"\\b";
		var regx = new RegExp(patt,"i");
		var search_res = regx.test(street);
		
		if ( search_res == true ){

			replace = replace_arr[i];
			result = street.replace(regx, replace, street);

		}
		
	}

	return result;

}


function clearStreetName_old(street){

	var result = street;
	
	var street_long = [
		'Alley','Arcade','Avenue','Boulevard','Bypass','Circuit','Close','Corner','Court','Circle','Crescent','Cul-de-sac','Drive','Esplanade',
		'Green','Grove','Highway','Junction','Parade','Place','Ridge','Road','Square','Street','Terrace','Access','Airwalk','Alley','Alleyway',
		'Amble','Approach','Arcade','Arterial','Artery','Avenue','Banan','Beach','Boardwalk','Brace','Branch','Break','Brett','Bridge','Broadwalk',
		'Broadway','Busway','Bypass','Byway','Causeway','Centre','Centreway','Chase','Circlet','Circus','Cluster','Colonnade','Common','Commons',
		'Concord','Concourse','Connection','Copse','Corso','Course','Courtyard','Crest','Crief','Crook','Cross','Crossing','Cruiseway','Cutting',
		'Deviation','Distributor','Divide','Domain','Downs','Driveway','Easement','Elbow','Entrance','Estate','Expressway','Extension','Fairway',
		'Firebreak','Fireline','Firetrack','Firetrail','Flats','Follow','Footway','Foreshore','Formation','Freeway','Front','Frontage','Garden',
		'Gardens','Gate','Gateway','Glade','Grange','Green','Grove','Gully','Harbour','Haven','Heath','Heights','Highroad','Hollow','Inlet',
		'Interchange','Island','Junction','Knoll','Ladder','Landing','Laneway','Leader','Lookout','Manor','Meander','Motorway','North','Parkway',
		'Passage','Pathway','Peninsula','Piazza','Plaza','Pocket','Point','Precinct','Promenade','Pursuit','Quadrant','Quay','Quays','Ramble',
		'Range','Reach','Reserve','Retreat','Return','Ridge','Right Of Way','Rising','River','Roads','Roadway','Rotary','Round','Route','Serviceway',
		'Skyline','Slope','South','Square','Steps','Straight','Strait','Strand','Strip','Subway','Terrace','Thoroughfare','Throughway','Tollway',
		'Track','Trail','Tramway','Traverse','Trunkway','Tunnel','Underpass','Valley','Viaduct','Views','Villa','Village','Villas','Vista','Walkway',
		'Waters','Waterway','Wharf','Wood','Woods'
	];
	var street_short = [
		'Ally','Arc','Ave','Bvd','Bypa','Cct','Cl','Crn','Ct','Cir','Cres','Cds','Dr','Esp',
		'Grn','Gr','Hwy','Jnc','Pde','Pl','Rdge','Rd','Sq','St','Tce','Accs','Awlk','Ally','Alwy',
		'Ambl','App','Arc','Artl','Arty','Av','Ba','Bch','Bwlk','Br','Bran','Brk','Bret','Bdge','Brdwlk',
		'Bdwy','Bswy','Bypa','Bywy','Cswy','Ctr','Cnwy','Ch','Clt','Crcs','Clr','Clde','Cmmn','Cmmns',
		'Cncd','Con','Cntn','Cps','Cso','Crse','Ctyd','Crst','Crf','Crk','Crss','Crsg','Cuwy','Cutt',
		'De','Dstr','Div','Dom','Dwns','Dvwy','Esmt','Elb','Ent','Est','Exp','Extn','Fawy',
		'Fbrk','Flne','Ftrk','Fitr','Flts','Folw','Ftwy','Fshr','Form','Fwy','Frnt','Frtg','Gdn',
		'Gdns','Gte','Gwy','Glde','Gra','Grn','Gr','Gly','Hrbr','Hvn','Hth','Hts','Hird','Hllw','Inlt',
		'Intg','Id','Jnc','Knol','Ladr','Ldg','Lnwy','Ledr','Lkt','Manr','Mndr','Mtwy','Nth','Pwy',
		'Psge','Pway','Psla','Piaz','Plza','Pkt','Pnt','Prec','Prom','Prst','Qdrt','Qy','Qys','Rmbl',
		'Rnge','Rch','Res','Rtt','Rtn','Rdge','Rofw','Rsng','Rvr','Rds','Rdwy','Rty','Rnd','Rte','Svwy',
		'Skln','Slpe','Sth','Sq','Stps','Strt','Stai','Stra','Strp','Sbwy','Tce','Thfr','Thru','Tlwy',
		'Trk','Trl','Tmwy','Tvse','Tkwy','Tunl','Upas','Vlly','Viad','Vws','Vlla','Vlge','Vlls','Vsta','Wkwy',
		'Wtrs','Wtwy','Whrf','Wd','Wds'
	];

	var street_comb = street_long.concat(street_short); // combine the two street full namd and abbreviation

	
	for( var i = 0; i < street_comb.length; i++ ){		
		
		
		var patt = "\\b"+street_comb[i]+"\\b"; // \b is word boundry
		var regx = new RegExp(patt,"gi"); // global and case-insensitive
		var search_res = regx.test(street);
		
		if ( search_res == true ){

			result = street.replace(regx, '', street);

		}		
		
	}
	
	return result;
	
}


function clearStreetName(address){
	
	var street_long = [
		'Alley','Arcade','Avenue','Boulevard','Bypass','Circuit','Close','Corner','Court','Circle','Crescent','Cul-de-sac','Drive','Esplanade',
		'Green','Grove','Highway','Junction','Parade','Place','Ridge','Road','Square','Street','Terrace','Access','Airwalk','Alley','Alleyway',
		'Amble','Approach','Arcade','Arterial','Artery','Avenue','Banan','Beach','Boardwalk','Brace','Branch','Break','Brett','Bridge','Broadwalk',
		'Broadway','Busway','Bypass','Byway','Causeway','Centre','Centreway','Chase','Circlet','Circus','Cluster','Colonnade','Common','Commons',
		'Concord','Concourse','Connection','Copse','Corso','Course','Courtyard','Crest','Crief','Crook','Cross','Crossing','Cruiseway','Cutting',
		'Deviation','Distributor','Divide','Domain','Downs','Driveway','Easement','Elbow','Entrance','Estate','Expressway','Extension','Fairway',
		'Firebreak','Fireline','Firetrack','Firetrail','Flats','Follow','Footway','Foreshore','Formation','Freeway','Front','Frontage','Garden',
		'Gardens','Gate','Gateway','Glade','Grange','Green','Grove','Gully','Harbour','Haven','Heath','Heights','Highroad','Hollow','Inlet',
		'Interchange','Island','Junction','Knoll','Ladder','Landing','Laneway','Leader','Lookout','Manor','Meander','Motorway','North','Parkway',
		'Passage','Pathway','Peninsula','Piazza','Plaza','Pocket','Point','Precinct','Promenade','Pursuit','Quadrant','Quay','Quays','Ramble',
		'Range','Reach','Reserve','Retreat','Return','Ridge','Right Of Way','Rising','River','Roads','Roadway','Rotary','Round','Route','Serviceway',
		'Skyline','Slope','South','Square','Steps','Straight','Strait','Strand','Strip','Subway','Terrace','Thoroughfare','Throughway','Tollway',
		'Track','Trail','Tramway','Traverse','Trunkway','Tunnel','Underpass','Valley','Viaduct','Views','Villa','Village','Villas','Vista','Walkway',
		'Waters','Waterway','Wharf','Wood','Woods'
	];
	var street_short = [
		'Ally','Arc','Ave','Bvd','Bypa','Cct','Cl','Crn','Ct','Cir','Cres','Cds','Dr','Esp',
		'Grn','Gr','Hwy','Jnc','Pde','Pl','Rdge','Rd','Sq','St','Tce','Accs','Awlk','Ally','Alwy',
		'Ambl','App','Arc','Artl','Arty','Av','Ba','Bch','Bwlk','Br','Bran','Brk','Bret','Bdge','Brdwlk',
		'Bdwy','Bswy','Bypa','Bywy','Cswy','Ctr','Cnwy','Ch','Clt','Crcs','Clr','Clde','Cmmn','Cmmns',
		'Cncd','Con','Cntn','Cps','Cso','Crse','Ctyd','Crst','Crf','Crk','Crss','Crsg','Cuwy','Cutt',
		'De','Dstr','Div','Dom','Dwns','Dvwy','Esmt','Elb','Ent','Est','Exp','Extn','Fawy',
		'Fbrk','Flne','Ftrk','Fitr','Flts','Folw','Ftwy','Fshr','Form','Fwy','Frnt','Frtg','Gdn',
		'Gdns','Gte','Gwy','Glde','Gra','Grn','Gr','Gly','Hrbr','Hvn','Hth','Hts','Hird','Hllw','Inlt',
		'Intg','Id','Jnc','Knol','Ladr','Ldg','Lnwy','Ledr','Lkt','Manr','Mndr','Mtwy','Nth','Pwy',
		'Psge','Pway','Psla','Piaz','Plza','Pkt','Pnt','Prec','Prom','Prst','Qdrt','Qy','Qys','Rmbl',
		'Rnge','Rch','Res','Rtt','Rtn','Rdge','Rofw','Rsng','Rvr','Rds','Rdwy','Rty','Rnd','Rte','Svwy',
		'Skln','Slpe','Sth','Sq','Stps','Strt','Stai','Stra','Strp','Sbwy','Tce','Thfr','Thru','Tlwy',
		'Trk','Trl','Tmwy','Tvse','Tkwy','Tunl','Upas','Vlly','Viad','Vws','Vlla','Vlge','Vlls','Vsta','Wkwy',
		'Wtrs','Wtwy','Whrf','Wd','Wds'
	];

	var street_comb = street_long.concat(street_short); // combine the two street full namd and abbreviation

	
	for( var i = 0; i < street_comb.length; i++ ){		
		
		
		var patt = "\\b"+street_comb[i]+"\\b"; // \b is word boundry
		var regx = new RegExp(patt,"gi"); // global and case-insensitive
		var search_res = regx.test(address);
		
		if ( search_res == true ){

			address = address.replace(regx, '', street_comb[i]);

		}		
		
	}
	
	return address;
	
}



function split_street_number(street_name){

	var street_name1 = '';
	var street_name2 = '';

	street_name1 = street_name.split("/");
	street_name2 = street_name1.split("-");

	return street_name2;
	
}


function toggle_button(btn_node,orig_btn_txt,toggle_div,cancel_txt='Cancel'){

	var btn_txt = btn_node.text();

	if( btn_txt == orig_btn_txt ){

		btn_node.text(cancel_txt);
		toggle_div.show();

	}else{ // cancel

		btn_node.text(orig_btn_txt);
		toggle_div.hide();

	}
	
}


function toggle_inline_edit(btn_node,orig_btn_txt,cancel_txt='Cancel',btn_update_class='.btn_update'){

	var btn_txt = btn_node.text();
	var parent_row = btn_node.parents("tr:first"); 

	if( btn_txt == orig_btn_txt ){

		btn_node.text(cancel_txt);	
		parent_row.find(".txt_hid").show();
		parent_row.find(".txt_lbl").hide();
		parent_row.find(btn_update_class).show();	

	}else{ // cancel

		btn_node.text(orig_btn_txt);
		parent_row.find(".txt_hid").hide();
		parent_row.find(".txt_lbl").show();
		parent_row.find(btn_update_class).hide();		

	}

}