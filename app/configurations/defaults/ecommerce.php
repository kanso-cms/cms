<?php

return
[
    /*
     * ---------------------------------------------------------
     * Braintree configurations
     * ---------------------------------------------------------
     *
     * @see https://articles.braintreepayments.com/control-panel/important-gateway-credentials
     *
     * environment   : Braintree environment
     * merchant_id   : Braintree merchant_id
     * public_key    : Braintree public_key
     * private_key   : Braintree private_key
     */
    'braintree' =>
    [
        'environment' => 'sandbox',
        'merchant_id' => 'mfhqnkhjt853xprn',
        'public_key'  => 'wyxtwdg72d823fwf',
        'private_key' => '845ffbe44fba5930fb3504152f2027a0',
    ],

    /*
     * Confirmation email address to send orders to
     */
    'confirmation_email' => 'info@example.com',

    /*
     * The default shipping price to use
     */
    'shipping_price' => 9.95,

    /*
     * 1 Dollar = x loyalty points
     */
    'dollars_to_points' => 0.5,

    /*
     * 100 loyalty point = x% discount
     */
    'points_to_discount' => 10,

    /*
     * URL to tracking website
     */
    'tracking_url' => 'https://auspost.com.au/mypost/track/#/search',

    /*
     * Address for invoices
     */
    'company_address' => '<strong>Powered By Kanso CMS</strong><br>1 City Road<br>Melbourne, VIC 3148<br>AUSTRALIA',

    /*
     * Array of coupons - COUPON_CODE => Discount as percentage
     */
    'coupons' =>
    [
        'SPECIAL_10' => 10,
        'SPECIAL_20' => 20,
        'SPECIAL_30' => 30,
    ],

    /*
     * Array of product post_ids that have free shipping
     */
    'free_shipping_products' =>
    [

    ],
];
