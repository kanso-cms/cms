<div class="row floor-xs">
	<p>Buy these products</p>
	<p class="text-italic color-gray">Customers will need to buy <strong>all</strong> of these products.</p>
	<div class="table-responsive"><table class="table js-bogo-products-in-table <?php echo $isBunldeBogo ? '' : 'empty-table'; ?>"><thead><tr><th></th><th></th><th></th><th></th><th></th><th></th></tr></thead><tbody>
	<?php if ($isBunldeBogo) : foreach ($post_meta['bundle_configuration']['products_in'] as $entry) : ?>
	<?php
	    $product = $kanso->PostManager->byId($entry['product_id']);
	    $sku   = $kanso->ProductProvider->sku($entry['product_id'], $entry['sku']);
	    if (!$sku || !$product) continue;
	?>
	<tr class="js-product-entry" data-quantity="<?php echo $entry['quantity']; ?>" data-product-id="<?php echo $entry['product_id']; ?>" data-sku="<?php echo $entry['sku']; ?>" data-product-title="<?php echo $product->title; ?>" data-product-offer="<?php echo $sku['name']; ?>" data-product-price="<?php echo admin_format_price($sku['price']); ?>" data-product-sale-price="<?php echo admin_format_price($sku['sale_price']); ?>">
	    <th>
	        <span class="form-field">
	            <select name="bundle_product_bogo_in_quantities[]" class="js-product-qnty-select">
	                <?php for ($i = 1; $i < 11; $i++) :?>
	                    <option value="<?php echo $i; ?>" <?php echo $i === $entry['quantity'] ? 'selected' : ''; ?>><?php echo $i; ?></option>
	                <?php endfor; ?>
	            </select>
	        </span>
	    </th>
	    <th>
	        <img width="100" height="100" src="<?php echo the_post_thumbnail_src($product->id); ?>" alt="Product image">
	    </th>
	    <td><strong><?php echo $product->title; ?></strong> - <?php echo $sku['name']; ?></td>
	    <td><span class="color-gray"><del>$<span class="js-reg-price"><?php echo admin_format_price($sku['price']); ?></span></del></span></td>
	    <td>$<span class="js-sale-price"><?php echo admin_format_price($sku['sale_price']); ?></span></td>
	    <td>
	        <button type="button" class="btn btn-outline btn-xs js-remove-product-row">
	            <span class="glyph-icon glyph-icon-minus"></span>
	        </button>
	        <input type="hidden" class="hidden" name="bundle_product_bogo_in_ids[]" value="<?php echo $entry['product_id']; ?>">
	        <input type="hidden" class="hidden" name="bundle_product_offer_bogo_in_ids[]" value="<?php echo $entry['sku']; ?>">
	    </td>
	</tr>
	<?php endforeach; ?>
	<tr class="price-summary">
		<td><strong>Total</strong></td>
		<td></td>
		<td></td>
		<td></td>
		<td><strong>$<span class="js-table-price-total"></span></strong></td>
		<td></td>
	</tr>
	<?php endif; ?>
	</tbody></table></div>
	<button class="btn js-bogo-products-in-trigger" type="button">Select Products</button>
</div>

<p>Get these products</p>
<p class="text-italic color-gray">Customers will receive <strong>all</strong> of these products for free.</p>
<div class="table-responsive"><table class="table js-bogo-products-out-table <?php echo $isBunldeBogo ? '' : 'empty-table'; ?>"><thead><tr><th></th><th></th><th></th><th></th><th></th><th></th></tr></thead><tbody>
	<?php if ($isBunldeBogo) : $total = 0; foreach ($post_meta['bundle_configuration']['products_out'] as $entry) : ?>
	<?php
	    $product = $kanso->PostManager->byId($entry['product_id']);
	    $sku   = $kanso->ProductProvider->sku($entry['product_id'], $entry['sku']);
	    if (!$sku || !$product) continue;
	?>
	<tr class="js-product-entry" data-quantity="<?php echo $entry['quantity']; ?>" data-product-id="<?php echo $entry['product_id']; ?>" data-sku="<?php echo $entry['sku']; ?>" data-product-title="<?php echo $product->title; ?>" data-product-offer="<?php echo $sku['name']; ?>" data-product-price="<?php echo admin_format_price($sku['price']); ?>" data-product-sale-price="<?php echo admin_format_price($sku['sale_price']); ?>">
	    <th>
	        <span class="form-field">
	            <select name="bundle_product_bogo_out_quantities[]" class="js-product-qnty-select">
	                <?php for ($i = 1; $i < 11; $i++) :?>
	                    <option value="<?php echo $i; ?>" <?php echo $i === $entry['quantity'] ? 'selected' : ''; ?>><?php echo $i; ?></option>
	                <?php endfor; ?>
	            </select>
	        </span>
	    </th>
	    <th>
	        <img width="100" height="100" src="<?php echo the_post_thumbnail_src($product->id); ?>" alt="Product image">
	    </th>
	    <td><strong><?php echo $product->title; ?></strong> - <?php echo $sku['name']; ?></td>
	    <td><span class="color-gray"><del>$<span class="js-reg-price"><?php echo admin_format_price($sku['price']); ?></span></del></span></td>
	    <td>$<span class="js-sale-price"><?php echo admin_format_price($sku['sale_price']); ?></span></td>
	    <td>
	        <button type="button" class="btn btn-outline btn-xs js-remove-product-row">
	            <span class="glyph-icon glyph-icon-minus"></span>
	        </button>
	        <input type="hidden" class="hidden" name="bundle_product_bogo_out_ids[]" value="<?php echo $entry['product_id']; ?>">
	        <input type="hidden" class="hidden" name="bundle_product_offer_bogo_out_ids[]" value="<?php echo $entry['sku']; ?>">
	    </td>
	</tr>
	<?php endforeach; ?>
	<tr class="price-summary">
		<td><strong>Total</strong></td>
		<td></td>
		<td></td>
		<td></td>
		<td><strong>$<span class="js-table-price-total"></span></strong></td>
		<td></td>
	</tr>
	<?php endif; ?>
</tbody></table></div>
<button class="btn js-bogo-products-out-trigger" type="button">Select Products</button>