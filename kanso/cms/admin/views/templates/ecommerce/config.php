<?php $config = $kanso->Config->get('ecommerce'); ?>
<div class="col-8 floor-sm">
    <form method="post" class="js-validation-form" id="configuration">

        <h5 class="roof-xs">Shipping</h5>

        <p class="color-gray">Manage your shipping configuration options.</p>

        <div class="form-field row floor-sm">
            <label for="company_address">Company Address</label>
            <p class="color-gray">Your company address will appear on invoices.</p>
            <input type="text" name="company_address" id="company_address" placeholder="<strong>Powered By Kanso CMS</strong><br>1 City Road<br>Melbourne, VIC 3148<br>AUSTRALIA" value="<?php echo $config['company_address']; ?>" data-js-required="true">
        </div>

        <div class="form-field row floor-sm">
            <label for="tracking_url">Tracking URL</label>
            <p class="color-gray">The base URL of your postal service tracking search. The tracking code will be appended and in the customer posted confirmation email.</p>
            <input type="text" name="tracking_url" id="tracking_url" placeholder="https://postalservice/tracking/search/" value="<?php echo $config['tracking_url']; ?>" data-js-required="true">
        </div>

        <div class="form-field row floor-sm">
            <label for="shipping_price">Shipping Price</label>
            <p class="color-gray">Your flat rate shipping price on all orders.</p>
            <input type="text" name="shipping_price" id="shipping_price" placeholder="9.95" value="<?php echo $config['shipping_price']; ?>" data-js-required="true">
        </div>

        <div class="form-field row floor-sm">
            <label for="free_shipping_products">Free Shipping</label>
            <p class="color-gray">Enter a comma separated list of product ids that have free shipping.</p>
            <input type="text" name="free_shipping_products" id="free_shipping_products" placeholder="1, 2, 3, 4" value="<?php echo implode(', ', $config['free_shipping_products']); ?>">
        </div>

        <div class="form-field row floor-sm">
            <label for="confirmation_email">Confirmation Email</label>
            <p class="color-gray">The email address to send confirmation emails to.</p>
            <input type="text" name="confirmation_email" id="confirmation_email" placeholder="test@example.com" value="<?php echo $config['confirmation_email']; ?>" data-js-required="true" data-js-validation="email">
        </div>

        <hr class="divider">

        <h5 class="roof-xs">Rewards</h5>

        <p class="color-gray">Reward settings are used as a loyalty program for returning customers. Customers earn points and can redeem those points for special discount coupons.</p>

        <div class="form-field row floor-sm">
            <label for="dollars_to_points">Rewards Points Per Dollar</label>
            <p class="color-gray">Enter how many rewards points are earned for every dollar spent.</p>
            <input type="text" name="dollars_to_points" id="dollars_to_points" placeholder="0.4" value="<?php echo $config['dollars_to_points']; ?>" data-js-required="true">
        </div>

        <div class="form-field row floor-sm">
            <label for="points_to_discount">Points To Discount</label>
            <p class="color-gray">Enter your discount as a percentage on every 100 points.</p>
            <input type="text" name="points_to_discount" id="points_to_discount" placeholder="10" value="<?php echo $config['points_to_discount']; ?>" data-js-required="true">
        </div>

        <hr class="divider">

        <h5 class="roof-xs">Braintree</h5>
        <p class="color-gray">Manage your Braintree gateway configuration options.</p>
        
        <div class="form-field row floor-sm">
            <label for="bt_environment">Environment</label>
            <input type="text" name="bt_environment" id="bt_environment" placeholder="sandbox" value="<?php echo $config['braintree']['environment']; ?>" data-js-required="true">
        </div>

        <div class="form-field row floor-sm">
            <label for="bt_merchant_id">Merchant ID</label>
            <input type="text" name="bt_merchant_id" id="bt_merchant_id" placeholder="gwitOKV7PO" value="<?php echo $config['braintree']['merchant_id']; ?>" data-js-required="true">
        </div>

        <div class="form-field row floor-sm">
            <label for="bt_public_key">Public Key</label>
            <input type="text" name="bt_public_key" id="bt_public_key" placeholder="4sXmm3JFRpqSguezI503Hhudue9II1" value="<?php echo $config['braintree']['public_key']; ?>" data-js-required="true">
        </div>

        <div class="form-field row floor-sm">
            <label for="bt_private_key">Private Key</label>
            <input type="text" name="bt_private_key" id="bt_private_key" placeholder="X3YkMObB3c0PJ91RNbhwnMneQ0YR6Bp6X8nRfLKf" value="<?php echo $config['braintree']['private_key']; ?>" data-js-required="true">
        </div>

        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
        <input type="hidden" name="form_name" value="configuration">
        <button type="submit" class="btn btn-success">Update Settings</button>
    </form>
</div>