<h3>Kanso Settings</h3>

<p class="info text-masked" style="margin-bottom:25px;">
	These are the settings that make Kanso run. Change with caution.
</p>

<?php // <!-- KANSO SETTINGS FORM --> ?> 
<form class="ajax-form js-kanso-settings-form">

	<div class="input-wrap">
		<label class="bold">Site Title</label>
		<p class="info text-masked">
			The name of your website. This is used by Kanso to stucture page titles.
		</p>
		<input class="input-default small" type="text" name="site-title" data-js-required="true"  data-js-min-legnth="1" data-js-max-legnth="50" maxlength="50" value="<?php echo $KANSO_SITE_TITLE?>" placeholder="My Website" autocomplete="off"/>
		<p class="input-error">* Please enter a site title.</p>
	</div>

	<div class="input-wrap">
		<label class="bold">Site Description</label>
		<p class="info text-masked">
			Your website description is used by Kanso to output SEO data amongst other things.
		</p>
		<input class="input-default small" type="text" name="site-description" data-js-required="true" data-js-min-legnth="1" data-js-max-legnth="300" maxlength="300" value="<?php echo $KANSO_SITE_DESCRIPTION?>" placeholder="My Website is awesome" autocomplete="off"/>
		<p class="input-error">* Please enter a site description.</p>
	</div>

	<div class="input-wrap">
		<label class="bold">Theme</label>
		<p class="info text-masked">
			This is where Kanso will look for your theme files. To add a new theme, drop a new folder in the themes directory with 
			the appropriate templates.
		</p>
		<?php echo $themeRadios;?>
	</div>

	<div class="input-wrap">
		<label class="bold">Permalinks</label>
		<p class="info text-masked">
			Permalinks are used to structure article urls. The postname is mandatory.
			Full options are / postname / category / author / year / month / day / hour / minute / second
		</p>
		<input class="input-default small" type="text" name="permalinks" data-js-required="true" data-js-validation="permalinks" value="<?php echo $KANSO_PERMALINKS;?>" autocomplete="off"/>
		<p class="input-error">* Please enter a valid permalinks url.</p>
	</div>

	<div class="input-wrap">
		<label class="bold">Posts Per Page</label>
		<p class="info text-masked">
			How many posts should Kanso display per page. Defaults to 10.
		</p>
		<input class="input-default small js-input-mask-number" type="text" name="posts-per-page" data-js-required="true" data-js-validation="numbers" value="<?php echo $KANSO_POSTS_PER_PAGE;?>" autocomplete="off"/>
		<p class="input-error">* Please a number of how many posts to show.</p>
	</div>

	<div class="input-wrap">
		<label class="bold">Thumbnail Sizes</label>
		<p class="info text-masked">
			Kanso automatically resizes your images into 3 sizes with the suffix _small, _medium, _large. 
			Specify a comma seperated list of sizes with width and height (in px). e.g 400 200, 800 600. 
			To resize without cropping, specify only the width.
		</p>
		<input class="input-default small" type="text" name="thumbnail-sizes" data-js-required="true" data-js-validation="comma-list-numbers" value="<?php echo $thumbnailSizes;?>" autocomplete="off"/>
		<p class="input-error">* Please a comma separated list of sizes.</p>
	</div>

	<div class="input-wrap">
		<label class="bold">Thumbnail Quality</label>
		<p class="info text-masked">
			What image quality should Kanso use. 0 is terrible. 100 is awesome.
		</p>
		<p class="info text-masked"></p> 
		<input class="input-default small js-input-mask-number" type="text" name="thumbnail-quality" data-js-required="true" data-js-validation="numbers" value="<?php echo $KANSO_IMG_QUALITY;?>" autocomplete="off"/>
		<p class="input-error">* Please enter a number between 1 and 100</p>
	</div>

	<div class="input-wrap">
		<label class="bold">Sitemap URL</label>
		<p class="info text-masked"></p>
		<p class="info text-masked">
			Where should Kanso route your sitemap to for search engines. Defaults to sitemap.xml.
		</p>
		<input class="input-default small" type="text" name="sitemap-url" data-js-required="true" data-js-validation="url-path" value="<?php echo $KANSO_SITEMAP;?>" autocomplete="off"/>
		<p class="input-error">* Please enter a valid path to your sitemap.</p>
	</div>

	<div class="input-wrap">
		<div class="check-wrap">
			<p class="bold label">Tags</p>
			<p class="info text-masked">
				Do you want all of your tags to have their own article listings.
			</p>
			<input id="tagsCheck" type="checkbox" name="route-tags" <?php echo ($KANSO_ROUTE_TAGS === true ? 'checked' : '');?> />
			<label class="checkbox small" for="tagsCheck"></label>
		</div>
	</div>

	<div class="input-wrap">
		<div class="check-wrap">
			<p class="bold label">Categories</p>
			<p class="info text-masked">
				Do you want all of your categories to have their own article listings.
			</p>
			<input id="categoriesCheck" type="checkbox" name="route-categories" <?php echo ($KANSO_ROUTE_CATEGORIES === true ? 'checked' : '');?>>
			<label class="checkbox small" for="categoriesCheck"></label>
		</div>
	</div>

	<div class="input-wrap">
		<div class="check-wrap">
			<p class="info text-masked"></p>
			<p class="bold label">Authors</p>
			<p class="info text-masked">
				Do you want all of your authors to have their own article listings.
			</p>
			<input id="authorsCheck" type="checkbox" name="route-authors" <?php echo ($KANSO_ROUTE_AUTHORS === true ? 'checked' : '');?> />
			<label class="checkbox small" for="authorsCheck"></label>
		</div>
	</div>

	<div class="input-wrap">
		<div class="check-wrap">
			<p class="info text-masked"></p>
			<p class="bold label">Allow Comments</p>
			<p class="info text-masked">
				Do you want visitors to be able to post comments through Kanso's native comments system.
			</p>
			<input id="commentsCheck" type="checkbox" name="enable-comments" <?php echo ($KANSO_COMMENTS_OPEN === true ? 'checked' : '');?> />
			<label class="checkbox small" for="commentsCheck"></label>
		</div>
	</div>

	<div class="input-wrap">
		<div class="check-wrap input-wrap">
			<p class="bold label">Use CDN</p>
			<p class="info text-masked">
				If you want to use a CDN, Kanso will automatically replace all asset urls (including images) with your CDN url.
			</p>
			<input id="CDNcheck" type="checkbox" name="use-CDN" <?php echo ($KANSO_USE_CDN === true ? 'checked' : '');?> />
			<label class="checkbox small" for="CDNcheck"></label>
		</div>

		<div class="hidden <?php echo ($KANSO_USE_CDN === true ? 'active' : '');?>">
			<div class="input-wrap">
				<label class="bold">CDN URL</label>
				<input class="input-default small" type="text" name="CDN-url" data-js-validation="website" placeholder="cdn.example.com" value="<?php echo $KASNO_CDN_URL;?>">
				<p class="input-error">* If you're using a CDN, you need to enter the CDN's URL.</p>
				<div class="clearfix" style="height:20px"></div>
			</div>
		</div>

	</div>

	<div class="input-wrap">
		<div class="check-wrap input-wrap">
			<p class="bold label">Enable Cache</p>
			<p class="info text-masked">
				Enabling caching will tell Kanso to save html output to files. When another request is made for the same page,
				Kanso loads the HTML directly from the file without having to run a through variable processing, loops etc... 
				This greatly improves Kanso's performance and load times.
			</p>
			<input id="cacheCheck" type="checkbox" name="use-cache" <?php echo ($KANSO_USE_CACHE === true ? 'checked' : '');?> />
			<label class="checkbox small" for="cacheCheck"></label>
		</div>

		<div class="hidden <?php echo ($KANSO_USE_CACHE === true ? 'active' : '');?>">
			<div class="input-wrap">
				<label class="bold">Cache Life</label>
				<p class="info text-masked">
					How long should Kanso keep cached page versions before creating a new one. e.g 1 minute, 2 hours, 1 week, 3 months.
				</p>
				<input class="input-default small" type="text" name="cache-life" value="<?php echo $KANSO_CACHE_LIFE;?>"/>
				<p class="input-error">* If you're using a Kanso's cache, you need to specify a cache lifetime.</p>
			</div>
			<div class="input-wrap">
				<label class="bold">Clear Cache</label>
				<p class="info text-masked">
					Clear Kanso's entire cache library. This is usefull when you've made changes to your templates
					or a large number of posts.
				</p>
				<a class="button submit with-spinner js-clear-kanso-cache" href="#">
					Clear Cache
					<span class="spinner1"></span>
					<span class="spinner2"></span>
				</a>
			</div>
		</div>
	</div>

	<div class="input-wrap">
		<button type="submit" class="button submit with-spinner">
			Update Settings
			<span class="spinner1"></span>
			<span class="spinner2"></span>
		</button>
	</div>

</form>