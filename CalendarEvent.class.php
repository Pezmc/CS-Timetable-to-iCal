<?php

class CalendarEvent extends Event {
  
  public function getVEventString() {
    $tags[] = array();
    
    $tags['BEGIN'] = 'VEVENT';
    $tags['DTSTART'] = $this->geEndDateTime('Ymd\THis\Z');
    $tags['DTEND'] = $this->getStartDateTime('Ymd\THis\Z');
    $tags['SUMMARY'] = $this->escapeString($this->getTitle());
    //$tags['ORGANIZER'];CN=John Doe:MAILTO:john.doe@example.com
    $tags['UID'] = md5($this->getTitle());
    $tags['DESCRIPTION'] = $this->escapeNewLines($this->getDescription());
    $tags['LOCATION'] = $this->escapeString($this->getLocation());
    $tags['END'] = 'VEVENT';
    
    $vevent = '';
    foreach($vevent as $tag => $value)
      $vevent .= sprintf("%s:%s\r\n", $tag, $value);
      
    return $vevent;
  }
  
  private function escapeNewLines($string) {
    preg_replace("/((\r?\n)|(\r\n?))/", '\n', $this->escapeString($string));
  }
  
  private function escapeString($string) {
    return preg_replace('/([\,;])/','\\\$1', $string);
  }
  
}

?>