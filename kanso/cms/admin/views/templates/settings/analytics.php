<?php
    $config = $kanso->Config->get('analytics');

    $gAnalyticsEnabled = $config['google']['analytics']['enabled'];

    $gAnalyticsId = $config['google']['analytics']['id'];

    $adwordsEnabled = $config['google']['adwords']['enabled'];

    $gAdwordsId = $config['google']['adwords']['id'];

    $gAwCvId = $config['google']['adwords']['conversion'];

    $fbEnabled = $config['facebook']['enabled'];

    $fbPixelId = $config['facebook']['pixel'];

?>

<div class="col-12 col-md-8 roof-xs floor-xs">
    <form method="post" class="js-validation-form">
        <p class="color-gray">
            Your analytics settings are used by the CMS to integrate services such as Google Analytics and Facebook Pixel.
        </p>
        
        <div class="form-field row floor-sm">
            <label for="gAnalytics_enable">Google Analytics</label>
            <p class="color-gray">Enable or disable Google Analytics integration.</p>
            <span class="checkbox checkbox-primary js-collapse" data-collapse-target="ganalytics">
                <input type="checkbox" name="gAnalytics_enable" id="gAnalytics_enable" <?php echo $gAnalyticsEnabled === true ? 'checked' : ''; ?> />
                <label for="gAnalytics_enable">Enable Google Analytics</label>
            </span>
        </div>

        <div class="<?php echo ($gAnalyticsEnabled ? 'hide-overflow' : 'hide-overflow collapsed'); ?> " id="ganalytics">
            <div class="gutter-lg gutter-l">
                <div class="form-field row floor-sm">
                    <label for="gAnalytics_id">Analytics Tracking Id</label>
                    <input type="text" name="gAnalytics_id" id="gAnalytics_id" value="<?php echo $gAnalyticsId; ?>">
                </div>
            </div>
        </div>

        <div class="form-field row floor-sm">
            <label for="gAdwords_enable">Google Adwords Tracking</label>
            <p class="color-gray">Enable or disable Google Adwords integration.</p>
            <span class="checkbox checkbox-primary js-collapse" data-collapse-target="gawords">
                <input type="checkbox" name="gAdwords_enable" id="gAdwords_enable" <?php echo $adwordsEnabled === true ? 'checked' : ''; ?> />
                <label for="gAdwords_enable">Enable Google Adwords</label>
            </span>
        </div>

        <div class="<?php echo ($gAnalyticsEnabled ? 'hide-overflow' : 'hide-overflow collapsed'); ?>" id="gawords">
            <div class="gutter-lg gutter-l">
                <div class="form-field row floor-sm">
                    <label for="gAdwords_id">Adwords Tracking Id</label>
                    <input type="text" name="gAdwords_id" id="gAdwords_id" value="<?php echo $gAdwordsId; ?>">
                </div>
                <div class="form-field row floor-sm">
                    <label for="gAdwords_cnv_id">Adwords Conversion Tracking Id</label>
                    <input type="text" name="gAdwords_cnv_id" id="gAdwords_cnv_id" value="<?php echo $gAwCvId; ?>">
                </div>
            </div>
        </div>

        <div class="form-field row floor-sm">
            <label for="fbPixel_enable">Facebook Pixel</label>
            <p class="color-gray">Enable or disable Facebook Pixel integration.</p>
            <span class="checkbox checkbox-primary js-collapse" data-collapse-target="fbPixel">
                <input type="checkbox" name="fbPixel_enable" id="fbPixel_enable" <?php echo $fbEnabled === true ? 'checked' : ''; ?> />
                <label for="fbPixel_enable">Enable Google Analytics</label>
            </span>
        </div>

        <div class="<?php echo ($fbEnabled ? 'hide-overflow' : 'hide-overflow collapsed'); ?> " id="fbPixel">
            <div class="gutter-lg gutter-l">
                <div class="form-field row floor-sm">
                    <label for="fbPixel_id">Facebook Pixel Id</label>
                    <input type="text" name="fbPixel_id" id="fbPixel_id" value="<?php echo $fbPixelId; ?>">
                </div>
            </div>
        </div>

        
        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
        <input type="hidden" name="form_name" value="analytics_settings">
        <button type="submit" class="btn btn-success">Update Settings</button>
    </form>
</div>