<?php

class Subject {
  private $dates = array();
  private $groups = array();
  private $startTime;
  private $endTime;
  private $title;
  private $location;
  private $weekInfo;
  
  public function addDate($date) {
    if(!$date instanceof DateTime)
      throw new Exception("That's not a date!");
      
    $this->dates[] = $date;
  }
  
  public function setDates($dates) {
    $this->dates = $dates;
  }
  
  public function setStartTime($time) {
    $this->startTime = $time;
  }
  
  public function setEndTime($time) {
    $this->endTime = $time;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }
  
  public function setLocation($loc) {
    $this->location = $loc;
  }
  
  public function setGroups($groups) {
    $this->groups = $groups;
  }
  
  public function setWeekInfo($week) {
    $this->weekInfo = $week;
  }
  
  public function isValid() {
    return isset($this->title) && !empty($this->dates);
  }
  
  public function __toString() {
    return sprintf('%s (%s - %s) @ %s : %s', $this->title, $this->startTime, $this->endTime, $this->location, implode("+", $this->groups));
  }
}

?>