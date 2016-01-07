<div class="site-container cleafix">
	<div class="panel">

		<div class="tabs-wrap js-tabs-wrap js-url-tabs">
			<ul>
				<?php echo adminTabNav(); ?>	
			</ul>
		</div>

		<?php 
		$panels = adminTabPanels();

		foreach ($panels as $panel) : ?>
			
			<div class="tab-panel <?php echo $panel['active'] === true ? 'active' : '';?><?php echo ' '.$panel['class'];?>" id="<?php echo $panel['id'];?>">
				<?php require_once($panel['file_path']);?>
			</div>

		<?php endforeach; ?>

	</div>
</div>
