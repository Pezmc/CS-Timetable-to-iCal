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
  		
  		// Description
  		$description = '';
  		$description .= empty($subject->getTitle()) ? '' : sprintf('Subject: %s\n', $subject->getTitle());
  		$description .= empty($subject->getID()) ? '' : sprintf('Course Code: %s\n', $subject->getID());
  		$description .= empty($subject->getGroups()) ? '' : sprintf('Groups: %s\n', implode(", ", $subject->getGroups()));
  		$description .= empty($subject->getWeekInfo()) ? '' : sprintf('Week: %s\n', $subject->getWeekInfo());
  		$description .= empty($subject->getDates()) ? '' : sprintf('\nOccurrences: %s\n', '\n - '.implode('\n - ', $subject->getDates('l jS F Y')));
  		$event->setDescription($description);
  		
  		// Location
  		$event->setLocation($subject->getLocation());
  		
  		// Loop through all our dates and sort them into groups of repeating events
  		$remainingDates = $subject->getDates();
  		$dateGroups = array();
  		while(!empty($remainingDates)) {
  			
  			// Get the first item
  			/* @var $startDate DateTime */
  			$startDate = array_shift($remainingDates);
  			
  			// Try weekly
				$events = self::findRepeatingEvents($startDate, $remainingDates, 7);
				if($events->getOccurrences() == 1) {
					
					// Try bi-weekly
					$events = self::findRepeatingEvents($startDate, $remainingDates, 14);
				}
  			
  			$dateGroups[] = $events;
  		}
  		
  		// Add an event for every group of events
  		foreach($dateGroups as $events) {
  			
  			$dateEvent = clone $event;
  			
  			$dateEvent->setStartDate($events->getStartDate());
  			$dateEvent->setStartTimeString($subject->getStartTime());
  				
  			$dateEvent->setEndDate($events->getStartDate());
  			$dateEvent->setEndTimeString($subject->getEndTime());
  			
  			// Repeating events
  			if($events->getOccurrences() > 1) {
	  			$dateEvent->setRepeatCount($events->getOccurrences());
	  			$dateEvent->setRepeatFrequency(CalendarEvent::Weekly);
	  			
	  			if($events->getInterval()->d > 7)
	  				$dateEvent->setRepeatInterval(floor($events->getInterval()->d / 7));
  			}
  				
  			$calendar->addEvent($dateEvent);
  		}
  		
  	}
  	
  	return $calendar;
  }
  
  private static function findRepeatingEvents($startDate, &$datesArray, $intervalDays=7) {
  	
  	$event = new RepeatingEvent($startDate);
  	
  	$endDate = $startDate;
  	$repeatCount = 1;
  		
  	foreach($datesArray as $key => $date) {
  		$interval = $endDate->diff($date);
  		if($interval->d == $intervalDays) {
  			unset($datesArray[$key]);
  			$endDate = clone $date;
  			$repeatCount++;
  		}
  	}
  	
  	if($repeatCount > 1) {
	  	$event->setEndDate($endDate);
	  	$event->setOccurrences($repeatCount);
	  	$event->setIntervalSpec('P'.$intervalDays.'D');
  	}
  	
  	return $event;
  }
  
}

?>
