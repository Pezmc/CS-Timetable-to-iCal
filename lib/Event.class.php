<?php

// Represents a single event
class Event {
  
  private $startDateTime, $endDateTime;
  private $title, $description, $location;
  
  public function __construct() {
    $this->startDateTime = new DateTime();
    $this->endDateTime = new DateTime();
  }
  
  //// Start Time ////
  public function getStartDateTime($format=null) {
    if($format) return $this->startDateTime->format($format);
    else return $this->startDateTime;
  }
  
  public function setStartDateTime(DateTime $datetime) {
    $this->startDateTime = $datetime;
  }
  
  /**
   * Set the time from the format \d{1,2}:\d{2}
   */
  public function setStartTimeString($time) {
    $times = $this->hoursMinsFromString($time);
  
    $this->startDateTime->setTime($times[0], $times[1]);
  }
  
  public function setStartDate(DateTime $date) {
    $this->startDateTime->setDate($date->format('Y'), $date->format('m'), $date->format('d'));
  }
  
  //// End Time ////
  public function getEndDateTime($format=null) {
    if($format) return $this->startDateTime->format($format);
    else return $this->endDateTime;
  }
  
  public function setEndDateTime(DateTime $datetime) {
    $this->endDateTime = $datetime;
  }
  
  /**
   * @param $time Time in the format \d{1,2}:\d{2}
   */
  public function setEndTimeString($time) {
    $times = $this->hoursMinsFromString($time);
  
    $this->endDateTime->setTime($times[0], $times[1]);
  }
  
  public function setEndDate(DateTime $date) {
    $this->endDateTime->setDate($date->format('Y'), $date->format('m'), $date->format('d'));
  }  
  
  //// Title ////
  public function getTitle() {
    return $this->title;
  }
  
  public function setTitle($name) {
    $this->title = $name;
  }
  
  //// Description ////
  public function getDescription() {
    return $this->description;
  }
  
  public function setDescription($description) {
    $this->description = $description;
  }
  
  //// Location ////
  public function getLocation() {
    return $this->location;
  }
  
  public function setLocation($location) {
    $this->location = $location;
  }
  
  /**
   * @param $time Time in the format \d{1,2}:\d{2}
   * @return int[] array(hour, mins)
   */
  private function hoursMinsFromString($time) {
    if(substr_count($time, ':') != 1)
      die("I expect the time in the format 00:00");
      
    return split(':', $time, 2);
  }
}

?>