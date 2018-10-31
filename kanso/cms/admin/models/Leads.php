<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models;

use kanso\framework\utility\Arr;

/**
 * Admin leads page/list model.
 *
 * @author Joe J. Howard
 */
class Leads extends BaseModel
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
     * Parse the $_GET request variables and filter the leads for the requested page.
     *
     * @access private
     * @return array
     */
    private function parseGet(): array
    {
        // Prep the response
        $response =
        [
            'visitors'      => $this->loadVisitors(),
            'max_page'      => 0,
            'queries'       => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
            'active_tab'    => 'leads',
        ];

        // If the leads are empty,
        // There's no need to check for max pages
        if (!empty($response['visitors']))
        {
            $response['max_page'] = $this->loadVisitors(true);
        }

        return $response;
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
            $queries['sort']   === 'last_active' &&
            $queries['status'] === false &&
            $queries['action'] === false &&
            $queries['channel'] === false &&
            $queries['medium'] === false
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
        if (!isset($queries['sort']))     $queries['sort']     = 'last_active';
        if (!isset($queries['status']))   $queries['status']   = false;
        if (!isset($queries['action']))   $queries['action']   = false;
        if (!isset($queries['channel']))  $queries['channel']  = false;
        if (!isset($queries['medium']))   $queries['medium']   = false;

        return $queries;
    }

    /**
     * Returns the list of leads for display.
     *
     * @access private
     * @param  bool      $checkMaxPages Count the max pages
     * @return array|int
     */
    private function loadVisitors(bool $checkMaxPages = false)
    {
        // Get queries
        $queries = $this->getQueries();

        // Default operation values
        $page         = ((int) $queries['page']);
        $sort         = 'DESC';
        $sortKey      = 'last_active';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $status       = $queries['status'];
        $search       = $queries['search'];
        $action       = $queries['action'];
        $medium       = $queries['medium'];
        $channel      = $queries['channel'];

        // Filter and sanitize the sort order
        if ($queries['sort'] === 'email' || $queries['sort'] === 'name') $sort = 'ASC';
        if ($queries['sort'] === 'email')   $sortKey = 'email';
        if ($queries['sort'] === 'name')    $sortKey = 'name';
        if ($queries['sort'] === 'id')      $sortKey = 'id';

        // Select the leads
        $this->SQL->SELECT('id, email, name')->FROM('crm_visitors');

        // Set the order
        $this->SQL->ORDER_BY($sortKey, $sort);

        // Search by user name, user email, or id
        if ($search)
        {
            $this->SQL->OR_WHERE('email', 'like', '%' . $queries['search'] . '%');
            $this->SQL->OR_WHERE('name', 'like', '%' . $queries['search'] . '%');
            $this->SQL->OR_WHERE('id', '=', $queries['search']);
            $this->SQL->OR_WHERE('visitor_id', 'like', '%' . $queries['search'] . '%');
        }

        // Find the leads
        $rows = $this->SQL->FIND_ALL();

        // If we're sorting by name or email, those values could be empty
        // Reorder the array so empty values go on the end
        if ($sortKey === 'email' || $sortKey === 'name')
        {
            foreach ($rows as $i => $row)
            {
                if (empty($row[$sortKey]))
                {
                    unset($rows[$i]);

                    $rows[] = $row;
                }
            }

            // rebuild array index
            $rows = array_values($rows);
        }

        // Filter status
        $visitors = [];

        foreach ($rows as $row)
        {
            $visitor = $this->Crm->leadProvider()->byKey('id', $row['id']);

            $visitor->status = $visitor->grade(null, true);

            // Are we filtering the funnel status
            if ($status)
            {
                if ($visitor->status === $status)
                {
                    $visitors[] = $visitor;
                }
            }
            else
            {
                $visitors[] = $visitor;
            }
        }

        // Filter actions
        if ($action)
        {
            foreach ($visitors as $i => $visitor)
            {
                if ($action === 'not-bounced')
                {
                    if ($visitor->bounced())
                    {
                        unset($visitors[$i]);
                    }
                }
                elseif ($action === 'created-account')
                {
                    if (empty($visitor->user_id))
                    {
                        unset($visitors[$i]);
                    }
                }
                elseif ($action === 'bounced')
                {
                    if (!$visitor->bounced())
                    {
                        unset($visitors[$i]);
                    }
                }
                elseif ($action === 'visited-checkout')
                {
                    if (!$visitor->visitedCheckout())
                    {
                        unset($visitors[$i]);
                    }
                }
            }

            $visitors = array_values($visitors);
        }

        // Filter channel
        if ($channel)
        {
            foreach ($visitors as $i => $visitor)
            {
                if ($visitor->channel() !== $channel)
                {
                    unset($visitors[$i]);
                }
            }

            $visitors = array_values($visitors);
        }

        // Filter medium
        if ($medium)
        {
            foreach ($visitors as $i => $visitor)
            {
                if ($visitor->medium() !== $medium)
                {
                    unset($visitors[$i]);
                }
            }

            $visitors = array_values($visitors);
        }

        // Are we sorting by status
        if ($queries['sort'] === 'funnel')
        {
            usort($visitors, function($a, $b)
            {
                return strcmp($a->status, $b->status);
            });
        }

        // Are we sorting by visit count
        if ($queries['sort'] === 'visits')
        {
            usort($visitors, function($a, $b)
            {
                return $a->countVisits() < $b->countVisits();
            });
        }

        // Are we checking the pages ?
        if ($checkMaxPages)
        {
            return ceil(count($visitors) / $perPage);
        }

        // Return the paginated results
        $paged = Arr::paginate($visitors, $page, $perPage);
        $page  = $page === 1 || $page === 0 ? 0 : $page-1;

        if (!$paged || !isset($paged[$page]))
        {
            return [];
        }

        return $paged[$page];

    }
}
