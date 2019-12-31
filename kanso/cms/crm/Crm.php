<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\crm;

use Exception;
use kanso\cms\auth\Gatekeeper;
use kanso\cms\wrappers\providers\LeadProvider;
use kanso\cms\wrappers\Visitor;
use kanso\framework\database\query\Builder;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;

/**
 * CRM Utility Class.
 *
 * @author Joe J. Howard
 */
class Crm
{
    /**
     * Request instance.
     *
     * @var \kanso\framework\http\request\Request
     */
    private $request;

    /**
     * Response instance.
     *
     * @var \kanso\framework\http\response\Response
     */
    private $response;

    /**
     * Gatekeeper instance.
     *
     * @var \kanso\cms\auth\Gatekeeper
     */
    private $gatekeeper;

    /**
     * Gatekeeper instance.
     *
     * @var \kanso\cms\wrappers\providers\LeadProvider
     */
    private $leadProvider;

    /**
     * Sql builder instance.
     *
     * @var \kanso\framework\database\query\Builder
     */
    private $sql;

    /**
     * The current visitor making the request.
     *
     * @var \kanso\cms\wrappers\Visitor
     */
    private $visitor;

    /**
     * Is this request via the CLI?
     *
     * @var bool
     */
    private $isCommandLine;

    /**
     * Is this a request to the admin panel?
     *
     * @var bool
     */
    private $isAdmin;

    /**
     * The current visitor a bot.
     *
     * @var bool
     */
    private $isCrawler;

    /**
     * The cookie key to be used to identify visitors.
     *
     * @var string
     */
    private $cookieKey = 'crm_visitor_id';

    /**
     * Constructor.
     *
     * @param \kanso\framework\http\request\Request   $request    Request instance
     * @param \kanso\framework\http\response\Response $response   Response instance
     * @param \kanso\cms\auth\Gatekeeper              $gatekeeper Gatekeeper instance
     * @param \kanso\cms\wrappers\providers\LeadProvider LeadProvider instance
     * @param \kanso\framework\database\query\Builder SQL builder instance
     * @param bool $isCommandLine Is the CMS running via command line? (optional) (default false)
     * @param bool $isCrawler     Is this a request from a bot? (optional) (default false)
     * @param bool $isAdmin       Is this a request for the admin panel? (optional) (default false)
     */
    public function __construct(Request $request, Response $response, Gatekeeper $gatekeeper, LeadProvider $leadProvider, Builder $sql, bool $isCommandLine = false, bool $isCrawler = false, bool $isAdmin = false)
    {
        $this->request       = $request;
        $this->response      = $response;
        $this->gatekeeper    = $gatekeeper;
        $this->sql           = $sql;
        $this->leadProvider  = $leadProvider;
        $this->isCommandLine = $isCommandLine;
        $this->isAdmin       = $isAdmin;
        $this->isCrawler     = $isCrawler;

        // Only load if not in CLI
        if (!$this->isCommandLine)
        {
            // Real humans
            if (!$this->isCrawler)
            {
                $this->findVisitor();

                // Only save current visit if this is a GET request
                if ($this->request->isGet())
                {
                    if (!$this->isAdmin)
                    {
                        $this->visitor->addVisit($this->newVisitRow());
                    }
                }
            }

            // Crawlers/bots get merged by user agent rather than cookies
            else
            {
                if ($this->request->isGet())
                {
                    $this->findCrawler();

                    $this->visitor->addVisit($this->newVisitRow());
                }
            }
        }

    }

    /**
     * Finds/and/or returns the current visitor.
     *
     * @return \kanso\cms\wrappers\Visitor
     */
    public function visitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * Get the lead provider.
     *
     * @return \kanso\cms\wrappers\providers\LeadProvider
     */
    public function leadProvider(): LeadProvider
    {
        return $this->leadProvider;
    }

    /**
     * Links the logged in user with the current visitor.
     */
    public function login(): void
    {
        if (!$this->gatekeeper->isLoggedIn())
        {
            throw new Exception('Error logging in CRM visitor. The user is not logged in via the Gatekeeper.');
        }

        // Update the user with the visitor
        $user = $this->gatekeeper->getUser();

        $user->visitor_id = $this->visitor->visitor_id;

        $user->save();

        // Update the visitor with the user
        $this->visitor->email = $user->email;

        $this->visitor->name = $user->name;

        $this->visitor->save();

        $this->response->cookie()->put($this->cookieKey, $this->visitor->visitor_id);
    }

    /**
     * After a visitor logs out, their cookie and sessions get wiped
     * This function retains their original visitor id.
     */
    public function logout(): void
    {
        // Add the crm visitor cookie again
        $this->response->cookie()->put($this->cookieKey, $this->visitor->visitor_id);
    }

    /**
     * Merges the current visitor with another one.
     *
     * @param  string $newVisitorId New visitor id
     * @return bool
     */
    public function mergeVisitor(string $newVisitorId): bool
    {
        if ($newVisitorId !== $this->visitor->visitor_id)
        {
            $newVisitor = $this->sql->SELECT('*')->FROM('crm_visitors')->WHERE('visitor_id', '=', $newVisitorId)->ROW();

            if ($newVisitor)
            {
                if (isset($this->visitor->id))
                {
                    $this->sql->DELETE_FROM('crm_visitors')->WHERE('id', '=', $this->visitor->id)->QUERY();

                    $this->sql->UPDATE('crm_visits')->SET(['visitor_id' => $newVisitorId])->WHERE('visitor_id', '=', $this->visitor->visitor_id)->QUERY();
                }

                foreach ($newVisitor as $key => $value)
                {
                    $this->visitor->$key = $value;
                }

                $this->response->cookie()->put($this->cookieKey, $newVisitorId);

                $this->visitor->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Find the current visitor.
     *
     * @return \kanso\cms\wrappers\Visitor
     */
    private function findVisitor(): Visitor
    {
        // Logged in users
        if ($this->gatekeeper->isLoggedIn())
        {
            $this->visitor = $this->leadProvider()->byKey('visitor_id', $this->gatekeeper->getUser()->visitor_id);

            if (!$this->visitor)
            {
                $this->visitor = $this->leadProvider()->create($this->newVisitorRow());

                $this->login();
            }
        }
        // Returning visitors
        elseif ($this->response->cookie()->has($this->cookieKey))
        {
            $this->visitor = $this->leadProvider()->byKey('visitor_id', $this->response->cookie()->get($this->cookieKey));
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

        $this->response->cookie()->put($this->cookieKey, $this->visitor->visitor_id);

        return $this->visitor;
    }

    /**
     * Find the current bot visitor.
     *
     * @return \kanso\cms\wrappers\Visitor
     */
    private function findCrawler(): Visitor
    {
        $this->visitor = $this->leadProvider()->byKey('user_agent', $this->request->environment()->HTTP_USER_AGENT);

        // If we couldn't find the bot by user_agent,
        // try to find them by IP
        if (!$this->visitor)
        {
            $this->visitor = $this->leadProvider()->byKey('ip_address', $this->request->environment()->REMOTE_ADDR);
        }

        // Fallback to new visitor
        if (!$this->visitor)
        {
            $this->visitor = $this->leadProvider()->create($this->newVisitorRow());
        }

        $this->response->cookie()->put($this->cookieKey, $this->visitor->visitor_id);

        return $this->visitor;
    }

    /**
     * Returns the base array for a new visitor.
     *
     * @return array
     */
    private function newVisitorRow(): array
    {
        return
        [
            'ip_address'  => $this->request->environment()->REMOTE_ADDR,
            'name'        => '',
            'email'       => '',
            'last_active' => time(),
            'user_agent'  => $this->request->environment()->HTTP_USER_AGENT,
            'is_bot'      => $this->isCrawler,
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
     * @return array
     */
    private function newVisitRow(): array
    {
        $queries = $this->request->queries();

        return
        [
            'visitor_id'   => $this->visitor->visitor_id,
            'ip_address'   => $this->request->environment()->REMOTE_ADDR,
            'page'         => substr($this->request->environment()->REQUEST_URL, 0, 255),
            'date'         => time(),
            'medium'       => isset($queries['md']) ? $queries['md'] : null,
            'channel'      => isset($queries['ch']) ? $queries['ch'] : 'direct',
            'campaign'     => isset($queries['cp']) ? $queries['cp'] : null,
            'keyword'      => isset($queries['kw']) ? $queries['kw'] : null,
            'creative'     => isset($queries['cr']) ? $queries['cr'] : null,
            'browser'      => $this->request->environment()->HTTP_USER_AGENT,
        ];
    }
}
