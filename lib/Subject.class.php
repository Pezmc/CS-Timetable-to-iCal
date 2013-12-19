<?php

class Subject {
  private $dates = array();
  private $groups = array();
  private $startTime;
  private $endTime;
  private $id;
  private $title;
  private $location;
  private $weekInfo;
  
  public function addDate($date) {
    if(!$date instanceof DateTime)
      throw new Exception("That's not a date!");
      
    $this->dates[] = $date;
  }
  
  /**
   * @param $format Convert the dates to a string
   * @return DateTime[]
   */
  public function getDates($format=null) {
  	if($format != null) {
  		$dates = array();
  		foreach($this->dates as $date)
  			$dates[] = $date->format($format);
  		return $dates;
  	}
  	return $this->dates;
  }
  
  public function setDates($dates) {
    $this->dates = $dates;
  }
  
  public function getID() {
  	return $this->id;
  }
  
  public function setID($id) {
  	$this->id = $id;
  }
  
  /**
   * @return string Time in format 00:00
   */
  public function getStartTime() {
  	return $this->startTime;
  }
  
  public function setStartTime($time) {
    $this->startTime = $time;
  }
  
  /**
   * @return string Time in format 00:00
   */
  public function getEndTime() {
  	return $this->endTime;
  }
  
  public function setEndTime($time) {
    $this->endTime = $time;
  }

  public function getTitle() {
		return $this->title;
  }

  public function setTitle($title) {
    $this->title = $title;
  }
  
  public function getLocation() {
  	return $this->location;
  }
  
  public function setLocation($loc) {
    $this->location = $loc;
  }
  
  /**
   * @return string[]
   */
  public function getGroups() {
  	return $this->groups;
  }
  
  public function setGroups($groups) {
    $this->groups = $groups;
  }
  
  public function getWeekInfo() {
  	return $this->weekInfo;
  }
  
  public function setWeekInfo($week) {
    $this->weekInfo = $week;
  }
  
  /**
   * Does the subject have a title and at least one date
   * @return boolean
   */
  public function isValid() {
    return isset($this->id) && !empty($this->dates);
  }
  
  public function __toString() {
    return sprintf('%s (%s - %s) @ %s : %s', $this->title, $this->startTime, $this->endTime, $this->location, implode("+", $this->groups));
  }
}

?>