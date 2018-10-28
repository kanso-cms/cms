<div class="list-powers">
	
    <!-- type AND SORTS -->
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
						<li><a href="/admin/logs/error-logs/?<?php echo "date=$queries[date]&type=$queries[type]&sort=newest"; ?>" <?php if ($queries['sort'] === 'newest') echo 'class="selected"'; ?>>Newest</a></li>
						<li><a href="/admin/logs/error-logs/?<?php echo "date=$queries[date]&type=$queries[type]&sort=oldest"; ?>" <?php if ($queries['sort'] === 'oldest') echo 'class="selected"'; ?>>Oldest</a></li>
						<li><a href="/admin/logs/error-logs/?<?php echo "date=$queries[date]&type=$queries[type]&sort=type"; ?>" <?php if ($queries['sort'] === 'type') echo 'class="selected"'; ?>>Type</a></li>
		            </ul>
		        </div>
		    </div>
		</div>
		<div class="drop-container">
		    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
		        Error Types
		        &nbsp;<span class="caret-s"></span>
		    </button>
		    <div class="drop-menu drop-sw">
		        <div class="drop">
		            <ul>
		                <li class="drop-header">Filter by:</li>
		                <li><a href="/admin/logs/error-logs/?<?php echo "date=$queries[date]&type=non404&sort=$queries[sort]"; ?>" <?php if ($queries['type'] === 'non404') echo 'class="selected"'; ?>>Exclude 404</a></li>
		                <li><a href="/admin/logs/error-logs/?<?php echo "date=$queries[date]&type=&sort=$queries[sort]"; ?>" <?php if ($queries['type'] === false) echo 'class="selected"'; ?>>All</a></li>
						<li><a href="/admin/logs/error-logs/?<?php echo "date=$queries[date]&type=parse&sort=$queries[sort]"; ?>" <?php if ($queries['type'] === 'parse') echo 'class="selected"'; ?>>Parse</a></li>
						<li><a href="/admin/logs/error-logs/?<?php echo "date=$queries[date]&type=fatal&sort=$queries[sort]"; ?>" <?php if ($queries['type'] === 'fatal') echo 'class="selected"'; ?>>Fatal</a></li>
		            	<li><a href="/admin/logs/error-logs/?<?php echo "date=$queries[date]&type=warning&sort=$queries[sort]"; ?>" <?php if ($queries['type'] === 'warning') echo 'class="selected"'; ?>>Warning</a></li>
		            	<li><a href="/admin/logs/error-logs/?<?php echo "date=$queries[date]&type=notice&sort=$queries[sort]"; ?>" <?php if ($queries['type'] === 'notice') echo 'class="selected"'; ?>>Notice</a></li>
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
		                <li><a href="/admin/logs/error-logs/?<?php echo "date=&type=$queries[type]&sort=$queries[sort]"; ?>" <?php if ($queries['date'] === false) echo 'class="selected"'; ?>>All</a></li>
						<li><a href="/admin/logs/error-logs/?<?php echo "date=today&type=$queries[type]&sort=$queries[sort]"; ?>" <?php if ($queries['date'] === 'today') echo 'class="selected"'; ?>>Today</a></li>
						<li><a href="/admin/logs/error-logs/?<?php echo "date=yesterday&type=$queries[type]&sort=$queries[sort]"; ?>" <?php if ($queries['date'] === 'yesterday') echo 'class="selected"'; ?>>Yesterday</a></li>
		            	<li><a href="/admin/logs/error-logs/?<?php echo "date=last_7_days&type=$queries[type]&sort=$queries[sort]"; ?>" <?php if ($queries['date'] === 'last_7_days') echo 'class="selected"'; ?>>Last 7 Days</a></li>
		            	<li><a href="/admin/logs/error-logs/?<?php echo "date=last_14_days&type=$queries[type]&sort=$queries[sort]"; ?>" <?php if ($queries['date'] === 'last_14_days') echo 'class="selected"'; ?>>Last 14 Days</a></li>
		            	<li><a href="/admin/logs/error-logs/?<?php echo "date=last_30_days&type=$queries[type]&sort=$queries[sort]"; ?>" <?php if ($queries['date'] === 'last_30_days') echo 'class="selected"'; ?>>Last 30 Days</a></li>
		            	<li><a href="/admin/logs/error-logs/?<?php echo "date=last_60_days&type=$queries[type]&sort=$queries[sort]"; ?>" <?php if ($queries['date'] === 'last_60_days') echo 'class="selected"'; ?>>Last 60 Days</a></li>
		            	<li><a href="/admin/logs/error-logs/?<?php echo "date=last_90_days&type=$queries[type]&sort=$queries[sort]"; ?>" <?php if ($queries['date'] === 'last_90_days') echo 'class="selected"'; ?>>Last 90 Days</a></li>
		            </ul>
		        </div>
		    </div>
		</div>

		<a href="/admin/logs/error-logs/" class="btn <?php echo !$empty_queries ? 'btn-info' : ''; ?> tooltipped tooltipped-s" data-tooltip="Clear filters &amp; sorts">
			<span class="glyph-icon glyph-icon-times"></span>
		</a>
	</div>

	<!-- CLEAR -->
	<form method="post" class="inline-block float-right">
	    <div class="form-field">
	        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
        	<input type="hidden" name="form_name" value="clear-logs">
        	<button type="submit" class="btn btn-danger">Clear Logs</button>
	    </div>
	</form>

</div>