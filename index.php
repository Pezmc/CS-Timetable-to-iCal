<?php

include_once('simple_html_dom.php');
include_once('Subject.class.php');
include_once('SubjectFactoryFromHTML.class.php');
include_once('htmLawed.php');

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
  
  $data = getTimetables();
  file_put_contents($cache_file, json_encode($data), LOCK_EX);
  return $data;

}

// --- Parse the timetables list into all the possible timetable combinations ---

function getTimetables() {

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

$timetables = getCachedTimetablesList();

if(empty($timetables["Year 1"]["All First Years"]["Sem1"]))
  die("Empty...");

$url = 'http://studentnet.cs.manchester.ac.uk/ugt/timetable/'.$timetables["Year 1"]["All First Years"]["Sem1"];
$url = htmlspecialchars_decode($url);

$timetablesHTML = getCachedURL($url);

$html = str_get_html($timetablesHTML);

// Set a list of possible subjects
$subjectList = array();
$lecturesHTML = $html->find('div.timetablebackground ul li');
foreach($lecturesHTML as $lectureHTML) {
  $lectureHTML = explode(" ", $lectureHTML->plaintext, 2);
  $subjectList[$lectureHTML[0]] = $lectureHTML[1];
}

// Conver the timetable to an array
$timetable = new Timetable();
$tableHTML = $html->find('div[id=timetabletable] table', 0);
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

$timetable->printTimetableAsHTML();

//var_dump($timetable);

class Timetable {

  private $timetableArray;
  
  public function __construct() {
    
  }
  
  /**
   * Add an event to this timetable
   */
  public function addEvent($dayOfWeek, $time, $subject) {  
    if(!isset($this->timetableArray[$dayOfWeek])) $this->timetableArray[$dayOfWeek] = array();
    if(!isset($this->timetableArray[$dayOfWeek][$time])) $this->timetableArray[$dayOfWeek][$time] = array();
  
    $this->timetableArray[$dayOfWeek][$time][] = $subject;
  }

  /**
   * Echo out this table as an html table
   */
  public function printTimetableAsHTML() {  
    $html =  "<table>";
    $html .=  "<thead><tr><td>Time</td><td>Monday</td><td>Tuesday</td><td>Wednesday</td><td>Thursday</td><td>Friday</td></tr></thead><tbody>";
    foreach($this->getTimetableTableArray() as $time => $row) {
      $html .= "<tr><td>$time</td>";
      for($i = 1; $i <= 5; $i++) {
        $html .= "<td>".(isset($row[$i]) ? $row[$i] : '')."</td>\n";
      }
      $html .= "</tr>";
    }
    $html .= "</tbody></table>";

    echo htmLawed($html, array('tidy'=>4)); ;
  }
  
  
  /**
   * Convert this timetable to a row/column structure
   */
  private function getTimetableTableArray() {
    $timetableTable = array();
    foreach($this->timetableArray as $columnID => $date) {
      foreach($date as $time => $subjects) {
        if(!isset($timetableTable[$time])) $timetableTable[$time] = array();
        foreach($subjects as $subject) {
          if(!isset($timetableTable[$time][$columnID])) $timetableTable[$time][$columnID] = '';
          $timetableTable[$time][$columnID] .= $subject . "\n<br />";
        }
      }
    }
    return $timetableTable;
  }
}

/*
class Timetable {
  
  function getHTML() {
    
  }
}*/
