<?php 

class CalendarFromTimetableFactory {

  public static function build(Timetable $timetable) {
  	$calendar = new Calendar();
  	
  	$subjects = $timetable->getSubjects();
  	
  	/* @var $subject Subject */
  	foreach($subjects as $subject) {
  		
  		// Create event for each subject
  		$event = new CalendarEvent();
  		$event->setTitle($subject->getTitle());
  		$event->setDescription(sprintf('Subject: %s\nGroups: %s\nWeeks: %s',
								  										$subject->getTitle(),
								  										implode(", ", $subject->getGroups()),
								  										$subject->getWeekInfo()));
  		$event->setLocation($subject->getLocation());
 
  		// Add an event for every occurance
  		foreach($subject->getDates() as $date) {
  			
  			$event->setStartDateTime($date);
  			$event->setStartTimeString($subject->getStartTime());
  			
  			$event->setEndDateTime($date);
  			$event->setEndTimeString($subject->getEndTime());
  			
  			$calendar->addEvent($event);
  		}
  		
  	}
  	
  	return $calendar;
  }
   
}

?>
