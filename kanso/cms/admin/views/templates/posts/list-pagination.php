<?php 
if ($queries['page'] == 0) $queries['page'] = 1; 
if ($max_page == 0) $max_page = 1;
?>
<div class="list-footer">
	<nav>
	    <ul class="pagination">
	    	<?php if ($queries['page'] < 2) : ?>
	    		<li class="disabled"><span>«</span></li>
	    		<li class="disabled"><span>‹</span></li>
	    	<?php else : ?>
	    		<?php $prev = $queries['page'] - 1; ?>
	    		<li><a href="/admin/<?php echo $postSlug;?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=$queries[sort]&search=$queries[search]&author=$queries[author]&page=0";?>">«</a></li>
	    		<li><a href="/admin/<?php echo $postSlug;?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=$queries[sort]&search=$queries[search]&author=$queries[author]&page=$prev";?>">‹</a></li>
	    	<?php endif; ?>

	    	<li class="elips"><span><?php echo "$queries[page] of $max_page";?></span></li>

	    	<?php if ($queries['page'] < $max_page) : ?>
				<?php $next = $queries['page'] + 1; ?>
	    		<li><a href="/admin/<?php echo $postSlug;?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=$queries[sort]&search=$queries[search]&author=$queries[author]&page=$next";?>">›</a></li>
	    		<li><a href="/admin/<?php echo $postSlug;?>/?<?php echo "tag=$queries[tag]&category=$queries[category]&status=$queries[status]&sort=$queries[sort]&search=$queries[search]&author=$queries[author]&page=$max_page";?>">»</a></li>
	    	<?php else : ?>
	    		<li class="disabled"><span>›</span></li>
	    		<li class="disabled"><span>»</span></li>
	    	<?php endif; ?>
	    </ul>
	</nav>
</div>