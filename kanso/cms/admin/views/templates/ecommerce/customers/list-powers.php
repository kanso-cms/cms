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
				<option value="confirmed">Confirmed</option>
				<option value="pending">Account pending</option>
				<option value="locked">Account locked</option>
				<option value="banned">Account banned</option>
				<option value="delete">Delete</option>
			</select>
			<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
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
						<li><a href="/admin/e-commerce/customers/?<?php echo "status=$queries[status]&sort=email&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'email') echo 'class="selected"';?>>Email</a></li>
						<li><a href="/admin/e-commerce/customers/?<?php echo "status=$queries[status]&sort=name&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'name') echo 'class="selected"';?>>Name</a></li>
						<li><a href="/admin/e-commerce/customers/?<?php echo "status=$queries[status]&sort=status&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'status') echo 'class="selected"';?>>Status</a></li>
						<li><a href="/admin/e-commerce/customers/?<?php echo "status=$queries[status]&sort=id&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'id') echo 'class="selected"';?>>Id</a></li>
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
		                <li><a href="/admin/e-commerce/customers/?<?php echo "status=&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['status'] === false) echo 'class="selected"';?>>All</a></li>
						<li><a href="/admin/e-commerce/customers/?<?php echo "status=confirmed&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['status'] === 'confirmed') echo 'class="selected"';?>>Confirmed</a></li>
						<li><a href="/admin/e-commerce/customers/?<?php echo "status=pending&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['status'] === 'pending') echo 'class="selected"';?>>Pending</a></li>
						<li><a href="/admin/e-commerce/customers/?<?php echo "status=locked&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['status'] === 'locked') echo 'class="selected"';?>>Locked</a></li>
      					<li><a href="/admin/e-commerce/customers/?<?php echo "status=banned&sort=$queries[sort]&search=$queries[search]"; ?>" <?php if ($queries['status'] === 'banned') echo 'class="selected"';?>>Banned</a></li>
		            </ul>
		        </div>
		    </div>
		</div>
		

		<a href="/admin/e-commerce/customers/" class="btn <?php echo !$empty_queries ? 'btn-info' : '';?> tooltipped tooltipped-s" data-tooltip="Clear filters &amp; sorts">
			<span class="glyph-icon glyph-icon-times"></span>
		</a>
	</div>

	<!-- SEARCH -->
	<form method="get" class="inline-block float-right">
	    <div class="form-field field-group ">
	        <input type="text" name="search" id="search" placeholder="Search..." value="<?php echo $queries['search'];?>">
	        <input type="hidden" name="status" value="<?php echo $queries['status'];?>">
	        <input type="hidden" name="sort" value="<?php echo $queries['sort'];?>">
	        <button type="submit" class="btn btn-primary">
	        	&nbsp;&nbsp;<span class="glyph-icon glyph-icon-search"></span>&nbsp;&nbsp;
	        </button>
	    </div>
	</form>

</div>