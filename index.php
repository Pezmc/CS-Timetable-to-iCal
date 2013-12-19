<?php

include_once 'timetable.php';

// Simple autoloader
function __autoload($className) {
	$filePath = 'lib/' . $className . '.class.php';
	if (file_exists($filePath)) {
		require_once $filePath;
		return true;
	}
	return false;
}

$timetable = getTimetableFor("Year 1", "All First Years", "Sem1");
$calendar = CalendarFromTimetableFactory::build($timetable);
echo $calendar->downloadVCalendar();

