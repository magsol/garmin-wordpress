<?php

require_once('garmin_helper.php');

if (!class_exists("SimplePie")) 
	require_once('simplepie.inc');
if (!class_exists("Cache_Lite"))
	require_once('CacheLite.php');
 
class GarminAPI
{

	public $DEBUG = FALSE;
	private $userName;
	private $dateTimeFormat = "d-m-Y g:iA";
	private $autoLogin = "";
	private $jSessionID = "";
	private $pool = "";
	private $userActivities;
	private $response_meta_info;
	private $api_user = "";
	private $api_password = "";
	private $enable_caching = TRUE;
	private $cache_duration = 900;
	private $cache_location = "";
	private $rss_source = FALSE;	

	/*******************************************************************************************
			Public functions one should call to make use of this API
			
				GarminAPI()
				setUserName($user)
				getUserName()
				setAPIUser($user)
				setAPIPassword($password)
				getUserActivities()
				loadActivities()
				getActivityType()	
				decodeErrorMessage($code)
			
			An example usage of the API would go as follows:
			
				$GarminAPI = new GarminAPI();
				$GarminAPI->setAPIUser(get_option('gc_user_name'));
				$GarminAPI->setAPIPassword(get_option('gc_password'));
				$GarminAPI->setUserName(get_option('gc_user_name'));
				if (!$GarminAPI->loadActivities($numResults)) {
					echo "<B>There was an error loading the activities</B>";
				}
				$activity_array = $GarminAPI->getUserActivities();
				if (!empty($activity_array)) {
					//Parse and do something with array here
				}
				
						
	*******************************************************************************************/

	/*
		Default constructor
	*/
	public function GarminAPI() {
		$this->setCacheLocation(dirname(__FILE__) . "/cache");
	}
	
	/*
		Sets the user name of the user to get data for
	*/
	public function setUserName($user) {
		$this->userName = $user;
	}
	
	/*
		Gets the user name of the user to get data for	
	*/
	public function getUserName() {
		return $this->userName;
	}
	
	/*
		Sets the user that is used to authenticate to the API
	*/
	public function setAPIUser($user) {
		$this->api_user = $user;
	}
	
	/*
		Sets the password that is used to authenticate to the API
	*/
	public function setAPIPassword($password) {
		$this->api_password = $password;
	}
	
	public function setEnableCaching($bool) {
		$this->enable_caching = $bool;
	}
	
	public function getEnableCaching() {
		return $this->enable_caching;
	}
	
	public function setDateTimeFormat($dt) {
		$this->dateTimeFormat = $dt;
	}
	
	public function getDateTimeFormat() {
		return $this->dateTimeFormat;
	}

	public function setCacheDuration($time) {
		$this->cache_duration = $time;
	}
	
	public function getCacheDuration() {
		return $this->cache_duration;
	}
	
	private function setCacheLocation($loc) {
		$this->cache_location = $loc;
	}
	
	private function getCacheLocation() {
		return $this->cache_location;
	}
	
	public function setRSSSource($bool) {
		$this->rss_source = $bool;
	}
	
	public function getRSSSource() {
		return $this->rss_source;
	}
	

	
	/*
		Add code to Check for valid login and remove the login code at the top
	*/
	public function loadActivities($numResults) {
		if (!$this->getRSSSource()) {
			return $this->getUsersActivityAPI("beginTimestamp", "desc", $numResults);			
		} else {
			$feedURL = $this->getUsersActivityRSSURL();
			$feed = new SimplePie();
			$feed->enable_cache($this->getEnableCaching());
			$feed->set_cache_duration($this->getCacheDuration());
			$feed->set_cache_location(dirname(__FILE__) . "/cache");
			$feed->set_feed_url($feedURL);
			$feed->init();
 			$feed->handle_content_type();
 			if ($feed->error()) {
 				echo $feed->error();
 			} else { 
				$this->setUserActivities($this->parseActivityRSSData($feed, $numResults));
			}			
		}
		return true;	
	}
	
	/*
		Returns the array that was created when calling loadActivities
	*/
	public function getUserActivities() {
		return $this->userActivities;
	}


	/*******************************************************************************************
			Public helper functions for others using this API
	*******************************************************************************************/
	
	/*
		Helper function to take the activityType and translate it and generalize it into a pretty name
	*/
	public function getActivityType($activity_type, $activity_parent) {
		if (strpos($activity_type,"running") !== FALSE || strpos($activity_parent,"running") !== FALSE ) {
			$activity_type="Run";
		} elseif (strpos($activity_type,"biking") !== FALSE || strpos($activity_parent,"biking") !== FALSE || strpos($activity_type,"cycling") !== FALSE || strpos($activity_parent,"cycling") !== FALSE ) {
			$activity_type="Bike";
		} elseif (strpos($activity_type,"swimming") !== FALSE || strpos($activity_parent,"swimming") !== FALSE ) {
			$activity_type="Swim";
		}
		return $activity_type;
	}
		
	/*
		Decodes any error messages that we set based on responses from API
	*/
	public function decodeErrorMessage($code) {
		switch($code) {
			case "AUTHENTICATION_CONNECT_ERROR":
				return "Error connecting with Garmin Connect.<BR/>Unable to login";
				break;
			case "API_CREDENTIAL_ERROR":
				return "Invalid username and password provided.<BR/>Unable to login";
				break;
			case "GC_ACTIVITY_UNKNOWN_ERROR":
				return "Unable to retrieve activites from Garmin Connect currently";
				break;
			case "GC_SERVICE_DOWN":
				return "Unable to connect to the Garmin Connect API at this time";
				break;
				
		}
		return "Unknown error code";
	}
	

	public function convertTime($timestamp) {
		
						$szTZ = "0000";
						if((strlen($timestamp) > 5) && (substr($timestamp,-5,1) == "+") || (substr($timestamp,-5,1) == "-"))
						{
							$szTZ = substr($timestamp,-4);
							$timestamp = substr($timestamp,0,-5);
						}

						$tPubDate = strtotime($timestamp);

				/*		// This is a workaround for the MB bug that gets pubdates for PM timestamps wrong by 12 hours
						if(strstr($xmlItem->description," PM near ") !== false)
						{
							// If the description contains PM near then this should be a PM timestamp!
							$arrDate = getdate($tPubDate);
							if($arrDate["hours"] < 12)
								$tPubDate += 12 * 3600;
						}*/
						return $tPubDate;
	}
	/*******************************************************************************************
			Private helper functions utilized within this API
	*******************************************************************************************/

	/*
		Creates an authenticated session with Garmin Connect
			- Must call setAPIUser & setAPIPassword prior to making this call
			
		This function will be eventually updated to use Garmin API once that API is added
		
		Also add some form of error checking to the 2nd call
	*/	
	private function createSession() 
	{
		// First page load is to get our Jsession and Pool cookies set 
		$this->setAutoLogin("1067131:de4f242d-cf1f-4ff9-9216-59c2b2034f7d");
		$url = 'https://connect.garmin.com/signin';
		$signin_page = $this->loadGarminuRL($url, $url, "PrimaryGarminUserLocalePref=en", '');

		// We also need to pull the viewStateID variable out out 
		$viewState = $this->extractViewState($signin_page);
		if (!$viewState) {
			return "AUTHENTICATION_CONNECT_ERROR";
		}
		if (!$this->checkIfAlreadyLoggedIn($signing_page)) {
			$cookies = $this->getResponseCookies();
			$this->setJSessionID($cookies['jsession']);
			$this->setPool($cookies['pool']);
			$ref = $url;
			$url = $url . ";jsessionid=" . $this->getJSessionID();	
			// Second page load is to actually do the login 
			$cookie = "PrimaryGarminUserLocalePref=en; JSESSIONID=" . $this->getJSessionID() . "; BIGipServerconnect.garmin.com.80.pool=" . $this->getPool();
			$post  = 'login=login&login%3AloginUsernameField=' . $this->getAPIUser() . '&login%3Apassword=' . $this->getAPIPassword() . '&login%3Aj_id73=Sign+In&javax.faces.ViewState=' . $viewState;
			$page = $this->loadGarminURL($url, $ref, $cookie, $post);
			// The auto-Login cookie is set on this page load, so we must save it before continuing and returning the page 
			$cookies = $this->getResponseCookies();
			$this->setAutoLogin($cookies['autologin']);

			/*
				Add code to check for the response to see if we got logged in
			*/
			if (!$this->verifyLoginSuccess($page)) {
				return "API_CREDENTIAL_ERROR";
			}	
		}
		
		return true;
	}
	
	
	/*
		Ends the session to the Garmin API
	*/
	private function destroySession() {
		return true;
	}

	private function checkIfAlreadyLoggedIn($page) {
		if (stripos($page, 'You are signed in as ' . $this->getAPIUser() . ' |') !== FALSE) {
			return true;
		} 
		return false;
	}
	
	private function verifyLoginSuccess($page) {
		if (stripos($page, '<ul class="messages"><li class="errorMessage">Invalid username/password combination.</li></ul>') !== FALSE) {
			return false;
		} 
		return true;
	}
				
					
					
	/*
		Helper function for loadActivities() - actually makes the call to the web service
	*/
	private function getUsersActivityAPI($sort_by, $order, $num_results) {	
		if ($this->getUserName() != $this->getAPIUser()) {
			$explore = "true";
		} else {
			$explore = "false";
		}
		$explore = "true";
		if($this->getEnableCaching()) {
			$id = md5($this->getUserName()) . "." . $num_results;
			//echo "id is " . $id;
			$options = array(
			    'cacheDir' => $this->getCacheLocation() . "/",
			    'lifeTime' => $this->getCacheDuration()
			);
			$Cache_Lite = new Cache_Lite($options);
			if ($data = $Cache_Lite->get($id)) {
    			$this->setUserActivities($this->parseActivityAPIData($data));
			} else {
   			 	if (($return = $this->createSession()) !== TRUE) {
					return $return;
				}		
				$apiData = $this->makeActivityAPICall($num_results, $explore);			
				if (($return = $this->validActivityData($apiData)) !== TRUE) {
					return $return;
				} 
				$this->setUserActivities($this->parseActivityAPIData($apiData));		
				if (($return = $this->destroySession()) !== TRUE) {
					return $return;
				}
   				$Cache_Lite->save($apiData);
   				return true;
			}
		} else {
			if (($return = $this->createSession()) !== TRUE) {
				return $return;
			}		
			$apiData = $this->makeActivityAPICall($num_results, $explore);			
			if (($return = $this->validActivityData($apiData)) !== TRUE) {
				return $return;
			} 
			$this->setUserActivities($this->parseActivityAPIData($apiData));		
			if (($return = $this->destroySession()) !== TRUE) {
				return $return;
			}
   			return true;
		}
		return true;
	}
	
	private function makeActivityAPICall($num_results, $explore = 'false') {
		$cookie = "JSESSIONID=" . $this->getJSessionID() . ";BIGipServerconnect.garmin.com.80.pool=" . $this->getPool() . "; org.jboss.seam.security.username=" . $this->getUserName();
		$url = "http://connect.garmin.com/proxy/activity-search-service-1.1/json/activities?owner=" . $this->getUserName() . "&ignoreNonGps=false&explore=" . $explore . "&start=0&limit=$num_results";
  		$referer = 'http://connect.garmin.com/activities';
  		$cookies = $this->getResponseCookies();
  		$post = '';
		return $this->loadGarminURL($url, $referer, $cookie, $post);
	}
	
	private function reverseGeocode($lat, $long) {	
		$api_key = "ABQIAAAAnfs7bKE82qgb3Zc2YyS-oBT2yXp_ZAY8_ufC3CFXhHIE1NvwkxSySz_REpPq-4WZA27OwgbtyR3VcA";
		$url = "http://maps.google.com/maps/geo?q=" . $lat . "," . $long . "&output=json&oe=utf8&sensor=true&key=" . $api_key;
		$cookie='';
  		$post = '';
		return $this->loadGarminURL($url, $referer, $cookie, $post);
	}
	
	/*
		Helper function for loadActivities() - actually makes the call to load the RSS page
	*/
	private function getUsersActivityRSSURL() {		
  		$url = "http://connect.garmin.com/feed/rss/activities?feedname=GarminConnect&owner=" . $this->getUserName();
  		return $url;
  	}
	
	/*
		Helper function for loadActivities() - makes sure the response we get from API is list of activities
	*/
	private function validActivityData($apiData) {
		if (stripos($apiData, "Error report</title>") !== FALSE) {
			return "GC_ACTIVITY_UNKNOWN_ERROR";
		} else if (stripos($apiData, "<title>Service Unavailable</title>") !== FALSE) {
			return "GC_SERVICE_DOWN";
		}
		return true;
	}


	private function parseActivityRSSData($feed, $numResults) {
		$garmin_data = array();
		$j=0;
		foreach ($feed->get_items() as $item):
			if ($j >= $numResults)
				break;
			$garmin_data[]=array();
			$dom = new domDocument;
   			$dom->loadHTML($item->get_description());
    		$dom->preserveWhiteSpace = false;
    		$tables = $dom->getElementsByTagName('table');
			$rows = $tables->item(0)->getElementsByTagName('tr');
			
			$title =  $item->get_title();
			$date =  $item->get_date();
			$url = $item->get_link();		
			$location_lat = $item->get_latitude();
			$location_long = $item->get_longitude();
    		$i = 0;
    		
    		foreach ($rows as $row)
    		{
    			switch($i) {
    				case 3:
    					$activity_type = $row->getElementsByTagName('td')->item(1)->nodeValue;
    					break;
    				case 4:
    					$event_type = $row->getElementsByTagName('td')->item(1)->nodeValue;
    					break;  				
    				case 5:
    					$distance = $row->getElementsByTagName('td')->item(1)->nodeValue;
    					bzeak;
    				case 6:
    					$time = $row->getElementsByTagName('td')->item(1)->nodeValue;
    					break;
    				case 7:
    					$elevation_gain = $row->getElementsByTagName('td')->item(1)->nodeValue;
    					break;   			
    			}
    			$i++;

    		}  
    		
    		$seconds = $this->translateHHMMSStoSeconds($time);
    		$pos = strpos($distance, " ");
    		$units = substr($distance, 0, $pos);
 
    		$mph = ($units ) / ($seconds / 3600); 
    		$speed = (1*(60/1))/$mph;
    		list($s_minutes, $s_fraction) = explode(".", $speed);
			$s_seconds_raw = round(60 * ("." . $s_fraction));
			$s_seconds = str_pad($s_seconds_raw, 2, "0", STR_PAD_LEFT);
    		$speed_w_units = $s_minutes . ":" . $s_seconds . " min/" . substr($distance, $pos+1);
    		
     		$garmin_data[$j]['activityId'] = substr($url, strrpos($url, "/")+1);
    		$garmin_data[$j]['activityName'] = $title;
    		$garmin_data[$j]['activitySummaryBeginTimestamp'] = $date;
    		$garmin_data[$j]['activitySummarySumDistance'] = $distance;
    		$garmin_data[$j]['activitySummarySumDuration'] = $time;
    		$garmin_data[$j]['activitySummaryWeightedMeanSpeed'] = $speed_w_units;
    		$garmin_data[$j]['activitySummaryBeginLongitude'] = $location_long;
    		$garmin_data[$j]['activitySummaryBeginLatitude'] = $location_lat;
    		$garmin_data[$j]['activitySummaryGainElevation'] = $elevation_gain;
    		$garmin_data[$j]['activityTypeDisplay'] = $activity_type;
    		$garmin_data[$j]['eventTypeDisplay'] = $event_type;
      		//var_dump($this->reverseGeocode($location_lat, $location_long));
     		 $j++;
   		endforeach;
   		return $garmin_data;
	}
	
	function translateHHMMSStoSeconds($time) {
		if (substr_count($time, ":") == 1) {
    		$pos = strpos($time, ":");
    		$seconds = (substr($time, 0, $pos) * 60) + substr($time, $pos+1);
    	} else if (substr_count($time, ":") == 2) {
    		$pos1 = strpos($time, ":");
    		$pos2 = strpos($time, ":", $pos1+1);
    		$seconds = (substr($time, 0, $pos1) * 3600) + (substr($time, $pos1+1, $pos2-$pos1-1)*60) + substr($time, $pos2 + 1);    			
    	} else {
    		$seconds = $time;
    	}
    	return $seconds;
	}
	/*
		Helper function for loadActivities() - transforms JSON of activities into usable array
	*/
	private function parseActivityAPIData($apiData) {
		$garmin_data = array();
		$matches = array();
		$json = json_decode($apiData, true);
		if ($json && $json['results']['activities']) {
			$activities = $json['results']['activities'];
			$i = 0;
			foreach($activities as $key => $value) {
				$activityID = $value['activity']['activityId'];
				$garmin_data[]=array();
				$garmin_data[$i]['activityId']=$value['activity']['activityId'];
				$garmin_data[$i]['activityName']=$value['activity']['activityName']['value'];
				$garmin_data[$i]['activityDescription']=$value['activity']['activityDescription']['value'];
				$garmin_data[$i]['locationName']=$value['activity']['locationName']['value'];
				$garmin_data[$i]['activityType']=$value['activity']['activityType']['key'];
				$garmin_data[$i]['activityTypeDisplay']=$value['activity']['activityType']['display'];
				$garmin_data[$i]['activityTypeParent']=$value['activity']['activityType']['parent']['key'];
				$garmin_data[$i]['eventType']=$value['activity']['eventType']['key'];
				$garmin_data[$i]['eventTypeDisplay']=$value['activity']['eventType']['display'];
				$garmin_data[$i]['activityTimeZone']=$value['activity']['activityTimeZone']['offset'];
				$garmin_data[$i]['activitySummaryMinElevation']=$value['activity']['activitySummaryMinElevation']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryBeginLatitude']=$value['activity']['activitySummaryBeginLatitude']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryMinSpeed']=$value['activity']['activitySummaryMinSpeed']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryMinHeartRate']=$value['activity']['activitySummaryMinHeartRate']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryMaxSpeed']=$value['activity']['activitySummaryMaxSpeed']['withUnitAbbr'];
				$garmin_data[$i]['activitySummarySumEnergy']=$value['activity']['activitySummarySumEnergy']['withUnitAbbr'];
				$t_time = $value['activity']['activitySummaryBeginTimestamp']['display'];
				$values = explode(" ", $t_time);
				if (is_numeric($values[1]) && (strlen($values[1]) == 4)) {
					$t_time = $values[0] . " " . $values[2] . " " . $values[3] . " " . $values[1] . " " . $values[4];
				}
				$c_time = strtotime($t_time);
				if ($c_time == 0) {
					date_default_timezone_set(get_option('timezone_string'));
					$c_time = $value['activity']['activitySummaryBeginTimestamp']['millis']/1000;
				}
				$garmin_data[$i]['activitySummaryBeginTimestamp'] = date($this->getDateTimeFormat(), $c_time);
				
				$speed = $value['activity']['activitySummaryWeightedMeanSpeed']['withUnitAbbr'];
				if ($value['activity']['activityType']['type']['key'] == 'cycling') {
					$speed = $value['activity']['activitySummaryWeightedMeanSpeed']['display'] . " " .  $value['activity']['activitySummaryWeightedMeanSpeed']['uom'];
				}
				$garmin_data[$i]['activitySummaryWeightedMeanSpeed']=$speed;
				
				$garmin_data[$i]['activitySummaryLossElevation']=$value['activity']['activitySummaryLossElevation']['withUnitAbbr'];
				$garmin_data[$i]['activitySummarySumDuration']=$value['activity']['activitySummarySumDuration']['display'];
				$garmin_data[$i]['activitySummaryWeightedMeanBikeCadence']=$value['activity']['activitySummaryWeightedMeanBikeCadence']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryWeightedMeanRunCadence']=$value['activity']['activitySummaryWeightedMeanRunCadence']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryWeightedMeanHeartRate']=$value['activity']['activitySummaryWeightedMeanHeartRate']['withUnitAbbr'];
				$garmin_data[$i]['activitySummarySumDistance']=$value['activity']['activitySummarySumDistance']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryBeginLongitude']=$value['activity']['activitySummaryBeginLongitude']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryGainElevation']=$value['activity']['activitySummaryGainElevation']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryMaxElevation']=$value['activity']['activitySummaryMaxElevation']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryMaxRunCadence']=$value['activity']['activitySummaryMaxRunCadence']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryMinRunCadence']=$value['activity']['activitySummaryMinRunCadence']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryMaxBikeCadence']=$value['activity']['activitySummaryMaxBikeCadence']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryMinBikeCadence']=$value['activity']['activitySummaryMinBikeCadence']['withUnitAbbr'];
				$garmin_data[$i]['activitySummaryMaxHeartRate']=$value['activity']['activitySummaryMaxHeartRate']['withUnitAbbr'];
				$i++;
			}
		}
		if ($this->DEBUG) {
			print_r($garmin_data);
		}
		return $garmin_data;
	}
	
	/*
		Helper function for loadActivities() - stores the array that we create from the API call
	*/
	protected function setUserActivities($data) {
		$this->userActivities=$data;
	}
	
	/*
		Helper function for createSession() - loads the user for use in the API authentication
	*/
	private function getAPIUser() {
		return $this->api_user;
	}
	
	/*
		Helper function for createSession() - loads the password for use in the API authentication
	*/
	private function getAPIPassword() {
		return $this->api_password;
	}
	
	
	/*
		Method to load any URL or API call to Garmin Connect
	*/
	public function loadGarminURL($url, $referer, $cookie, $post ) {
		//echo "Loading the URL <B>$url</B><BR><BR>";
  		$header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
  		$header[] = "Cache-Control: no-cache";
  		$header[] = "Connection: keep-alive";
  		$header[] = "Keep-Alive: 300";
  		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
  		$header[] = "Accept-Language: en-us,en;q=0.5";
  		if (isset($cookie)) {
  			$header[] = "Cookie: $cookie";
  		}
  		$header[] = "Pragma: no-cache"; 
	
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL,$url);
		curl_setopt($curl_handle, CURLOPT_HEADER, 0);
		curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($curl_handle, CURLOPT_REFERER, $referer);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,1);
		if ($post != '') {
   	 		curl_setopt($curl_handle, CURLOPT_POST, 1);
			curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post);
		}
		curl_setopt($curl_handle, CURLOPT_HEADERFUNCTION, array(&$this,'readHeader'));
		$agent     = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.4; en-US; rv:1.9) Gecko/2008061004 Firefox/3.0";
		curl_setopt($curl_handle, CURLOPT_USERAGENT, $agent);
		$result = curl_exec ($curl_handle);

		if (curl_error($curl_handle))
    	{
       	 printf("Error %s: %s", curl_errno($curl_handle), curl_error($curl_handle));
       	 echo (" ->> There has been an error");
       	 return 'ERROR';
    	}
    	curl_close ($curl_handle);
    	if ($this->DEBUG) {
    		echo "<HR><B>Packet Response</B><BR/>" . $result . "<BR/><HR><BR>";
    	}
    	return $result;
	}

	/*
		Helper function for loadGarminURL() 
	*/		
	private function readHeader($curl_handle, $header) {		
		//Change ';' to '\n' to get the path info as well
		$jsession = $this->extractCustomHeader('Set-Cookie: JSESSIONID=', ';', $header);
		if ($jsession) {
			$this->response_meta_info['jsession'] = trim($jsession);
		}
		$pool = $this->extractCustomHeader('Set-Cookie: BIGipServerconnect.garmin.com.80.pool=', ';', $header);
		if ($pool) {
			$this->response_meta_info['pool'] = trim($pool);
		}		
		$autoLogin = $this->extractCustomHeader('Set-Cookie: com.garmin.user.autologin.AutoLogin=', ';', $header);
		if ($autoLogin) {
			$this->response_meta_info['autologin'] = trim($autoLogin);
		}
		return strlen($header);
	}

	/*
		Helper function for loadGarminURL()
	*/
	private function extractCustomHeader($start,$end,$header) {
		$pattern = '/'. $start .'(.*?)'. $end .'/';
		if (preg_match($pattern, $header, $result)) {
			return $result[1];
		} else {
			return false;
		}
	}
	
	/*
		Method/Data used for createSession()
	*/
	protected function setAutoLogin($id) {
		$this->autoLogin = $id;
	}
	
	/*
		Method/Data used for createSession()
	*/
	protected function getAutoLogin() {
		return $this->autoLogin;
	}
	
	/*
		Method/Data used for createSession()
	*/
	protected function setJSessionID($id) {
		$this->jSessionID = $id;
	}
	
	/*
		Method/Data used for createSession()
	*/
	protected function getJSessionID() {
		return $this->jSessionID;
	}
	
	/*
		Method/Data used for createSession()
	*/
	protected function setPool($id) {
		$this->pool = $id;
	}
	
	/*
		Method/Data used for createSession()
	*/
	protected function getPool() {
		return $this->pool;
	}
	
	/*
		Helper function for createSession() 
	*/
	private function extractViewState($page) {
		$pattern = '/id=\"javax.faces.ViewState\" value=\"(.*?)" \/\>/';
		if (preg_match($pattern, $page, $result)) {
			return str_replace(":", "%3A", $result[1]);
		} else {
			return false;
		}
	}
	
	/*
		Helper function for createSession()
	*/
	private function getResponseCookies() {
		return $this->response_meta_info;
	}
	
	/*
		Helper function that is useful for debugging responses from Garmin Connect API
	*/
	private function decode_xml($xml) {
        return nl2br(str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", str_replace(" ", "&nbsp;", htmlentities($xml))));
	}

	
}

?>