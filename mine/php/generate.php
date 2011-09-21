<?php
// Questo script crea dei file .m3u rispettando l'ordine e le fascie orarie definite in base al numero di passaggi.
// Per chiamarlo: php generate.php . I files csv vanno chiamati con lo stesso nome dei generi dell'array $time_slots.


$from = strtotime('23-09-2011');

function readSongsFromCSV($file) {
  $songs = array();
  if (($handle = fopen($file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
      $songs[] = $data[0];
      //if (!file_exists('/home/giorgio/Music/high_tech/songs/'.$data[0])) {
      //  die('/home/giorgio/Music/high_tech/songs/'.$data[0]);
     // }
    }
  }
  return $songs;
}

//numero di canzoni da suonare per genere. Definisce anche le fasce orarie attraverso il numero di passaggi

$time_slots = array(
//  'jazz' => 45,
  'lounge_soft' => 18,
  'lounge' => 26,
  'pop_rock_r_b' => 80
);

$old_half_album = -1;
$to = strtotime('+2 months', $from);
$to = strtotime('+15 days', $to);
$counter = 1;
//per ogni playlist giornaliera
for ($i = $from; $i <= $to; $i += 86400) {
  $m3u = '';
  $y = sprintf("%02d", $counter);
  $counter++; 
  $curr_date = date('d-m-Y', $i);
  $filename = 'palinsesti/'.$y.'-palinsesto-'.$curr_date.'.m3u';
  $fh = fopen($filename, 'w') or die("can't open file");
  $selected_songs = array();
  $classica = array(1,2,3,4); //4 album di music classica
  $ok = false;
  while (!$ok) {
    shuffle($classica); //shuffle degli album di classica
    $sel_classica_albums = array_slice($classica,0,2); //ne selezioniamo due
    $ok = ($old_half_album != $sel_classica_albums[1]) && ($old_full_album != $sel_classica_albums[0]); //controlla se il primo album è diverso dal precedente completo e se il secondo è diverso dal precedente non completo
  }

  $selected_songs = readSongsFromCSV('classica'.(string) $sel_classica_albums[0].'.csv');
  $old_full_album = $sel_classica_albums[0];
  $old_half_album = $sel_classica_albums[1];
  $second_classica_album = readSongsFromCSV('classica'.(string) $sel_classica_albums[1].'.csv');
  $selected_songs = array_merge($selected_songs, array_slice($second_classica_album,0,round(0.5*sizeof($second_classica_album)))); //merge delle canzoni del primo album con la metà di quelle del secondo


//inclusione delle canzoni di generi che non devono rispettare l'ordine
  foreach ($time_slots as $name => $nofsongs) {
    $songs = readSongsFromCSV($name.'.csv');
    shuffle($songs);
    $new_songs = array_slice($songs,0,$nofsongs);
    $new_songs_number = sizeof($new_songs);
    $selected_songs = array_merge($selected_songs, $new_songs);
  }
  foreach ($selected_songs as $selected_song) {
    $m3u .= $selected_song."\n";
  }
  fwrite($fh, $m3u); //scrive playlist
}
