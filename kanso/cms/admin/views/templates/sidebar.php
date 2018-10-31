<!-- SIDEBAR -->
<section class="sidebar raised js-sidebar">
	<div class="sb-header">
		<a class="logo js-toggle-sb" href="#">
			<svg viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo-white"></use></svg>
		</a>
	</div>
	<nav>
		<ul class="list-unstyled">
			<?php $sbLinks  = admin_sirebar_links(); foreach ($sbLinks as $itemName => $item) : ?>
			<?php $activeSB = $itemName === admin_page_name() ? 'active' : ''; ?>
			<?php
				if (!empty($item['children'])) {
					foreach ($item['children'] as $_subName => $_subItem)
					{
						if ($_subName === admin_page_name()) {
							$activeSB = 'active';
						}
					}
				}
			?>
			<li class="<?php echo $activeSB; ?>">
				<?php if (!empty($item['children'])) : ?>
				<span class="glyph-icon glyph-icon-chevron-down toggle-list js-toggle-down"></span>
				<?php endif; ?>
				<a href="<?php echo $item['link']; ?>">
					<span class="glyph-icon glyph-icon-<?php echo $item['icon']; ?>"></span>
					<?php echo $item['text']; ?>
				</a>
				<?php if (!empty($item['children'])) : ?>
				<ul class="list-unstyled">
					<?php foreach ($item['children'] as $subName => $subItem) : ?>
						<?php $activeSB = $subName === admin_page_name() ? 'active' : ''; ?>
						<li class="<?php echo $activeSB; ?>">
							<a href="<?php echo $subItem['link']; ?>">
								<span class="glyph-icon glyph-icon-<?php echo $subItem['icon']; ?>"></span>
								<?php echo $subItem['text']; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</nav>
</section>