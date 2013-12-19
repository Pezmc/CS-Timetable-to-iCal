<?php

class CalendarEvent extends Event {
  
	public function getVEventTagArray() {
		$tags = array();
		
		$tags['BEGIN'] = 'VEVENT';
		$tags['DTSTART'] = $this->getStartDateTime('Ymd\THis\Z');
		$tags['DTEND'] = $this->getEndDateTime('Ymd\THis\Z');
		$tags['SUMMARY'] = $this->escapeString($this->getTitle());
		//$tags['ORGANIZER'];CN=John Doe:MAILTO:john.doe@example.com
		$tags['UID'] = md5($this->getTitle()) . md5($this->getStartDateTime('U')) . md5($this->getEndDateTime('U'));
		$tags['DESCRIPTION'] = $this->escapeNewLines($this->getDescription());
		$tags['LOCATION'] = $this->escapeString($this->getLocation());
		$tags['END'] = 'VEVENT';

		return $tags;
	}
	
  public function getVEventString() {

    $vevent = '';
    foreach($this->getVEventTagArray() as $tag => $value)
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