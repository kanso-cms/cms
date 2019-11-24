<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models\ecommerce;

use kanso\cms\admin\models\BaseModel;
use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\RequestException;
use kanso\framework\utility\Str;

/**
 * Admin customers page/list model.
 *
 * @author Joe J. Howard
 */
class Customers extends BaseModel
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
        return $this->parsePost();
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
     * Parse and validate the POST request from any submitted forms.
     *
     * @return array|false
     */
    private function parsePost()
    {
        $validate = $this->validatePost();

        if (!$validate)
        {
            return false;
        }

        $customerIds = array_filter(array_map('intval', $this->post['customers']));

        if (!empty($customerIds))
        {
            if ($this->post['bulk_action'] === 'delete')
            {
                $this->delete($customerIds);

                return $this->postMessage('success', 'Customers were successfully deleted!');
            }
            if (in_array($this->post['bulk_action'], ['confirmed', 'pending', 'locked', 'banned', 'delete']))
            {
                $this->changeStatus($customerIds, $this->post['bulk_action']);

                return $this->postMessage('success', 'Customers were successfully updated!');
            }
        }

        return false;
    }

    /**
     * Delete an order.
     *
     * @param array $ids List of post ids
     */
    private function delete(array $ids): void
    {
        foreach ($ids as $id)
        {
            $this->sql()->DELETE_FROM('users')->WHERE('id', '=', $id)->QUERY();
        }
    }

    /**
     * Change order status.
     *
     * @param array  $ids    List of post ids
     * @param string $status Post status to change to
     */
    private function changeStatus(array $ids, string $status): void
    {
        foreach ($ids as $id)
        {
            $this->sql()->UPDATE('users')->SET(['status' => $status])->WHERE('id', '=', $id)->QUERY();
        }
    }

    /**
     * Validates all POST variables are set.
     *
     * @return bool|array
     */
    private function validatePost()
    {
        // Validation
        if (!isset($this->post['access_token']) || !$this->Gatekeeper->verifyToken($this->post['access_token']))
        {
            throw new InvalidTokenException('Bad Admin Panel POST Request. The CSRF token was either not provided or was invalid.');
        }

        if (!isset($this->post['bulk_action']) || empty($this->post['bulk_action']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        if (!in_array($this->post['bulk_action'], ['confirmed', 'pending', 'locked', 'banned', 'delete']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        if (!isset($this->post['customers']) || !is_array($this->post['customers']) || empty($this->post['customers']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        return true;
    }

    /**
     * Parse the $_GET request variables and filter the customers for the requested page.
     *
     * @return array
     */
    private function parseGet(): array
    {
        // Prep the response
        $response =
        [
            'customers'     => $this->loadCustomers(),
            'max_page'      => 0,
            'queries'       => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
            'active_tab'    => Str::getAfterLastChar(Str::queryFilterUri($this->Request->environment()->REQUEST_URI), '/'),
        ];

        // If the customers are empty,
        // There's no need to check for max pages
        if (!empty($response['customers']))
        {
            $response['max_page'] = $this->loadCustomers(true);
        }

        return $response;
    }

    /**
     * Check if the GET URL queries are either empty or set to defaults.
     *
     * @return bool
     */
    private function emptyQueries(): bool
    {
        $queries = $this->getQueries();

        return (
            $queries['search'] === false &&
            $queries['page']   === 0 &&
            $queries['sort']   === 'email' &&
            $queries['status'] === false
        );
    }

    /**
     * Returns the requested GET queries with defaults.
     *
     * @return array
     */
    private function getQueries(): array
    {
        // Get queries
        $queries = $this->Request->queries();

        // Set defaults
        if (!isset($queries['search']))   $queries['search']   = false;
        if (!isset($queries['page']))     $queries['page']     = 0;
        if (!isset($queries['sort']))     $queries['sort']     = 'email';
        if (!isset($queries['status']))   $queries['status']   = false;

        return $queries;
    }

    /**
     * Returns the list of customers for display.
     *
     * @param  bool      $checkMaxPages Count the max pages
     * @return array|int
     */
    private function loadCustomers(bool $checkMaxPages = false)
    {
        // Get queries
        $queries = $this->getQueries();

        // Default operation values
        $page         = ((int) $queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = 'ASC';
        $sortKey      = 'email';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $status       = $queries['status'];
        $search       = $queries['search'];

        // Filter and sanitize the sort order
        if ($queries['sort'] === 'email' || $queries['sort'] === 'name') $sort = 'ASC';
        if ($queries['sort'] === 'email')   $sortKey = 'email';
        if ($queries['sort'] === 'name')    $sortKey = 'name';
        if ($queries['sort'] === 'status')  $sortKey = 'status';
        if ($queries['sort'] === 'id')      $sortKey = 'id';

        // Select the customers
        $this->sql()->SELECT('*')->FROM('users')->WHERE('role', '=', 'customer');

        // Set the order
        $this->sql()->ORDER_BY($sortKey, $sort);

        // Filter status
        if ($status === 'pending')
        {
            $this->sql()->AND_WHERE('status', '=', 'pending');
        }
        elseif ($status === 'confirmed')
        {
            $this->sql()->AND_WHERE('status', '=', 'confirmed');
        }
        elseif ($status === 'banned')
        {
            $this->sql()->AND_WHERE('status', '=', 'banned');
        }
        elseif ($status === 'locked')
        {
            $this->sql()->AND_WHERE('status', '=', 'locked');
        }
         elseif ($status === 'active')
        {
            $this->sql()->AND_WHERE('status', '=', 'active');
        }

        // Search by user name, user email, or id, status
        if ($search)
        {
            $this->sql()->OR_WHERE('email', 'like', '%' . $queries['search'] . '%');
            $this->sql()->OR_WHERE('name', 'like', '%' . $queries['search'] . '%');
            $this->sql()->OR_WHERE('status', 'like', '%' . $queries['search'] . '%');
            $this->sql()->OR_WHERE('id', '=', $queries['search']);
        }

        // Set the limit - Only if we're returning the actual customers
        if (!$checkMaxPages)
        {
            $this->sql()->LIMIT($offset, $limit);
        }

        // Find the customers
        $rows = $this->sql()->FIND_ALL();

        // Are we checking the pages ?
        if ($checkMaxPages)
        {
            return ceil(count($rows) / $perPage);
        }

        return $rows;
    }
}
