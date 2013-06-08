<?php
/*
Plugin Name: Garmin Connect
Plugin URI: http://garminconnect.codedrobot.com
Description: Provides a widget for displaying latest activities from Garmin Connect on your site
Author: Coded Robot LLC
Author URI: http://www.codedrobot.com
Version: 1.1.8
*/

require_once('includes/php/garminAPI.php');


class GarminConnect extends WP_Widget {

    var $plugin_folder = '';

	/* 
		First 4 functions required for all WP plugins to operate correctly.  Don't change the names
	*/                  

    function GarminConnect() {     
     	$widget_ops = array('classname' => 'widget_garminconnect', 'description' => 'Display Garmin Connect activities');
        $control_ops = array('width' => 250, 'height' => 100, 'id_base' => 'garminconnect');
  		$this->WP_Widget('garminconnect', 'Garmin Connect', $widget_ops, $control_ops);
        $this->plugin_folder = get_option('siteurl').'/'.PLUGINDIR.'/garmin-wordpress/';
        add_action('admin_menu', array($this,'garminconnect_menu'));
       	add_action('wp_head', array($this, 'garminconnect_admin_head'), 1);

        if ( get_option('gc_user_name') == NULL || get_option('gc_user_name') == "" ) {
     	   add_action('admin_notices', create_function( '', "echo '<div class=\"error\"><p>Please update your <a href=\"".get_bloginfo('wpurl')."/wp-admin/options-general.php?page=garminconnect\">Garmin Connect settings</a>.</p></div>';" ) );
		}
		if ( get_option('gc_data_source') == NULL || get_option('gc_data_source') == "" ) {
			update_option('gc_data_source', 'api');
		}
		
		if ( get_option('gc_cache_length') == NULL || get_option('gc_cache_length') < 900 ) {
			update_option('gc_cache_length', '900');
		}
		
		if ( get_option('date_time_format') == NULL || get_option('date_time_format') == "" ) {
			update_option('date_time_format', 'd-m-Y g:iA');
		}
		
		add_shortcode('gcmap', array($this,'gcmap_func'));
    }
   
	function widget($args, $instance) {
		$before_widget = $args['before_widget'];
		$after_widget = $args['after_widget'];
		$before_title = (empty($args['before_title']) ? "<H2 class='GarminConnectWidgetTitle'>" : $args['before_title']);
		$after_title = (empty($args['after_title']) ? "</H2>" : $args['after_title']);

		$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
		$widgetStyle = $instance['widgetStyle'];
		$numResults = $instance['numResults'];
		
		$displayMapping = array();
		$displayMapping['displayDate'] = strip_tags($instance['displayDate']) == 'short' ? 1 : 0;
		$displayMapping['displayName'] = strip_tags($instance['displayName']) == 'displayName' ? 1 : 0;
		$displayMapping['displayDuration'] = strip_tags($instance['displayDuration']) == 'displayDuration' ? 1 : 0;
		$displayMapping['displayDistance'] = strip_tags($instance['displayDistance']) == 'displayDistance' ? 1 : 0;
		$displayMapping['displaySpeed'] = strip_tags($instance['displaySpeed']) == 'displaySpeed' ? 1 : 0;
		$displayMapping['displayHR'] = strip_tags($instance['displayHR']) == 'displayHR' ? 1 : 0;
		$displayMapping['displayEnergy'] = strip_tags($instance['displayEnergy']) == 'displayEnergy' ? 1 : 0;
		$displayMapping['displayCadence'] = strip_tags($instance['displayCadence']) == 'displayCadence' ? 1 : 0;
		$displayMapping['displayActivityType'] = strip_tags($instance['displayActivityType']) == 'displayActivityType' ? 1 : 0;
		$displayMapping['displayEventType'] = strip_tags($instance['displayEventType']) == 'displayEventType' ? 1 : 0;
		$displayMapping['displayCoordinates'] = strip_tags($instance['displayCoordinates']) == 'displayCoordinates' ? 1 : 0;
		$displayMapping['displayMap'] = strip_tags($instance['displayMap']) == 'displayMap' ? 1 : 0;
		
		echo $before_widget . $before_title . $title. $after_title;
		echo $this->garminconnect_get_widget_content($numResults, $widgetStyle, $displayMapping);	
		echo $after_widget;
	}
 
 	function update($new_instance, $old_instance) {
		 $instance = $old_instance;
		 $instance['title'] = strip_tags($new_instance['title']);
		 $instance['widgetStyle'] = strip_tags($new_instance['widgetStyle']);
		 $instance['numResults'] = strip_tags($new_instance['numResults']);
		 $instance['displayName'] = strip_tags($new_instance['displayName']);
		 $instance['displayDate'] = strip_tags($new_instance['displayDate']);
		 $instance['displayDuration'] = strip_tags($new_instance['displayDuration']);
		 $instance['displayDistance'] = strip_tags($new_instance['displayDistance']);
		 $instance['displaySpeed'] = strip_tags($new_instance['displaySpeed']);
		 $instance['displayHR'] = strip_tags($new_instance['displayHR']);
		 $instance['displayEnergy'] = strip_tags($new_instance['displayEnergy']);
		 $instance['displayCadence'] = strip_tags($new_instance['displayCadence']);
		 $instance['displayActivityType'] = strip_tags($new_instance['displayActivityType']);
		 $instance['displayEventType'] = strip_tags($new_instance['displayEventType']);
		 $instance['displayCoordinates'] = strip_tags($new_instance['displayCoordinates']);
		 $instance['displayMap'] = strip_tags($new_instance['displayMap']);
		 return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'widgetStyle' => '3', 'numResults' => '5', 'displayName' => 'displayName', 'displayDate' => 'long', 'displayDuration' => 'displayDuration', 'displayDistance' => 'displayDistance','displaySpeed' => 'displaySpeed','displayHR' => 'displayHR','displayEnergy' => '','displayCadence' => '','displayActivityType' => '','displayEventType' => '','displayCoordinates' => '','displayMap' => '') );
		$title = strip_tags($instance['title']);
		$widgetStyle = strip_tags($instance['widgetStyle']);
		$numResults = strip_tags($instance['numResults']);
		$activity_name = strip_tags($instance['displayName']);
		$date = strip_tags($instance['displayDate']);
		$duration = strip_tags($instance['displayDuration']);
		$distance = strip_tags($instance['displayDistance']);
		$speed = strip_tags($instance['displaySpeed']);
		$hr = strip_tags($instance['displayHR']);
		$energy = strip_tags($instance['displayEnergy']);
		$cadence = strip_tags($instance['displayCadence']);
		$activity_type = strip_tags($instance['displayActivityType']);
		$event_type = strip_tags($instance['displayEventType']);
		$coordinates = strip_tags($instance['displayCoordinates']);
		$map = strip_tags($instance['displayMap']);
        include("garmin-connect-control-form.php");
	}	

	/* 
		Following functions are to give the Garmin Connect widget our functionality
	*/

	function garminconnect_get_widget_content($numResults, $widgetStyle, $displayMapping) {		
		if ($widgetStyle == 1 || $widgetStyle == 2 ) {
			return $this->garminconnect_widget_jquery_content($numResults, $widgetStyle, $displayMapping);
		} else if ($widgetStyle == 3) {
			return $this->garminconnect_widget_table_content($numResults, $widgetStyle, $displayMapping);
		}
	}
	
	function garminconnect_widget_jquery_content($numResults, $widgetStyle, $displayMapping) {
		$widgetContent = "";
		$widgetContent .= '<div id="GCAccordion' . $widgetStyle . '" class="GCAccordionStyle';
		if ($widgetStyle == 2) {
			$widgetContent .= ' GCAccordionStyle2"';
		}
		$widgetContent .= '"></div>';
		$widgetContent .= '<script src="' . $this->plugin_folder . 'includes/js/gcRecentActivityJQuery.js" type="text/javascript"></script>';
		$widgetContent .= '<script src="' . $this->plugin_folder . 'garminConnectJQuery.php?type=recent&style=' . $widgetStyle . '&numResults=' . $numResults . $this->encodeMapping($displayMapping). '" type="text/javascript"></script>';		
		if ($widgetStyle == 1) {
			$header = "ui-icon-plus";
			$selected = "ui-icon-minus";
		} else if ($widgetStyle == 2) {
			$header = "ui-icon-custom";
			$selected = "ui-icon-custom-expand";
		}
  		$widgetContent .= ' <script type="text/javascript">
  			jQuery(document).ready(function($){
   			 jQuery("#GCAccordion' . $widgetStyle . '").accordion({ active: false, collapsible: true,icons: { "header": "' . $header . '", "headerSelected": "' . $selected . '" } });
 			 });
  			</script>';
  		return $widgetContent;
	}
	
	function encodeMapping($displayMapping) {
		$string = "";
		foreach ($displayMapping as $key => $value) {
			if ($value == 1) {
				$string.= "&" . $key . "=1";
			}
		}
		return $string;
	}
		
	function garminconnect_widget_table_content($numResults, $widgetStyle, $displayMapping) {
		$widgetContent = "";
		if ($widgetStyle == 3) {
			
			
			if ( get_option('gc_user_name') == NULL || get_option('gc_user_name') == "") {
				$widgetContent = "Widget has not been configured";
				return $widgetContent;
			} else if ($type == null || $type == 'recent') {					
				$GarminAPI = new GarminAPI();
				if (get_option('gc_cache_disable') == NULL) {
					$GarminAPI->setEnableCaching(TRUE);
					if (get_option('gc_cache_length') != NULL && get_option('gc_cache_length') > 900) {
						$GarminAPI->setCacheDuration(get_option('gc_cache_length'));
					} else  {
						$GarminAPI->setCacheDuration(900);
					}
				} else {
					$GarminAPI->setEnableCaching(FALSE);
				}
				$GarminAPI->setAPIUser(get_option('gc_user_name'));
				$GarminAPI->setUserName(get_option('gc_user_name'));
				$GarminAPI->setDateTimeFormat(get_option('date_time_format'));
				if (get_option('gc_password') != NULL) {
					$GarminAPI->setAPIPassword(get_option('gc_password'));
				} else {
					$GarminAPI->setRSSSource(TRUE);
				}
				if (get_option('gc_data_source') == 'rss') {
					$GarminAPI->setRSSSource(TRUE);
				}
				if (($response = $GarminAPI->loadActivities($numResults)) !== TRUE) {				
					$widgetContent = "<BR><BR>" . $GarminAPI->decodeErrorMessage($response);
					return $widgetContent;
				}
				$activity_array = $GarminAPI->getUserActivities();
				if (!empty($activity_array) && $response === TRUE) {
					$widgetContent .= "<div id='garminConnectWidget'>";
					for ($i = 0; $i< sizeof($activity_array); $i++) {
				
						$activity = $activity_array[$i];
				
						if (isset($activity['activityType'])) {
							$activity_type = $GarminAPI->getActivityType($activity['activityType'], $activity['activityTypeParent']);		
						} else {
							$activity_type = $activity['activityTypeDisplay'];
						}
						if ($displayMapping['displayDate'] == 1) {
							$dateDisplay = date(get_option("date_format"), strtotime($activity['activitySummaryBeginTimestamp']));
						} else {
							$dateDisplay = date(get_option("date_format") . " " . get_option("time_format"), strtotime($activity['activitySummaryBeginTimestamp']));
						}
						$widgetContent .= "<UL>";
						$setMain = false;
						if ($displayMapping['displayName'] == 1 && isset($activity['activityName']) && $activity['activityName'] != "" && $activity['activityName'] != "Untitled" ) {
							$widgetContent .= "<LI class='main'>";
							$widgetContent .= "<a href='http://connect.garmin.com/activity/" . $activity['activityId'] . "'>" .  $activity['activityName'] . "</a>";
							$widgetContent .= "</LI>";
							$setMain = true;
						}
						$widgetContent .= "<LI";
						if (!$setMain) {
							$widgetContent .= " class='main'>";
						} else {
							$widgetContent .= " class='info'>";
							$widgetContent .= "<IMG src='" . $this->plugin_folder . "includes/images/runner.png'>&nbsp;&nbsp;";

						}
						if ($displayMapping['displayActivityType'] == 1)
							$widgetContent .= $activity_type . " on ";
						
						if ($displayMapping['displayName'] == 0 || !isset($activity['activityName']) || $activity['activityName'] == "" || $activity['activityName'] == "Untitled" ) {
							$widgetContent .= "<a href='http://connect.garmin.com/activity/" . $activity['activityId'] . "'>";
						}
						$widgetContent .= $dateDisplay;
						if ($displayMapping['displayName'] == 0 || !isset($activity['activityName']) || $activity['activityName'] == "" || $activity['activityName'] == "Untitled" ) {

							$widgetContent .="</a>";
						}
						$widgetContent .= "</LI>";
						
						if (($displayMapping['displayDistance'] == 1 && isset($activity['activitySummarySumDistance']) && $activity['activitySummarySumDistance'] != "" ) || ($displayMapping['displayDuration'] == 1 && isset($activity['activitySummarySumDuration']) && $activity['activitySummarySumDuration'] != "") || ($displayMapping['displaySpeed'] == 1 && isset($activity['activitySummaryWeightedMeanSpeed']) && $activity['activitySummaryWeightedMeanSpeed'] != "")) {
							$widgetContent .= "<LI class='info'>";
							$widgetContent .= "<IMG src='" . $this->plugin_folder . "includes/images/stopwatch.png'>&nbsp;";
							if ($displayMapping['displayDistance'] == 1 && isset($activity['activitySummarySumDistance']) && $activity['activitySummarySumDistance'] != "" ) {
								if (get_option('gc_data_source') == 'rss' && get_option('gc_rss_metric') != NULL) {
									list($distance, $units) = explode(" ", $activity['activitySummarySumDistance']);
									if ($units == "Mile") {
										$display_distance = miles2km($distance);
									} else {
										$display_distance = $activity['activitySummarySumDistance'];
									}
									$widgetContent .=  $display_distance;
								} else { 
									$widgetContent .=  $activity['activitySummarySumDistance'];
								}
							}
							if ($displayMapping['displayDuration'] == 1 && isset($activity['activitySummarySumDuration']) && $activity['activitySummarySumDuration'] != "") {
								if ($displayMapping['displayDistance'] == 1 && isset($activity['activitySummarySumDistance']) && $activity['activitySummarySumDistance'] != "") {
									$widgetContent .= " in ";
								}
								$widgetContent .= $activity['activitySummarySumDuration'] ;
							}
							if ($displayMapping['displaySpeed'] == 1 && isset($activity['activitySummaryWeightedMeanSpeed']) && $activity['activitySummaryWeightedMeanSpeed'] != "") {
								if (($displayMapping['displayDistance'] == 1 && isset($activity['activitySummarySumDistance']) && $activity['activitySummarySumDistance'] != "" ) || ($displayMapping['displayDuration'] == 1 && isset($activity['activitySummarySumDuration']) && $activity['activitySummarySumDuration'] != "")) {
									$widgetContent .= " (";
								}
								if (get_option('gc_data_source') == 'rss' && get_option('gc_rss_metric') != NULL) {
									list($speed, $units) = explode(" ", $activity['activitySummaryWeightedMeanSpeed']);
									if ($units == "min/Mile") {
										$display_speed = pace2metric($speed);
									} else {
										$display_speed = $activity['activitySummaryWeightedMeanSpeed'];
									}
									$widgetContent .=  $display_speed;
								} else { 
									$widgetContent .=  $activity['activitySummaryWeightedMeanSpeed'];
								}
								if (($displayMapping['displayDistance'] == 1 && isset($activity['activitySummarySumDistance']) && $activity['activitySummarySumDistance'] != "" ) || ($displayMapping['displayDuration'] == 1 && isset($activity['activitySummarySumDuration']) && $activity['activitySummarySumDuration'] != "")) {
									$widgetContent .= ")";	
								}
							}
							$widgetContent .= "</LI>";
						}
						if (($displayMapping['displayHR'] == 1 && isset($activity['activitySummaryWeightedMeanHeartRate']) && $activity['activitySummaryWeightedMeanHeartRate'] != "") || ($displayMapping['displayEnergy'] == 1 && isset($activity['activitySummarySumEnergy']) && $activity['activitySummarySumEnergy'] != "" )) {
							$widgetContent .= "<LI class='info'>";
							$widgetContent .= "<IMG src='" . $this->plugin_folder . "includes/images/heart.png'>&nbsp;";
							if ($displayMapping['displayHR'] == 1 && isset($activity['activitySummaryWeightedMeanHeartRate']) && $activity['activitySummaryWeightedMeanHeartRate'] != "") {
								$widgetContent .=  "HR " . $activity['activitySummaryWeightedMeanHeartRate'];
							}
							if ($displayMapping['displayEnergy'] == 1 && isset($activity['activitySummarySumEnergy']) && $activity['activitySummarySumEnergy'] != "" ) {
								if ($displayMapping['displayHR'] == 1 && isset($activity['activitySummaryWeightedMeanHeartRate']) && $activity['activitySummaryWeightedMeanHeartRate'] != "") {
									$widgetContent .= " - ";
								}
								$widgetContent .= "Burned " . $activity['activitySummarySumEnergy'] ;
							}
							$widgetContent .= "</LI>";
						}
					
						$cadenceValue = null;
						if ( isset($activity['activitySummaryWeightedMeanRunCadence']) && $activity['activitySummaryWeightedMeanRunCadence'] != "")
							$cadenceValue = $activity['activitySummaryWeightedMeanRunCadence'];
						else if ( isset($activity['activitySummaryWeightedMeanBikeCadence']) && $activity['activitySummaryWeightedMeanBikeCadence'] != "")
							$cadenceValue = $activity['activitySummaryWeightedMeanBikeCadence'];
							
						if ($displayMapping['displayCadence'] == 1 && isset($cadenceValue) ) {
							$widgetContent .= "<LI class='info'>";
							$widgetContent .= "<IMG src='" . $this->plugin_folder . "includes/images/ekg.png'>&nbsp;";

							if ($displayMapping['displayCadence'] == 1 && isset($cadenceValue)) {
								$widgetContent .=  "Cadence " . $cadenceValue;
							}
							$widgetContent .= "</LI>";
						}
						$widgetContent .= "</UL>";
									
					}
					$widgetContent .= "</div>";
				} else if ($widgetContent == null || $widgetContent == "") {
					$widgetContent = "<BR>No activities found";
				}
			}
		}

		return $widgetContent;
	}

	function garminconnect_admin_head() {
     	if (function_exists('wp_enqueue_script')) {			
			$addJQuery = false;
			$addHTML = false;
        	foreach (get_option('widget_garminconnect') as $key => $value) {
				if (is_array($value)) {
					foreach($value as $key1 => $value1) {
						if ($key1 == "widgetStyle" && ($value1 == "1" || $value1 == "2")) {
							$addJQuery = true;
						}
						if ($key1 == "widgetStyle" && ($value1 == "3")) {
							$addHTML = true;
						}

					}
				}
			}
			if ($addJQuery) {
   		   		wp_enqueue_script('jquery');
				echo "<link href='" . $this->plugin_folder . "includes/jquery-ui/1.7.2/css/ui.all.css' rel='stylesheet' type='text/css'/>\n";			
				wp_enqueue_script('garmin-connect7', $this->plugin_folder . 'includes/jquery-ui/1.7.2/js/ui.core.js');
				wp_enqueue_script('garmin-connect8', $this->plugin_folder . 'includes/jquery-ui/1.7.2/js/ui.accordion.js');	
	
   			}	
   			if ($addHTML) {
   			  	echo "<link href='" . $this->plugin_folder . "includes/css/garminconnect.css' rel='stylesheet' type='text/css'/>\n";			
   			}
  		}
    }
 
 
	function garminconnect_menu() {
		  add_options_page('Garmin Connect Options', 'Garmin Connect', 'edit_theme_options', 'garminconnect', array($this,'garminconnect_options'));
	}
	
	function garminconnect_options() {
?>
		<div class="wrap">
		<h2>Garmin Connect</h2>

		<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		<table class="form-table">

		<tr valign="top">
		<th scope="row">Garmin Connect User Name</th>
		<td><input type="text" name="gc_user_name" value="<?php echo get_option('gc_user_name'); ?>" /></td>
		</tr>
 
		<tr valign="top">
		<th scope="row">Garmin Connect Password</th>
		<td><input type="password" name="gc_password" value="<?php echo get_option('gc_password'); ?>" /></td>
		</tr>

		<tr valign="top">
		<th scope="row">Data source for widget</th>
		<td>
		  <input type="radio" name="gc_data_source" value="api" <?php echo get_option('gc_data_source') == 'api'? ' checked' : ''; ?> />API<BR/>
		  <input type="radio" name="gc_data_source" value="rss" <?php echo get_option('gc_data_source') == 'rss'? ' checked' : ''; ?> />RSS
		</td>
		</tr>
		
		<tr valign="top">
		<th scope="row">Use metric for RSS data source?</th>
		<td><input type="checkbox" name="gc_rss_metric" value="gc_rss_metric" <?php echo get_option('gc_rss_metric')  == 'gc_rss_metric' ? ' checked' : ''; ?>/></td>
		<td><font size='1'>Please note: This setting only has an effect if your Data Source (above) is set to RSS.  If your Data source is set to API, the settings within the Garmin Connect website itself will be used.  To change your setting, visit the <a href="http://connect.garmin.com/settings">settings page within Garmin Connect</a> and change 'Measurement Units' to 'Metric' on the 'Display Preferences' tab.</font></td>
		</tr>
		
		<tr valign="top">
		<th scope="row">Use metric for Map shortcode?</th>
		<td><input type="checkbox" name="gc_shortcode_metric" value="gc_shortcode_metric" <?php echo get_option('gc_shortcode_metric')  == 'gc_shortcode_metric' ? ' checked' : ''; ?>/></td>
		</tr>
		
		<tr valign="top">
		<th scope="row">Disable caching of data?</th>
		<td><input type="checkbox" name="gc_cache_disable" value="gc_cache_disable" <?php echo get_option('gc_cache_disable')  == 'gc_cache_disable' ? ' checked' : ''; ?>/></td>
		</tr>
		
		<?PHP if (get_option('gc_cache_disable') == null) { ?>
		<tr valign="top">
		<th scope="row">Seconds to Cache (min 900)</th>
		<td><input type="text" name="gc_cache_length" value="<?php echo get_option('gc_cache_length'); ?>" /></td>
		</tr>
		<?PHP } ?>
		
		<tr valign="top">
		<th scope="row">Format for date/time (click <a href="http://www.php.net/manual/en/function.date.php">here</a> for format syntax)</th>
		<td><input type="text" name="date_time_format" value="<?php echo get_option('date_time_format'); ?>" /></td>
		</tr>
	
		</table>
		
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="gc_user_name,gc_password, gc_data_source, gc_rss_metric, gc_shortcode_metric, gc_cache_disable, gc_cache_length, date_time_format" />

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>

		</form>
		</div>
<?PHP
	}	
	
	
	
	//[gcmap act="activity id" class="css class" m_type="roadmap"]
	function gcmap_func($atts) {
		extract(shortcode_atts(array(
			'act' => false,		// User specifies the activity ID they want to show with this
			'class' => false,	// Any additional class for the activity output DIV
			'm_type' => 'roadmap',	//	Can be, roadmap, satellite, terrain or hybrid
			'sizex' => 200,
			'sizey' => 150,
			'target' => false
		), $atts));

		$iX = $sizex;
		$iY = $sizey;

		$szMapType = $m_type;
		
		
		//
		//	Request some activity detail from Garmin Connect's JSON API
		//
		$szURL = "http://connect.garmin.com/proxy/activity-service-1.2/json/activity/".$act;
		$szFile = file_get_contents($szURL);
		
		if($szFile)
		{
			$activity = json_decode($szFile);
	
			$szTitle = htmlspecialchars($activity->activity->activityName->value,ENT_QUOTES);

			//
			//	Build the HTML we're going to replace our [gclink] short code with
			//		NB, ideally this content would be configureable
			//		NB, ideally so would the units etc.
			//
			$szHTML = "<div class='gclink_act {$class}'>";
			
			if($target === false)
				$szHTML .= "<a href='http://connect.garmin.com/activity/{$act}'>";
			else
				$szHTML .= "<a target=$target href='http://connect.garmin.com/activity/{$act}'>";
		
			$szHTML .= "<img src='" . $this->plugin_folder . "map_generator.php?gcl_o=map&act={$act}&gcl_x={$iX}&gcl_y={$iY}&m_type={$szMapType}' alt='{$szTitle}' title='{$szTitle}'>";
			$szHTML .= "</a><br>";
			$szHTML .= "<a href='http://connect.garmin.com/activity/{$act}'>";
			$szHTML .= $szTitle;
			$szHTML .= "</a><br>";
			$szHTML .= "<span>".$activity->activity->activityType->display." for ".$activity->activity->activitySummarySumDuration->display."</span><br>";
			if (get_option('gc_shortcode_metric') == null)  {
				$szHTML .= "<span>".$activity->activity->activitySummarySumDistance->withUnitAbbr." @ ".$activity->activity->activitySummaryWeightedMeanSpeed->withUnitAbbr."</span><br>";
			} else {
				$szHTML .= "<span>";
				list($distance, $units) = explode(" ", $activity->activity->activitySummarySumDistance->withUnitAbbr);
				if ($units == "mi") {
					$display_distance = miles2km($distance);
				} else {
					$display_distance = $activity->activity->activitySummarySumDistance->withUnitAbbr;
				}
				$szHTML .= $display_distance;
				
				$szHTML .= " @ ";
				list($speed, $units) = explode(" ", $activity->activity->activitySummaryWeightedMeanSpeed->withUnitAbbr);
				if ($units == "min/mi") {
					$display_speed = pace2metric($speed);
				} else if ($units == "mph") {
					$display_speed = speed2metric($speed);
				} else {
					$display_speed = $activity->activity->activitySummaryWeightedMeanSpeed->withUnitAbbr;
				}
				$szHTML .= $display_speed;
				$szHTML .= "</span><br>";
			}
			$szHTML .= "</div>";

		} else {
			//
			//	No GC info available? Might be temporary
			//
			$szHTML = "<div class='gclink_act {$class}'>";
			$szHTML .= "<a href='http://connect.garmin.com/activity/{$act}'>";
			$szHTML .= "<img src='{$szImageURL}'>";
			$szHTML .= "</a><br>";
			$szHTML .= "GarminConnect...<br>information not currently available...";
			$szHTML .= "</div>";
		}



		return $szHTML;
	}

}

add_action('widgets_init', 'garminconnect_widget_gc_init');

function garminconnect_widget_gc_init() {
	register_widget('GarminConnect');
}

?>
