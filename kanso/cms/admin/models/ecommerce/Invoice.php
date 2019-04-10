<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace app\models\admin;

use kanso\framework\mvc\model\Model;
use kanso\framework\utility\Str;

/**
 * Admin panel invoice model.
 *
 * @author Joe J. Howard
 */
class Invoice extends Model
{
    /**
     * {@inheritdoc}
     */
    public function getInvoice()
    {
        $transactionId = explode('/', Str::queryFilterUri($this->Request->environment()->REQUEST_URI));
        $transactionId = array_pop($transactionId);

        // SQL Builder
        $sql = $this->Database->connection()->builder();

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
}
