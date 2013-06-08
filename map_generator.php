<?PHP

//
//	Simple utility function that I use a lot for looking in arrays and returning a
//	default if the key isn't found
//
function gclink_getParam($arr, $key, $def = false)
{
	if(isset($arr[$key]))
		return $arr[$key];

	return $def;
}


function gcmap_outputStaticMap()
{
	//
	//	Get some params off the URL
	//
	$act = gclink_getParam($_REQUEST,"act");

	$iX = gclink_getParam($_REQUEST,"gcl_x",200);
	$iY = gclink_getParam($_REQUEST,"gcl_y",150);
	$szType = gclink_getParam($_REQUEST,"m_type","roadmap");

	//
	//	NB, we're going to try and cache the image so we don't have to 
	//			a) call GC for route information
	//			b) call Google for a static map
	//
	//	every time we need to show this image.
	//
	//	BIG NOTE: NOTHING CLEARS THIS CACHE (YET)!
	//
	$szFile = "gcl_{$act}_{$szType}_{$iX}_{$iY}.png";

	$szFullPath = "gc_maps/".$szFile;

	if(!file_exists($szFullPath))
	{
		if($act)
		{
			$arrPts = gcmap_getActivityPoints($act);

			if($arrPts)
			{
				//
				//	Build the static map request url
				//
				//	NB, This is v1 static map API. It should be updated to V2 format,
				//		which is pretty easy to do
				//

				$szURL = "http://maps.google.com/staticmap?format=png32&maptype={$szType}&size={$iX}x{$iY}";

				if($arrPts !== false)
				{
					$szURL .= "&path=rgb:0x000080,weight:3";
					foreach($arrPts as $arrPt)
					{
						$fLat = round($arrPt["lat"]."",5);
						$fLong = round($arrPt["long"]."",5);
			
						if(($fLat != 0) && ($fLong != 0))
							$szURL .= "|".$fLat.",".$fLong;
					}

					$szURL .= "&markers=";
					$iLast = count($arrPts) - 1;
					$szURL .= $arrPts[$iLast]["lat"].",".$arrPts[$iLast]["long"].",tinyred";
					$szURL .= "|".$arrPts[0]["lat"].",".$arrPts[0]["long"].",tinygreen";
				}

				//
				//	Try to load the image from the URL
				//
				$im = @imagecreatefrompng($szURL);
				if($im !== false)
				{
					//
					//	Save the image to the cache location
					//
					imagepng($im, $szFullPath);
				}
				else
				{
					header("HTTP/1.0 404 Not Found");

					echo "No remote image '$szURL'!";
				}	
			}
			else
			{
				header("HTTP/1.0 404 Not Found");

				echo "No activity points";
			}
		}
		else
		{
			header("HTTP/1.0 404 Not Found");

			echo "No activity ID";
		}	
	}

	//
	//	File in the cache? (either already there or we just put it there)
	//
	if(file_exists($szFullPath))
	{
		//
		//	Yup - then send it out. Job done.
		//
		header("Content-Type: image/png");

		readfile($szFullPath);
	}
	else
	{
		header("HTTP/1.0 404 Not Found");

		echo "No file found";
	}
}

function gcmap_getActivityPoints($iAct)		
{
	$arrPts = false;

	//
	//	Call the GC API to obtain the TCX for the activity (NB, full=true so we get route points)
	//
	$szURL = "http://connect.garmin.com/proxy/activity-service-1.2/tcx/activity/".$iAct."?full=true";
	
	$szFile = file_get_contents($szURL);

	//
	//	Remove namespaces (otherwise we can't parse the XML correctly)
	//
	$iPos = strpos($szFile,"<TrainingCenterDatabase");
	if($iPos !== false)
	{
		$iEndPos = strpos($szFile, ">", $iPos);
		if($iEndPos !== false)
		{
			$szFile = substr_replace($szFile, "<TrainingCenterDatabase>", $iPos, $iEndPos - $iPos + 1);
		}
	}

	$iPos = strpos($szFile,"<Creator");
	if($iPos !== false)
	{
		$iEndPos = strpos($szFile, ">", $iPos);
		if($iEndPos !== false)
		{
			$szFile = substr_replace($szFile, "<Creator>", $iPos, $iEndPos - $iPos + 1);
		}
	}

	$iPos = strpos($szFile,"<Author");
	if($iPos !== false)
	{
		$iEndPos = strpos($szFile, ">", $iPos);
		if($iEndPos !== false)
		{
			$szFile = substr_replace($szFile, "<Author>", $iPos, $iEndPos - $iPos + 1);
		}
	}

	//
	//	NOTE, reliese on simplexml_load_string. Maybe just writing a simpler / dumber XML parser
	//		to get the bits we want will be better for people that don't have the simplexml module
	//
	$xml = @simplexml_load_string($szFile);

	if($xml !== false)
	{
		$arrPts = Array();
		$xmlTrack = $xml->trk;

		//
		//	First get _all_ of the track points
		//
		$arrXmlPts = $xml->xpath("//Position");


		//
		//	Now we try to trim it to a maximum of 80 pts long (definitely including the first
		//	and last points).
		//
		//	This is to try to ensure that the generate GMaps Static Map URL is < 2048 chars.
		//	This probably isn't he best place to do this. This func. should really just return
		//	the points. We can then recut it as necessary to find the best number of pts as
		//	we build the URL.
		//		
		$iMaxPts = 80;
		$iStep = count($arrXmlPts) / ($iMaxPts-1);
		if($iStep < 1)
			$iStep = 1;

		$fPos = 0;
		for($iPt = 1; $iPt < $iMaxPts; $iPt++)
		{
			$iPos = round($fPos);
			
			$arrPts[] = Array("lat"=>$arrXmlPts[$iPos]->LatitudeDegrees,"long"=>$arrXmlPts[$iPos]->LongitudeDegrees);

			$fPos += $iStep;

			if($fPos >= count($arrXmlPts))
				break;
		}

		$arrPts[] = Array("lat"=>$arrXmlPts[count($arrXmlPts) - 1]->LatitudeDegrees,"long"=>$arrXmlPts[count($arrXmlPts) - 1]->LongitudeDegrees);
		
	}

	return $arrPts;
}


gcmap_outputStaticMap();


?>