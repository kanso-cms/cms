<div class="list-powers">
	 
	<!-- CHECK ALL -->
	<div class="form-field">    
        <span class="checkbox checkbox-primary">
            <input type="checkbox" id="cb-article-checkall" class="js-list-check-all">
            <label for="cb-article-checkall"></label>
        </span>
    </div>

	<!-- BULK ACTIONS -->
	<form class="inline-block js-bulk-actions-form" method="post">
		<div class="form-field field-group">
	    	<select name="bulk_action">
				<option value="" selected="">Bulk actions</option>
				<option value="clear">Clear</option>
				<option value="delete">Delete</option>
			</select>
			<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
			<button type="submit" class="btn">Apply</button>
	    </div>
	</form>
	
	<span>&nbsp;&nbsp;</span>
	
    <!-- STATUS AND SORTS -->
    <div class="btn-group inline-block">

	    <div class="drop-container">
		    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
		        Sort
		        &nbsp;<span class="caret-s"></span>
		    </button>
		    <div class="drop-menu drop-sw">
		        <div class="drop">
		            <ul>
		                <li class="drop-header">Sort by:</li>
		                <li><a href="/admin/tags/?<?php echo "sort=name&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'name') echo 'class="selected"'; ?>>Name</a></li>
						<li><a href="/admin/tags/?<?php echo "sort=count&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'count') echo 'class="selected"'; ?>>Article count</a></li>
					</ul>
		        </div>
		    </div>
		</div>
		<a href="/admin/tags/" class="btn tooltipped <?php echo !$empty_queries ? 'btn-info' : ''; ?> tooltipped-s" data-tooltip="Clear filters &amp; sorts">
			<span class="glyph-icon glyph-icon-times"></span>
		</a>
	</div>

	<!-- SEARCH -->
	<form method="get" class="inline-block float-right">
	    <div class="form-field field-group ">
	        <input type="text" name="search" id="search" placeholder="Search..." value="<?php echo $queries['search']; ?>">
	        <input type="hidden" name="sort" value="<?php echo $queries['sort']; ?>">
	        <button type="submit" class="btn btn-primary">
	        	&nbsp;&nbsp;<span class="glyph-icon glyph-icon-search"></span>&nbsp;&nbsp;
	        </button>
	    </div>
	</form>

</div>