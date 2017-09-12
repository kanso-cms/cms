<?php

$kansoConfig   = admin_kanso_config('cms');
$cache_enabled = admin_kanso_config('cache.http_cache_enabled');
$cdn_enabbled  = admin_kanso_config('cdn.enabled');

?>
<div class="col-12 col-md-8 roof-sm floor-sm">
    <form method="post" class="js-validation-form" id="kanso_form">
        <p class="color-gray">
            These settings control how Kanso functions. Be sure you know what you're doing before 
            you change anything here. Please check out the <a href="http://kanso-cms.github.io/docs/0.0.01/getting-started/configuration/" target="_blank">documentation</a> if you are unsure.
        </p>

        <div class="form-field row floor-sm">
            <label for="site_title">Site title</label>
            <p class="color-gray">
                The name of your website. This is used by Kanso to structure page titles for SEO.
            </p>
            <input type="text" name="site_title" id="site_title" data-js-required="true"  data-js-min-legnth="1" data-js-max-legnth="50" maxlength="50" value="<?php echo $kansoConfig['site_title']; ?>" placeholder="My Website" autocomplete="off">
            <p class="help-danger">* Please enter a website title.</p>
        </div>

        <div class="form-field row floor-sm">
            <label for="site_description">Site description</label>
            <p class="color-gray">
                The description of your website is used by Kanso for SEO.
            </p>
            <input type="text" name="site_description" id="site_description" data-js-required="true" data-js-min-legnth="1" data-js-max-legnth="300" maxlength="300" value="<?php echo $kansoConfig['site_description']; ?>" placeholder="My Website is awesome." autocomplete="off"/>
            <p class="help-danger">* Please enter a website description.</p>
        </div>

        <div class="form-field row floor-sm">
            <label>Theme</label>
            <p class="color-gray">
                This is where Kanso will look for your theme files. To add a new theme, drop 
                a new folder in the themes directory with the appropriate templates.
            </p>
            <?php foreach ($themes as $i => $theme) : ?>
            <?php $checked = ($theme === $kansoConfig['theme_name'] ? 'checked' : ''); ?>
            <span class="radio radio-primary">
                <input type="radio" name="theme" id="theme_radio_<?php echo $i;?>"  value="<?php echo $theme;?>" <?php echo $checked; ?> />
                <label for="theme_radio_<?php echo $i; ?>"><?php echo $theme;?></label>
            </span>
            <?php endforeach; ?>
        </div>

        <div class="form-field row floor-sm">
            <label for="blog_location">Blog location</label>
            <p class="color-gray">
                You can prefix your blog routes with a path (e.g. "blog"). This will add a fixed value prefix to all your post, category, tag and author 
                pages. Leave this blank if you are unsure.
            </p>
            <input type="text" name="blog_location" id="blog_location" value="<?php echo $kansoConfig['blog_location'];?>" autocomplete="off"/>
        </div>

        <div class="form-field row floor-sm">
            <label for="permalinks">Permalinks</label>
            <p class="color-gray">
                Permalinks are used to structure URLs. The postname is mandatory. 
                Full options are / postname / category / author / year / month / day / hour / minute / second.
            </p>
            <input type="text" name="permalinks" id="permalinks" data-js-required="true" value="<?php echo $kansoConfig['permalinks'];?>" autocomplete="off"/>
            <p class="help-danger">* Please enter a valid permalinks structure.</p>
        </div>

        <div class="form-field row floor-sm">
            <label for="posts_per_page">Posts per page</label>
            <p class="color-gray">
                How many posts to display per page. Default is 10.
            </p>
            <input type="text" name="posts_per_page" id="posts_per_page" class="js-mask-numeric" data-js-required="true" data-js-validation="numeric" value="<?php echo $kansoConfig['posts_per_page'];?>" autocomplete="off"/>
            <p class="help-danger">* Please enter the posts per page.</p>
        </div>

        <div class="form-field row floor-sm">
            <label for="thumbnail_quality">Thumbnail quality</label>
            <p class="color-gray">
                What image quality should Kanso use for resizing. 0 is bad, 100 is great.
            </p>
            <input type="text" name="thumbnail_quality" id="thumbnail_quality" class="js-mask-numeric" data-js-required="true" data-js-validation="numeric" value="<?php echo $kansoConfig['uploads']['thumbnail_quality'];?>" autocomplete="off"/>
            <p class="help-danger">* Please enter a thumbnail quality.</p>
        </div>

        <div class="form-field row floor-sm">
            <label for="sitemap_url">Sitemap</label>
            <p class="color-gray">
                Where should Kanso route your XML sitemap for search engines. Default is "sitemap.xml".
            </p>
            <input type="text" name="sitemap_url" id="sitemap_url" data-js-required="true" value="<?php echo $kansoConfig['sitemap_route'];?>" autocomplete="off"/>
            <p class="help-danger">* Please enter a valid sitemap URL path.</p>
        </div>

        <div class="form-field row floor-sm">
            <label for="sitemap_url">Tags</label>
            <p class="color-gray">
                Do you want tags to have publicly accessible article listings.
            </p>
            <span class="checkbox checkbox-primary">
                <input type="checkbox" name="enable_tags" id="enable_tags" <?php echo ($kansoConfig['route_tags'] === true ? 'checked' : '');?> />
                <label for="enable_tags">Enable tag listings</label>
            </span>
        </div>

        <div class="form-field row floor-sm">
            <label for="enable_cats">Categories</label>
            <p class="color-gray">
                Do you want categories to have publicly accessible article listings.
            </p>
            <span class="checkbox checkbox-primary">
                <input type="checkbox" name="enable_cats" id="enable_cats" <?php echo ($kansoConfig['route_categories'] === true ? 'checked' : '');?> />
                <label for="enable_cats">Enable category listings</label>
            </span>
        </div>

        <div class="form-field row floor-sm">
            <label for="enable_authors">Authors</label>
            <p class="color-gray">
                Do you want authors to have publicly accessible article listings.
            </p>
            <span class="checkbox checkbox-primary">
                <input type="checkbox" name="enable_authors" id="enable_authors" <?php echo ($kansoConfig['route_authors'] === true ? 'checked' : '');?>  />
                <label for="enable_authors">Enable author listings</label>
            </span>
        </div>

        <div class="form-field row floor-sm">
            <label for="enable_comments">Comments</label>
            <p class="color-gray">
                Enable comments globally on posts and page.
            </p>
            <span class="checkbox checkbox-primary">
                <input type="checkbox" name="enable_comments" id="enable_comments" <?php echo ($kansoConfig['enable_comments'] === true ? 'checked' : '');?>  />
                <label for="enable_comments">Enable comments</label>
            </span>
        </div>

        <div class="form-field row floor-sm">
            <label for="enable_cache">Cache</label>
            <p class="color-gray">
                Enabling caching will tell Kanso to save HTML output to files. When another request is made for the same page,
                Kanso loads the HTML directly from the file without having to run a through variable processing, loops etc... 
                This greatly improves Kanso's performance and load times.
            </p>
            <span class="checkbox checkbox-primary js-collapse" data-collapse-target="cache-details">
                <input type="checkbox" name="enable_cache" id="enable_cache" <?php echo ($cache_enabled ? 'checked' : '');?>  />
                <label for="enable_cache">Enable Cache</label>
            </span>
        </div>

        <div class="<?php echo $cache_enabled ? 'hide-overflow' : 'hide-overflow collapsed';?> " id="cache-details" >
            <div class="gutter-lg gutter-l">
                <div class="form-field row floor-sm">
                    <label for="cache_life">Cache lifetime</label>
                    <p class="color-gray">
                        How long should Kanso keep cached page versions before creating a new one. 
                        e.g 1 minute, 2 hours, 1 week, 3 months.
                    </p>
                    <input type="text" name="cache_life" id="cache_life" value="<?php echo date('c' ,admin_kanso_config('cache.configurations.'.admin_kanso_config('cache.default').'.expire')) ;?>">
                </div>
                <div class="form-field row floor-sm">
                    <p class="color-gray">
                        Clear Kanso's entire cache library. This is useful when you've made changes to your templates
                        or a large number of posts.
                    </p>
                    <script type="text/javascript">
                        function cleaKansoCache() {
                            var form = document.getElementById('kanso_form');
                            form.innerHTML += '<input type="hidden" name="clear_cache" value="true">';
                            form.submit();
                        };
                    </script>
                    <button type="button" class="btn btn-danger" onclick="cleaKansoCache()">Clear cache</button>
                </div>
            </div>
        </div>

        <div class="form-field row floor-sm">
            <label for="enable_cdn">CDN</label>
            <p class="color-gray">
                If you want to use a CDN, Kanso will automatically replace all asset URLs (including images), 
                with your CDN url.
            </p>
            <span class="checkbox checkbox-primary js-collapse" data-collapse-target="cdn-url">
                <input type="checkbox" name="enable_cdn" id="enable_cdn" <?php echo $cdn_enabbled === true ? 'checked' : '';?> />
                <label for="enable_cdn">Enable CDN</label>
            </span>
        </div>

        <div class="<?php echo ($cdn_enabbled ? 'hide-overflow' : 'hide-overflow collapsed');?> " id="cdn-url">
            <div class="gutter-lg gutter-l">
                <div class="form-field row floor-sm">
                    <label for="cdn_url">CDN URL</label>
                    <input type="text" name="cdn_url" id="cdn_url" value="<?php echo admin_kanso_config('cdn.host');?>">
                </div>
            </div>
        </div>
        
        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
        <input type="hidden" name="form_name" value="kanso_settings">
        <button type="submit" class="btn btn-success">Update Settings</button>
    </form>
</div>