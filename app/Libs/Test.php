<?php
/**
 * Created by PhpStorm.
 * User: hasee
 * Date: 2019/1/24
 * Time: 20:52
 */
use App\Libs\Base64;
include "Base64.php";
include "RSAKey.php";
$string = Zeal_Security_RSAPublicKey::getPublicKeyFromModExp(Base64::b64tohex("AJtoTGJn/Xhsn8C7pRUadgS/xP+gfcVo3HhpQ4poMrzRHUJYoszCiSS/PNp6sNSJ2a3JuJp7XuhH5NOU0F7I/zinCHkuruTeUEtWmZQYakjUVQLSuB/PXKC789+47q9JzS2inlr6XgdKagMS4+SJS4tBx0fsPIE4d6lcNbvBLSHJ"),
    Base64::b64tohex("AQAB"));
//$string = Zeal_Security_RSAPublicKey::getPublicKeyFromModExp("AJtoTGJn/Xhsn8C7pRUadgS/xP+gfcVo3HhpQ4poMrzRHUJYoszCiSS/PNp6sNSJ2a3JuJp7XuhH5NOU0F7I/zinCHkuruTeUEtWmZQYakjUVQLSuB/PXKC789+47q9JzS2inlr6XgdKagMS4+SJS4tBx0fsPIE4d6lcNbvBLSHJ","AQAB");
function rsa_pub_encrypt($data, $rsaPublicKey) {


    /* 从PEM文件中提取公钥 */
    $res = openssl_get_publickey($rsaPublicKey);

    /* 对数据进行加密 */
    openssl_public_encrypt($data, $encrypted, $res);

    /* 释放资源 */
    openssl_free_key($res);

    /* 对签名进行Base64编码，变为可读的字符串 */
//    $encrypted = base64_encode($encrypted);

    return $encrypted;
}
//echo Base64::b64tohex("AJtoTGJn/Xhsn8C7pRUadgS/xP+gfcVo3HhpQ4poMrzRHUJYoszCiSS/PNp6sNSJ2a3JuJp7XuhH5NOU0F7I/zinCHkuruTeUEtWmZQYakjUVQLSuB/PXKC789+47q9JzS2inlr6XgdKagMS4+SJS4tBx0fsPIE4d6lcNbvBLSHJ")."\n";

$s =  rsa_pub_encrypt("hu16111101135",$string);
$c_safe = bin2hex($s);
//echo $c_safe;
//echo Base64::hex2b64($c_safe);
echo Base64::b64tohex("AQAB");