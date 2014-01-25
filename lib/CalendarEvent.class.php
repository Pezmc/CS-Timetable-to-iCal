<?php

class CalendarEvent extends Event {
  
	// RRule Frequency RFC Spec
	const Secondly = 'MONTHLY';
	const Minutely = 'MINUTELY';
	const Hourly = 'HOURLY';
	const Daily = 'DAILY';
	const Weekly = 'WEEKLY';
	const Monthly = 'MONTHLY';
	const Yearly = 'YEARLY';
	
	private $occurrences = 1;
	private $interval = 1;
	private $frequency = '';
	
	public function setRepeatCount($count) {
		$this->occurrences = $count;
	}
	
	public function setRepeatInterval($interval) {
		$this->interval = $interval;
	}
	
	public function setRepeatFrequency($frequency) {
		$this->frequency = $frequency;
	}
	
	public function getVEventTagArray() {
		$tags = array();
		
		$tags['BEGIN'] = 'VEVENT';
		$tags['DTSTART'] = $this->getStartDateTime('Ymd\THis\Z');
		$tags['DTEND'] = $this->getEndDateTime('Ymd\THis\Z');
		$tags['SUMMARY'] = $this->escapeString($this->getTitle());
		//$tags['ORGANIZER'];CN=John Doe:MAILTO:john.doe@example.com
		$tags['UID'] = md5($this->getTitle()) . md5($this->getStartDateTime('U')) . md5($this->getEndDateTime('U') . md5($this->getLocation()));
		$tags['DESCRIPTION'] = $this->escapeNewLines($this->getDescription());
		$tags['LOCATION'] = $this->escapeString($this->getLocation());
		
		if($this->occurrences > 1) 
			$tags['RRULE'] = sprintf('FREQ=%s;COUNT=%d', $this->frequency, $this->occurrences);
		
		if($this->interval > 1)
			$tags['RRULE'] .= sprintf(';INTERVAL=%d', $this->interval);
		
		//$tags['-'] = $this->getVStringFromTags($this->getAlarm());
		
		$tags['END'] = 'VEVENT';

		return $tags;
	}
	
	public function getAlarm() {
		$tags = array();
		
		// Add an alert 10 mins before the end
		$tags['BEGIN'] = 'VALARM';
		$tags['ACTION'] = 'DISPLAY';
		$tags['DESCRIPTION'] = 'This is an event reminder';
		$tags['TRIGGER'] = '-P0DT0H10M0S';
		$tags['END'] = 'VALARM';
		
		return $tags;
	}
	
  public function getVStringFromTags($tags) {

    $vevent = '';
    foreach($tags as $tag => $value)
      $vevent .= sprintf("%s:%s\r\n", $tag, $value);
      
    return $vevent;
  }
  
  private function escapeNewLines($string) {
    return preg_replace("/((\r?\n)|(\r\n?))/", '\n', $this->escapeString($string));
  }
  
  private function escapeString($string) {
    return preg_replace('/([\,;])/','\\\$1', $string);
  }
  
}

?>