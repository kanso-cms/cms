<div class="reviewer js-review-wrap">

	<div class="container">
		
		<div class="col-12 col-md-8 center-horizontal">
			
			<form class="ajax-form js-writer-form">

				<div class="feature-img js-feature-img <?php if (!empty($the_post->thumbnail_id)) echo 'active'; ?>">
					<div class="form-field row floor-xs">
						<label>Feature Image</label>
						<?php 
						if (!empty($the_post->thumbnail_id)) {
							echo display_thumbnail(the_post_thumbnail($the_post->id), 'original', '', '', 'js-feature-img'); 
						}
						else {
							echo '<img src="" class="js-feature-img" >';
						}
						?>
						<input  type="hidden" name="thumbnail_id" class="js-feature-id" value="<?php echo $the_post->thumbnail_id;?>" />
						<button type="button" class="btn select-img-trigger js-select-img-trigger js-show-media-lib">Select image</button>
						<button type="button" class="btn remove-img-trigger js-remove-img-trigger">Remove image</button>
					</div>
				</div>

				<div class="form-field row floor-xs">
					<label for="title">Title</label>
					<p class="color-gray">The title for this post.</p>
					<input type="text" name="title" id="title" value="<?php echo $the_post->title;?>" autocomplete="off"/>
				</div>

				<div class="form-field row floor-xs">
					<label for="category">Category</label>
					<p class="color-gray">Choose an article category.</p>
					<input type="text" name="category" id="category" value="<?php echo $the_post->category['name'] === 'Uncategorized' ? '' :  $the_post->category['name'];?>" autocomplete="off"/>
				</div>

				<div class="form-field row floor-xs">
					<label for="tags">Tags</label>
					<p class="color-gray">Enter a comma separated list of tags.</p>
					<input type="text" name="tags" id="tags" value="<?php echo $tags_list;?>" autocomplete="off"/>
				</div>

				<div class="form-field row floor-xs">
					<label for="excerpt">Excerpt</label>
					<p class="color-gray">Excerpts are used for SEO as well as templating. Leave blank to have it generated automatically.</p>
					<textarea type="text" name="excerpt" id="excerpt" rows="5"><?php echo $the_post->excerpt;?></textarea>
				</div>

				<div class="form-field row floor-xs">
					<label for="type">Type</label>
					<p class="color-gray">Set the post type.</p>
					<select name="type">
						<?php $postTypes = $ADMIN_INCLUDES->postTypes(); ?>
						<?php foreach ($postTypes as $typeName => $nameVal) : ?>
							<option value="<?php echo $nameVal; ?>" <?php echo $the_post->type === $nameVal ? 'selected' : '' ;?>><?php echo $typeName; ?></option>
						<?php endforeach; ?>
						
					</select>
				</div>

				<div class="form-field row floor-sm">
		            <span class="checkbox checkbox-primary">
		                <input type="checkbox" name="comments" id="comments" <?php echo $the_post->comments_enabled == true ? 'checked' : '';?> >
		                <label for="comments">Enable comments</label>
		            </span>
		        </div>
		        
				<button class="btn btn-success with-spinner" type="submit">
	                <svg viewBox="0 0 64 64" class="loading-spinner"><circle class="path" cx="32" cy="32" r="30" fill="none" stroke-width="4"></circle></svg>
	                Publish
	            </button>

			</form>
		</div>
	</div>

</div>