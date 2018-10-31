<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\RequestException;
use kanso\framework\utility\Arr;
use kanso\framework\utility\Str;

/**
 * Comment users model.
 *
 * @author Joe J. Howard
 */
class CommentUsers extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        if ($this->isLoggedIn)
        {
            return $this->parseGet();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onPOST()
    {
        if ($this->isLoggedIn)
        {
            return $this->parsePost();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function onAJAX()
    {
        return false;
    }

    /**
     * Parse the $_GET request variables and filter the articles for the requested page.
     *
     * @access private
     * @return array
     */
    private function parseGet(): array
    {
        $response = [
            'commenters'    => $this->loadUsers(),
            'max_page'      => 0,
            'queries'       => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
        ];

        if (!empty($response['commenters']))
        {
            $response['max_page'] = $this->loadUsers(true);
        }

        return $response;
    }

    /**
     * Parse and validate the POST request from any submitted forms.
     *
     * @access private
     * @return array|false
     */
    private function parsePost()
    {
        if (!$this->validatePost())
        {
            return false;
        }

        $userIps = array_filter(array_map('trim', $this->post['users']));

        if (!empty($userIps))
        {
            if (in_array($this->post['bulk_action'], ['whitelist', 'blacklist', 'nolist']))
            {
                $this->moderateIps($userIps, $this->post['bulk_action']);

                return $this->postMessage('success', 'IP Addresses were successfully moderated!');
            }
        }

        return false;
    }

    /**
     * Moderate a list of ip addresses.
     *
     * @access private
     * @param  array   Array of ip addresses to moderate
     * @param  string  The list to add them to
     */
    private function moderateIps(array $ips, string $list)
    {
        foreach ($ips as $ip) {
            if ($list === 'blacklist')
            {
                $this->SpamProtector->unWhitelistIpAddress($ip);
                $this->SpamProtector->blacklistIpAddress($ip);
            }
            elseif ($list === 'whitelist')
            {
                $this->SpamProtector->whitelistIpAddress($ip);
                $this->SpamProtector->unBlacklistIpAddress($ip);
            }
            elseif ($list === 'nolist')
            {
                $this->SpamProtector->unWhitelistIpAddress($ip);
                $this->SpamProtector->unBlacklistIpAddress($ip);
            }
        }
    }

    /**
     * Validates all POST variables are set.
     *
     * @access private
     * @return bool
     */
    private function validatePost(): bool
    {
        // Validation
        if (!isset($this->post['access_token']) || !$this->Gatekeeper->verifyToken($this->post['access_token']))
        {
            throw new InvalidTokenException('Bad Admin Panel POST Request. The CSRF token was either not provided or was invalid.');
        }

        if (!isset($this->post['bulk_action']) || empty($this->post['bulk_action']))
        {
            throw new RequestException('Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        if (!in_array($this->post['bulk_action'], ['whitelist', 'blacklist', 'nolist']))
        {
            throw new RequestException('Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        if (!isset($this->post['users']) || !is_array($this->post['users']) || empty($this->post['users']))
        {
            throw new RequestException('Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        return true;
    }

    /**
     * Check if the GET URL queries are either empty or set to defaults.
     *
     * @access private
     * @return bool
     */
    private function emptyQueries(): bool
    {
        $queries = $this->getQueries();

        return (
            $queries['search'] === false &&
            $queries['page']   === 0 &&
            $queries['sort']   === 'newest' &&
            $queries['status'] === false
        );
    }

    /**
     * Returns the requested GET queries with defaults.
     *
     * @access private
     * @return array
     */
    private function getQueries(): array
    {
        // Get queries
        $queries = $this->Request->queries();

        // Set defaults
        if (!isset($queries['search']))   $queries['search']   = false;
        if (!isset($queries['page']))     $queries['page']     = 0;
        if (!isset($queries['sort']))     $queries['sort']     = 'newest';
        if (!isset($queries['status']))   $queries['status']   = false;

        return $queries;
    }

    /**
     * Returns the list of users for display.
     *
     * @access private
     * @param  bool      $checkMaxPages Count the max pages
     * @return array|int
     */
    private function loadUsers(bool $checkMaxPages = false)
    {
       // Get queries
        $queries = $this->getQueries();

        // Default operation values
        $page         = ((int) $queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = $queries['sort'] === 'newest' ? 'DESC' : 'ASC';
        $sortKey      = 'date';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $search       = $queries['search'];
        $filter       = $queries['status'];

        // Filter and sanitize the sort order
        if ($queries['sort'] === 'name')  $sortKey   = 'name';
        if ($queries['sort'] === 'email') $sortKey   = 'email';

        $this->SQL->SELECT('*')->FROM('comments');

        // Is this a search
        if ($search)
        {
            if (Str::contains($search, ':'))
            {
                $keys = explode(':', $search);

                if (in_array($keys[0], ['name', 'email', 'ip_address']))
                {
                    $this->SQL->AND_WHERE($keys[0], 'LIKE', "%$keys[1]%");
                }
            }
        }

        // Set the order
        $this->SQL->ORDER_BY($sortKey, $sort);

        // Find comments
        $comments = $this->SQL->FIND_ALL();

        // Create a list of users
        $users = [];
        foreach ($comments as $comment)
        {
            $blacklisted = $this->SpamProtector->isIpBlacklisted($comment['ip_address']);
            $whitelisted = $this->SpamProtector->isIpWhiteListed($comment['ip_address']);

            if ($filter === 'whitelist' && !$whitelisted)
            {
                continue;
            }
            elseif ($filter === 'blacklist' && !$blacklisted)
            {
                continue;
            }

            if (!isset($users[$comment['ip_address']]))
            {
                $users[$comment['ip_address']] = [
                    'reputation'   => $comment['rating'],
                    'posted_count' => 1,
                    'spam_count'   => $comment['status'] === 'spam' ? 1 : 0,
                    'first_date'   => $comment['date'],
                    'blacklisted'  => $blacklisted,
                    'whitelisted'  => $whitelisted,
                    'ip_address'   => $comment['ip_address'],
                    'name'         => $comment['name'],
                    'email'        => $comment['email'],
                ];
            }
            else
            {
                $users[$comment['ip_address']]['reputation'] += $comment['rating'];
                $users[$comment['ip_address']]['posted_count'] += 1;
                if ($comment['status'] === 'spam') $users[$comment['ip_address']]['spam_count'] += 1;
                if ($comment['date'] < $users[$comment['ip_address']]['first_date']) $users[$comment['ip_address']]['first_date'] = $comment['date'];
            }
        }

        // Are we checking the pages ?
        if ($checkMaxPages) return ceil(count($users) / $perPage);

        // Append custom keys
        $paged = Arr::paginate($users, $page, $perPage);

        if (isset($paged[$page]))
        {
            return $paged[$page];
        }

        return [];
    }
}
