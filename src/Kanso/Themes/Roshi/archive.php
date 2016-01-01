<?php the_header(); ?>
		
		<!-- MAIN CONTENT -->
		<div class="clear">

			<div class="col-12 clear">
				
				<h1>Archives</h1>

			</div>

			<div class="col-12 clear">

			<?php $archive = get_archives(); ?>

			<?php
				foreach ($archive as $year => $month_group) {
					echo '<ul class="year">';
						echo '<li><span>'.$year.'</span>';
							foreach ($month_group as $month_name => $posts) {
								echo '<ul>';
									echo '<li><span>'.$month_name.'</span>';
										echo '<ol>';
										$i = count($posts);
										foreach ($posts as $post) {
											echo '<li><a href="'.get_the_permalink($post['id']).'">'.get_the_title($post['id']).'</a></li>';
											$i--;
										}
										echo '</ol>';
									echo '</li>';
								echo '</ul>';
							}
					echo '</li>';
				echo '</ul>';
					
				}
			?>
			
			</div>

		</div>

		<?php the_sidebar(); ?>


<?php the_footer(); ?>