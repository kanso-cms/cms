<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models\ecommerce;

use kanso\cms\admin\models\BaseModel;
use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\RequestException;

/**
 * Admin coupons page.
 *
 * @author Joe J. Howard
 */
class Coupons extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        return
        [
            'active_tab'  => 'coupons',
        ];
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
     * Parse and validate the POST request from the submitted form.
     *
     * @access private
     * @return array|false
     */
    private function parsePost()
    {
        $validate = $this->validatePost();

        if (!$validate)
        {
            return false;
        }

        $result = [];

        foreach ($this->post['coupon_keys'] as $i => $key)
        {
            $value = intval($this->post['coupon_values'][$i]);

            $key = trim($key);

            $result[$key] = $value;
        }

        $this->Config->set('ecommerce.coupons', $result);

        $this->Config->save();

        return $this->postMessage('success', 'Settings were successfully updated!');
    }

    /**
     * Validates all POST variables are set.
     *
     * @access private
     * @return bool
     */
    private function validatePost(): bool
    {
        if (!isset($this->post['access_token']) || !$this->Gatekeeper->verifyToken($this->post['access_token']))
        {
            throw new InvalidTokenException('Bad Admin Panel POST Request. The CSRF token was either not provided or was invalid.');
        }

        // Validation
        if (!isset($this->post['form_name']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        if ($this->post['form_name'] !== 'coupons')
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        if (!isset($this->post['coupon_values']) || !isset($this->post['coupon_keys']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        if (count($this->post['coupon_values']) !== count($this->post['coupon_keys']))
        {
            throw new RequestException(500, 'Bad Admin Panel POST Request. The POST data was either not provided or was invalid.');
        }

        return true;
    }
}
