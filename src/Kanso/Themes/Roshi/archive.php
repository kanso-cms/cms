<?php
/**
 * The archive page template file
 *
 * This file will be loaded whenever a
 * request for a the archive is made
 * e.g http://example.com/archive/
 *
 */
the_header(); ?>

<div class="col col-12 no-gutter tablet-col tablet-huge-gutter tablet-col-9 tablet-right-gutter">

	<h1>Archives</h1>

	<?php $archive = archives(); ?>

	<?php if ( have_posts() ) : ?>

		<?php foreach ($archive as $year => $month_group) : ?>
			<ul class="year">
				<li>
					<span><?php echo $year; ?></span>
					<?php foreach ($month_group as $month_name => $posts) : ?>
						<ul>
							<li>
								<span><?php echo $month_name; ?></span>
								<ol>
									<?php foreach ($posts as $post) : ?>
									<li>
										<a href="<?php echo the_permalink($post->id);?>"><?php echo the_title($post->id); ?></a>
									</li>
									<?php endforeach; ?>
								</ol>
							</li>
						</ul>
					<?php endforeach; ?>
				</li>
			</ul>
		<?php endforeach; ?>


	<?php else : ?>

		<div class="info message">
            <div class="message-icon">
                <span class="ion">⊝</span>
            </div>
            <div class="message-body">
                <p>Sorry, there are no posts to display.</p>
            </div>
        </div>

    <?php endif; ?>
	
</div>

<?php the_sidebar(); ?>

<?php the_footer(); ?>