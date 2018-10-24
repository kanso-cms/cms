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
		                <li><a href="/admin/categories/?<?php echo "sort=name&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'name') echo 'class="selected"'; ?>>Name</a></li>
						<li><a href="/admin/categories/?<?php echo "sort=count&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'count') echo 'class="selected"'; ?>>Article count</a></li>
					</ul>
		        </div>
		    </div>
		</div>
		<a href="#" class="btn tooltipped tooltipped-s js-expand-collapse-all" data-tooltip="Expad/Collapse all">
			<span class="glyph-icon glyph-icon-list-ul"></span>
		</a>
		<a href="/admin/categories/" class="btn tooltipped <?php echo !$empty_queries ? 'btn-info' : ''; ?> tooltipped-s" data-tooltip="Clear filters &amp; sorts">
			<span class="glyph-icon glyph-icon-times"></span>
		</a>
		<script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function()
            {
                document.querySelector('.js-expand-collapse-all').addEventListener('click', function (e)
                {
                	e = e || window.event;
                	e.preventDefault();
                	var helper      = Modules.get('JSHelper');
                	var collapseAll = helper.$All('.taxonomy-edit-wrap');
                	var isOpen      = false;

                	for (i = 0; i < collapseAll.length; i++)
                	{
                		if (collapseAll[i].style.height === 'auto')
                		{
                			isOpen = true;
                		}
                	}

                	for (j = 0; j < collapseAll.length; j++)
                	{
                		if (isOpen)
                		{
                			collapseAll[j].style.height = '0';
                		}
                		else
                		{
                			collapseAll[j].style.height = 'auto';
                		}
					}                    
                });
            });
		</script>
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