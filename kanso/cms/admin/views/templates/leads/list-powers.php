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
		                <li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=last_active&action=$queries[action]&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'last_active') echo 'class="selected"'; ?>>Last Active</a></li>
						<li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=email&action=$queries[action]&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'email') echo 'class="selected"'; ?>>Email</a></li>
						<li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=name&action=$queries[action]&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'name') echo 'class="selected"'; ?>>Name</a></li>
						<li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=funnel&action=$queries[action]&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'funnel') echo 'class="selected"'; ?>>Funnel</a></li>
						<li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=visits&action=$queries[action]&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'visits') echo 'class="selected"'; ?>>Visits</a></li>
						<li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=id&action=$queries[action]&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['sort'] === 'id') echo 'class="selected"'; ?>>Id</a></li>
		            </ul>
		        </div>
		    </div>
		</div>

		<div class="drop-container">
		    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
		        Actions
		        &nbsp;<span class="caret-s"></span>
		    </button>
		    <div class="drop-menu drop-sw">
		        <div class="drop">
		            <ul>
		                <li class="drop-header">Filter by:</li>
		                <li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['action'] === false) echo 'class="selected"'; ?>>None</a></li>
		                <li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=created-account&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['action'] === 'created-account') echo 'class="selected"'; ?>>Created Account</a></li>
		                <li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=not-bounced&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['action'] === 'not-bounced') echo 'class="selected"'; ?>>Unbounced</a></li>
		                <li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=bounced&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['action'] === 'bounced') echo 'class="selected"'; ?>>Bounced</a></li>
		            	<li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=visited-checkout&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['action'] === 'visited-checkout') echo 'class="selected"'; ?>>Visited Checkout</a></li>
		            </ul>
		        </div>
		    </div>
		</div>

		<div class="drop-container">
		    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
		        Channel
		        &nbsp;<span class="caret-s"></span>
		    </button>
		    <div class="drop-menu drop-sw">
		        <div class="drop">
		            <ul>
		                <li class="drop-header">Filter by:</li>
		                <li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=$queries[action]&medium=$queries[medium]&channel=&search=$queries[search]"; ?>" <?php if ($queries['channel'] === false) echo 'class="selected"'; ?>>All</a></li>
		                <li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=$queries[action]&medium=$queries[medium]&channel=social&search=$queries[search]"; ?>" <?php if ($queries['channel'] === 'social') echo 'class="selected"'; ?>>Social</a></li>
		                <li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=$queries[action]&medium=$queries[medium]&channel=cpc&search=$queries[search]"; ?>" <?php if ($queries['channel'] === 'cpc') echo 'class="selected"'; ?>>CPC</a></li>
		                <li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=$queries[action]&medium=$queries[medium]&channel=display&search=$queries[search]"; ?>" <?php if ($queries['channel'] === 'display') echo 'class="selected"'; ?>>Display</a></li>
		                <li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=$queries[action]&medium=$queries[medium]&channel=email&search=$queries[search]"; ?>" <?php if ($queries['channel'] === 'email') echo 'class="selected"'; ?>>Email</a></li>
		            </ul>
		        </div>
		    </div>
		</div>

		<div class="drop-container">
		    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
		        Medium
		        &nbsp;<span class="caret-s"></span>
		    </button>
		    <div class="drop-menu drop-sw">
		        <div class="drop">
		            <ul>
		                <li class="drop-header">Filter by:</li>
		               	<li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=$queries[action]&medium=&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['medium'] === false) echo 'class="selected"'; ?>>All</a></li>
		               	<li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=$queries[action]&medium=google&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['medium'] === 'google') echo 'class="selected"'; ?>>Google</a></li>
		               	<li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=$queries[action]&medium=facebook&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['medium'] === 'facebook') echo 'class="selected"'; ?>>Facebook</a></li>
		               	<li><a href="/admin/leads/?<?php echo "status=$queries[status]&sort=$queries[sort]&action=$queries[action]&medium=instagram&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['medium'] === 'instagram') echo 'class="selected"'; ?>>Instagram</a></li>
		            </ul>
		        </div>
		    </div>
		</div>

		<div class="drop-container">
		    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
		        Funnel
		        &nbsp;<span class="caret-s"></span>
		    </button>
		    <div class="drop-menu drop-sw">
		        <div class="drop">
		            <ul>
		                <li class="drop-header">Filter by:</li>
		                <li><a href="/admin/leads/?<?php echo "status=&sort=$queries[sort]&action=$queries[action]&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['status'] === false) echo 'class="selected"'; ?>>All</a></li>
						<li><a href="/admin/leads/?<?php echo "status=visitor&sort=$queries[sort]&action=$queries[action]&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['status'] === 'visitor') echo 'class="selected"'; ?>>Visitor</a></li>
						<li><a href="/admin/leads/?<?php echo "status=lead&sort=$queries[sort]&action=$queries[action]&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['status'] === 'lead') echo 'class="selected"'; ?>>Lead</a></li>
						<li><a href="/admin/leads/?<?php echo "status=sql&sort=$queries[sort]&action=$queries[action]&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['status'] === 'sql') echo 'class="selected"'; ?>>Sales Qualified Lead</a></li>
      					<li><a href="/admin/leads/?<?php echo "status=customer&sort=$queries[sort]&action=$queries[action]&medium=$queries[medium]&channel=$queries[channel]&search=$queries[search]"; ?>" <?php if ($queries['status'] === 'customer') echo 'class="selected"'; ?>>Customer</a></li>

		            </ul>
		        </div>
		    </div>
		</div>

		<a href="/admin/leads/" class="btn <?php echo !$empty_queries ? 'btn-info' : ''; ?> tooltipped tooltipped-s" data-tooltip="Clear filters &amp; sorts">
			<span class="glyph-icon glyph-icon-times"></span>
		</a>
	</div>

	<!-- SEARCH -->
	<form method="get" class="inline-block float-right">
	    <div class="form-field field-group ">
	        <input type="text" name="search" id="search" placeholder="Search..." value="<?php echo $queries['search']; ?>">
	        <input type="hidden" name="status" value="<?php echo $queries['status']; ?>">
	        <input type="hidden" name="sort" value="<?php echo $queries['sort']; ?>">
	        <input type="hidden" name="action" value="<?php echo $queries['action']; ?>">
	        <input type="hidden" name="medium" value="<?php echo $queries['medium']; ?>">
	        <input type="hidden" name="channel" value="<?php echo $queries['channel']; ?>">
	        <button type="submit" class="btn btn-primary">
	        	&nbsp;&nbsp;<span class="glyph-icon glyph-icon-search"></span>&nbsp;&nbsp;
	        </button>
	    </div>
	</form>

</div>