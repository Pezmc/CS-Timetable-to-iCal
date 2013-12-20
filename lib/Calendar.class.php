<?php

class Calendar {

  private $events = array();
  private $title = "CS-Calendar";
  
  public function addEvent(CalendarEvent $event) {
  	$this->events[] = $event;
  }
  
  public function setTitle($title) {
  	$this->title = $title;
  }

  public function downloadVCalendar() {
  	// Decide on the title
  	$title = $this->title;
  	$title = preg_replace('/[^a-z0-9_-]/ui', '-', $title);
  	
  	// Forces file download instead of web page
  	header('Content-type: text/calendar; charset=utf-8');
  	header('Content-Disposition: inline; filename='.$title.'.ics');

		echo $this->createVCalendar();
  }
  
  public function createVCalendar() {
  	
  	$vcalendar = $this->tagsToVCalendar($this->getHeaderTags());
  	
  	/* @var $event CalendarEvent */
  	foreach($this->events as $event) {
  		$vcalendar .= $this->tagsToVCalendar($event->getVEventTagArray());
  	}
  	
  	$vcalendar .= $this->tagsToVCalendar($this->getFooterTags());
  	
  	return $vcalendar;
  }
  
  private function getHeaderTags() {
  	// iCal header
  	$tags = array();
  	$tags['BEGIN'] = 'VCALENDAR';
  	$tags['VERSION'] = '2.0';
  	$tags['X-WR-CALNAME'] = "CS Manchester Timetable";
  	$tags['X-WR-TIMEZONE'] = date_default_timezone_get();
  	$tags['PRODID'] = "-//Pez Cuckow//CS Timetable to Ical//EN";
  	$tags['UID'] = md5(uniqid(mt_rand(), true)) . "@PezCuckow.com";
  	return $tags;
  }
  
  private function getFooterTags() {
  	$tags["END"] = 'VCALENDAR';
  	return $tags;
  }
  
  private function tagsToVCalendar($tags) {
  	$vcalendar = '';
  	foreach($tags as $tag => $value) {
  		// - is a special tag containing a strine
  		if($tag == '-') $vcalendar .= $value;
  		else $vcalendar .= strtoupper($tag) . ':' . $value . "\r\n";
  	}
  	
  	return $vcalendar;
  }
}

?>