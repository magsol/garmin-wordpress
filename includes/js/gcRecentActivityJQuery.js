function displayGCWidget(obj, map, style) {
	var times = obj;
	var contentHTML = '';
	var mapping = map;

	for (var i=0; i<times.length; i++) {
	
		var type = '';
		if (mapping[0].activity_type != null) {
			type = times[i].type + " on ";
		} 
		var title = '';
		if (mapping[0].name != null && times[i].name != null && times[i].name != "" && times[i].name != "Untitled") {
			title = times[i].name;
		} else {
			title = type + times[i].date;
		}
		contentHTML += '<H3 class="GCAccordionHeader"><a href="#" class="GCAccordionHeaderTitle">' + title + '</a></H3>';
		contentHTML += '<div class="GCAccordionDivTag">';
		contentHTML += '<p class="GCAccordionPTag">';
		
		if (mapping[0].name != null && times[i].name != null && times[i].name != "" && times[i].name != "Untitled") {
			contentHTML += '<span class="GCAccordionLinkText">' + type + times[i].date + '</span>';	
		}
		contentHTML += '<TABLE width="100%" class="GCAccordionDataTable">';
		if (mapping[0].duration != null && times[i].time != null && times[i].time != "") {
			contentHTML += '<TR class="GCAccordionDataRow"><TD class="GCAccordionDataTitle">Time</TD><TD class="GCAccordionDataValue">' + times[i].time + '</TD></TR>';
		}
		if (mapping[0].distance != null && times[i].distance != null && times[i].distance != "") {
			contentHTML += '<TR class="GCAccordionDataRow"><TD class="GCAccordionDataTitle">Distance</TD><TD class="GCAccordionDataValue">' + times[i].distance + '</TD></TR>';
		}
		if (mapping[0].speed != null && times[i].speed != null && times[i].speed != "") {
			contentHTML += '<TR class="GCAccordionDataRow"><TD class="GCAccordionDataTitle">Speed</TD><TD class="GCAccordionDataValue">' + times[i].speed + '</TD></TR>';
		}
		if (mapping[0].hr != null && times[i].heartrate != null && times[i].heartrate != "") {
			contentHTML += '<TR class="GCAccordionDataRow"><TD  class="GCAccordionDataTitle">Avg HR</TD><TD class="GCAccordionDataValue">' + times[i].heartrate + '</TD></TR>';
		} 
		if (mapping[0].energy != null && times[i].energy != null && times[i].energy != "") {
			contentHTML += '<TR class="GCAccordionDataRow"><TD class="GCAccordionDataTitle">Energy Spent</TD><TD class="GCAccordionDataValue">' + times[i].energy + '</TD></TR>';
		}
		if (mapping[0].cadence != null && times[i].cadence != null && times[i].cadence != "") {
			contentHTML += '<TR class="GCAccordionDataRow"><TD class="GCAccordionDataTitle">Avg Cadence</TD><TD class="GCAccordionDataValue">' + times[i].cadence + '</TD></TR>';
		}
		contentHTML += '</TABLE>';
		contentHTML += '<BR/>';
		contentHTML += '<span class="GCAccordionLinkText">Details @ <a href="' + times[i].url + '" target="_new" class="GCAccordionLinkValue">Garmin Connect</a><BR/></span>';	
		contentHTML += '</p>';
		contentHTML += '</div>';
	
	}
	document.getElementById('GCAccordion' + style).innerHTML = contentHTML;

}

function displayGCError(string, style) {
	document.getElementById('GCAccordion' + style).innerHTML = string;

}
