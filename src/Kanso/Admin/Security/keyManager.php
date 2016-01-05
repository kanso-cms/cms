<?php

namespace Kanso\Admin\Security;

/**
* Key Manager
*
* On all GET requests to the admin panel, an additional POST request
* is made from the client for their public key and salt. The key is
* a random string, encrpyted with another random string (salt) stored in the 
* clients's database entry.
*
* If the user is NOT logged in, (i.e register,login forgot password, etc... pages), the keys are 
* simply stored in the SESSION - as the user has no database entry at that point.
*
* When the client receives their public key and salt, the key must be decrypted 
* on the client side (in the browser) using a specified method.
*
* When any subsequent POST requests are made to the admin panel, they must
* be signed with the decrypted key.
*
* When the server receives the public key, it's compared to their existing
* decrypted public key (which should be the same).
*
* The keys are reset and changed every 12 hourse on GET requests. 
* 
* NOTE that this 2-way encryption formula is completely seperate from clients's
* password encryption, for obvious reasons.
*
*/
class KeyManager 
{

    /**
     * @var \Kanso\Database\Query\Builder
     */
    protected static $Query;

    /**
     * Get a clients public key and salt
     *
     * @return array|null
     */
    public static function getPublicKeys()
    {

        # Get the database instance
        if (!self::$Query) self::$Query = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Declare an array for the key variables
        $userKeys = [
            'KANSO_PUBLIC_KEY'  => null,
            'KANSO_PUBLIC_SALT' => null,
            'KANSO_KEYS_TIME'   => null,
        ];

        # If the user IS logged in get their public key
        # from the databse
        if (\Kanso\Admin\Security\sessionManager::isLoggedIn()) {

            $clientID    = \Kanso\Admin\Security\sessionManager::get('id');
            $clientEntry = self::$Query->SELECT('*')->FROM('authors')->where('id', '=', (int)$clientID)->FIND();

            # If it doesn't exist return false
            if ($clientEntry) {

                # If the keys were created more than 12 hours ago - reset them
                if ( time() - $clientEntry->get('kanso_keys_time') > 43200) return self::generateKeys(true);

                # Otherwise return the keys
                $userKeys['KANSO_PUBLIC_KEY']  = $clientEntry['kanso_public_key'];
                $userKeys['KANSO_PUBLIC_SALT'] = $clientEntry['kanso_public_salt'];
                $userKeys['KANSO_KEYS_TIME']   = $clientEntry['kanso_keys_time'];

                return $userKeys;
            }
        }

        # Otherwise if the user is NOT logged in grab the keys from the session
        $userKeys['KANSO_PUBLIC_KEY']  = \Kanso\Admin\Security\sessionManager::get('KANSO_PUBLIC_KEY');
        $userKeys['KANSO_PUBLIC_SALT'] = \Kanso\Admin\Security\sessionManager::get('KANSO_PUBLIC_SALT');
        $userKeys['KANSO_KEYS_TIME']   = \Kanso\Admin\Security\sessionManager::get('KANSO_KEYS_TIME');

        # If they are more than 12 hours ago - reset them
        if ((time() - $userKeys['KANSO_KEYS_TIME'] > 43200 )) return self::generateKeys();

        return $userKeys;

    }

    /**
     * Gegenerate the clients keys
     *
     * @param    bool      $saveToDB   Save the keys to the database (optional) Defaults to null
     * @return   array 
     */
    public static function generateKeys($saveToDB = null)
    {

        # Get the database instance
        if (!self::$Query) self::$Query = \Kanso\Kanso::getInstance()->Database()->Builder();

        $publicKey    = \Kanso\Utility\Str::generateRandom(850);
        $publicSalt   = \Kanso\Utility\Str::generateRandom(100);

        $encryptedKey = self::encrypt($publicKey, $publicSalt);

        $keys = [
            'KANSO_PUBLIC_KEY'  => $encryptedKey,
            'KANSO_PUBLIC_SALT' => $publicSalt,
            'KANSO_KEYS_TIME'   => time(),
        ];

        if ($saveToDB) self::saveKeysToDatabase($keys);

        return $keys;
    }

    /**
     * Save the keys to the database
     *
     * @param    keys      self::generateKeys(true)
     * @return   boolean
     */
    public static function saveKeysToDatabase($keys) 
    {

        # Get the database instance
        if (!self::$Query) self::$Query = \Kanso\Kanso::getInstance()->Database()->Builder();

        $clientID = \Kanso\Admin\Security\sessionManager::get('id');

        $clientRow = self::$Query->SELECT('*')->FROM('authors')->WHERE('id', '=', (int)$clientID )->FIND();

        if (!$clientEntry->matchedQuery()) return false;

        $clientEntry->kanso_public_key  = $keys['KANSO_PUBLIC_KEY'];
        $clientEntry->kanso_public_salt = $keys['KANSO_PUBLIC_SALT'];
        $clientEntry->kanso_keys_time   = time();
        $clientEntry->save();

        return true;

    }

    /**
     * Compare the client-side-decrypted key to server-side version
     *
     * @param    string    $decyptedKey
     * @return   boolean   
     */
    public static function authenticateSignature($decyptedKey) 
    {

        $clientKeys = self::getPublicKeys();

        if ($clientKeys['KANSO_PUBLIC_KEY'] === null || $clientKeys['KANSO_PUBLIC_SALT'] === null) return false;

        return $decyptedKey === self::decrypt($clientKeys['KANSO_PUBLIC_KEY'], $clientKeys['KANSO_PUBLIC_SALT']);

    }

    /**
     * Encrypt key with salt
     *
     * @param     string    $sData
     * @param     string    $sKey
     * @return    string 
     */
    public static function encrypt($sData, $sKey)
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
    public static function decrypt($sData, $sKey) 
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
    public static function encode_base64($sData)
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
    public static function decode_base64($sData)
    { 
        $sBase64 = strtr($sData, '-_', '+/'); 
        return base64_decode($sBase64); 
    }  

}