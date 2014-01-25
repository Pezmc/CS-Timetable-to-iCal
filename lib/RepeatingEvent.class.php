<?php

class RepeatingEvent {
	private $startDate, $endDate, $occurrences = 1, $interval;
	
	public function __construct($startDate) {
		$this->startDate = $startDate;
		$this->endDate = $startDate;
	}
	
	public function setEndDate($endDate) {
		$this->endDate = $endDate;
	}
	
	public function setOccurrences($occurrences) {
		$this->occurrences = $occurrences;
	}
	
	public function setInterval(DateInterval $interval) {		
		$this->interval = $interval;
	}

	public function setIntervalSpec($spec) {
		$this->interval = new DateInterval($spec);
	}
	
	public function getInterval() {
		return $this->interval;
	}
	
	public function getOccurrences() {
		return $this->occurrences;
	}
	
	public function getStartDate() {
		return $this->startDate;
	}
	
	public function getDates() {
		$date = $this->startDate;
		
		if($this->getOccurrences() <= 1 || !isset($this->interval))
			return array($date);
		
		$dates = array();
		while($date <= $this->endDate) {
			$dates[] = clone $date;

			$date = $dates->add($this->interval);
		}
		
		return $dates;
	}
	
}

?>