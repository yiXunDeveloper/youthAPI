<?php
/**
 * Created by PhpStorm.
 * User: hasee
 * Date: 2019/1/24
 * Time: 20:52
 */
use App\Libs\Base64;
include "Base64.php";
//$jar = new \GuzzleHttp\Cookie\CookieJar;
$guzzle = new \GuzzleHttp\Client(['cookies'=>'true']);
$guzzle->request('GET','http://hqfw.sdut.edu.cn/');
$r = $guzzle->request('GET','http://hqfw.sdut.edu.cn/login.aspx');
echo $r->getBody();

