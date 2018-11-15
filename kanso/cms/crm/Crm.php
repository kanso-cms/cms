<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\crm;

use Exception;
use kanso\cms\wrappers\providers\LeadProvider;
use kanso\cms\wrappers\Visitor;
use kanso\framework\ioc\ContainerAwareTrait;

/**
 * CRM Utility Class.
 *
 * @author Joe J. Howard
 */
class Crm
{
    use ContainerAwareTrait;

    /**
     * The cookie key to be used to identify visitors.
     *
     * @var string
     */
    protected $cookieKey = 'crm_visitor_id';

    /**
     * The current visitor making the request.
     *
     * @var \kanso\cms\wrappers\Visitor
     */
    private $visitor;

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {
        // Only store data on humans
        if (!$this->UserAgent->isCrawler())
        {
            $this->findVisitor();

            // Only save current visit if this is a GET request
            if ($this->Request->isGet())
            {
                if (!$this->Gatekeeper->isAdmin())
                {
                    $this->visitor->addVisit($this->newVisitRow());
                }
            }
        }
    }

    /**
     * Return a new query builder instance.
     *
     * @access private
     * @return \kanso\framework\database\query\Builder
     */
    protected function sql(): Builder
    {
        return $this->Database->connection()->builder();
    }

    /**
     * Finds/and/or returns the current visitor.
     *
     * @access public
     * @return \kanso\cms\wrappers\Visitor
     */
    public function visitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * Get the visitor provider.
     *
     * @access public
     * @return \kanso\cms\wrappers\providers\LeadProvider
     */
    public function leadProvider(): LeadProvider
    {
        return $this->LeadManager->provider();
    }

    /**
     * Find the current visitor.
     *
     * @access private
     * @return \kanso\cms\wrappers\Visitor
     */
    private function findVisitor(): Visitor
    {
        // Logged in users
        if ($this->Gatekeeper->isLoggedIn())
        {
            $this->visitor = $this->leadProvider()->byKey('visitor_id', $this->Gatekeeper->getUser()->visitor_id);

            if (!$this->visitor)
            {
                $this->visitor = $this->leadProvider()->create($this->newVisitorRow());

                $this->login();
            }
        }
        // Returning visitors
        elseif ($this->Response->cookie()->has($this->cookieKey))
        {
            $this->visitor = $this->leadProvider()->byKey('visitor_id', $this->Response->cookie()->get($this->cookieKey));
        }
        else
        {
            // New visitors
            $this->visitor = $this->leadProvider()->create($this->newVisitorRow());
        }

        // Fallback
        if (!$this->visitor)
        {
            $this->visitor = $this->leadProvider()->create($this->newVisitorRow());
        }

        $this->Response->cookie()->put($this->cookieKey, $this->visitor->visitor_id);

        return $this->visitor;
    }

    /**
     * Returns the base array for a new visitor.
     *
     * @access private
     * @return array
     */
    private function newVisitorRow(): array
    {
        return [
            'ip_address'          => $this->Request->environment()->REMOTE_ADDR,
            'name'                => '',
            'email'               => '',
            'last_active'         => time(),
        ];
    }

    /**
     * Returns the base array for the current visit.
     *
     * channel => 'social'      - Social media website
     *            'referral'    - Referral. (e.g someone else's website)
     *            'cpc'         - Paid search.
     *            'organic'     - Organic search.
     *            'email'       - Email
     *            'display'     - Display advertising
     *            'direct'      - Direct visits.
     * medium =>  The medium
     *             'facebook', 'instagram', 'google', 'outlook' etc..
     *
     * @access private
     * @return array
     */
    private function newVisitRow(): array
    {
        $queries = $this->Request->queries();

        return
        [
            'visitor_id'   => $this->visitor->visitor_id,
            'ip_address'   => $this->Request->environment()->REMOTE_ADDR,
            'page'         => $this->Request->environment()->REQUEST_URL,
            'date'         => time(),
            'medium'       => isset($queries['md']) ? $queries['md'] : null,
            'channel'      => isset($queries['ch']) ? $queries['ch'] : 'direct',
            'campaign'     => isset($queries['cp']) ? $queries['cp'] : null,
            'keyword'      => isset($queries['kw']) ? $queries['kw'] : null,
            'creative'     => isset($queries['cr']) ? $queries['cr'] : null,
        ];
    }

    /**
     * Links the logged in user with the current visitor.
     *
     * @access public
     */
    public function login()
    {
        if (!$this->Gatekeeper->isLoggedIn())
        {
            throw new Exception('Error logging in CRM visitor. The user is not logged in via the Gatekeeper.');
        }

        // Update the user with the visitor
        $user = $this->Gatekeeper->getUser();

        $user->visitor_id = $this->visitor->visitor_id;

        $user->save();

        // Update the visitor with the user
        $this->visitor->email = $user->email;

        $this->visitor->name = $user->name;

        $this->visitor->save();

        $this->Response->cookie()->put($this->cookieKey, $this->visitor->visitor_id);
    }

    /**
     * After a visitor logs out, their cookie and sessions get wiped
     * This function retains their original visitor id.
     *
     * @access public
     */
    public function logout()
    {
        // Add the crm visitor cookie again
        $this->Response->cookie()->put($this->cookieKey, $this->visitor->visitor_id);
    }
}
