<?php $post_meta = !$the_post ? [] : the_post_meta($the_post->id); ?>
<div class="reviewer js-review-wrap">

	<div class="container">
			
		<form class="ajax-form js-writer-form">

			<div class="row floor-sm">
				<div class="card">
	    			<div class="card-header">Post Settings</div>
	    			<div class="card-block">
	    				<p class="color-gray text-italic">Setup your post settings.</p>
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
							<textarea name="excerpt" id="excerpt" rows="5" style="resize:vertical"><?php echo $the_post ? $the_post->excerpt : null; ?></textarea>
						</div>

						<div class="form-field row floor-xs">
							<label for="all_the_authors">Author</label>
							<p class="color-gray">Set the post author.</p>
							<select name="author" id="author">
								<?php foreach (all_the_authors() as $i => $author) : ?>
									<?php
									$selected = false;

									if ($the_post && $the_post->author_id === $author->id)
									{
										$selected = true;
									}
									elseif($author->id === 1)
									{
										$selected = true;
									}
									?>
									<option value="<?php echo $author->id; ?>" <?php echo $selected ? 'selected' : ''; ?>>
										<?php echo $author->name; ?>	
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-field row floor-xs">
							<label for="type">Post Type</label>
							<p class="color-gray">Set or change the post type.</p>
							<select name="type">
								<?php foreach (admin_post_types() as $typeName => $nameVal) : ?>
									<?php if (!$the_post && $kanso->Request->queries('post-type') === $nameVal) : ?>
									<option value="<?php echo $nameVal; ?>" selected><?php echo $typeName; ?></option>
									<?php else : ?>
									<option value="<?php echo $nameVal; ?>" <?php echo (($the_post && $the_post->type === $nameVal) || !$the_post && $nameVal === 'post') ? 'selected' : ''; ?>>
										<?php echo $typeName; ?>	
									</option>
									<?php endif; ?>
								<?php endforeach; ?>
								
							</select>
						</div>

						<div class="form-field row floor-sm">
				            <span class="checkbox checkbox-primary">
				                <input type="checkbox" name="comments" id="comments" <?php echo $the_post && $the_post->comments_enabled == true ? 'checked' : ''; ?> >
				                <label for="comments">Enable comments</label>
				            </span>
				        </div>
				    </div>
			  	</div>
			</div>

		  	<div class="row floor-sm">
			  	<div class="card">
	    			<div class="card-header">SEO</div>
	    			<div class="card-block">
	    				<p class="color-gray text-italic">Your settings for SEO meta.</p>
				        <div class="form-field row floor-xs">
							<label for="meta_title">SEO Meta Title</label>
							<p class="color-gray">The SEO meta title for this post.</p>
							<input type="text" name="meta_title" id="meta_title" value="<?php echo $the_post && isset(the_post_meta($the_post->id)['meta_title']) ? the_post_meta($the_post->id)['meta_title'] : ''; ?>" autocomplete="off"/>
						</div>

						<div class="form-field row floor-xs">
							<label for="meta_description">SEO Meta Description</label>
							<p class="color-gray">The SEO meta description for this post.</p>
							<textarea name="meta_description" id="meta_description" rows="5" style="resize:vertical"><?php echo $the_post && isset(the_post_meta($the_post->id)['meta_description']) ? the_post_meta($the_post->id)['meta_description'] : ''; ?></textarea>
						</div>
					</div>
				</div>
			</div>

			<div class="row floor-sm product-options js-product-options">
			  	<div class="card">
	    			<div class="card-header">Product Options</div>
	    			<div class="card-block">
				        <p class="color-gray text-italic">Offers allow you to manage different product variations.</p>
					    <button class="btn js-add-product-offer" type="button">Add offer +</button>
				        <div class="col-12 col-md-5 roof-xs">
				        	<ul class="tab-nav tab-border js-tab-nav">
						    	<?php if (isset($post_meta['offers'])) : foreach ($post_meta['offers'] as $i => $offer) : ?>
					            <li><a href="#" <?php echo $i === 0 ? 'class="active"' : ''; ?> data-tab="offer-<?php echo $i + 1; ?>">Offer <?php echo $i + 1; ?></a></li>
					        	<?php endforeach; else : ?>
					        	<li><a href="#" data-tab="offer-1" class="active">Offer 1</a></li>
					        	<?php endif; ?>
					        </ul>
					        <div class="tab-panels-wrap js-tab-panels-wrap">
				        		<?php if (isset($post_meta['offers'])) : foreach ($post_meta['offers'] as $i => $offer) : ?>
						        <div class="tab-panel <?php echo $i === 0 ? 'active' : ''; ?>" data-tab-panel="offer-<?php echo $i + 1; ?>">
					        		<div class="form-field row floor-xs">
										<label>SKU</label><input type="text" name="product_offer_<?php echo $i + 1; ?>_id" value="<?php echo $offer['offer_id']; ?>" autocomplete="off" placeholder="SKU001">
									</div>
									<div class="form-field row floor-xs">
										<label>Name</label><input type="text" name="product_offer_<?php echo $i + 1; ?>_name" value="<?php echo $offer['name']; ?>" autocomplete="off" placeholder="XXS">
									</div>
									<div class="form-field row floor-xs">
										<label>Price</label><input type="text" name="product_offer_<?php echo $i + 1; ?>_price" value="<?php echo admin_format_price($offer['price']); ?>" autocomplete="off" placeholder="19.95">
									</div>
									<div class="form-field row floor-xs">
										<label>Sale Price</label><input type="text" name="product_offer_<?php echo $i + 1; ?>_sale_price" value="<?php echo admin_format_price($offer['sale_price']); ?>" autocomplete="off" placeholder="9.95">
									</div>
									<div class="form-field row floor-xs">
										<label>Weight (g)</label><input type="text" name="product_offer_<?php echo $i + 1; ?>_weight" value="<?php echo $offer['weight']; ?>" autocomplete="off" placeholder="500">
									</div>
									<div class="form-field row floor-xs">
							            <span class="checkbox checkbox-primary">
							                <input type="checkbox" name="product_offer_<?php echo $i + 1; ?>_free_shipping" id="product_offer_<?php echo $i + 1; ?>_free_shipping" <?php echo $offer['free_shipping'] === true ? 'checked' : ''; ?>>
							                <label for="product_offer_<?php echo $i + 1; ?>_free_shipping">Free Shipping</label>
							            </span>
							        </div>
									<div class="form-field row floor-xs">
							            <span class="checkbox checkbox-primary">
							                <input type="checkbox" name="product_offer_<?php echo $i + 1; ?>_instock" id="product_offer_<?php echo $i + 1; ?>_instock" <?php echo $offer['instock'] === true ? 'checked' : ''; ?>>
							                <label for="product_offer_<?php echo $i + 1; ?>_instock">In Stock</label>
							            </span>
							        </div>
							        <button class="btn btn-danger js-remove-offer" type="button">Remove Offer</button>
						        </div>
						        <?php endforeach; else : ?>
									<div class="tab-panel active" data-tab-panel="offer-1">
									    <div class="form-field row floor-xs">
									        <label>SKU</label>
									        <input type="text" name="product_offer_1_id" value="" autocomplete="off" placeholder="SKU001">
									    </div>
									    <div class="form-field row floor-xs">
									        <label>Name</label>
									        <input type="text" name="product_offer_1_name" value="" autocomplete="off" placeholder="XXS">
									    </div>
									    <div class="form-field row floor-xs">
									        <label>Price</label>
									        <input type="text" name="product_offer_1_price" value="" autocomplete="off" placeholder="19.95">
									    </div>
									    <div class="form-field row floor-xs">
									        <label>Sale Price</label>
									        <input type="text" name="product_offer_1_sale_price" value="" autocomplete="off" placeholder="9.95">
									    </div>
									    <div class="form-field row floor-xs">
									        <label>Weight (g)</label>
									        <input type="text" name="product_offer_1_weight" value="" autocomplete="off" placeholder="900">
									    </div>
									    <div class="form-field row floor-xs"><span class="checkbox checkbox-primary"><input type="checkbox" name="product_offer_1_free_shipping" id="product_offer_1_free_shipping"><label for="product_offer_1_free_shipping">Free Shipping</label></span></div>
									    <div class="form-field row floor-xs"><span class="checkbox checkbox-primary"><input type="checkbox" name="product_offer_1_instock" id="product_offer_1_instock" checked=""><label for="product_offer_1_instock">In Stock</label></span></div>
									    <button class="btn btn-danger js-remove-offer" type="button">Remove Offer</button>
									</div>
								<?php endif; ?>
						    </div>
				    	</div>
				    </div>
				</div>
			</div>

			<?php
				$isBundle      = $the_post && isset($post_meta['bundle_configuration']['type']) && $the_post->type === 'bundle';
				$isBunldeGroup = $isBundle && $post_meta['bundle_configuration']['type'] === 'group';
				$isBunldeCombo = $isBundle && $post_meta['bundle_configuration']['type'] === 'combo';
				$isBunldeBogo  = $isBundle && $post_meta['bundle_configuration']['type'] === 'bogo';
			?>
			<div class="row floor-sm bundle-options js-bundle-options">
			  	<div class="card">
	    			<div class="card-header">Bundle Options</div>
	    			<div class="card-block">
				       	<p class="color-gray text-italic">Bundle products together to sell at a discount.</p>
					    <div class="col-12 col-md-6">
						    <div class="form-field row floor-xs">
								<label for="bundle_type">Bundle Type</label>
								<p class="color-gray">Choose a bundle type</p>
								<select name="bundle_type" id="bundle_type" class="js-bundle-type-select">
								<?php if ($isBundle) : ?>
									<option value="group" <?php echo $isBunldeGroup ? 'selected' : ''; ?>>Group  : A fixed group of products offered at a discount</option>
									<option value="combo" <?php echo $isBunldeCombo ? 'selected' : ''; ?>>Combo  : A user-customizable group of products offered at a fixed price</option>
									<option value="bogo"  <?php echo $isBunldeBogo  ? 'selected' : ''; ?>>Buy/Get : Buy any number of one product and get another free</option>
								<?php else : ?>
									<option value="group" selected>Group  : A fixed group of products offered at a discount</option>
									<option value="combo">Combo  : A user-customizable group of products offered at a fixed price</option>
									<option value="bogo">Buy/Get : Buy any number of one product and get another free</option>
								<?php endif; ?>
								</select>
							</div>
						</div>

						<div class="bundle-option js-bundle-option js-bundle-option-group <?php echo $isBunldeGroup ? 'active' : ''; ?>" data-bundle-type="group">
							<?php require 'ecommerce/bundle-group-options.php'; ?>
						</div>

						<div class="bundle-option js-bundle-option js-bundle-option-bogo <?php echo $isBunldeBogo ? 'active' : ''; ?>" data-bundle-type="bogo">
							<?php require 'ecommerce/bundle-bogo-options.php'; ?>
						</div>

						<div class="bundle-option js-bundle-option js-bundle-option-combo <?php echo $isBunldeCombo ? 'active' : ''; ?>" data-bundle-type="combo">
							<?php require 'ecommerce/bundle-combo-options.php'; ?>
						</div>

						<?php require 'ecommerce/products-list.php'; ?>
					</div>
				</div>
			</div>  

			<div class="row floor-sm">
			  	<div class="card">
	    			<div class="card-header">Post Meta</div>
		    		<div class="card-block">
		    			<p class="color-gray text-italic">Add post meta key/values to save custom data to your post.</p>
			        	<div class="row">
				            <button class="btn js-add-post-meta-btn" type="button">Add field +</button>
				            <div class="row floor-sm js-post-meta-container">
				            	<?php if ($the_post) :
			            		$post_meta = the_post_meta($the_post->id);
			            		if (is_array($post_meta) && !empty($post_meta)) : foreach ($post_meta as $key => $value) : if (is_array($value) || $key === 'meta_description' || $key === 'meta_title') continue; ?>
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
