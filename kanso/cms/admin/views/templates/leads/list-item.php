<tr>
	<th><?php echo $visitor->id; ?></th>
	<th><?php echo $visitor->visitor_id; ?></th>
	<th><?php echo $visitor->name; ?></th>
	<th><?php echo $visitor->email; ?></th>
	<th><?php echo kanso\framework\utility\Humanizer::timeAgo($visitor->last_active); ?> ago</th>
	<th><?php echo $visitor->status; ?></th>
	<th><?php echo $visitor->journey; ?></th>
	<th><?php echo $visitor->countVisits(); ?></th>
	<th><?php echo $visitor->medium(); ?></th>
	<th><?php echo $visitor->channel(); ?></th>
	<th>
		<button type="button" class="btn btn-pure btn-xs btn-primary tooltipped tooltipped-n " data-tooltip="Show visits" onclick="document.getElementById('visits-view-<?php echo $visitor->id; ?>').classList.toggle('hidden');">
			<span class="glyph-icon glyph-icon-eye icon-md"></span>
		</button>
	</th>
</tr>
<tr class="hidden" id="visits-view-<?php echo $visitor->id; ?>" style="width:100%;">
	<th width="100%" colspan="11">
		<ol class="" style="margin-bottom: 20px">
			<?php foreach (array_reverse($visitor->visits()) as $visit) : ?>
		    <li><?php echo $visit->page; ?></li>
		    <?php endforeach; ?>
		</ol>
	</th>
</tr>
