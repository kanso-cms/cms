<?php
    $enable_robots  = $kanso->Config->get('cms.security.enable_robots');
    $robots_content = $kanso->Config->get('cms.security.robots_text_content');

    $enable_ip_block = $kanso->Config->get('cms.security.ip_blocked');
    $ip_whitelist    = $kanso->Config->get('cms.security.ip_whitelist');
?>

<div class="col-12 col-md-8 roof-xs floor-xs">
    <form method="post" class="js-validation-form">
        <p class="color-gray">
            Kanso access and security settings add an extra layer of security and access integration to your site.
        </p>
        
        <div class="form-field row floor-sm">
            <label for="block_robots">Robots</label>
            <p class="color-gray">
                Blocking all robots from the site, will disallow all bots (e.g Google indexing) from accessing your site.
            </p>
            <span class="checkbox checkbox-primary js-collapse" data-collapse-target="robots-details">
                <input type="checkbox" name="block_robots" id="block_robots" <?php echo (!$enable_robots ? 'checked' : ''); ?>  />
                <label for="block_robots">Block all robots</label>
            </span>
        </div>

        <div class="<?php echo $enable_robots ? 'hide-overflow' : 'hide-overflow collapsed'; ?> " id="robots-details" >
            <div class="gutter-lg gutter-l">
                <div class="form-field row floor-sm">
                    <label for="robots_content">Robots.txt content</label>
                    <p class="color-gray">
                        Enter a custom value for your "robots.txt" file. To allow all bots access, leave this blank.
                    </p>
                    <textarea name="robots_content" id="robots_content" rows="5" style="resize: vertical;"><?php echo $robots_content; ?></textarea>
                </div>
            </div>
        </div>

        <div class="form-field row floor-sm">
            <label for="enable_ip_block">IP blocking</label>
            <p class="color-gray">
                You can block access from all visitors to your site except a subset of IP addresses. 
                This can be useful when your site is still under development and you want to hide it from preying eyes.
            </p>

            <blockquote class="bq-danger">
                <p>Warning! Enabling IP blocking can lock you out from accessing your site. 
                Please double check your <a href="https://www.whatismyip.com/ip-address-lookup/" target="_blank" style="font-weight: 900">IP Address</a> before 
                making changes.
                </p>
            </blockquote>
            <blockquote class="bq-info">
                <p>If you are locked out of your site, you can change these settings by using an FTP agent and 
                changing the settings in the 'cms' configuration file. 
            </blockquote>
            <span class="checkbox checkbox-primary js-collapse" data-collapse-target="whitelist-details">
                <input type="checkbox" name="enable_ip_block" id="enable_ip_block" <?php echo ($enable_ip_block ? 'checked' : ''); ?>  />
                <label for="enable_ip_block">Enable IP blocking</label>
            </span>
        </div>

        <div class="<?php echo $enable_ip_block ? 'hide-overflow' : 'hide-overflow collapsed'; ?> " id="whitelist-details" >
            <div class="gutter-lg gutter-l">
                <div class="form-field row floor-sm">
                    <label for="ip_whitelist">IP whitelist</label>
                    <p class="color-gray">
                        Enter a comma seperated list of IP addresses to whitelist for access.
                    </p>
                    <textarea name="ip_whitelist" id="ip_whitelist" rows="5" style="resize: vertical;"><?php echo implode(',', $ip_whitelist); ?></textarea>
                </div>
            </div>
        </div>

        
        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
        <input type="hidden" name="form_name" value="access_settings">
        <button type="submit" class="btn btn-success">Update Settings</button>
    </form>
</div>