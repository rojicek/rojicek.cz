<!DOCTYPE html>
<html> 
<head> 

<title>Home HDO Monitor</title>
<meta charset="UTF-8">
<link rel=icon href=enicon.png>

</head>



<body style="font-family: 'Verdana', 'Geneva', 'Kalimati', sans-serif;">

<?php

 
function inInterval($val, $zacatky, $konce, $size) 
{

 for ($i = 0; $i < $size; $i++)
 {
  if (($val>= $zacatky[$i]) && ($val<= $konce[$i]))
        {return 1;} 
 }

    return 0;
}


function tod2str($tod)
{ 
    $h =  (int) ($tod*24);
    $h2 = $h;
    if ($h>23) {$h2 =  $h2-24;}
    if ($h2<10) {$h2 = '0' . $h2;}
    $m = (int) (($tod - $h/24)*24*60);
    if ($m<10) {$m = '0' . $m;}
    return $h2 . ":" . $m;
} 

   
  try
  {
   include "/var/www/rojicek.cz/web/db/includedb.php"; 
   
      
  echo "Plati pro: " . date('d.m. H:i', time()) . "<br>";
  echo "<p>";
  
  $actDatum = new DateTime();
  
  
  //asi to bude kostrbate - je to slepene z ruznych casti
  $datetime_now = time();
  
  $date_today = date('Y-m-d', $datetime_now);
  $date_tomorrow = date('Y-m-d', $datetime_now + 86400);


  $holiday_today_sql = "select count(*) as pocet from holidays_tbl where datum = '" . $date_today . "'";
  $holiday_today_q = $databaseConnection -> query($holiday_today_sql);
  $row = $holiday_today_q->fetch_row();
  $holiday_today = (int)$row[0];
  if ($holiday_today == 1)
     $DoWdnes = " 'HOL' ";
  else
     $DoWdnes = " ucase(SUBSTRING(DAYNAME(now()),1,3)) ";  
     
     
  
  $holiday_tomorrow_sql = "select count(*) as pocet from holidays_tbl where datum = '" . $date_tomorrow . "'";
  $holiday_tomorrow_q = $databaseConnection -> query($holiday_tomorrow_sql);
  $row = $holiday_tomorrow_q->fetch_row();
  $holiday_tomorrow = (int)$row[0];
  if ($holiday_tomorrow == 1)
      $DoWzitra = " 'HOL' ";
  else
      $DoWzitra = " ucase(SUBSTRING(DAYNAME(DATE_ADD(now(), INTERVAL 1 DAY) ),1,3)) ";  #mozna nahradim HOL, takze takhle
  
     
  

   $selectDNESKA = " select DoW,peakStart, hour(STR_TO_DATE(peakStart, '%H:%i:%s'))/24+ minute(STR_TO_DATE(peakStart, '%H:%i:%s'))/24/60 as start, peakStop , hour(STR_TO_DATE(peakStop , '%H:%i:%s'))/24+ minute(STR_TO_DATE(peakStop , '%H:%i:%s'))/24/60 as konec from hdo_tbl where date(now())>=dateStart AND date(now())<=dateEnd AND ".$DoWdnes."  = DoW order by timeid";  
   $hdoResultsDNESKA  = $databaseConnection->query ($selectDNESKA) ;// or die (mysql_error());
   
   $selectZITRA = " select DoW,peakStart, hour(STR_TO_DATE(peakStart, '%H:%i:%s'))/24+ minute(STR_TO_DATE(peakStart, '%H:%i:%s'))/24/60 as start, peakStop , hour(STR_TO_DATE(peakStop , '%H:%i:%s'))/24+ minute(STR_TO_DATE(peakStop , '%H:%i:%s'))/24/60 as konec from hdo_tbl where date(now())>=dateStart AND date(now())<=dateEnd AND ".$DoWzitra."  = DoW order by timeid";  
   $hdoResultsZITRA  = $databaseConnection->query ($selectZITRA) ;// or die (mysql_error());

   
   $ix=0 ;
   $zacatky = array();
   $konce = array();
   
   $tod = (float)date('H')/24.0 + (float)date('i')/24.0/60.0 + (float)date('s')/24.0/60.0/60.0;  
   // echo tod2str($tod) . "<p>";
 
 
 //echo $selectDNESKA . "<p>";
 //echo $selectZITRA . "<p>";
   
    while($row = $hdoResultsDNESKA->fetch_assoc())
    {
       $zacatky[$ix] = (float)$row['start'];
       $konce[$ix] = (float)$row['konec'];
       //echo  $zacatky[$ix]  . " * <br>";
       $ix = $ix + 1;      
    }
    while($row = $hdoResultsZITRA->fetch_assoc())
    {
       $zacatky[$ix] = (float)$row['start']+1;
       $konce[$ix] = (float)$row['konec']+1;
       //echo  $zacatky[$ix]  . " * <br>";
       $ix = $ix + 1;      
    }
   
   $pocetZaznamu =  $ix;
   
    
    /*
    for ($zix = 0; $zix <$pocetZaznamu; $zix++)
    { 
    echo  $zacatky[$zix] . "-" . $konce [$zix] ." <br>";
    }
   
   echo "--------------<p>";
      */
              /*
   echo "0.4 : "  . inInterval(0.4, $zacatky, $konce, $pocetZaznamu)  ." <br>";
   echo "0.52 : "  . inInterval(0.52, $zacatky, $konce, $pocetZaznamu)  ." <br>";
   echo "1.35 : "  . inInterval(1.35, $zacatky, $konce, $pocetZaznamu)  ." <br>";
   echo "0.1 : "  . inInterval(0.1, $zacatky, $konce, $pocetZaznamu)  ." <br>";
   echo "1.9 : "  . inInterval(1.9, $zacatky, $konce, $pocetZaznamu)  ." <br>";
   echo "1.85 : "  . inInterval(1.85, $zacatky, $konce, $pocetZaznamu)  ." <br>";
   echo "1.67 : "  . inInterval(1.67, $zacatky, $konce, $pocetZaznamu)  ." <br>";
                */
                
     $delkaMycky = 2.5/24;
     
     echo "Kdy spustit mycku/pracku (<b>2h30 program</b>)<br>";
      for ($hodinyDopredu = 0; $hodinyDopredu < 24; $hodinyDopredu++)
      {
      $pozadovanyStart =$tod +  $hodinyDopredu/24;
      $pozadovanyKonec =   $pozadovanyStart  + $delkaMycky;
       
     
      #detect conflict
      $conflict = 0;
      //$maxConflicts = $delkaMycky*24*60;
      $maxConflicts = 100;
      
      for ($myckaBezi = $pozadovanyStart; $myckaBezi <= $pozadovanyKonec; $myckaBezi= $myckaBezi + ($pozadovanyKonec-$pozadovanyStart)/$maxConflicts)
      {
         if (inInterval($myckaBezi, $zacatky, $konce, $pocetZaznamu) == 1)
          {
               $conflict = $conflict + 1; // can break here
          }
      }
                       
       
       //echo "od ". $pozadovanyStart . " do " .   $pozadovanyKonec   . " : konflikt = " . 100*$conflict/($maxConflicts+1). "% <br>";
       $proc =  100-100*$conflict/($maxConflicts+1);
       
       if ($proc>95){$color = "#577234";}
       elseif ($proc>90) {$color = "#a3911f";}
       else  {$color = "#a22c1e";}
       
       echo "<font color=" . $color . ">";
       if    ($proc>90)
         {
          echo "<b>start za " . $hodinyDopredu . "h (konec v " . tod2str($pozadovanyKonec) . ") je " . (int)($proc)   . "% ok<br></b>";
          //echo "zacne v " . tod2str($pozadovanyStart)  . "<br>";
          
         }
         else
         {
         echo "start za " . $hodinyDopredu . "h je spatne (" . (int)($proc) . "%)<br>";
         //echo   tod2str($pozadovanyStart) . " - " .  tod2str($pozadovanyKonec)  . "<br>";
         }
       echo "</font>";
      // echo "<p>";
       
       //od ". $pozadovanyStart . " do " .   $pozadovanyKonec   . " : konflikt = " . 100*$conflict/($maxConflicts+1). "% <br>";
       
      }
  
  //////////////////////////////////////////////////////////////
  echo "<p>";
  echo "<hr>";
  echo "<p>"; 
  
  $delkaMycky = 1.5/24;
     
     echo "Kdy spustit mycku/pracku (<b>1h30 program</b>)<br>";
      for ($hodinyDopredu = 0; $hodinyDopredu < 24; $hodinyDopredu++)
      {
      $pozadovanyStart =$tod +  $hodinyDopredu/24;
      $pozadovanyKonec =   $pozadovanyStart  + $delkaMycky;
       
     
      #detect conflict
      $conflict = 0;
      //$maxConflicts = $delkaMycky*24*60;
      $maxConflicts = 100;
      
      for ($myckaBezi = $pozadovanyStart; $myckaBezi <= $pozadovanyKonec; $myckaBezi= $myckaBezi + ($pozadovanyKonec-$pozadovanyStart)/$maxConflicts)
      {
         if (inInterval($myckaBezi, $zacatky, $konce, $pocetZaznamu) == 1)
          {
               $conflict = $conflict + 1; // can break here
          }
      }
                       
       
       //echo "od ". $pozadovanyStart . " do " .   $pozadovanyKonec   . " : konflikt = " . 100*$conflict/($maxConflicts+1). "% <br>";
       $proc =  100-100*$conflict/($maxConflicts+1);
       
       if ($proc>95){$color = "#577234";}
       elseif ($proc>90) {$color = "#a3911f";}
       else  {$color = "#a22c1e";}
       
       echo "<font color=" . $color . ">";
       if    ($proc>90)
         {
          echo "<b>start za " . $hodinyDopredu . "h (konec v " . tod2str($pozadovanyKonec) . ") je " . (int)($proc)   . "% ok<br></b>";
          //echo "zacne v " . tod2str($pozadovanyStart)  . "<br>";
          
         }
         else
         {
         echo "start za " . $hodinyDopredu . "h je spatne (" . (int)($proc) . "%)<br>";
         //echo   tod2str($pozadovanyStart) . " - " .  tod2str($pozadovanyKonec)  . "<br>";
         }
       echo "</font>";
      // echo "<p>";
       
       //od ". $pozadovanyStart . " do " .   $pozadovanyKonec   . " : konflikt = " . 100*$conflict/($maxConflicts+1). "% <br>";
       
      }
  
  }
  catch (Exception $e)
  {
    echo "chyba" + $e;
  }
  
?>


</body>
</html>
