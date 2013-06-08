<?PHP

	function miles2km ($miles, $precision = 2) {
		return round($miles / 0.62137119,$precision) . " km";
	}
	
	function speed2metric($mph, $precision = 2) {
		return round($mph * 1.609344,$precision) . " km/h";
	}
	
	function pace2metric($mph, $precision = 2) {
		list($minutes, $seconds) = explode(":", $mph);
		$mph_raw = $minutes . "." . round($seconds*100/60);
		$time = round($mph_raw * 0.621371192, $precision);
		list($minutes, $fraction) = explode(".", $time);
		$seconds_raw = round(60 * ("." . $fraction));
		$seconds = str_pad($seconds_raw, 2, "0", STR_PAD_LEFT);
		return $minutes . ":" . $seconds . " min/km";
	}

?>