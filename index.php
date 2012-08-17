<html>
 <head>
  <title>PINAC stati</title>
  <meta http-equiv="refresh" content="300" >
  <link rel="stylesheet" type="text/css" href="style.css" media="screen" />
 </head>

<body>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$EXT = 'dat';
$DIR = "./";

$cpu = array();
$mem = array();
$output = array();

// Open a known directory, and proceed to read its contents
if (is_dir($DIR)) {
    if ($dh = opendir($DIR)) {
        while (($f = readdir($dh)) !== false) {
            $file = $DIR.$f;
            if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) == $EXT) {
                //print("Loading ".$file."<br />\n");
                include($file);
            }
        }
        closedir($dh);
    }
}

//print_r($output);
//print_r($cpu);

// families of machines: old to new
$families = array( 1 => array('pinac11', 'pinac12', 'pinac13', 'pinac14', 'pinac15', 'pinac16', 'pinac17', 'pinac18', 'pinac19', 'pinac20'),
                   2 => array('pinac21', 'pinac22', 'pinac23', 'pinac24', 'pinac25', 'pinac26', 'pinac27', 'pinac28', 'pinac29', 'pinac30'),
                 );

$families_notes = array( 0 => 'Various desktop PCs that are (seemingly) not used as such', // machines not in a family
                         1 => '4-thread machines <a title="1-socket 4-core 4-thread">[1-4-4]</a>',
                         2 => '8-thread machines <a title="1-socket 4-core 8-thread">[1-4-8]</a>');

asort($cpu);
$time_local = time();
$not_responding = array();
$top_users = array();
$all = array_keys($cpu);

print('<div class="box">Basic rules: always leave room for someone to join the party, avoid <a href="http://en.wikipedia.org/wiki/Load_(computing)">high load</a></div>');

print('<div class="left"><h3>Available machines</h3>');
print('<table border="1">');
print('<tr><td>&nbsp;</td><td>&nbsp;</td><td width="80px">% CPU</td><td width="80px">% MEM</td><td width="50px">Load</td><td colspan="9">Users <i>(bold = cpu-intensive process)</i> </td></tr>');
$c = 1;
for ($i = count($families); $i >= 0; $i--) {
  // filter families
  $todo = array();
  if ($i == 0) {
    $todo = $all;
  } else {
    $todo = array_intersect($all, $families[$i]);
    $all = array_diff($all, $todo);
  }

  // filler above families
  printf('<tr><td colspan=14 style="padding: 4px 0 4px 30px">%s</td></tr>', $families_notes[$i]);

  // the loop
  foreach($todo as $key) {
    $uss = '';
    foreach ($users[$key] as $u) {
      $uss .= $u.'</td><td>';
      if (array_key_exists($u, $top_users))
        $top_users[$u] += 1;
      else
        $top_users[$u] = 1;
      //$uss .= $u.', ';
    }
    if ($time_local - round(@$time[$key]) > 590) {
      array_push($not_responding, $key);
    } else {

      $myload = $load[$key][0];
      $myusers = count($users[$key]);
      if ($myusers < floatval($myload)*0.8) {
        // too much load, probably swapping, warn
        $myload = sprintf('<div style="color: maroon">%s</div>',$myload);
      }
      printf('<tr><td>%s</td><td><a href="#%s">%s</a></td><td>%s</td><td>%s</td><td><i>%s</i></td><td>%s</td></tr>',
              $c++, $key, $key, $cpu[$key], $mem[$key], $myload, substr($uss, 0, -2));
      //print('<tr><td>'.$key.'</td><td><a href="'.$value.'</td><td>'.$cpu[$value].'</td>
      //      <td>'.$key.'</td><td>'.$mem_keys[$key].'</td><td>'.$mem[$mem_keys[$key]].'</td><tr>');
    }
  }
}
print('</table> <p /></div>');

if (count($top_users) != 0) {
    arsort($top_users);
    print('<div class="right"><h3>Top users</h3><ol>');
    foreach ($top_users as $u => $c) {
        if ($u != '')
            print('<li><i>'.$u.'</i>: '.$c.' processes</li>');
    }
    print('</ol></div>');
}

// the non-resonding machines, including static ones
//if (true && count($not_responding) > 0) {
if (true) {
    sort($not_responding);
    print('<div class="left"><h3>Unavailable machines</h3><ul>');
    print('<li><i>pinac01-10</i>, reinstalled as desktop machines</li>');
    foreach($not_responding as $key) {
        printf('<li><a href="#%s">%s</a>, no data received since %s</li>',
                $key, $key, date('l jS \of F, G:i:s', round(@$time[$key])));
    }
    print('</ul><p></p></div>');
}

print('<div class="left"><h3>Detailed machine information</h3>');
ksort($output);
print('<table border="1">');
foreach ($output as $key => $value) {
    print('<tr><td colspan=6><a name="'.$key.'">&nbsp;</a></td></tr>');
    print($value);
}
print('</table></div>');


?>
</body>

</html>
