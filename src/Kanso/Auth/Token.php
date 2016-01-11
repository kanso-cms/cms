<?php

namespace Kanso\Auth;

/**
* Ajax Token 
*
* This class creates and validated Kanso's Ajax token
* validation
*
*/
class Token 
{

    /**
     * Gegenerate an ajax Token
     *
     * @return   array 
     */
    public static function generate()
    {

        $key = \Kanso\Utility\Str::generateRandom(100);
        
        $salt = \Kanso\Utility\Str::generateRandom(21);

        $encryptedKey = self::encrypt($key, $salt);

        $keys = [
            'key'  => $encryptedKey,
            'salt' => $salt,
        ];

        return $keys;
    }

    /**
     * Compare the client-side-decrypted key to server-side version
     *
     * @param    string    $token
     * @param    string    $key
     * @param    string    $salt
     * @return   boolean   
     */
    public static function verify($token, $key, $salt) 
    {
        return utf8_encode(self::decrypt($key, $salt)) === $token;
    }

    /**
     * Encrypt key with salt
     *
     * @param     string    $sData
     * @param     string    $sKey
     * @return    string 
     */
    private static function encrypt($sData, $sKey)
    { 
        $sResult = ''; 
        for($i=0;$i<strlen($sData);$i++){ 
            $sChar    = substr($sData, $i, 1); 
            $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1); 
            $sChar    = chr(ord($sChar) + ord($sKeyChar)); 
            $sResult .= $sChar; 
        } 
        return self::encode_base64($sResult); 
    } 

    /**
     * Decrypt key with salt
     *
     * @param     string    $sData
     * @param     string    $sKey
     * @return    string 
     */
    private static function decrypt($sData, $sKey) 
    { 
        $sResult = ''; 
        $sData   = self::decode_base64($sData); 
        for($i=0;$i<strlen($sData);$i++){ 
            $sChar    = substr($sData, $i, 1); 
            $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1); 
            $sChar    = chr(ord($sChar) - ord($sKeyChar)); 
            $sResult .= $sChar; 
        } 
        return $sResult; 
    }

    /**
     * Encode base64
     *
     * @param     string    $sData
     * @return    string 
     */
    private static function encode_base64($sData)
    { 
        $sBase64 = base64_encode($sData);
        return strtr($sBase64, '+/', '-_'); 
    }

    /**
     * Decode base64
     *
     * @param     string    $sData
     * @return    string 
     */
    private static function decode_base64($sData)
    { 
        $sBase64 = strtr($sData, '-_', '+/'); 
        return base64_decode($sBase64); 
    }  

}