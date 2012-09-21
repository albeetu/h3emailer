<?php

//$userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.24 (KHTML, like Gecko) Chrome/19.0.1055.1 Safari/535.24';
$userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:7.0.1) Gecko/20100101 Firefox/7.0.1';
$emailRequestUrl = 'http://h3e.mlspin.com/Email/ACTION_SendClientEmail.asp';
$emailFile = "emails.txt";
$emailList = array();
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

if (is_readable("messagetosend.txt"))
{
  preg_match_all('/(?P<subject>[\w\d\s\S]+)={10}(?P<body>[\w\d\s\S]+)/',file_get_contents("messagetosend.txt"),$matches);
  $messageSubject = trim($matches['subject'][0]);
  $messageToSend = trim(str_replace("\n","\r\n",$matches['body'][0]));
  //echo $messageToSend;
}
else
{
 // this read failed
 exit;
}
// Get clientIDs
if (($emails = fopen($emailFile,"r")) !== FALSE)
{
  echo "=====Capturing ClientIDs======\n";
  while (($eList = fgetcsv($emails, 100000000, ",")) !== FALSE)
  {
    $num += count($eList);
    foreach($eList as $id)
    {
      if (is_numeric($id))
      {
        $batch .= "'".$id."',";
      }
    }
    array_push($emailList,substr($batch,0,-1));
    $batch = array();
  }
 print_r($emailList);
exit;
//  $emailList = array("'2818377','1591841'","'2818377','1591841'","'2818377','1591841'");
  echo $num ." Client Ids ready to be emailed\n";
}
else
{
  echo $emailFile . "can't be opened\n";
  exit(7);
}
  
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

// First response should be a 302. But cookie is now captured. Start sending emails

if ($resp->getResponseCode() == 302)
{
// Test the login 

$headers = $r->getResponseHeader();
$test = new HttpRequest('http://h3e.mlspin.com/Email/SendClientEmail.asp?ClientId=2818377',HttpRequest::METH_GET);
$test->setHeaders(array('User-Agent' => $userAgent,
                        'Cookie' => implode("; ",$headers['Set-Cookie'])));
try
{
  $testResp = $test->send();
} catch (HttpException $ex)
{
  echo $ex;
  exit;
}
echo $test->getRequestMessage()."\n";
echo $test->getResponseMessage()."\n";

//prepare file to open
      foreach ($emailList as $batch)
      {
  //build request
      $emailRequest = new HttpRequest($emailRequestUrl,HttpRequest::METH_POST);
      $requestHeaders = array('Host' => 'h3e.mlspin.com',
                              'User-Agent' => $userAgent,
                              'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                              'Accept-Language' => 'en-us,en;q=0.5',
                              'Accept-Encoding' => 'gzip,deflate',
                              'Accept-Charset' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                              'Proxy-Connection' => 'keep-alive',
                              'Referer' => 'http://h3e.mlspin.com/Email/SendClientEmail.asp?ClientId=2818377',
                              'Cookie' => implode("; ",$headers['Set-Cookie']),
                              'Content-Type' => 'application/x-www-form-urlencoded');
      $emailRequest->setHeaders($requestHeaders);
      $clientIds = $batch;
      print_r($batch);
      $postFields = array('ClientId'=>$clientIds,
                          'from'=>'threadllc@gmail.com',
                          'subject'=> $messageSubject,
                          'message'=> $messageToSend);
      $emailRequest->setPostFields($postFields);
      try
      {
        //$emailsend = $emailRequest->send();
      }catch (HttpException $ex)
      {
        echo $ex;
      }
      echo $emailRequest->getRawRequestMessage()."\n==============================================\n";
      echo $emailRequest->getRawResponseMessage()."\n";
      $emailRequest = null;
   }
   exit;
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
