<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models\ecommerce;

use kanso\cms\admin\models\BaseModel;

/**
 * Admin ecomerrce config page.
 *
 * @author Joe J. Howard
 */
class Config extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        return
        [
            'active_tab'  => 'configuration',
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
     * @return array|false
     */
    private function parsePost()
    {
        $validate = $this->validatePost();

        if ($validate !== true)
        {
            return $validate;
        }

        $this->Config->set('ecommerce.braintree.environment', $this->post['bt_environment']);
        $this->Config->set('ecommerce.braintree.merchant_id', $this->post['bt_merchant_id']);
        $this->Config->set('ecommerce.braintree.public_key', $this->post['bt_public_key']);
        $this->Config->set('ecommerce.braintree.private_key', $this->post['bt_private_key']);

        $this->Config->set('ecommerce.confirmation_email', $this->post['confirmation_email']);
        $this->Config->set('ecommerce.shipping_price', $this->post['shipping_price']);
        $this->Config->set('ecommerce.company_address', $this->post['company_address']);
        $this->Config->set('ecommerce.tracking_url', $this->post['tracking_url']);

        $this->Config->set('ecommerce.dollars_to_points', $this->post['dollars_to_points']);
        $this->Config->set('ecommerce.points_to_discount', $this->post['points_to_discount']);

        if (isset($this->post['free_shipping_products']))
        {
            $freeShipping = array_map('intval', array_filter(array_map('trim', explode(',', $this->post['free_shipping_products']))));

            $this->Config->set('ecommerce.free_shipping_products', $freeShipping);
        }

        $this->Config->save();

        return $this->postMessage('success', 'Settings were successfully updated!');
    }

    /**
     * Validates all POST variables are set.
     *
     * @return bool|array
     */
    private function validatePost()
    {
        // Validate the user is an admin
        if ($this->Gatekeeper->getUser()->role !== 'administrator')
        {
            return false;
        }

        $post  = $this->post;
        $rules =
        [
            'bt_environment'     => ['required'],
            'bt_merchant_id'     => ['required'],
            'bt_public_key'      => ['required'],
            'bt_private_key'     => ['required'],
            'shipping_price'     => ['required'],
            'confirmation_email' => ['required'],
            'dollars_to_points'  => ['required'],
            'points_to_discount' => ['required'],
            'tracking_url'       => ['required'],
            'company_address'    => ['required'],
        ];
        $filters =
        [
            'bt_environment'         => ['trim'],
            'bt_merchant_id'         => ['trim'],
            'bt_public_key'          => ['trim'],
            'bt_private_key'         => ['trim'],
            'shipping_price'         => ['trim'],
            'free_shipping_products' => ['trim'],
            'confirmation_email'     => ['trim', 'email'],
            'dollars_to_points'      => ['trim'],
            'points_to_discount'     => ['trim'],
            'tracking_url'           => ['trim'],
            'company_address'        => ['trim'],
        ];

        $validator = $this->container->Validator->create($this->post, $rules, $filters);

        if (!$validator->isValid())
        {
            $errors = $validator->getErrors();

            return $this->postMessage('warning', array_shift($errors));
        }

        $this->post = $validator->filter();

        return true;
    }
}
