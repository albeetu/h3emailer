<?php
$r = new HttpRequest('http://h3u.mlspin.com/signin.asp', HttpRequest::METH_POST);
$r->addPostFields(array('user_name' => 'BB803643', 'pass' => 'F5B0!#'));
try {
    echo $r->send()->getBody();
} catch (HttpException $ex) {
    echo $ex;
}
?>>
