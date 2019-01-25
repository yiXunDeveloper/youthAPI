<?php
/**
 * Created by PhpStorm.
 * User: hasee
 * Date: 2019/1/25
 * Time: 18:23
 */

namespace App\Libs;


use http\Exception\RuntimeException;

class RSAKey
{
    private $n;
    private $e;
    private $d;
    private $p;
    private $q;
    private $dmp1;
    private $dmq1;
    private $coeff;

    /**
     * RSAKey constructor.
     * @param $n
     * @param $e
     * @param $d
     * @param $p
     * @param $q
     * @param $dmp1
     * @param $dmq1
     * @param $coeff
     */
    public function __construct()
    {
        $this->n = null;
        $this->e = 0;
        $this->d = null;
        $this->p = null;
        $this->q = null;
        $this->dmp1 = null;
        $this->dmq1 = null;
        $this->coeff = null;
    }
    public function doPublic() {

    }
    public function setPublic($n,$e) {
        if ($n != null && $e != null && strlen($n) >0 && strlen($e) >0) {
            $this->n = intval($n,16);
            $this->e = intval($e,16);
        }else {
            throw new RuntimeException("Invalid RSA public key");
        }
    }
    public function encrypt($text) {
        $m = $this->pkcs1pad2($text,)
    }
    private function pkcs1pad2($s,$n) {
        if ($n < strlen($s) + 11) {
            throw new RuntimeException("Message too long for RSA");
        }
        $ba = array();
        $i = strlen($s)-1;
        while ($i >= 0 && $n >0) {

        }
    }

}