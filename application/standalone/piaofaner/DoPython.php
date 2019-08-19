<?php

$cinemanid = $argv[1];
$cinemaname = $argv[2];
$movieid = $argv[3];
$piaoSign = $argv[4];
$do = "/usr/bin/python3.4 /home/www/app/film/application/standalone/piaofaner/changcilist.py $cinemanid $cinemaname $movieid $piaoSign";

if(isset($argv[5])){
	$currentDay = $argv[5];
	$do .= " ".$currentDay;
}

exec($do,$ret,$stu);

if($stu == 0){
	echo $ret[0];
}else{
	echo "fail";
}





