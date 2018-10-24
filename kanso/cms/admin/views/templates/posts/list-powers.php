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
				<option value="published">Publish</option>
				<option value="draft">Draft</option>
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
		                <li><a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=title&search=$queries[search]&author=$queries[author]"; ?>" <?php if ($queries['sort'] === 'title') echo 'class="selected"'; ?>>Title</a></li>
						<li><a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=newest&search=$queries[search]&author=$queries[author]"; ?>" <?php if ($queries['sort'] === 'newest') echo 'class="selected"'; ?>>Newest</a></li>
						<li><a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=oldest&search=$queries[search]&author=$queries[author]"; ?>" <?php if ($queries['sort'] === 'oldest') echo 'class="selected"'; ?>>Oldest</a></li>
						<li><a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=category&search=$queries[search]&author=$queries[author]"; ?>" <?php if ($queries['sort'] === 'category') echo 'class="selected"'; ?>>Category</a></li>
						<li><a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=tags&search=$queries[search]&author=$queries[author]"; ?>" <?php if ($queries['sort'] === 'tags') echo 'class="selected"'; ?>>Tags</a></li>
						<li><a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=drafts&search=$queries[search]&author=$queries[author]"; ?>" <?php if ($queries['sort'] === 'drafts') echo 'class="selected"'; ?>>Drafts</a></li>
						<li><a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=published&search=$queries[search]&author=$queries[author]"; ?>"<?php if ($queries['sort'] === 'published') echo 'class="selected"'; ?>>Published</a></li>
		            </ul>
		        </div>
		    </div>
		</div>
		<div class="drop-container">
		    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
		        Status
		        &nbsp;<span class="caret-s"></span>
		    </button>
		    <div class="drop-menu drop-sw">
		        <div class="drop">
		            <ul>
		                <li class="drop-header">Filter by:</li>
		                <li><a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=&sort=$queries[sort]&search=$queries[search]&author=$queries[author]"; ?>" <?php if ($queries['status'] === false) echo 'class="selected"'; ?>>All</a></li>
						<li><a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=drafts&sort=$queries[sort]&search=$queries[search]&author=$queries[author]"; ?>" <?php if ($queries['status'] === 'drafts') echo 'class="selected"'; ?>>Drafts</a></li>
						<li><a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=published&sort=$queries[sort]&search=$queries[search]&author=$queries[author]"; ?>" <?php if ($queries['status'] === 'published') echo 'class="selected"'; ?>>Published</a></li>
		            </ul>
		        </div>
		    </div>
		</div>
		<div class="drop-container">
		    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
		        Author
		        &nbsp;<span class="caret-s"></span>
		    </button>
		    <div class="drop-menu drop-sw">
		        <div class="drop">
		            <ul>
		                <li class="drop-header">Written by:</li>
	                	<li><a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=$queries[sort]&search=$queries[search]&author="; ?>" <?php if ($queries['author'] === false) echo 'class="selected"'; ?>>All</a></li>
	                	<?php foreach (all_the_authors() as $_author) : ?>
	                	<li>
	                		<a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=$queries[sort]&search=$queries[search]&author=$_author->id"; ?>" <?php if (intval($queries['author']) === $_author->id) echo 'class="selected"'; ?>>
	                			<?php echo $_author->name; ?>
	                		</a>
	                	</li>
	                	<?php endforeach; ?>
		            </ul>
		        </div>
		    </div>
		</div>
		<div class="drop-container">
		    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
		        Category
		        &nbsp;<span class="caret-s"></span>
		    </button>
		    <div class="drop-menu drop-sw">
		        <div class="drop">
		            <ul>
		                <li class="drop-header">Filter by:</li>
	                	<li><a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=&status=$queries[status]&sort=$queries[sort]&search=$queries[search]&author=$queries[author]"; ?>" <?php if ($queries['category'] === false) echo 'class="selected"'; ?>>All</a></li>
	                	<?php foreach (all_the_categories() as $_category) : ?>
	                	<li>
	                		<a href="/admin/<?php echo $postSlug; ?>/?<?php echo "tag=$queries[tag]&category=$_category->id&status=$queries[status]&sort=$queries[sort]&search=$queries[search]&author=$queries[author]"; ?>" <?php if (intval($queries['category']) === $_category->id) echo 'class="selected"'; ?>>
	                			<?php echo $_category->name; ?>
	                		</a>
	                	</li>
	                	<?php endforeach; ?>
		            </ul>
		        </div>
		    </div>
		</div>

		<a href="/admin/<?php echo $postSlug; ?>/" class="btn <?php echo !$empty_queries ? 'btn-info' : ''; ?> tooltipped tooltipped-s" data-tooltip="Clear filters &amp; sorts">
			<span class="glyph-icon glyph-icon-times"></span>
		</a>
	</div>

	<!-- SEARCH -->
	<form method="get" class="inline-block float-right">
	    <div class="form-field field-group ">
	        <input type="text" name="search" id="search" placeholder="Search..." value="<?php echo $queries['search']; ?>">
	        <input type="hidden" name="status" value="<?php echo $queries['status']; ?>">
	        <input type="hidden" name="sort" value="<?php echo $queries['sort']; ?>">
	        <input type="hidden" name="author" value="<?php echo $queries['author']; ?>">
	        <input type="hidden" name="category" value="<?php echo $queries['category']; ?>">
	        <input type="hidden" name="tag" value="<?php echo $queries['tag']; ?>">
	        <button type="submit" class="btn btn-primary">
	        	&nbsp;&nbsp;<span class="glyph-icon glyph-icon-search"></span>&nbsp;&nbsp;
	        </button>
	    </div>
	</form>

</div>