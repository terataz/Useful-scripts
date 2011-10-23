<?php
//read files from folder
$folder = "/home/kekko/Music/";
$streaming_dir = "";

$song = "";

$dbuser = 'dbuser';
$dbhost = 'soundreef.cgwpjynqhrxz.eu-west-1.rds.amazonaws.com';
$dbpass = 'yatudob';
$db = 'soundreef';
$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die('Error connecting to mysql');
mysql_select_db($db);

if ($handle = opendir("$folder")) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && substr(strrchr($file, '.'), 1) == 'mp3') {
        $query = "SELECT song.artist,song.title FROM song_file JOIN physical_file ON physical_file.id = song_file.physical_file_id JOIN song ON song.id = song_file.song_id WHERE physical_file.path = '".$file."'" ;
	$result = mysql_query($query);	
	while ($row = mysql_fetch_assoc($result)) {
 		$artist = $row['artist'];
 		$title = $row['title'];
	}
     $title = $artist." - ".$title;
     $filenoext = strstr($file, '.mp3', true);  
     
     exec('sox '.escapeshellarg($folder.$file).' '.escapeshellarg($folder.$filenoext).'.ogg');
	$song .= "{ title:\"".$title."\", free:true, mp3:\"".$streaming_dir.$file."\", oga:\"".$streaming_dir.$filenoext.".ogg\"}, ";  
        }
    }
    closedir($handle);
}
$song = substr("$song", 0, -2);

//assemble rest of static file
$firstpart = file_get_contents('player_firstpart.html');
$secondpart = file_get_contents('player_lastpart.html');
$csv = $firstpart.$song.$secondpart;
//write to new file
$fp = fopen('player_generated.html','w');
fwrite($fp,$csv);
fclose($fp);

?>
