<?php 

class Timetable {

  private $timetableArray = array();
  private $possibleSubjects = array();
  private $excludedSubjects = array();
  
  public function __construct() {
    
  }
  
  /**
   * Add a subject to the timetable
   */
  public function addSubject($id, $name) {
  	$this->possibleSubjects[$id] = $name;
  }
  
  public function getSubjects() {
  	return $this->possibleSubjects;
  }
  
  public function excludeSubjects($array=null) {
  	if(is_array($array)) {
	  	foreach($array as $subjectID => $value) {
	  		$excludedSubjects[$subjectID] = $value;
	  	}
  	}
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
   * @return Subject[] All Subjects
   */
  public function getSubjectEvents() {
  	$allSubjects = array();
  	
  	// For every day of the week
  	foreach($this->timetableArray as $dow => $times) {
  		
  		// For every time of day
  		foreach($times as $time => $events) {

  			// Handle duplicate events (two hour events back to back)
  			$lastHour = isset($thisHour) ? $thisHour : array();
  			$thisHour = array();
  			
  		  /* @var $subject Subject */
  			foreach($events as $subject) {
  				
  				// If this subject isn't excluded
  				if(array_key_exists($subject->getID(), $this->excludedSubjects))
  					continue;
  				
  				// Set the title using our subject list
  				if(isset($this->possibleSubjects[$subject->getID()]))
  					$subject->setTitle($this->possibleSubjects[$subject->getID()]);
  				else 
  					$subject->setTitle($subject->getID());
  				
  				// Add to our arrays if it wasn't there last hour
  				if(!array_key_exists($subject->getID(), $lastHour))	 {
  					$allSubjects[] = $subject;
  				}
  				
  				// Include in the last hour list
  				$thisHour[$subject->getID()] = $subject->getID();
  				
  			}
  		}
  	}
  	
  	return $allSubjects;
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

    include_once('vendor/htmLawed.php');
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

?>
