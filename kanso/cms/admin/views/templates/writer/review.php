<div class="reviewer js-review-wrap">

	<div class="container">
			
		<form class="ajax-form js-writer-form">

			<div class="feature-img js-feature-img <?php echo $the_post && !empty($the_post->thumbnail_id) ? 'active' : null; ?>">
				<div class="form-field row floor-xs">
					<label>Feature Image</label>
					<?php
					if ($the_post && !empty($the_post->thumbnail_id))
					{
						echo display_thumbnail(the_post_thumbnail($the_post->id), 'original', '', '', 'js-feature-img');
					}
					else
					{
						echo '<img src="" class="js-feature-img" >';
					}
					?>
					<input  type="hidden" name="thumbnail_id" class="js-feature-id" value="<?php echo $the_post && !empty($the_post->thumbnail_id) ? $the_post->thumbnail_id : null; ?>" />
					<button type="button" class="btn select-img-trigger js-select-img-trigger js-show-media-lib">Select image</button>
					<button type="button" class="btn remove-img-trigger js-remove-img-trigger">Remove image</button>
				</div>
			</div>

			<div class="form-field row floor-xs">
				<label for="title">Title</label>
				<p class="color-gray">The title for this post.</p>
				<input type="text" name="title" id="title" value="<?php echo $the_post ? $the_post->title : null; ?>" autocomplete="off"/>
			</div>

			<div class="form-field row floor-xs">
				<label for="category">Category</label>
				<p class="color-gray">Enter a comma separated list of categoires.</p>
				<input type="text" name="category" id="category" value="<?php echo $the_post ? admin_writer_categories($the_post->id) : null; ?>" autocomplete="off"/>
			</div>

			<div class="form-field row floor-xs">
				<label for="tags">Tags</label>
				<p class="color-gray">Enter a comma separated list of tags.</p>
				<input type="text" name="tags" id="tags" value="<?php echo $the_post ? the_tags_list($the_post->id) : null; ?>" autocomplete="off"/>
			</div>

			<div class="form-field row floor-xs">
				<label for="excerpt">Excerpt</label>
				<p class="color-gray">Excerpts are used for SEO as well as templating. Leave blank to have it generated automatically.</p>
				<textarea type="text" name="excerpt" id="excerpt" rows="5"><?php echo $the_post ? $the_post->excerpt : null; ?></textarea>
			</div>

			<div class="form-field row floor-xs">
				<label for="type">Type</label>
				<p class="color-gray">Set the post type.</p>
				<select name="type">
					<?php foreach (admin_post_types() as $typeName => $nameVal) : ?>
						<option value="<?php echo $nameVal; ?>" <?php echo (($the_post && $the_post->type === $nameVal) || !$the_post && $nameVal === 'post') ? 'selected' : ''; ?>>
							<?php echo $typeName; ?>	
						</option>
					<?php endforeach; ?>
					
				</select>
			</div>

			<div class="form-field row floor-xs">
				<label for="all_the_authors">Author</label>
				<p class="color-gray">Set the post author.</p>
				<select name="author" id="author">
					<?php foreach (all_the_authors() as $i => $author) : ?>
						<option value="<?php echo $author->id; ?>" <?php echo $i === 0 ? 'selected' : ''; ?>>
							<?php echo $author->name; ?>	
						</option>
					<?php endforeach; ?>
					
				</select>
			</div>

			<div class="form-field row floor-sm">
	            <span class="checkbox checkbox-primary">
	                <input type="checkbox" name="comments" id="comments" <?php echo $the_post && $the_post->comments_enabled == true ? 'checked' : ''; ?> >
	                <label for="comments">Enable comments</label>
	            </span>
	        </div>

	        <div class="form-field row">
				<label>Post Meta</label>
		        <p class="color-gray">Post meta allows you to save additional data to a post.</p>
		    </div>
		    
        	<div class="row">
	            <button class="btn js-add-post-meta-btn" type="button">Add field +</button>
	            
	            <div class="row floor-sm js-post-meta-container">
	            	<?php if ($the_post) :
            		$post_meta = the_post_meta($the_post->id);
            		if (is_array($post_meta) && !empty($post_meta)) : foreach ($post_meta as $key => $value) : if (is_array($value)) continue; ?>
            			<div class="row roof-xs js-meta-row">
	            			<div class="form-field floor-xs">
							    <label>Key</label>
							    <input type="text" name="post-meta-keys[]" value="<?php echo $key; ?>" autocomplete="off" size="20">
							</div>&nbsp;&nbsp;&nbsp;<div class="form-field floor-xs">
							    <label>Value</label>
							    <input type="text" name="post-meta-values[]" value="<?php echo $value; ?>" autocomplete="off" size="60">
							</div>&nbsp;&nbsp;&nbsp;<button class="btn btn-danger js-rmv-post-meta-btn" type="button">Remove</button>

							<div class="row clearfix"></div>
						</div>
	            	<?php endforeach; endif; endif; ?>
	            </div>
	        </div>

	        <div class="row floor-xs product-options js-product-options <?php echo ($the_post && $the_post->type === 'product') ? 'active' : '';?>">
		        <div class="form-field row">
					<label>Product Offers</label>
			        <p class="color-gray">Offers allow you to manage different product variations.</p>
			    </div>

			    <button class="btn js-add-product-offer" type="button">Add offer +</button>

		        <div class="col-12 col-md-5 roof-xs">
		        	<ul class="tab-nav tab-border js-tab-nav">
				    	<?php if (isset($post_meta['offers'])) : foreach ($post_meta['offers'] as $i => $offer) : ?>
			            <li><a href="#" <?php echo $i === 0 ? 'class="active"' : '';?> data-tab="offer-<?php echo $i + 1;?>">Offer <?php echo $i + 1;?></a></li>
			        	<?php endforeach; endif; ?>
			        </ul>
			        <div class="tab-panels-wrap js-tab-panels-wrap">
		        		<?php if (isset($post_meta['offers'])) : foreach ($post_meta['offers'] as $i => $offer) : ?>
				        <div class="tab-panel <?php echo $i === 0 ? 'active' : '';?>" data-tab-panel="offer-<?php echo $i + 1;?>">
			        		<div class="form-field row floor-xs">
								<label>ID</label><input type="text" name="product_offer_<?php echo $i + 1;?>_id" value="<?php echo $offer['offer_id'];?>" autocomplete="off" placeholder="SKU001">
							</div>
							<div class="form-field row floor-xs">
								<label>Name</label><input type="text" name="product_offer_<?php echo $i + 1;?>_name" value="<?php echo $offer['name'];?>" autocomplete="off" placeholder="XXS">
							</div>
							<div class="form-field row floor-xs">
								<label>Price</label><input type="text" name="product_offer_<?php echo $i + 1;?>_price" value="<?php echo $offer['price'];?>" autocomplete="off" placeholder="19.95">
							</div>
							<div class="form-field row floor-xs">
								<label>Sale Price</label><input type="text" name="product_offer_<?php echo $i + 1;?>_sale_price" value="<?php echo $offer['sale_price'];?>" autocomplete="off" placeholder="9.95">
							</div>
							<div class="form-field row floor-xs">
					            <span class="checkbox checkbox-primary">
					                <input type="checkbox" name="product_offer_<?php echo $i + 1;?>_instock" id="product_offer_<?php echo $i + 1;?>_instock" <?php echo $offer['instock'] === true ? 'checked' : '';?>>
					                <label for="product_offer_<?php echo $i + 1;?>_instock">In Stock</label>
					            </span>
					        </div>
					        <button class="btn btn-danger js-remove-offer" type="button">Remove Offer</button>
				        </div>
				        <?php endforeach; endif; ?>
				    </div>
		    	</div>
		    </div>

			<button class="btn btn-success with-spinner" type="submit">
                <svg viewBox="0 0 64 64" class="loading-spinner"><circle class="path" cx="32" cy="32" r="30" fill="none" stroke-width="4"></circle></svg>
                Publish
            </button>

		</form>
	</div>
</div>
