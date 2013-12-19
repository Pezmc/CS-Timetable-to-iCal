<?php

include_once 'timetable.php';

// Simple autoloader
function autoloadLib($className) {
	$filePath = 'lib/' . $className . '.class.php';
	if (file_exists($filePath)) {
		require_once $filePath;
		return true;
	}
	return false;
}
spl_autoload_register('autoloadLib');

require_once 'Twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
		'cache' => 'cache',
		'debug' => true
));

$_PAGE = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

$timetables = getCachedTimetablesList();

if($_PAGE == 1) {
	$variables = array('page' => 2);
	
	echo $twig->render("page1.twig", array('var' => 'year', 'vars' => $variables, 'timetables' => $timetables));
} elseif($_PAGE == 2 && isset($_GET['year'])) {
	
	$variables = array('year' => $_GET['year'], 'page' => 3);
	
	echo $twig->render("page1.twig", array('var' => 'grp', 'vars' => $variables, 'timetables' => $timetables[$_GET['year']]));
	
} elseif($_PAGE == 3 && isset($_GET['year']) && isset($_GET['grp'])) {
	
	$variables = array('year' => $_GET['year'], 'grp' => $_GET['grp'], 'page' => 4);
	
	echo $twig->render("page1.twig", array('var' => 'sem', 'vars' => $variables, 'timetables' => $timetables[$_GET['year']][$_GET['grp']]));
} elseif($_PAGE == 4 && isset($_GET['year']) && isset($_GET['grp']) && isset($_GET['sem'])) {
	
	$variables = array('year' => $_GET['year'], 'grp' => $_GET['grp'], 'sem' => $_GET['sem'], 'page' => 5);
	
	$timetable = getTimetableFor($_GET['year'], $_GET['grp'], $_GET['sem']);
	
	echo $twig->render("page4.twig", array('vars' => $variables, 'subjects' => $timetable->getSubjects()));
	
} else {

	$timetable = getTimetableFor($_GET['year'], $_GET['grp'], $_GET['sem']);
	$disabledSubjects = array_diff_key($timetable->getSubjects(), array_flip($_POST['subject']));
	
	$timetable->excludeSubjects($disabledSubjects);
	
	
	$calendar = CalendarFromTimetableFactory::build($timetable);
	echo $calendar->downloadVCalendar();
}

//$timetable = getTimetableFor("Year 1", "All First Years", "Sem1");
//$calendar = CalendarFromTimetableFactory::build($timetable);
//echo $calendar->downloadVCalendar();

