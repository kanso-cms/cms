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
        'merchant_id' => 'de232f435t3',
        'public_key'  => 'dasfdfsDac32',
        'private_key' => 'fsadfdsgdsff3e32e423',
    ],

    /*
     * Used as the default key to store session data
     */
    'session_id' => 'kanso_shopping_cart',

    /*
     * Currency code
     *
     * @see https://www.xe.com/iso4217.php
     */
    'currency' => 'AUD',

    /*
     * All tax calculations are inclusive (rather than added to pricing)
     * Set your tax amount as a percentage. Usually GST is 10%
     */
    'tax' => 10,

    /*
     * Allow customers to use multiple coupons on their cart
     */
    'multiple_coupons' => false,

    /*
     * 1 Dollar = x loyalty points
     */
    'dollars_to_points' => 0.5,

    /*
     * 100 loyalty point = x% discount
     */
    'points_to_discount' => 10,

    /*
     * Confirmation email address to send orders to
     */
    'confirmation_email' => 'info@example.com',

    /*
     * URL to tracking website
     */
    'tracking_url' => 'https://auspost.com.au/mypost/track/#/search',

    /*
     * Address for invoices
     */
    'company_address' => '<strong>Powered By Kanso CMS</strong><br>1 City Road<br>Melbourne, VIC 3148<br>AUSTRALIA',

    /*
     * ---------------------------------------------------------
     * Shipping configurations
     * ---------------------------------------------------------
     *
     *
     * is_free        : Use free shipping on all items (overrides all other options)
     * is_flat_rate   : Use a flat rate shipping price  (overrides 'weight_rates')
     * free_threshold : If 'is_flat_rate' is set to TRUE, the minimum cart price that gives free shipping.
     * flat_rate      : If 'is_flat_rate' is set to TRUE, the flat rate for shipping.
     * weight_rates   : If 'is_flat_rate' is set to FALSE, the tiered shipping rates per weight (weight is in grams)
     */
    'shipping'         =>
    [
        'is_free'        => false,
        'is_flat_rate'   => true,
        'free_threshold' => 99.99,
        'flat_rate'      => 9.95,
        'weight_rates'   =>
        [
            [
                'max_weight' => 500,
                'price'      => 9.95,
            ],
            [
                'max_weight' => 1000,
                'price'      => 13.95,
            ],
            [
                'max_weight' => 3000,
                'price'      => 16.95,
            ],
            [
                'max_weight' => 1000000000,
                'price'      => 19.95,
            ],
        ],
    ],

    /*
     * Array of coupons - COUPON_CODE => Discount as percentage
     */
    'coupons' =>
    [
    ],

];
