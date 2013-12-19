<?php 

class CalendarFromTimetableFactory {

  public static function build(Timetable $timetable) {
  	$calendar = new Calendar();
  	
  	$subjects = $timetable->getSubjectEvents();
  	
  	/* @var $subject Subject */
  	foreach($subjects as $subject) {
  		
  		// Create event for each subject
  		$event = new CalendarEvent();
  		
  		// Title (including id if we have it)
  		if($subject->getID() != $subject->getTitle())
  			$event->setTitle(sprintf('[%s] %s', $subject->getID(), $subject->getTitle()));
  		else 
  			$event->setTitle($subject->getID());
  		
  		$event->setDescription(sprintf('Subject: %s\nGroups: %s\nWeeks: %s\n\nOccurrences:%s\n',
								  										$subject->getTitle(),
								  										implode(", ", $subject->getGroups()),
								  										$subject->getWeekInfo(),
  																		implode('\n - ', $subject->getDates('l jS F Y'))
  													));
  		$event->setLocation($subject->getLocation());
 
  		// Add an event for every occurance
  		foreach($subject->getDates() as $date) {
  			$dateEvent = clone $event;

  			$dateEvent->setStartDate($date);
  			$dateEvent->setStartTimeString($subject->getStartTime());
  			
  			$dateEvent->setEndDate($date);
  			$dateEvent->setEndTimeString($subject->getEndTime());
  			
  			$calendar->addEvent($dateEvent);
  		}
  		
  	}
  	
  	return $calendar;
  }
   
}

?>
