<h3>Kanso Tools</h3>

<?php // <!-- KANSO TOOLS --> ?> 

<div class="input-wrap row">
	
	<label class="bold">Batch Import Articles</label>
	<p class="info text-masked">
		Use this tool to batch import articles into your Kanso installation by uploading a valid JSON file. 
		Find out more <strong><a href="#">here</a></strong> on how to create a valid JSON file for importing.
	</p>
	<div class="clearfix" style="height:10px;"></div>
	<a class="button file-input submit with-spinner js-batch-import" href="#">
		Import Articles
		<input type="file" name="import" accept=".json"/>
		<span class="spinner1"></span>
		<span class="spinner2"></span>
	</a>

</div>

<div class="input-wrap row">
	
	<label class="bold">Batch Upload Images</label>
	<p class="info text-masked">
		Use this tool to batch upload a folder of images into your Kanso installation.
		This is usefull when batch importing articles or when migrating from another framework.
	</p>
	<div class="clearfix" style="height:10px;"></div>
	<a class="button file-input submit with-spinner js-batch-images" href="#">
		Upload Images
		<input type="file" name="images" accept="image/*" multiple />
		<span class="spinner1"></span>
		<span class="spinner2"></span>
	</a>

</div>

<div class="input-wrap row">
	
	<label class="bold">Restore Defaults</label>
	<p class="info text-masked">
		Use this tool to restore Kanso's databse to its origional state. 
		Wanring, this will delete all data associated with your Kanso installation 
		including all your articles, tags, categories, accounts, passwords and
		immediately log you out of the admin panel. Use with caution.
	</p>
	<div class="clearfix" style="height:10px;"></div>
	<a class="button submit red with-spinner js-clear-kanso-database" href="#">
		Restore Kanso
		<span class="spinner1"></span>
		<span class="spinner2"></span>
	</a>

</div>
