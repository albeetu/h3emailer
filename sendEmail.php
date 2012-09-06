<?php

$row = 1;
$batches = 200;
$batch = 0;
$totalrecs = 0;
$used = array();
$files = array("A-D.txt","E-K.txt","L-R.txt","S-Z.txt","FSBO.txt");

for ($f=0; $f < count($files); $f++)
{
  if (($handle = fopen($files[$f], "r")) !== FALSE) 
  {
    echo "\nProcessing ".$files[$f]."...\n";
    while (($data = fgetcsv($handle, 10000000, ",")) !== FALSE) 
    {
        $num = count($data);
        echo "\n\n$num in this batch\n\n";
        $totalrecs += $num;
        $row++;
        for ($c=0; $c < $num; $c++) 
        {
            if ($c % $batches == 0)
            {
               echo "http://h3n.mlspin.com/Email/SendClientEmail.asp?ClientId=";
            }
            echo $data[$c] . ",";
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
  }
}
?>
