<?php

namespace Kanso\Comments\Spam;

/**
 * Spam Protector
 *
 * This class is responsible for validating and checking for spam
 * when new comments are added to an article.
 *
 */
class SpamProtector
{
    /**
     *
     * @var array
     */
    private $blacklistTerms;

    /**
     *
     * @var array
     */
    private $blacklistConstructs;
    
    /**
     *
     * @var array
     */
    private $blacklistHTML;

    /**
     *
     * @var array
     */
    private $blacklistIP;

    /**
     *
     * @var array
     */
    private $greylistKeyWords;

    /**
     *
     * @var array
     */
    private $blacklistURLshorteners;

    /**
     *
     * @var array
     */
    private $greylistDomains;

    /**
     *
     * @var array
     */
    private $greylistOpenings;

    /**
     *
     * @var string
     */
    private $commentName;

    /**
     *
     * @var string
     */
    private $rawComment;

    /**
     *
     * @var string
     */
    private $HTMLcomment;

    /**
     *
     * @var string
     */
    private $commentEmail;

    /**
     *
     * @var string
     */
    private $commentIP;

     /**
     *
     * @var int
     */
    private $spamRating = 0;

    /**
     * Constructor
     *
     * Set the current values from the comment and load dictionaries
     *
     * @param  string    $commentName     The name of the person commenting
     * @param  string    $commentEmail    The email address of the person commenting
     * @param  string    $rawComment      The raw comment content
     * @param  string    $HTMLcomment     The comment content converted to HTML
     */
    public function __construct($commentName, $commentEmail, $rawComment, $HTMLcomment)
    {

        # Load comment and client data
        $this->commentName  = strtolower($commentName);
        $this->commentEmail = strtolower($commentEmail);
        $this->rawComment   = strtolower($rawComment);
        $this->HTMLcomment  = strtolower($HTMLcomment);
        $this->commentIP    = $this->getClientIP();
        
        # Load dictionaries
        $this->blacklistTerms         = $this->loadDictionary('blacklist_words');
        $this->blacklistConstructs    = $this->loadDictionary('blacklist_constructs');
        $this->blacklistHTML          = $this->loadDictionary('blacklist_html');
        $this->blacklistIP            = $this->loadDictionary('blacklist_ip');
        $this->blacklistURLshorteners = $this->loadDictionary('blacklist_urlShorteners');
        $this->greylistKeyWords       = $this->loadDictionary('greylist_keywords');
        $this->greylistOpenings       = $this->loadDictionary('greylist_openings');
        $this->greylistDomains        = $this->loadDictionary('greylist_domains');
        $this->whitelistIP            = $this->loadDictionary('whitelist_ip');

    }

    /**
     * Load a dictionary and return it's content as an array 
     *
     * @param  string    $dictionary    The name of the dictionary file to load
     * @return array
     */
    public static function loadDictionary($dictionary)
    {

        $data    = [];
        $file    = __DIR__.DIRECTORY_SEPARATOR.'Dictionaries'.DIRECTORY_SEPARATOR.$dictionary.'.txt';

        if (file_exists($file) && is_file($file)) {
            $content = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'Dictionaries'.DIRECTORY_SEPARATOR.$dictionary.'.txt');
            $data    = array_map('strtolower', explode("\n", $content));
        }

        return array_map('trim', $data);

    }

    /**
     * Append a value to a dictionary
     *
     * @param  string   $term              The term to be added to the dictionary
     * @param  string   $dictionary        The name of the dictionary file to load
     * @return array
     */
    public static function appendToDictionary($term, $dictionary)
    {

        $file = __DIR__.DIRECTORY_SEPARATOR.'Dictionaries'.DIRECTORY_SEPARATOR.$dictionary.'.txt';
       
        if (file_exists($file) && is_file($file)) {
            $dictionary   = self::loadDictionary($dictionary);
            $dictionary[] = strtolower($term);
            $dictionary   = array_unique($dictionary);
            sort($dictionary);
            file_put_contents($file, implode("\n",$dictionary));
        }

    }

    /**
     * Remove a value from a dictionary
     *
     * @param  string   $term              The term to be added to the dictionary
     * @param  string   $dictionaryName    The name of the dictionary file to load
     * @return array
     */
    public static function removeFromDictionary($term, $dictionary)
    {

        $file = __DIR__.DIRECTORY_SEPARATOR.'Dictionaries'.DIRECTORY_SEPARATOR.$dictionary.'.txt';
        
        if (file_exists($file) && is_file($file)) {
            
            $dictionary = self::loadDictionary($file);

            $list       = [];
            foreach ($dictionary as $entry) {
                if ($entry !== $term) $list[] = $entry;
            }
            
            $list = array_unique($list);
            sort($list);
            file_put_contents($file, implode("\n",$list));
        }

    }

    /**
     * Get the clients IP Address
     *
     * @return string
     */
    public function getClientIP() 
    {

        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;

    }

    /**
     * Is the currnt comment considered SPAM ?
     *
     * @return bool
     */
    public function isSPAM()
    {
        
        if ($this->containsBlackListWords()) return true;
        if ($this->containsConstructs()) return true;
        if ($this->containsCodeBlacklist()) return true;
        if ($this->containsShorteners()) return true;
        if ($this->invalidName()) return true;
        if ($this->invalidEmail()) return true;
        if ($this->containsGibberish($this->rawComment)) return true;
        return false;

    }

    /**
     * Is the currnt IP address whitelisted ?
     *
     * @return bool
     */
    public function isWhiteListedIP()
    {
        return in_array($this->commentIP, $this->whitelistIP);
    }

    /**
     * Is the currnt IP address blacklisted ?
     *
     * @return bool
     */
    public function isBlacklistedIP()
    {
        return in_array($this->commentIP, $this->blacklistIP);
    }

    /**
     * Does the comment contain blacklisted words ?
     *
     * @return bool
     */
    private function containsBlackListWords()
    {

        $words = preg_split("/\s+/", $this->rawComment);
        foreach ($words as $word) {
            $term = trim(preg_replace("/([\.\,\;\:\"\'!])/", "", $word));
            if (!empty($term) && $term !== '' && !in_array($term, ['!', '.', ',', '-', '&', ';', ':', '"', "'"])) {
                if (in_array($term, $this->blacklistTerms)) return true;
            }
        }
        return false;
        
    }

    /**
     * Does the comment contain blacklisted phrases ?
     *
     * @return bool
     */
    private function containsConstructs()
    {

        foreach ($this->blacklistConstructs as $construct) {
            $pattern = str_replace(['{','}'], ['(',')'], $construct);
            if (preg_match("/$pattern/", $this->rawComment))  return true;
        }
        return false;

    }

    /**
     * Does the comment contain blacklisted code ?
     *
     * @return bool
     */
    private function containsCodeBlacklist()
    {

        foreach ($this->blacklistHTML as $term) {
            $pattern = preg_quote($term);
            if (preg_match("/$pattern/", $this->HTMLcomment))  return true;
        }
        return false;

    }

    /**
     * Does the comment contain blacklisted URL shorteners ?
     *
     * @return bool
     */
    private function containsShorteners()
    {

        foreach ($this->blacklistURLshorteners as $urlShortener) {
            $pattern = preg_quote($urlShortener);
            if (preg_match("/$pattern\/.+/", $this->rawComment))  return true;
        }
        return false;

    }

    /**
     * Does the comment name contain gibberish ?
     *
     * @return bool
     */
    private function invalidName()
    {
        return $this->containsGibberish($this->commentName);  
    }

    /**
     * Is the comment email valid / Does it contain gibberish ?
     *
     * @return bool
     */
    private function invalidEmail()
    {

        $domain  = substr(strrchr($this->commentEmail, "@"), 1);
        $domain  = strtolower(current(explode('.', $domain)));
        $address = strtolower(current(explode('@', $this->commentEmail)));

        if ($this->containsGibberish($address) || $this->containsGibberish($domain)) return true;

        return false;
    }

    /**
     * Check if a string contains gibberish
     *
     * @param  string   $text
     * @return bool
     */
    private function containsGibberish($text) 
    {

        $matrix_path = __DIR__.DIRECTORY_SEPARATOR.'Dictionaries'.DIRECTORY_SEPARATOR.'Gibberish.txt';
        $matrix      = unserialize(file_get_contents($matrix_path));
        return \Kanso\Comments\Spam\Dictionaries\Gibberish::test($text, $matrix_path, false);
        
    }

    /**
     * Get the spam rating based on points system
     *
     * @return bool
     */
    public function getSPAMrating()
    {
        # Get statistics
        $linkCount      = $this->countLinks();
        $bodyCount      = strlen(trim($this->rawComment));
        $keyWords       = $this->countKeyWordMatches();
        $bodyTLDFlags   = $this->TLDflags();
        $emailTLDFlag   = $this->emailTLDflags();
        $startsWithFlag = $this->startsWithFlag(); 

        # Rate links
        $linkCount > 2 ? $this->spamRating += -1 : $this->spamRating += 2;

        # Rate Length
        $bodyCount > 20 ? $this->spamRating += 2 : $this->spamRating += -1;

        # Keyword matches
        if ($keyWords > 0) $this->spamRating += '-'.$keyWords;

        # Body TLD Flags
        if ($bodyTLDFlags) $this->spamRating += -1;

        # Email TLD Flags
        if ($emailTLDFlag) $this->spamRating += -1;

        # Starts with a flag
        if ($startsWithFlag) $this->spamRating += -10;

        return $this->spamRating;
    }

    /**
     * How many links are in the comment body
     *
     * @return int
     */
    private function countLinks()
    {

        preg_match_all('/<a[^>]+href=([\'"])(.+?)\1[^>]*>/', $this->HTMLcomment, $linkTags);

        if (is_array($linkTags)) $linkTags = array_filter($linkTags);

        if (!empty($linkTags)) return count($linkTags[0]);

        return 0;

    }

    /**
     * How many greylisted words does the comment have ?
     *
     * @return int
     */
    private function countKeyWordMatches()
    {
        $words = preg_split("/\s+/", $this->rawComment);
        $count = 0;
        foreach ($words as $word) {
            if (in_array($word, $this->greylistKeyWords)) $count++;
        }
        return $count;

    }

    /**
     * Does the comment contain greylisted domain links ? e.g .cn .xxx etc..
     *
     * @return bool
     */
    private function TLDflags()
    {

        foreach ($this->greylistDomains as $tld) {
            if (strpos($this->rawComment, '.'.$tld) !== false) return true;
        }
        
        return false;

    }

    /**
     * Does the comment email contain greylisted domain names ? e.g .cn .xxx etc..
     *
     * @return bool
     */
    private function emailTLDflags()
    {
        $domain  = substr($this->commentEmail, strrpos($this->commentEmail, '.') + 1);
        foreach ($this->greylistDomains as $tld) {
            if (trim(strtolower($domain)) === $tld) return true;
        }
        return false;
    }

    /**
     * Does the comment start with a greylisted word ? e.g amazing!, Awesome! etc..
     *
     * @return bool
     */
    private function startsWithFlag()
    {
        $firstWord = trim(strtolower(strtok($this->rawComment, " ")));

        foreach ($this->greylistOpenings as $flag) {
            if ($firstWord === $flag) return true;
        }
        
        return false;
    }

}