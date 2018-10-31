<div class="list-powers">
	 
	<!-- CHECK ALL -->
	<div class="form-field">    
        <span class="checkbox checkbox-primary">
            <input type="checkbox" id="cb-order-checkall" class="js-list-check-all">
            <label for="cb-order-checkall"></label>
        </span>
    </div>

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
		                <li><a href="/admin/logs/email-logs/?<?php echo "filter=$queries[filter]&sort=date&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'date') echo 'class="selected"'; ?>>Send Date</a></li>
		                <li><a href="/admin/logs/email-logs/?<?php echo "filter=$queries[filter]&sort=to_email&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'to_email') echo 'class="selected"'; ?>>To Address</a></li>
		                <li><a href="/admin/logs/email-logs/?<?php echo "filter=$queries[filter]&sort=from_email&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'from_email') echo 'class="selected"'; ?>>From Address</a></li>
		                <li><a href="/admin/logs/email-logs/?<?php echo "filter=$queries[filter]&sort=from_name&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'from_name') echo 'class="selected"'; ?>>From Name</a></li>
		                <li><a href="/admin/logs/email-logs/?<?php echo "filter=$queries[filter]&sort=subject&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'subject') echo 'class="selected"'; ?>>Subject</a></li>
		            </ul>
		        </div>
		    </div>
		</div>
		<div class="drop-container">
		    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
		        Type
		        &nbsp;<span class="caret-s"></span>
		    </button>
		    <div class="drop-menu drop-sw">
		        <div class="drop">
		            <ul>
		                <li class="drop-header">Filter by:</li>
		                <li><a href="/admin/logs/email-logs/?<?php echo "filter=all&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['filter'] === 'all') echo 'class="selected"'; ?>>All</a></li>
						<li><a href="/admin/logs/email-logs/?<?php echo "filter=html&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['filter'] === 'html') echo 'class="selected"'; ?>>HTML Emails</a></li>
						<li><a href="/admin/logs/email-logs/?<?php echo "filter=text&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['filter'] === 'text') echo 'class="selected"'; ?>>Plain Text</a></li>

		            </ul>
		        </div>
		    </div>
		</div>

		<a href="/admin/logs/email-logs/" class="btn <?php echo !$empty_queries ? 'btn-info' : ''; ?> tooltipped tooltipped-s" data-tooltip="Clear filters &amp; sorts">
			<span class="glyph-icon glyph-icon-times"></span>
		</a>
	</div>

	<!-- SEARCH -->
	<form method="get" class="inline-block float-right">
	    <div class="form-field field-group ">
	        <input type="text" name="search" id="search" placeholder="Search..." value="<?php echo $queries['search']; ?>">
	        <input type="hidden" name="filter" value="<?php echo $queries['filter']; ?>">
	        <input type="hidden" name="sort" value="<?php echo $queries['sort']; ?>">
	        <button type="submit" class="btn btn-primary">
	        	&nbsp;&nbsp;<span class="glyph-icon glyph-icon-search"></span>&nbsp;&nbsp;
	        </button>
	    </div>
	</form>

</div>