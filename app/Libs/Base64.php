<?php
/**
 * Created by PhpStorm.
 * User: hasee
 * Date: 2019/1/24
 * Time: 20:10
 */

namespace App\Libs;


class Base64
{
    private static $b64map="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    private static $b64pad="=";


    public static function hex2b64($h) {
        $ret = "";
        for($i = 0; $i+3 <= strlen($h); $i+=3) {
            $c = intval(substr($h,$i,3),16);
            $ret .= self::$b64map[($c >> 6)] . self::$b64map[($c & 63)];
        }
        if($i+1 == strlen($h)) {
            $c = intval(substr($h,$i,1),16);
            $ret .= self::$b64map[($c << 2)];
        }
        else if($i+2 == strlen($h)) {
            $c = intval(substr($h,$i,2),16);
            $ret .= self::$b64map[($c >> 2)] . self::$b64map[(($c & 3) << 4)];
        }
        while((strlen($ret) & 3) > 0)
            $ret .= self::$b64pad;
        return $ret;
    }
    public static function b64tohex($s) {
        $k = 0;  // b64 state, 0-3
        $ret = "";
        $slop = 0;
        for ($i=0;$i<strlen($s);$i++) {
            if ($s[$i] == self::$b64pad) break;
            $v = strpos(self::$b64map,$s[$i]);
            if ($v === false) continue;
            if ($k == 0) {
                $ret .= self::int2char($v >> 2);
                $slop = $v & 3;
                $k = 1;
            }else if ($k == 1) {
                $ret .= self::int2char(($slop << 2) | ($v >> 4) );
                $slop = $v & 0xf;
                $k = 2;
            }else if ($k == 2) {
                $ret .= self::int2char($slop);
                $ret .= self::int2char($v >> 2);
                $slop = $v & 3;
                $k = 3;
            }else {
                $ret .= self::int2char(($slop << 2) | ($v >> 4));
                $ret .= self::int2char($v & 0xf);
                $k = 0;
            }
            echo "$slop -----------$k\n";
        }
        if ($k == 1)
            $ret .= self::int2char($slop << 2);
        return $ret;
    }
    public function b64toBA($s) {
        $h = self::b64tohex($s);
        $a = array();
        for ($i=0;2*$i < strlen($h);$i++) {
            $a[$i] = intval(substr($h,2*$i,2),16);
        }
        return $a;
    }
    protected static function int2char($n) {
        if (0<=$n && $n<=9) {
            return $n;
        }else if (9 < $n && $n <=35){
            return chr($n+87);
        }else {
            return "";
        }
    }
}