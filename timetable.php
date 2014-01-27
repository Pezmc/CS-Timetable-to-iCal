<?php

// Needed to parse the dom
include_once('vendor/simple_html_dom.php');

//////////

function getCachedFileOrFalse($cache_file, $time=3600, $forceUpdate=false) {
	
  if($forceUpdate)
     return false;
    
  if (file_exists($cache_file) && (filemtime($cache_file) > (time() - $time ))) {
     return file_get_contents($cache_file);
  } else {
     return false;
  }
}

/**
 * Download a url or get it from the cache
 */
function getCachedURL($url, $cache_file=null, $time=3600, $forceUpdate=false) {

  // Choose the cache filename and folder
  if(!$cache_file) $cache_file = md5($url).'.txt';
  $cache_file = 'cache/'.$cache_file;

  $data = getCachedFileOrFalse($cache_file, $time, $forceUpdate);
  
  if(!$data) {
     $data = file_get_contents($url);
     file_put_contents($cache_file, $data, LOCK_EX);
  }
  
  return $data;
}

////////

function getCachedTimetablesList($forceUpdate=false) {

  $cache_time = 86400 * 7; // cache for 7 days

  $cache_file = 'cache/timetables.json';
  
  $data = getCachedFileOrFalse($cache_file, $cache_time, $forceUpdate);
  if($data) {
    return json_decode($data, TRUE);
  }
  
  $data = getTimetableURLs();
  file_put_contents($cache_file, json_encode($data), LOCK_EX);
  return $data;

}

// --- Parse the timetables list into all the possible timetable combinations ---

function getTimetableURLs() {

  // Download the timetable page
  $timetablesHTML = getCachedURL("http://studentnet.cs.manchester.ac.uk/ugt/timetable/");
  $html = str_get_html($timetablesHTML);
  
  $timetables = array();
  
  // Find all the lists
  foreach($html->find('div[id=content] ul') as $list) {
  
    // Only the ones with titles
    $titles = $list->find('h2');
    if(!empty($titles)) { 
    
      $title = "";
    
      //$timetables[$title] = array();
      
      // For every timetable
      foreach ($list->find('li, h2') as $item) {
      
        if($item->tag == 'h2') {
          $title = $item->plaintext;
          continue;
        }
      
        // For every semmester
        $hrefs = array();
        foreach ($item->find('a') as $a) {    
            $img = $a->find('img');
            
            if(empty($img)) {
              $hrefs[$a->plaintext] = $a->href;
            }
        }
        
        // Get the timetable name
        $name = trim($item->find('text',7)->plaintext,'[] ');
        $timetables[$title][$name] = $hrefs;
      }
    }
  }
  
  return $timetables;
}

function getSubjectsFromTimetableHTML($timetableHTML) {
	
	// Set a list of possible subjects
	$subjectList = array();
	$lecturesHTML = $timetableHTML->find('div.timetablebackground ul li');
	foreach($lecturesHTML as $lectureHTML) {
		$lectureHTML = explode(" ", $lectureHTML->plaintext, 2);
		
		// Special case
		if($lectureHTML[0] == 'COMP-PASS') $lectureHTML[0] = 'PASS';
		elseif($lectureHTML[0] == 'COMP-1st') {
			$lectureHTML[0] = 'COMP1st';
			$lectureHTML[1] = '1st ' . $lectureHTML[1];
		}
		
		$subjectList[$lectureHTML[0]] = $lectureHTML[1];
	}

	return $subjectList;
}

function getTimetableFromTimetableHTML($timetableHTML) {
	
	// Our timetable instance
	$timetable = new Timetable();
	
	// Extract the subject names
	foreach(getSubjectsFromTimetableHTML($timetableHTML) as $id => $name) {
		$timetable->addSubject($id, $name);
	}
	
	// Parse the timetable HTML
	$tableHTML = $timetableHTML->find('div[id=timetabletable] table', 0);
	$rowNumber = 0;
	foreach($tableHTML->find('tr') as $row) {
		$rowNumber++;
			
		// Skip title, filter and day rows
		if($rowNumber <= 3) continue;
			
		// Skip notes and key
		if($rowNumber >= 14) continue;
	
		// Read in each column
		$time = "";
		$columnID = 0;
		foreach($row->find('td') as $column) {
			$columnID++;
	
			if($columnID <= 1) {
				$time = $column->plaintext;
				continue;
			}
	
			foreach($column->find('div') as $lectureHTML) {
				$subject = SubjectFactoryFromHTML::build($lectureHTML);
				if($subject && $subject->isValid()) $timetable->addEvent($columnID-1, $time, $subject);
			}
		}
	}
	
	return $timetable;
}
  
function getCachedTimetable($url, $forceUpdate=false) {

	$timetable_cache_time = 86400 * 3; //cache for 3 days
	$url_cache_time = 86400 * 7; //cache for 7 days
	
	$cache_file = 'cache/timetable_'.md5($url).'.txt';
	
	// Check in our object cache first
	$timetableText = getCachedFileOrFalse($cache_file, $timetable_cache_time, $foceUpdate);
	if($timetableText && ($timetable = unserialize($timetableText)))
		return $timetable;
	
	// Download and parse a (cache) of the timetable
	$timetablesHTML = getCachedURL($url, null, $url_cache_time, $forceUpdate);
	$html = str_get_html($timetablesHTML);
	
	// Create and cache timetable
	$timetable = getTimetableFromTimetableHTML($html);
	file_put_contents($cache_file, serialize($timetable));
	
	return $timetable;
}

function getTimetableFor($year, $group, $semester, $forceUpdate=false) {
	// Possible timetables
	$timetables = getCachedTimetablesList();
	
	// Unable to find the one the user is looking for
	if(empty($timetables[$year][$group][$semester])) 
		throw new Exception("There is no known timetable by $year, $group, $semester...");
	
	$url = 'http://studentnet.cs.manchester.ac.uk/ugt/timetable/'.$timetables[$year][$group][$semester];
	$url = htmlspecialchars_decode($url);
	
	// Get a timetable from cache or create from new
	return getCachedTimetable($url, $forceUpdate);
}

?>
