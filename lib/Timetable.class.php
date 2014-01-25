<?php 

class Timetable {

  private $timetableArray = array();
  private $possibleSubjects = array();
  private $excludedSubjects = array();
  
  // Timetable config inclusive
  private $startDay = 1;
  private $endDay = 5;
  
  private $startHour = 9;
  private $endHour = 17;
  
  public function __construct() {
  	
    // Create an empty timetable
    for($day=$this->startDay; $day <= $this->endDay; $day++) {
    	
    	if(!isset($this->timetableArray[$day])) $this->timetableArray[$day] = array();
    	
    	for($hour=$this->startHour; $hour <= $this->endHour; $hour++) {
    		
    		$time = sprintf("%02d:00", $hour);
    		
    		if(!isset($this->timetableArray[$day][$time])) $this->timetableArray[$day][$time] = array();
    		
    	}
    }
    
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
	  		$this->excludedSubjects[$subjectID] = $value;
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

  			$timesSubjects = $this->getIncludedSubjectsFromArray($events);
  			$allSubjects = array_merge($allSubjects, $timesSubjects);
  			
  		}
  	}
  	
  	return $allSubjects;
  }
  
  /**
   * Convert this timetable to a row/column structure suitable for a table
   * @return array[array[subject[]]] An array of subjects in the format $table[time][column] = Subject[];
   */
  public function getTimetableTableArray() {
    $timetableTable = array();
    
    // For every day of the week
    foreach($this->timetableArray as $columnID => $date) {

    	// Time of day
      foreach($date as $time => $subjects) {
      	
      	// Create empty coulmns
        if(!isset($timetableTable[$time])) {
        	$timetableTable[$time] = array();
        	for($i = $this->startDay;$i <= $this->endDay;$i++)	$timetableTable[$time][$i] = array();
        }
        
        // Get an array of included subjects for this day/time
        $timesSubjects = $this->getIncludedSubjectsFromArray($subjects, false);
        
        // Append to the table array
        foreach($timesSubjects as $subject) {
          $timetableTable[$time][$columnID][] = $subject;
        }
      }
    }
    return $timetableTable;
  }
  
  /**
   * Return an array of non duplicate events for a specific time
   * @warning Must be called per time period in ORDER
   * @param Subject[] $subjects
   */
  private $thisHour = array();
  private function getIncludedSubjectsFromArray($subjects, $ignoreBackToBack=true) {
  	
  	
  	// Handle duplicate events (two hour events back to back)
  	if($ignoreBackToBack)
  		$lastHour = isset($this->thisHour) ? $this->thisHour : array();
  	else 
  		$lastHour = array();
  	$this->thisHour = array();
  	
  	$validSubjects = array();
  	
  	/* @var $subject Subject */
  	foreach($subjects as $subject) {
  		
  		// Skip this subject if excluded
  		if(array_key_exists($subject->getID(), $this->excludedSubjects))
  			continue;
  	
  		// Set the title using our subject list
  		if(isset($this->possibleSubjects[$subject->getID()]))
  			$subject->setTitle($this->possibleSubjects[$subject->getID()]);
  		else
  			$subject->setTitle($subject->getID());
  	
  		// Add to our arrays if it wasn't there last hour
  		if(!array_key_exists($subject->getID(), $lastHour))	 {
  			$validSubjects[] = $subject;
  		}
  	
  		// Include in the last hour list
  		$this->thisHour[$subject->getID()] = $subject->getID();
  	
  	}
  	
  	return $validSubjects;
  }
  
}

?>
