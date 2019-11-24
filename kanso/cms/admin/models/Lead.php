<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\framework\utility\Str;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;

/**
 * Admin lead model.
 *
 * @author Joe J. Howard
 */
class Lead extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        return $this->parseGet();
    }

    /**
     * {@inheritdoc}
     */
    public function onPOST()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onAJAX()
    {
        // Process any AJAX requests here
        //
        // Returning an associative array will
        // send a JSON response to the client

        // Returning false sends a 404
        return false;
    }

    /**
     * Parse get request to find lead info.
     *
     * @return array|false
     */
    private function parseGet()
    {
        // Find lead
        $visitorId = explode('/', Str::queryFilterUri($this->Request->environment()->REQUEST_URI));
        $visitorId = array_pop($visitorId);
        $visitor   = $this->LeadProvider->byKey('visitor_id', $visitorId);

        if (!$visitor)
        {
            return false;
        }

        return
        [
            'visitor'  => $visitor,
            'location' => $this->getLocation($visitor),
            'browser'  => $this->getBrowser($visitor),
            'os'       => $this->getOs($visitor),
        ];
    }

    /**
     * Get location details from visitor IP address.
     *
     * @param  \kanso\cms\wrappers\Visitor $visitor Visitor object
     * @return array|false
     */
    private function getLocation($visitor)
    {
        $headers =
        [
            'authority: whatismyipaddress.com',
            'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36',
            'referer: https://whatismyipaddress.com/ip-lookup',
            'cache-control: no-cache',
            'pragma: no-cache',
        ];

        $ipAddress = $visitor->ip_address;

        if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)
        {
            $ipAddress = '93.135.133.91';
        }

        $curl_h = curl_init('https://whatismyipaddress.com/ip/' . $ipAddress);

        curl_setopt($curl_h, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl_h, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl_h);

        $latRegex = '/(<tr>\s?<th>\s?Latitude:\s?<\/th>\s?<td>\s?)([-+]?([1-8]?\d(\.\d+)?|90(\.0+)?))/';

        $longRegex = '/(<tr>\s?<th>\s?Longitude:\s?<\/th>\s?<td>\s?)(\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?))/';

        $countryRegex = '/(<tr>\s?<th>\s?Country:\s?<\/th>\s?<td>\s?)(\w+\s?\w+)/';

        $stateRegex = '/(<tr>\s?<th>\s?State\/Region:\s?<\/th>\s?<td>\s?)(\w+\s?\w+)/';

        $cityRegex = '/(<tr>\s?<th>\s?City:\s?<\/th>\s?<td>\s?)(\w+\s?\w+)/';

        preg_match($latRegex, $response, $latitude);

        preg_match($longRegex, $response, $longitude);

        preg_match($countryRegex, $response, $country);

        preg_match($stateRegex, $response, $state);

        preg_match($cityRegex, $response, $city);

        if (($latitude && isset($latitude[2])) && ($longitude && isset($longitude[2])) && ($city && isset($city[2])) && ($state && isset($state[2])) && ($country && isset($country[2])))
        {
            return
            [
                'lat'      => $latitude[2],
                'long'     => $longitude[2],
                'location' => $city[2] . ', ' . $state[2] . ', ' . $country[2],
            ];
        }

        return false;
    }

    /**
     * Determines visitor's most used browser.
     *
     * @param  \kanso\cms\wrappers\Visitor $visitor Visitor object
     * @return string
     */
    private function getOs($visitor)
    {
        $visits = $visitor->visits();

        $visit = array_pop($visits);

        $os = new Os($visit->browser);

        return $os->getName();
    }

    /**
     * Determines visitor's most used browser.
     *
     * @param  \kanso\cms\wrappers\Visitor $visitor Visitor object
     * @return string
     */
    private function getBrowser($visitor)
    {
        $visits = $visitor->visits();

        $visit = array_pop($visits);

        $browser = new Browser($visit->browser);

        return $browser->getName() . ' ' . $browser->getVersion();
    }
}
