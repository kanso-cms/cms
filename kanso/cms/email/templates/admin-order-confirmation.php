<?php

$content  = 'A new order at ' . $kanso->Request->environment()->DOMAIN_NAME . ' has been received.' . PHP_EOL . PHP_EOL;
$content .= 'Date      : ' . date('l jS F Y h:i:s A') . PHP_EOL;
$content .= 'Price     : ' . $total . PHP_EOL;
$content .= 'Reference : ' . $bt_transaction_id . PHP_EOL;
$content .= 'Email     : ' . $shipping['email'] . PHP_EOL;
$content .= 'Items     : ' . PHP_EOL;
foreach($cart->items() as $item)
{
    $content .= '    - ' . $item->name . ' - ' . $item->options['variant'] . ' - x ' . $item->quantity . PHP_EOL;
}
$content .= PHP_EOL . 'Shipping Details : ' . PHP_EOL;
$content .= $shipping['first_name'] . ' ' . $shipping['last_name'] . PHP_EOL;
$content .= $shipping['street_address_1'] . PHP_EOL;
$content .= $shipping['suburb'] . ' ' . $shipping['state'] . ' ' . $shipping['zip_code'] . PHP_EOL;

echo $content;
