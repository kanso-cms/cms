<div class="list-powers">
	 
	<!-- CHECK ALL -->
	<div class="form-field">    
        <span class="checkbox checkbox-primary">
            <input type="checkbox" id="cb-order-checkall" class="js-list-check-all">
            <label for="cb-order-checkall"></label>
        </span>
    </div>

	<!-- BULK ACTIONS -->
	<form class="inline-block js-bulk-actions-form" method="post">
		<div class="form-field field-group">
	    	<select name="bulk_action">
				<option value="" selected="">Bulk actions</option>
				<option value="delivered">Delivered</option>
				<option value="shipped">Shipped</option>
				<option value="received">Received</option>
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
						<li><a href="/admin/e-commerce/orders/?<?php echo "date=$queries[date]&status=$queries[status]&sort=newest&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'newest') echo 'class="selected"'; ?>>Newest</a></li>
						<li><a href="/admin/e-commerce/orders/?<?php echo "date=$queries[date]&status=$queries[status]&sort=oldest&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'oldest') echo 'class="selected"'; ?>>Oldest</a></li>
						<li><a href="/admin/e-commerce/orders/?<?php echo "date=$queries[date]&status=$queries[status]&sort=price&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'price') echo 'class="selected"'; ?>>Price</a></li>
						<li><a href="/admin/e-commerce/orders/?<?php echo "date=$queries[date]&status=$queries[status]&sort=user&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'user') echo 'class="selected"'; ?>>User</a></li>
						<li><a href="/admin/e-commerce/orders/?<?php echo "date=$queries[date]&status=$queries[status]&sort=shipped&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'shipped') echo 'class="selected"'; ?>>Shipped Date</a></li>
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
		                <li><a href="/admin/e-commerce/orders/?<?php echo "date=$queries[date]&status=&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['status'] === false) echo 'class="selected"'; ?>>All</a></li>
						<li><a href="/admin/e-commerce/orders/?<?php echo "date=$queries[date]&status=received&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['status'] === 'received') echo 'class="selected"'; ?>>Received</a></li>
						<li><a href="/admin/e-commerce/orders/?<?php echo "date=$queries[date]&status=shipped&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['status'] === 'shipped') echo 'class="selected"'; ?>>Shipped</a></li>
		            	<li><a href="/admin/e-commerce/orders/?<?php echo "date=$queries[date]&status=delivered&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['status'] === 'delivered') echo 'class="selected"'; ?>>Delivered</a></li>
		            </ul>
		        </div>
		    </div>
		</div>
		<div class="drop-container">
		    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
		        Date
		        &nbsp;<span class="caret-s"></span>
		    </button>
		    <div class="drop-menu drop-sw">
		        <div class="drop">
		            <ul>
		                <li class="drop-header">Filter by date:</li>
		                <li><a href="/admin/e-commerce/orders/?<?php echo "date=&status=$queries[status]&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['date'] === false) echo 'class="selected"'; ?>>All</a></li>
						<li><a href="/admin/e-commerce/orders/?<?php echo "date=today&status=$queries[status]&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['date'] === 'today') echo 'class="selected"'; ?>>Today</a></li>
						<li><a href="/admin/e-commerce/orders/?<?php echo "date=yesterday&status=$queries[status]&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['date'] === 'yesterday') echo 'class="selected"'; ?>>Yesterday</a></li>
		            	<li><a href="/admin/e-commerce/orders/?<?php echo "date=last_7_days&status=$queries[status]&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['date'] === 'last_7_days') echo 'class="selected"'; ?>>Last 7 Days</a></li>
		            	<li><a href="/admin/e-commerce/orders/?<?php echo "date=last_14_days&status=$queries[status]&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['date'] === 'last_14_days') echo 'class="selected"'; ?>>Last 14 Days</a></li>
		            	<li><a href="/admin/e-commerce/orders/?<?php echo "date=last_30_days&status=$queries[status]&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['date'] === 'last_30_days') echo 'class="selected"'; ?>>Last 30 Days</a></li>
		            	<li><a href="/admin/e-commerce/orders/?<?php echo "date=last_60_days&status=$queries[status]&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['date'] === 'last_60_days') echo 'class="selected"'; ?>>Last 60 Days</a></li>
		            	<li><a href="/admin/e-commerce/orders/?<?php echo "date=last_90_days&status=$queries[status]&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['date'] === 'last_90_days') echo 'class="selected"'; ?>>Last 90 Days</a></li>
		            </ul>
		        </div>
		    </div>
		</div>

		<a href="/admin/e-commerce/orders/" class="btn <?php echo !$empty_queries ? 'btn-info' : ''; ?> tooltipped tooltipped-s" data-tooltip="Clear filters &amp; sorts">
			<span class="glyph-icon glyph-icon-times"></span>
		</a>
	</div>

	<!-- SEARCH -->
	<form method="get" class="inline-block float-right">
	    <div class="form-field field-group ">
	        <input type="text" name="search" id="search" placeholder="Search..." value="<?php echo $queries['search']; ?>">
	        <input type="hidden" name="status" value="<?php echo $queries['status']; ?>">
	        <input type="hidden" name="sort" value="<?php echo $queries['sort']; ?>">
	        <input type="hidden" name="date" value="<?php echo $queries['date']; ?>">
	        <button type="submit" class="btn btn-primary">
	        	&nbsp;&nbsp;<span class="glyph-icon glyph-icon-search"></span>&nbsp;&nbsp;
	        </button>
	    </div>
	</form>

</div>