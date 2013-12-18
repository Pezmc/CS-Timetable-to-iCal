<?php

class Calendar {

  private $events = array();

  public function ServeFile() {
  // Forces file download instead of web page
  header('Content-type: text/calendar; charset=utf-8');
  header('Content-Disposition: inline; filename=calendar.ics');

  // iCal header
  print("BEGIN:VCALENDAR\r\n");
  print("VERSION:2.0\r\n");
  print("PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n");
  print("UID:" . md5(uniqid(mt_rand(), true)) . "@PezCuckow.com\r\n");

  // Basic log
  $fh = @fopen("simpleLog.txt", 'a');

  // For every row in the input
  foreach($rows as $row){

    $data = explode("\t", $row);

    // Simple file log for debugging
    @fwrite($fh, '['. date("Y-m-d H:i:s") . '] ' . $row. "\n");

    //Exam Code	Title	Date	Location	Seat	Start	Finish
    if(empty($data)||empty($data[6])||strtolower($data[0])=="exam code") continue;

    $start = strtotime($data[2]." ".$data[5]);
    $end = strtotime($data[2]." ".$data[6]);

    print("BEGIN:VEVENT\r\n");
    print("DTSTART:".date("Ymd", $start)."T".date("His", $start)."\r\n");
    print("DTEND:".date("Ymd", $end)."T".date("His", $end)."\r\n");
    print("SUMMARY:".smartTruncate($data[1], 40)." (".$data[0].")\r\n");
    print("DESCRIPTION:Title: ".$data[1]
                        ."\\nCode: ".$data[0]
                        ."\\nSeat: ".$data[4]
                        ."\\nStart: ".$data[5]
                        ."\\nEnd: ".$data[6]
                        ."\\n\\nRaw\\n". str_replace("\t", " - ", $row)."\r\n");
    print("LOCATION:".$data[3]."\r\n");
    print("END:VEVENT\r\n");

  }
  @fclose($fh);

  print("END:VCALENDAR\r\n");
  }
}

?>