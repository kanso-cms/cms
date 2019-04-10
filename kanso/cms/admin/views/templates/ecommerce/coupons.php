<?php $coupons = $kanso->Config->get('ecommerce.coupons'); ?>
<div class="col-8 floor-sm">
    <form method="post" class="js-validation-form" id="coupons">
        <p class="color-gray">Manage global promotional coupons used for checkout.</p>
        <div class="row">
            <button class="btn js-add-coupon-btn" type="button">Add Coupon +</button>
            <div class="row floor-sm js-coupon-entries">
                <?php foreach ($coupons as $code => $value) : ?>
                    <div class="row roof-xs js-coupon-row">
                        <div class="form-field floor-xs">
                            <label>Key</label>
                            <input type="text" name="coupon_keys[]" value="<?php echo $code; ?>" autocomplete="off" size="20">
                        </div>&nbsp;&nbsp;&nbsp;<div class="form-field floor-xs">
                            <label>Value</label>
                            <input type="text" name="coupon_values[]" value="<?php echo $value; ?>" autocomplete="off" size="60">
                        </div>&nbsp;&nbsp;&nbsp;<button class="btn btn-danger js-rmv-coupon-code" type="button">Remove</button>

                        <div class="row clearfix"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
        <input type="hidden" name="form_name" value="coupons">
        <button type="submit" class="btn btn-success">Update Settings</button>
    </form>
</div>