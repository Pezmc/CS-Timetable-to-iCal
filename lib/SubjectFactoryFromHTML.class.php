<?php

class SubjectFactoryFromHTML {

  public static function build($html) {

      $subject = new Subject();

      // Grab the subject section of the HTML
      $subjectHTML = self::getSubjectHTML($html);
      
      // Add the subject ID and groups
      if($subjectHTML) {
        $subject->setID(self::getSubjectIDFromSubjectHTML($subjectHTML));
        $subject->setGroups(self::getGroupsFromSubjectHTML($subjectHTML));
        
        // The CS timetable puts lots of info in the HTML title...
        $titleRaw = $subjectHTML->title;
        
        // Better way to do this? Hacky split...
        $split = '<b>Active dates</b><br />';

        // Grab the times      
        $timeRaw = substr($titleRaw, 0, strrpos($titleRaw, $split));
        $matches = array();
        preg_match_all('#(\d{2}:\d{2}) - (\d{2}:\d{2})#', $timeRaw, $matches);
        if(count($matches) == 3) {
          $subject->setStartTime($matches[1][0]);
          $subject->setEndTime($matches[2][0]); 
        }
        
        // Grab the dates text
        $datesRaw = substr($titleRaw, strrpos($titleRaw, $split) + strlen($split));
        
        // List of datetime objects
        $dates = self::getDatesFromDateStringArray(explode('<br />', $datesRaw));
        if(!empty($dates))
          $subject->setDates($dates);
      }
      
      // Add location
      $location = self::getLocationFromHTML($html);
      if($location) $subject->setLocation($location);
      
      // Add week info
      $week = self::getWeekInformation($html); 
      if($week) $subject->setWeekInfo($week);
      
      return $subject;
  }
  
  private static function getSubjectHTML($HTML) {
  
      // The first A contains the title of the subject ID
      $subject = $HTML->find("a.timetableunit", 0);
      
      return $subject;
      
  }
  
  private static function getSubjectIDFromSubjectHTML($subjectHTML) {
  
    // If the subject has groups (in an <i>) the subject name is the second text
    $index = $subjectHTML->find('i') ? 1 : 0;
    return $subjectHTML->find('text', $index)->plaintext;
    
  }
  
  private static function getGroupsFromSubjectHTML($subjectHTML) {
  
    // If the subject has groups (in an <i>) in the format A(+B)*
    $groupHTML = $subjectHTML->find('i', 0);
    if($groupHTML) {
      return explode("+", $groupHTML->plaintext, 2);
    }
   
    return array();   
  }
  
  private static function getLocationFromHTML($html) {
    $loc = $html->find("a.timetableunit", 1);
    if($loc) return $loc->plaintext;
    
    return null;
  }
  
  private static function getDatesFromDateStringArray($datesArray) {
    $dates = array();
  
    //String array in the format [i]=> string(28) "Thursday 26th September 2013"
    foreach($datesArray as $date) {
      $timestamp = strtotime($date);
      
      if($timestamp)
        $dates[] = date_timestamp_set(new DateTime(), $timestamp);
    }
    
    return $dates;
  }
  
  private static function getWeekInformation($html) {
    // Week info is in the 1st or 2nd sup
    // in the format [w2+] w3-5,7-12
    foreach($html->find('sup') as $sup)
      if(preg_match('#\[.*\]#i', $sup->plaintext))
        return trim($sup->plaintext, '[] '); 
  }
}

?>