<?php 

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

?>
