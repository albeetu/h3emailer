<?php

$row = 1;
$batches = 250;
$batch = 0;
$totalrecs = 0;
$used = array();
$files = array("A-D.txt","E-K.txt","L-R.txt","S-Z.txt","FSBO.txt");
$unsubscribe = "unsubscribe.txt";
$scrubList = array();
$remove = array();
$removed = 0;
$bList = array();
$blistcount = 0;

if (($handle = fopen($unsubscribe,"r")) !== FALSE)
{
  echo "\nLoading scrublist...\n";
  while (($uList = fgetcsv($handle, 100000000, ",")) !== FALSE)
  {
    $num = count($uList);
    $scrubList = $uList;
  }
  echo $num ." IDs blacklisted\n";
  $num = 0;
  fclose($handle);
}

for ($f=0; $f < count($files); $f++)
{
  if (($handle = fopen($files[$f], "r")) !== FALSE) 
  {
    echo "\nProcessing ".$files[$f]."...\n";
    while (($data = fgetcsv($handle, 10000000, ",")) !== FALSE) 
    {
        $num = count($data);
        print_r($data);
        echo "\n$num in this batch\n\n";
        $totalrecs += $num;
        $row++;
        for ($c=0; $c < $num; $c++) 
        {
            if ($c % $batches == 0)
            {
               echo "http://h3n.mlspin.com/Email/SendClientEmail.asp?ClientId=";
            }
            if (in_array($data[$c],$scrubList))
            {
                // List maintenance needed. Not sure what to do yet.
                $removed++;
                array_push($remove,$data[$c]);
                $totalrecs--;
            }
            else
            {
                echo $data[$c] . ","; // correctly formed data
            }
            if (in_array($data[$c],$used))
            {
                echo "repeat record ".$data[$c]."\n";
            }
            else
            {
                array_push($used,$data[$c]);
            }
            if ($c % $batches == $batches-1)
            {
               echo "\n\n";
               $batch++;
            }
        }
    }
    fclose($handle);
  echo "\n\n";
  echo "$totalrecs records produced\n";
  echo "$batch records bundled\n";
  echo "$removed additional records blacklisted\n";
  }
}
?>
