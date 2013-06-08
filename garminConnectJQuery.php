<?PHP	
	
	require_once('includes/php/garminAPI.php');
	$root = dirname(dirname(dirname(dirname(__FILE__))));
	require_once($root.'/wp-config.php');
	require_once($root.'/wp-includes/wp-db.php'); 
		
	$type = strip_tags($_GET['type']);
	$numResults = strip_tags($_GET['numResults']);
	$style = strip_tags($_GET['style']);
	if (isset($_GET['displayDate'])) $date = 1;
	if (isset($_GET['displayName'])) $displayName = 1;
	if (isset($_GET['displayDuration'])) $duration = 1;
	if (isset($_GET['displayDistance'])) $distance = 1;
	if (isset($_GET['displaySpeed'])) $speed = 1;
	if (isset($_GET['displayHR'])) $hr = 1;
	if (isset($_GET['displayEnergy'])) $energy = 1;
	if (isset($_GET['displayCadence'])) $cadence = 1;
	if (isset($_GET['displayActivityType'])) $activity_type = 1;
	if (isset($_GET['displayEventType'])) $event_type = 1;
	if (isset($_GET['displayCoordinates'])) $coordinates = 1;
	if (isset($_GET['displayMap'])) $map = 1;

	if ( get_option('gc_user_name') == NULL || get_option('gc_user_name') == "") {
			$content = "displayGCSettings();";
			echo $content;
	} else if ($type == null || $type == 'recent') {					
		$GarminAPI = new GarminAPI();
		$GarminAPI->setAPIUser(get_option('gc_user_name'));
		if (get_option('gc_password') != NULL) {
			$GarminAPI->setAPIPassword(get_option('gc_password'));
		} else {
			$GarminAPI->setRSSSource(TRUE);
		}
		if (get_option('gc_data_source') == 'rss') {
			$GarminAPI->setRSSSource(TRUE);
		}
		$GarminAPI->setUserName(get_option('gc_user_name'));
		if (get_option('gc_cache_disable') == NULL) {
			$GarminAPI->setEnableCaching(TRUE);
			if (get_option('gc_cache_length') != NULL  && get_option('gc_cache_length') > 900)
				$GarminAPI->setCacheDuration(get_option('gc_cache_length'));
			else 
				$GarminAPI->setCacheDuration(900);
		} else {
			$GarminAPI->setEnableCaching(FALSE);
		}
		if (($response = $GarminAPI->loadActivities($numResults)) !== TRUE) {	
			$content = "displayGCError('" . $GarminAPI->decodeErrorMessage($response) . "', '" . $style . "');";	
		}
		$activity_array = $GarminAPI->getUserActivities();
		if (!empty($activity_array) && $response === TRUE) {
			$content = "displayGCWidget([";
			for ($i = 0; $i< sizeof($activity_array); $i++) {
				
				$activity = $activity_array[$i];
				
				if (isset($activity['activityType'])) {
					$activity_type_display = $GarminAPI->getActivityType($activity['activityType'], $activity['activityTypeParent']);		
				} else {
					$activity_type_display = $activity['activityTypeDisplay'];
				}	
				if (isset($date) && $date == 1)
					$dateDisplay = date(get_option("date_format"), strtotime($activity['activitySummaryBeginTimestamp']));
				else
					$dateDisplay = date(get_option("date_format") . " " . get_option("time_format"), strtotime($activity['activitySummaryBeginTimestamp']));
					
				$cadenceValue = null;
				if ( isset($activity['activitySummaryWeightedMeanRunCadence']) && $activity['activitySummaryWeightedMeanRunCadence'] != "")
					$cadenceValue = $activity['activitySummaryWeightedMeanRunCadence'];
				else if ( isset($activity['activitySummaryWeightedMeanBikeCadence']) && $activity['activitySummaryWeightedMeanBikeCadence'] != "")
					$cadenceValue = $activity['activitySummaryWeightedMeanBikeCadence'];	
						
				$content .=  "{";
				$content .=  "\"date\":\"" . $dateDisplay . "\",";
				$content .=  "\"name\":\"" . $activity['activityName'] . "\",";
				$content .=  "\"type\":\"" . $activity_type_display . "\",";	
				if (get_option('gc_data_source') == 'rss' && get_option('gc_rss_metric') != NULL) {
					list($distance, $units) = explode(" ", $activity['activitySummarySumDistance']);
					if ($units == "Mile") {
						
						$display_distance = miles2km($distance);
					} else {
						$display_distance = $activity['activitySummarySumDistance'];
					}
					$content .=  "\"distance\":\"" . $display_distance . "\",";
				} else { 
					$content .=  "\"distance\":\"" . $activity['activitySummarySumDistance'] . "\",";
				}
				$content .=  "\"time\":\"" . $activity['activitySummarySumDuration'] . "\","; 
				if (get_option('gc_data_source') == 'rss' && get_option('gc_rss_metric') != NULL) {
					list($speed, $units) = explode(" ", $activity['activitySummaryWeightedMeanSpeed']);
					if ($units == "min/Mile") {
						$display_speed = pace2metric($speed);
					} else {
						$display_speed = $activity['activitySummaryWeightedMeanSpeed'];
					}
				 
					$content .=  "\"speed\":\"" . $display_speed . "\","; 
				} else { 
					$content .=  "\"speed\":\"" . $activity['activitySummaryWeightedMeanSpeed'] . "\","; 
				}
				$content .=  "\"heartrate\":\"" . $activity['activitySummaryWeightedMeanHeartRate'] . "\","; 
				$content .=  "\"energy\":\"" . $activity['activitySummarySumEnergy'] . "\","; 
				$content .=  "\"cadence\":\"" . $cadenceValue . "\","; 
				$content .=  "\"url\":\"http://connect.garmin.com/activity/" . $activity['activityId'] . "\""; 
				$content .=  "},";
			}
			$content = rtrim($content, ",");
			$content .= "]";
			
			$content2 = "[{";
			if (isset($duration)) {
				$content2 .= "\"duration\":\"1\",";
			}
			if (isset($displayName)) {
				$content2 .= "\"name\":\"1\",";
			}
			if (isset($speed)) {
				$content2 .= "\"speed\":\"1\",";
			}
			if (isset($distance)) {
				$content2 .= "\"distance\":\"1\",";
			}
			if (isset($hr)) {
				$content2 .= "\"hr\":\"1\",";
			}
			if (isset($energy)) {
				$content2 .= "\"energy\":\"1\",";
			}
			if (isset($cadence)) {
				$content2 .= "\"cadence\":\"1\",";
			}
			if (isset($activity_type)) {
				$content2 .= "\"activity_type\":\"1\",";
			}
			if (isset($event_type)) {
				$content2 .= "\"event_type\":\"1\",";
			}
			if (isset($coordinates)) {
				$content2 .= "\"coordinates\":\"1\",";
			}
			if (isset($map)) {
				$content2 .= "\"map\":\"1\",";
			}
			$content2 = rtrim($content2, ",");
			$content2 .= "}]";
			$content .= "," . $content2;	
			$content .= ",[" . $style . "]";
			$content .= ");";
		} else if ($content == null || $content == "") {
			$content = "displayGCError('Error getting activities', '" . $style . "');";	
		}
		echo $content;

	} else if (type == 'aggregate') {
		
		$numResults = 5;
		$GarminAPI = new GarminAPI();
		$GarminAPI->setUserName($userName);
		$GarminAPI->loadActivities($numResults);
		
		$GarminActivities = $GarminAPI->getUserActivities();
		
	
	}
?>