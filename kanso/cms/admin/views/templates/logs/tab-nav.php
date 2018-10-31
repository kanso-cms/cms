<ul class="tab-nav tab-border">
<?php foreach (admin_sirebar_links()['logs']['children'] as $name => $item) : ?>
	<li><a href="<?php echo $item['link']; ?>" <?php if ($ADMIN_PAGE_TYPE === $name) echo 'class="active"'; ?>><?php echo $item['text']; ?></a></li>
<?php endforeach; ?>
</ul>