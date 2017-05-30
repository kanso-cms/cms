<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\security\spam;

use kanso\framework\security\spam\gibberish\Gibberish;
use kanso\framework\config\Config;
use kanso\framework\utility\Str;

/**
 * SPAM manager
 *
 * @author Joe J. Howard
 */
class SpamProtector
{
    /**
     * Gibberish detector
     *
     * @var \kanso\framework\security\spam\Gibberish
     */
    private $gibberish;

    /**
     * Config loader
     *
     * @var \kanso\framework\config\Config
     */
    private $config;

    /**
     * Constructor
     *
     * @access public
     * @param \kanso\framework\security\spam\Gibberish $gibberish Gibberish detector
     * @param \kanso\framework\config\Config           $config    Config loader
     */
    public function __construct(Gibberish $gibberish, Config $config)
    {
        $this->gibberish = $gibberish;

        $this->config = $config;
    }

    /**
     * Checks if text is SPAM
     *
     * @access public
     * @param  string $text Text to check
     * @return bool
     */
    public function isSpam(string $text): bool
    {
        if ($this->listContains($this->config->get('spam.blacklist.constructs'), $text))
        {
            return true;
        }
        else if ($this->listContains($this->config->get('spam.blacklist.urls'), $text))
        {
            return true;
        }
        else if ($this->listContains($this->config->get('spam.blacklist.words'), $text))
        {
            return true;
        }
        else if ($this->listContains($this->config->get('spam.blacklist.html'), $text))
        {
            return true;
        }
        else if ($this->gibberish->test($text))
        {
            return true;
        }

        return false;
    }

    /**
     * Gets a SPAM rating
     *
     * @access public
     * @param  string $text Text to check
     * @return int
     */
    public function rating(string $text): int
    {
        $rating = 0;

        # Get statistics
        $linkCount      = $this->countLinks($text);
        $bodyCount      = strlen(trim($text));
        $keyWords       = $this->countGraylisted($text);
       
        # Rate links
        $linkCount > 2 ? $rating += -1 : $rating += 2;

        # Rate Length
        $bodyCount > 20 ? $rating += 2 : $rating += -1;

        # Keyword matches        
        $keyWords > 0 ? $rating += '-'.$keyWords : $rating;

        return $rating;
    }


    /**
     * Checks if an IP address is whitelisted
     *
     * @access public
     * @param  string $ipAddresses The IP address to check
     * @return bool
     */
    public function isIpWhiteListed(string $ipAddresses): bool
    {
        return $this->listContains($this->config->get('spam.whitelist.ipaddresses'), $ipAddresses);
    }

    /**
     * Checks if an IP address is blacklisted
     *
     * @access public
     * @param  string $ipAddresses The IP address to check
     * @return bool
     */
    public function isIpBlacklisted(string $ipAddresses): bool
    {
        return $this->listContains($this->config->get('spam.blacklist.ipaddresses'), $ipAddresses);
    }

    /**
     * Blacklists an ip address
     *
     * @access public
     * @param  string $ipAddresses The IP to blacklist
     */
    public function blacklistIpAddress(string $ipAddresses)
    {
        $this->config->set('spam.blacklist.ipaddresses', $this->addToList($ipAddresses, $this->config->get('spam.blacklist.ipaddresses')));

        $this->config->save();
    }

    /**
     * Remove an ip address from the blacklist
     *
     * @access public
     * @param  string $ipAddresses The IP to remove
     */
    public function unBlacklistIpAddress(string $ipAddresses)
    {
        $this->config->set('spam.blacklist.ipaddresses', $this->removeFromList($ipAddresses, $this->config->get('spam.blacklist.ipaddresses')));

        $this->config->save();
    }

    /**
     * whitelists an ip address
     *
     * @access public
     * @param  string $ipAddresses The IP to whitelist
     */
    public function whitelistIpAddress(string $ipAddresses)
    {
        $this->config->set('spam.whitelist.ipaddresses', $this->addToList($ipAddresses, $this->config->get('spam.whitelist.ipaddresses')));

        $this->config->save();
    }

    /**
     * Remove an ip address from the whitelist
     *
     * @access public
     * @param  string $ipAddresses The IP to remove
     */
    public function unWhitelistIpAddress(string $ipAddresses)
    {
        $this->config->set('spam.whitelist.ipaddresses', $this->removeFromList($ipAddresses, $this->config->get('spam.whitelist.ipaddresses')));

        $this->config->save();
    }

    /**
     * Check if a list contains a word
     *
     * @access private
     * @param  array   $list The array to check in
     * @param  string  $term The term to check for
     * @return bool
     */
    private function listContains(array $list, string $term): bool
    {
        foreach ($list as $item)
        {
            if ($item === $term || Str::contains($item, $term))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Add an item to a list
     *
     * @access private
     * @param  string  $item The item to add to the list
     * @param  array   $list The array to alter
     * @return array
     */
    private function addToList(string $item, array $list): array
    {
        $list[] = $item;

        $list = array_unique(array_values($list));

        sort($list);

        return $list;
    }

    /**
     * Remove an item from a list
     *
     * @access private
     * @param  string  $item The item to add to the list
     * @param  array   $list The array to alter
     * @return array
     */
    private function removeFromList(string $item, array $list): array
    {
        foreach ($list as $i => $value)
        {
            if ($value === $item)
            {
                unset($list[$i]);

                break;
            }
        }

        $list = array_unique(array_values($list));

        sort($list);

        return $list;
    }
    
    /**
     * Counts how many links are in text
     *
     * @access private
     * @param  string  $text Text to check
     * @return int
     */
    private function countLinks(string $text): int
    {
        $count = 0;

        preg_match_all('/<a[^>]+href=([\'"])(.+?)\1[^>]*>/', $text, $htmlLinks);

        preg_match_all('/http.+/', $text, $rawLinks);

        if (is_array($rawLinks) && isset($rawLinks[0]))
        {
            $count += count($rawLinks[0]);
        }

        if (is_array($htmlLinks) && isset($htmlLinks[0]))
        {
            $count += count($htmlLinks[0]);
        }

        return $count;
    }

    /**
     * Count how many graylisted words are in a string of text
     *
     * @access private
     * @param  string  $text Text to check
     * @return int
     */
    private function countGraylisted(string $text): int
    {
        $count = 0;

        $words = preg_split("/\s+/", $text);

        foreach ($words as $word)
        {
            $term = trim(preg_replace("/([\.\,\;\:\"\'!])/", "", $word));
            
            if (!empty($term) && $term !== '' && !in_array($term, ['!', '.', ',', '-', '&', ';', ':', '"', "'"]))
            {
                if (in_array($term, $this->config->get('spam.graylist.constructs')))
                {
                    $count++;
                }
                if (in_array($term, $this->config->get('spam.graylist.urls')))
                {
                    $count++;
                }
                if (in_array($term, $this->config->get('spam.graylist.words')))
                {
                    $count++;
                }
                if (in_array($term, $this->config->get('spam.graylist.html')))
                {
                    $count++;
                }
            }
        }
        
        return $count;
    }
}
