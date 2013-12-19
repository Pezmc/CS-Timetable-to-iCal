<?php

class Calendar {

  private $events = array();
  
  public function addEvent(CalendarEvent $event) {
  	echo "Add:";
  	var_dump($event);
  	$this->events[] = $event;
  }

  public function downloadVCalendar() {
  	// Forces file download instead of web page
  	header('Content-type: text/calendar; charset=utf-8');
  	header('Content-Disposition: inline; filename=calendar.ics');

		echo $this->createVCalendar();
  }
  
  public function createVCalendar() {
  	
  	$vcalendar = $this->tagsToVCalendar($this->getHeaderTags());
  	
  	/* @var $event CalendarEvent */
  	foreach($this->events as $event) {
  		var_dump($event);
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
  	foreach($tags as $tag => $value)
  		$vcalendar .= strtoupper($tag) . ':' . $value . "\r\n";
  	
  	return $vcalendar;
  }
}

?>