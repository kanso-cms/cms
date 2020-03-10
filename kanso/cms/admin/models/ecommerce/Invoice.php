<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\models\ecommerce;

use kanso\cms\admin\models\BaseModel;

/**
 * Admin panel invoice model.
 *
 * @author Joe J. Howard
 */
class Invoice extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public function onGET()
    {
        if (!$this->isLoggedIn())
        {
            return false;
        }

        $transactionId = explode('/', $this->Request->environment()->REQUEST_PATH);
        $transactionId = array_pop($transactionId);

        // SQL Builder
        $sql = $this->sql();

        // Find the transaction
        $transaction = $sql->SELECT('*')->FROM('transactions')->WHERE('bt_transaction_id', '=', $transactionId)->ROW();

        if (!$transaction)
        {
            return false;
        }

        $transaction['items'] = unserialize($transaction['items']);

        $customer = $sql->SELECT('*')->FROM('users')->WHERE('id', '=', $transaction['user_id'])->ROW();

        $address = $sql->SELECT('*')->FROM('shipping_addresses')->WHERE('id', '=', $transaction['shipping_id'])->ROW();

        return
        [
            'order'    => $transaction,
            'customer' => $customer,
            'address'  => $address,
        ];
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
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvoice(): void
    {

    }
}
