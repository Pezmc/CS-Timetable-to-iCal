<?php

// Represents a single event
class Event {
  
  private $startTime, $endTime, $startDate, $endDate;
  private $title, $description, $location;
}

    /*print("BEGIN:VEVENT\r\n");
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
    print("END:VEVENT\r\n");*/
    
?>