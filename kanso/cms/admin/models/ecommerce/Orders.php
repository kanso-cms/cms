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
 * Admin orders page/list model.
 *
 * @author Joe J. Howard
 */
class Orders extends BaseModel
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
     * @access private
     * @return array|false
     */
    public function parsePost()
    {
        $validate = $this->validatePost();

        if (!$validate || is_array($validate))
        {
            return $validate;
        }

        $orderIds = array_filter(array_map('intval', $this->post['orders']));

        if (!empty($orderIds))
        {
            if ($this->post['bulk_action'] === 'delete')
            {
                $this->delete($orderIds);

                return $this->postMessage('success', 'Orders were successfully deleted!');
            }
            if ($this->post['bulk_action'] === 'delivered' || $this->post['bulk_action'] === 'shipped' || $this->post['bulk_action'] === 'received')
            {
                $this->changeStatus($orderIds, $this->post['bulk_action']);

                return $this->postMessage('success', 'Orders were successfully updated!');
            }
        }

        return false;
    }

    /**
     * Delete an order.
     *
     * @access private
     * @param array $ids List of post ids
     */
    private function delete(array $ids)
    {
        foreach ($ids as $id)
        {
            $this->sql()->DELETE_FROM('transactions')->WHERE('id', '=', $id)->QUERY();
        }
    }

    /**
     * Change order status.
     *
     * @access private
     * @param array  $ids    List of post ids
     * @param string $status Post status to change to
     */
    private function changeStatus(array $ids, string $status)
    {
        foreach ($ids as $id)
        {
            $changes =
            [
                'status'  => $status,
                'shipped' => false,
            ];

            if ($status === 'shipped' || $status === 'delivered')
            {
                $changes['shipped'] = true;

                if (isset($this->post['tracking_code']))
                {
                    $changes['tracking_code'] = $this->post['tracking_code'];

                    if ($status === 'shipped')
                    {
                        $this->emailTrackingCode($changes['tracking_code'], $id);
                    }
                }
            }

            $this->sql()->UPDATE('transactions')->SET($changes)->WHERE('id', '=', $id)->QUERY();
        }
    }

    /**
     * Email customer their tracking code.
     *
     * @access private
     * @param string $trakingCode   Tracking code
     * @param int    $transactionId Transaction row id
     */
    private function emailTrackingCode(string $trakingCode, int $transactionId)
    {
        $transationRow = $this->sql()->SELECT('*')->FROM('transactions')->WHERE('id', '=', $transactionId)->ROW();
        $shippingRow   = $this->sql()->SELECT('*')->FROM('shipping_addresses')->WHERE('id', '=', $transationRow['shipping_id'])->ROW();

        $emailData =
        [
            'name'          => ucfirst($shippingRow['first_name']),
            'trackingCode'  => $trakingCode,
            'orderRefernce' => $transationRow['bt_transaction_id'],
        ];

        // Email credentials
        $senderName   = 'Vebena';
        $senderEmail  = 'orders@vebena.com.au';
        $emailSubject = 'Your Order Has Been Posted';
        $emailContent = $this->Email->preset('order-sent', $emailData, true);
        $emailTo      = $shippingRow['email'];

        $this->Email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);
    }

    /**
     * Validates all POST variables are set.
     *
     * @access private
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

        if (!in_array($this->post['bulk_action'], ['delivered', 'shipped', 'received', 'delete']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        if (!isset($this->post['orders']) || !is_array($this->post['orders']) || empty($this->post['orders']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        if ($this->post['bulk_action'] === 'delivered' || $this->post['bulk_action'] === 'shipped')
        {
            if (count($this->post['orders']) === 1)
            {
                if (!isset($this->post['tracking_code']) || empty($this->post['tracking_code']))
                {
                    return $this->postMessage('warning', 'You must enter a tracking code to set an order as shipped.');
                }
            }
        }

        return true;
    }

    /**
     * Parse the $_GET request variables and filter the orders for the requested page.
     *
     * @access private
     * @return array
     */
    private function parseGet(): array
    {
        // Prep the response
        $response =
        [
            'orders'        => $this->loadOrders(),
            'max_page'      => 0,
            'queries'       => $this->getQueries(),
            'empty_queries' => $this->emptyQueries(),
            'active_tab'    => Str::getAfterLastChar(Str::queryFilterUri($this->Request->environment()->REQUEST_URI), '/'),
        ];

        if ($response['active_tab'] === 'e-commerce')
        {
            $response['active_tab'] = 'orders';
        }

        // If the orders are empty,
        // There's no need to check for max pages
        if (!empty($response['orders']))
        {
            $response['max_page'] = $this->loadOrders(true);
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
            $queries['sort']   === 'newest' &&
            $queries['status'] === false &&
            $queries['date']   === false
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
        if (!isset($queries['date']))     $queries['date']     = false;

        return $queries;
    }

    /**
     * Returns the list of orders for display.
     *
     * @access private
     * @param  bool      $checkMaxPages Count the max pages
     * @return array|int
     */
    private function loadOrders(bool $checkMaxPages = false)
    {
        // Get queries
        $queries = $this->getQueries();

        // Default operation values
        $page         = ((int) $queries['page']);
        $page         = $page === 1 || $page === 0 ? 0 : $page-1;
        $sort         = 'ASC';
        $sortKey      = 'transactions.date';
        $perPage      = 10;
        $offset       = $page * $perPage;
        $limit        = $perPage;
        $status       = $queries['status'];
        $search       = $queries['search'];
        $date         = $queries['date'];

        // Filter and sanitize the sort order
        if ($queries['sort'] === 'newest' || $queries['sort'] === 'shipped') $sort = 'DESC';
        if ($queries['sort'] === 'oldest' || $queries['sort'] === 'price' || $queries['sort'] === 'user') $sort = 'ASC';

        if ($queries['sort'] === 'price')   $sortKey = 'transactions.total';
        if ($queries['sort'] === 'user')    $sortKey = 'transactions.user_id';
        if ($queries['sort'] === 'shipped') $sortKey = 'transactions.shipped_date';

        // Select the transactions
        $this->sql()->SELECT('transactions.*, users.name, users.email')->FROM('transactions');

        // Set the order
        $this->sql()->ORDER_BY($sortKey, $sort);

        // Apply basic joins for queries
        $this->sql()->LEFT_JOIN_ON('users', 'users.id = transactions.user_id');
        $this->sql()->GROUP_BY('transactions.id');

        // Filter status
        if ($status === 'received')
        {
            $this->sql()->AND_WHERE('transactions.status', '=', 'received');
        }
        elseif ($status === 'delivered')
        {
            $this->sql()->AND_WHERE('transactions.status', '=', 'delivered');
        }
        elseif ($status === 'shipped')
        {
            $this->sql()->AND_WHERE('transactions.status', '=', 'shipped')->OR_WHERE('transactions.shipped', '=', true);
        }

        // Search by user name, user email, or transaction id
        if ($search)
        {
            $this->sql()->AND_WHERE('users.email', 'like', '%' . $queries['search'] . '%');
            $this->sql()->OR_WHERE('users.name', 'like', '%' . $queries['search'] . '%');
            $this->sql()->OR_WHERE('transactions.bt_transaction_id', '=', $queries['search']);
        }

        // Filter by date ranges
        if ($date)
        {
            if ($date === 'today')
            {
                $this->sql()->AND_WHERE('transactions.date', '>=', strtotime('today'));
            }
            elseif ($date === 'yesterday')
            {
                $this->sql()->AND_WHERE('transactions.date', '>=', strtotime('-1 day'));
                $this->sql()->AND_WHERE('transactions.date', '<=', strtotime('today'));
            }
            elseif ($date === 'last_7_days')
            {
                $this->sql()->AND_WHERE('transactions.date', '>=', strtotime('-7 days'));
            }
            elseif ($date === 'last_14_days')
            {
                $this->sql()->AND_WHERE('transactions.date', '>=', strtotime('-14 days'));
            }
            elseif ($date === 'last_30_days')
            {
                $this->sql()->AND_WHERE('transactions.date', '>=', strtotime('-30 days'));
            }
            elseif ($date === 'last_60_days')
            {
                $this->sql()->AND_WHERE('transactions.date', '>=', strtotime('-60 days'));
            }
            elseif ($date === 'last_90_days')
            {
                $this->sql()->AND_WHERE('transactions.date', '>=', strtotime('-90 days'));
            }
        }

        // Set the limit - Only if we're returning the actual orders
        if (!$checkMaxPages)
        {
            $this->sql()->LIMIT($offset, $limit);
        }

        // Find the orders
        $rows = $this->sql()->FIND_ALL();

        // Are we checking the pages ?
        if ($checkMaxPages)
        {
            return ceil(count($rows) / $perPage);
        }

        foreach ($rows as $i => $row)
        {
            $rows[$i]['address'] = $this->sql()->SELECT('*')->FROM('shipping_addresses')->WHERE('id', '=', $row['shipping_id'])->ROW();
        }

        return $rows;
    }
}
