<?php
/**
 * Search form template file
 *
 * This is the template file for search forms but is not required by Kanso
 * If the file exists, Kanso will load this template whenever
 * get_search_form() is called. If this file does not exist, the 
 * default Kanso search form will be included.
 *
 */
?>
<form role="search" method="get" action="<?php home_url();?>/search-results/">

	<label for="q" hidden>Search:</label>

	<div class="row">
	    <input type="search" name="q" id="q" class="field" placeholder="Search...">
	</div>

	<div class="row">
	    <button type"submit" class="button primary">Search</button>
	</div>
    
</form>