<!-- SIDEBAR -->
<section class="sidebar raised js-sidebar">
	<div class="sb-header">
		<a class="logo js-toggle-sb" href="#">
			<svg viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo-white"></use></svg>
		</a>
	</div>
	<nav>
		<ul class="list-unstyled">
			<?php $sbLinks  = $ADMIN_INCLUDES->sidebarLinks(); foreach ($sbLinks as $itemName => $item) : ?>
			<?php $activeSB = $itemName === $ADMIN_INCLUDES->pageName() ? 'active' : ''; ?>
			<?php 
				if (!empty($item['children'])) {
					foreach ($item['children'] as $_subName => $_subItem) {
						if ($_subName === $ADMIN_INCLUDES->pageName()) {
							$activeSB = 'active';	
						}
					}
				}
			?>
			<li class="<?php echo $activeSB;?>">
				<a href="<?php echo $item['link'];?>">
					<span class="glyph-icon glyph-icon-<?php echo $item['icon'];?>"></span>
					<?php echo $item['text'];?>
				</a>
				<?php if (!empty($item['children'])) : ?>
				<ul class="list-unstyled">
					<?php foreach ($item['children'] as $subName => $subItem) : ?>
						<?php $activeSB = $subName === $ADMIN_INCLUDES->pageName() ? 'active' : ''; ?>
						<li class="<?php echo $activeSB;?>"><a href="<?php echo $subItem['link'];?>"><?php echo $subItem['text'];?></a></li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</nav>
</section>