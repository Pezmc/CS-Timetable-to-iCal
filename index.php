<?php

include_once('vendor/simple_html_dom.php');
include_once('vendor/htmLawed.php');
include_once('lib/Subject.class.php');
include_once('lib/SubjectFactoryFromHTML.class.php');
include_once('lib/Calendar.class.php');
include_once('lib/Event.class.php');
include_once('lib/CalendarEvent.class.php');
include_once('lib/Timetable.class.php');
include_once('lib/CalendarFromTimetableFactory.class.php');

//////////

function getCachedFileOrFalse($cache_file, $time=3600) {
    
  if (file_exists($cache_file) && (filemtime($cache_file) > (time() - $time ))) {
     return file_get_contents($cache_file);
  } else {
     return false;
  }
}

/**
 * Download a url or get it from the cache
 */
function getCachedURL($url, $cache_file=null, $time=3600) {

  // Choose the cache filename and folder
  if(!$cache_file) $cache_file = md5($url).'.txt';
  $cache_file = 'cache/'.$cache_file;

  $data = getCachedFileOrFalse($cache_file, $time);
  
  if(!$data) {
     $data = file_get_contents($url);
     file_put_contents($cache_file, $data, LOCK_EX);
  }
  
  return $data;
}

////////

function getCachedTimetablesList() {
  $cache_file = 'cache/timetables.json';
  
  $data = getCachedFileOrFalse($cache_file);
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
		$subjectList[$lectureHTML[0]] = $lectureHTML[1];
	}
	
	return $subjectList;
}

function getTimetableFromTimetableHTML($timetableHTML) {
	
	// Extract the subject names
	$subjectList = getSubjectsFromTimetableHTML($timetableHTML);
	
	// Parse the timetable HTML
	$timetable = new Timetable();
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
  
function getCachedTimetable($url) {
	
	$cache_file = 'cache/timetable_'.md5($url).'.txt';
	
	// Check in our object cache first
	$timetableText = getCachedFileOrFalse($cache_file);
	if($timetableText && ($timetable = unserialize($timetableText)))
		return $timetable;
	
	// Download and parse a (cache) of the timetable
	$timetablesHTML = getCachedURL($url);
	$html = str_get_html($timetablesHTML);
	
	// Create and cache timetable
	$timetable = getTimetableFromTimetableHTML($html);
	file_put_contents($cache_file, serialize($timetable));
	
	return $timetable;
}

function getTimetableFor($year, $group, $semester) {
	
	$timetables = getCachedTimetablesList();
	
	if(empty($timetables[$year][$group][$semester])) 
		die("There is no known timetable by $year, $group, $semester...");
	
	$url = 'http://studentnet.cs.manchester.ac.uk/ugt/timetable/'.$timetables[$year][$group][$semester];
	$url = htmlspecialchars_decode($url);
	
	// Get a timetable from cache or create from new
	return getCachedTimetable($url);
}

$timetable = getTimetableFor("Year 1", "All First Years", "Sem1");

$calendar = CalendarFromTimetableFactory::build($timetable);
echo $calendar->createVCalendar();

