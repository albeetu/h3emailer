<?php


$userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.24 (KHTML, like Gecko) Chrome/19.0.1055.1 Safari/535.24';
$folderids = array("10619","765","766","767","9432","1077");
$foldernames = array("A-D.txt","E-K.txt","L-R.txt","S-Z.txt","unsubscribe.txt","FSBO.txt");

// Read username/password file

if (($userpass = fopen("userpass.txt","r")) !== FALSE)
{
  $username = trim(fgets($userpass));
  $password = trim(fgets($userpass));
//  echo $username ."\n";
//  echo $password ."\n";

}
else
{
  echo "Need a userpass.txt file. Exiting\n";
  fclose($userpass);
  exit(2);
}
fclose($userpass);

// Create inital logon

$initReq = new HttpRequest('http://h3e.mlspin.com/signin.asp', HttpRequest::METH_POST);
$initReq->setHeaders(array('User-Agent' => $userAgent));
try
{
  $resp = $initReq->send();
} catch (HttpException $ex)
{
  echo $ex.": Failed due to connection issue.";
  exit(4);
}

// Send login 

preg_match_all('/INPUT name="(\w+)"/',$initReq->getResponseMessage(),$matches);


$r = new HttpRequest('http://h3e.mlspin.com/validate_new.asp', HttpRequest::METH_POST);
$r->setHeaders(array('User-Agent' => $userAgent));
$r->setPostFields(array($matches[1][0] => '0', 'Page_Loaded' =>'0','user_name' => $username, 'pass' => $password, 'signin' => 'Sign In'));
try 
{
  $resp = $r->send();
} catch (HttpException $ex) 
{
    echo $ex.": Logon failed due to connection issue.";
    exit(4);
}

// First response should be a 302. But cookie is now captured. Start harvesting

if ($resp->getResponseCode() == 302)
{
//  echo $r->getRequestMessage()."\n";
//  echo $r->getResponseMessage()."\n";
  $headers = $r->getResponseHeader();

  for ($i =0; $i < count($folderids); $i++)
  {
  //prepare file to open
    if (($handle = fopen($foldernames[$i],"w")) !== FALSE)
    {
  //build request
      $getFolder = new HttpRequest('http://h3e.mlspin.com/clients/index.asp?changefolder='.$folderids[$i],HttpRequest::METH_GET);
      $requestHeaders = array('Host' => 'h3e.mlspin.com',
                              'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.24 (KHTML, like Gecko) Chrome/19.0.1055.1 Safari/535.24',
                              'Referer' => 'http://h3e.mlspin.com/validate_new.asp',
                              'Cookie' => implode("; ",$headers['Set-Cookie']));
      $getFolder->setHeaders($requestHeaders);
    
      try
      {
        $folderMessage = $getFolder->send();
      }catch (HttpException $ex)
      {
        echo $ex;
      }
      //echo $getFolder->getResponseMessage()."\n";
      preg_match_all('/<a href="details.asp\?cid=(\d+)/',$getFolder->getResponseMessage(),$list);
      echo '--------------------Capture for '.$foldernames[$i]."--------------------\n";
      echo $getFolder->getUrl()."\n";
      echo count($list[1]).' IDs for '.$foldernames[$i]."\n";
//      echo implode(",",$list[1]);
      fwrite($handle,implode(",",$list[1])); 
      echo '--------------------Completed for '.$foldernames[$i]."------------------\n";
      //print_r($list);
     }
     else
     {
       //file didn't open
     }
     fclose($handle); 
  }
}
else
{
  echo "Login unsuccessful\n";
  exit(3);
}
/*
$testLoggedin = new HttpRequest('http://h3e.mlspin.com/index.asp',HttpRequest::METH_GET);
$testLoginHeaders = array('Host' => 'h3e.mlspin.com',
                          'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.24 (KHTML, like Gecko) Chrome/19.0.1055.1 Safari/535.24',
                          'Cookie' => implode("; ",$headers['Set-Cookie']));
$testLoggedin->setHeaders($testLoginHeaders);
// $testLoggedin->enableCookies();
try{
   $testLoggedinResp = $testLoggedin->send();
}catch (HttpException $ex)
{
  echo $ex;
}
*/
//echo "--------------Test Request--------------------\n";
//echo $redir->getRequestMessage()."\n";
//echo "--------------Tes Response------------------\n";
//echo $redir->getResponseMessage()."\n";

//echo "--------------Extract from unsubscribe-------\n";
//preg_match_all('/<a href="details.asp\?cid=(\d+)/',$redir->getResponseMessage(),$unsubscribelist);
//preg_match_all('/<a href="details.asp?cid={\d+}"/',$redir->getResponseMessage(),$unsubscribelist);
//print_r($unsubscribelist[1]);
//echo count($unsubscribelist[1])." ids on the unsubscribe list\n";
//echo implode(",",$unsubscribelist[1]);


/*
$getFolder = new HttpRequest('http://h3e.mlspin.com/clients/index.asp?changefolder=9432',HttpRequest::METH_GET);
$getFolderHeders = array('Host' => 'h3e.mlspin.com',
                          'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.24 (KHTML, like Gecko) Chrome/19.0.1055.1 Safari/535.24',
                          'Referer' => 'http://h3e.mlspin.com/clients/index.asp?clear=yes',
                          'Cookie' => implode("; ",$headers['Set-Cookie']));
$getFolder->setHeaders($getFolderHeaders);
$getFolder->enableCookies();
try
{
  $getFolderResp = $getFolder->send();
}catch (HttpException $ex){
  echo $ex;
}

echo "-------------Final Request--------------------\n";
echo $getFolder->getRequestMessage()."\n";
echo "-------------Final Response-------------------\n";
echo $getFolder->getResponseMessage()."\n";


echo "------------Test Request---------------------\n";
echo $redir->getRequestMessage()."\n";
echo "------------Test Response--------------------\n";
echo $redir->getResponseMessage()."\n";
*/
?>
