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
		'debug' => false
));

$_PAGE = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

$timetables = getCachedTimetablesList();

// Use sessions to tidy the header
session_start();

if($_PAGE == 1) {
	$variables = array('page' => 2);
	
	echo $twig->render("page1.twig", array('var' => 'year', 'vars' => $variables, 'timetables' => $timetables));
} elseif($_PAGE == 2 && isset($_GET['year'])) {
	
	// Save in session
	$_SESSION['year'] = $_GET['year'];
	
	// Extract the session array	
	extract($_SESSION, EXTR_PREFIX_ALL, "S");
	
	// Check it exists
	if(isset($timetables[$S_year]))
		$timetable = $timetables[$S_year];
	else
		die("Unable to find $S_year.");
	
	$variables = array('page' => 3);
	
	echo $twig->render("page1.twig", array('var' => 'grp', 'vars' => $variables, 'timetables' => $timetable));
	
} elseif($_PAGE == 3 && isset($_SESSION['year']) && isset($_GET['grp'])) {
	
	// Save in session
	$_SESSION['grp'] = $_GET['grp'];
	
	// Extract the session array
	extract($_SESSION, EXTR_PREFIX_ALL, "S");
	
	// Check it exists
	if(isset($timetables[$S_year][$S_grp]))
		$timetable = $timetables[$S_year][$S_grp];
	else
		die("Unable to find group $S_grp in $S_year.");
	
	$variables = array('page' => 4);
	
	echo $twig->render("page1.twig", array('var' => 'sem', 'vars' => $variables, 'timetables' => $timetable));
} elseif($_PAGE == 4 && isset($_SESSION['year']) && isset($_SESSION['grp']) && isset($_GET['sem'])) {
	
	// Save in session
	$_SESSION['sem'] = $_GET['sem'];
	
	// Extract the session array
	extract($_SESSION, EXTR_PREFIX_ALL, "S");
	
	// Check it exists
	$timetable = getTimetableFor($S_year, $S_grp, $S_sem);
	if(empty($timetable))
		die("Unable to find semester $SS_em for group $S_grp in $S_year.");
	
	$variables = array('page' => 5);
	
	echo $twig->render("page4.twig", array('vars' => $variables, 'subjects' => $timetable->getSubjects()));
	
} elseif($_PAGE == 5 && isset($_SESSION['year']) && isset($_SESSION['grp']) && isset($_SESSION['sem']) && isset($_POST['subject'])) {

	// Extract the session array
	extract($_SESSION, EXTR_PREFIX_ALL, "S");	
	
	$timetable = getTimetableFor($S_year, $S_grp, $S_sem);
	$disabledSubjects = array_diff_key($timetable->getSubjects(), array_flip($_POST['subject']));
	$timetable->excludeSubjects($disabledSubjects);
	
	$_SESSION['excludedSubjects'] = $disabledSubjects;

	$variables = array('page' => 6);
	
	echo $twig->render("page5.twig", array('vars' => $variables, 'table' => $timetable->getTimetableTableArray()));
	
} elseif($_PAGE == 6 && isset($_SESSION['year']) && isset($_SESSION['grp']) && isset($_SESSION['sem']) && isset($_SESSION['excludedSubjects'])) {
	
	// Extract the session array
	extract($_SESSION, EXTR_PREFIX_ALL, "S");
	
	// Get our timetable
	$timetable = getTimetableFor($S_year, $S_grp, $S_sem);
	$timetable->excludeSubjects($S_excludedSubjects);
	
	// Convert to a calendar
	$calendar = null;
	try {
		$calendar = CalendarFromTimetableFactory::build($timetable);
	} catch (Exception $e) {
		echo "Something failed while converting your timetable to a .ics file";
		die("<small><pre>".$e->getTraceAsString()."</pre></small>");
	}
	
	// Destroy session by wiping the cookie
	if(isset($_GET['wipe'])) {
		$cookieParams = session_get_cookie_params();
		setcookie(session_name(), '', 0, $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
		session_destroy();
	}
	
	$calendar->setTitle("CS-Calendar_".implode('-', array($S_year, $S_grp, $S_sem)));
	
	// Force download the file
	$calendar->downloadVCalendar();

} elseif($_PAGE == 7) {
	echo $twig->render("page7.twig");
		
} else {
	echo "<h2>Something went wrong!</h2>";
	echo "<p>Sorry about that, here's some debug info:</p>";
	echo "<br /><br /><pre>".var_dump($_SESSION)."</pre>";
	echo "<br /><pre>".var_dump($_POST)."</pre>";
	echo "<br /><pre>".var_dump($_GET)."</pre>";
}

