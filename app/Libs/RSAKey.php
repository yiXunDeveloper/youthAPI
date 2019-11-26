<?php
/**
 * Zeal Extends of ZendFramework
 *
 * @category   Zeal
 * @package    Zeal
 * @subpackage Security
 */

/**
 * RSA公钥格式转化
 *
 * @category   Zeal
 * @package    Zeal
 * @subpackage Security
 */
class Zeal_Security_RSAPublicKey
{
    /**
     * ASN.1 type INTEGER class
     */
    const ASN_TYPE_INTEGER = 0x02;

    /**
     * ASN.1 type BIT STRING class
     */
    const ASN_TYPE_BITSTRING = 0x03;

    /**
     * ASN.1 type SEQUENCE class
     */
    const ASN_TYPE_SEQUENCE = 0x30;

    /**
     * The Identifier for RSA Keys
     */
    const RSA_KEY_IDENTIFIER = '300D06092A864886F70D0101010500';

    /**
     * Constructor  (disabled)
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * Transform an RSA Key in x.509 string format into a PEM encoding and
     * return an PEM encoded string for openssl to handle
     *
     * @param string $certificate x.509 format cert string
     * @return string The PEM encoded version of the key
     */
    static public function getPublicKeyFromX509($certificate)
    {
        $publicKeyString = "-----BEGIN CERTIFICATE-----\n" .
            wordwrap($certificate, 64, "\n", true) .
            "\n-----END CERTIFICATE-----";

        return $publicKeyString;
    }

    /**
     * Transform an RSA Key in Modulus/Exponent format into a PEM encoding and
     * return an PEM encoded string for openssl to handle
     *
     * @param string $modulus The RSA Modulus in binary format
     * @param string $exponent The RSA exponent in binary format
     * @return string The PEM encoded version of the key
     */
    static public function getPublicKeyFromModExp($modulus, $exponent)
    {
        $modulusInteger  = self::_encodeValue($modulus,
            self::ASN_TYPE_INTEGER);
        $exponentInteger = self::_encodeValue($exponent,
            self::ASN_TYPE_INTEGER);
        $modExpSequence  = self::_encodeValue($modulusInteger .
            $exponentInteger,
            self::ASN_TYPE_SEQUENCE);
        $modExpBitString = self::_encodeValue($modExpSequence,
            self::ASN_TYPE_BITSTRING);

        $binRsaKeyIdentifier = pack( "H*", self::RSA_KEY_IDENTIFIER );

        $publicKeySequence = self::_encodeValue($binRsaKeyIdentifier .
            $modExpBitString,
            self::ASN_TYPE_SEQUENCE);

        $publicKeyInfoBase64 = base64_encode( $publicKeySequence );

        $publicKeyString = "-----BEGIN PUBLIC KEY-----\n";
        $publicKeyString .= wordwrap($publicKeyInfoBase64, 64, "\n", true);
        $publicKeyString .= "\n-----END PUBLIC KEY-----\n";

        return $publicKeyString;
    }

    /**
     * Encode a limited set of data types into ASN.1 encoding format
     * which is used in X.509 certificates
     *
     * @param string $data The data to encode
     * @param const $type The encoding format constant
     * @return string The encoded value
     * @throws Zend_InfoCard_Xml_Security_Exception
     */
    static protected function _encodeValue($data, $type)
    {
        // Null pad some data when we get it
        // (integer values > 128 and bitstrings)
        if( (($type == self::ASN_TYPE_INTEGER) && (ord($data) > 0x7f)) ||
            ($type == self::ASN_TYPE_BITSTRING)) {
            $data = "\0$data";
        }

        $len = strlen($data);

        // encode the value based on length of the string
        switch(true) {
            case ($len < 128):
                return sprintf("%c%c%s", $type, $len, $data);
            case ($len < 0x0100):
                return sprintf("%c%c%c%s", $type, 0x81, $len, $data);
            case ($len < 0x010000):
                return sprintf("%c%c%c%c%s", $type, 0x82,
                    $len / 0x0100,
                    $len % 0x0100, $data);
            default:
                throw
                new Zeal_Security_RSAPublicKey_Exception("Could not encode value",1);
        }

        throw
        new Zeal_Security_RSAPublicKey_Exception("Invalid code path",2);
    }
}

class Zeal_Security_RSAPublicKey_Exception extends Exception
{

}