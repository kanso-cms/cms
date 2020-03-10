<p>Bundled products</p>
<p class="text-italic color-gray">Customers will need to buy <strong>all</strong> of these products to receive a discount.</p>
<div class="table-responsive"><table class="table js-group-products-table <?php echo $isBunldeGroup ? '' : 'empty-table'; ?>"><thead><tr><th></th><th></th><th></th><th></th><th></th><th></th></tr></thead><tbody>
	<?php if ($isBunldeGroup) : foreach ($post_meta['bundle_configuration']['products'] as $entry) : ?>
	<?php
	    $product = $kanso->PostManager->byId($entry['product_id']);
	    $offer   = $kanso->Ecommerce->products()->offer($entry['product_id'], $entry['offer_id']);
	    if (!$offer || !$product) continue;
	?>
	<tr class="js-product-entry" data-quantity="<?php echo $entry['quantity']; ?>" data-product-id="<?php echo $entry['product_id']; ?>" data-offer-id="<?php echo $entry['offer_id']; ?>" data-product-title="<?php echo $product->title; ?>" data-product-offer="<?php echo $offer['name']; ?>" data-product-price="<?php echo admin_format_price($offer['price']); ?>" data-product-sale-price="<?php echo admin_format_price($offer['sale_price']); ?>">
	    <th>
	        <span class="form-field">
	            <select name="bundle_product_quantities[]" class="js-product-qnty-select">
	                <?php for ($i = 1; $i < 11; $i++) :?>
	                    <option value="<?php echo $i; ?>" <?php echo $i === $entry['quantity'] ? 'selected' : ''; ?>><?php echo $i; ?></option>
	                <?php endfor; ?>
	            </select>
	        </span>
	    </th>
	    <th>
	        <img width="100" height="100" src="<?php echo the_post_thumbnail_src($product->id); ?>" alt="Product image">
	    </th>
	    <td><strong><?php echo $product->title; ?></strong> - <?php echo $offer['name']; ?></td>
	    <td><span class="color-gray"><del>$<span class="js-reg-price"><?php echo admin_format_price($offer['price']); ?></span></del></span></td>
	    <td>$<span class="js-sale-price"><?php echo admin_format_price($offer['sale_price']); ?></span></td>
	    <td>
	        <button type="button" class="btn btn-outline btn-xs js-remove-product-row">
	            <span class="glyph-icon glyph-icon-minus"></span>
	        </button>
	        <input type="hidden" class="hidden" name="bundle_product_ids[]" value="<?php echo $entry['product_id']; ?>">
	        <input type="hidden" class="hidden" name="bundle_product_offer_ids[]" value="<?php echo $entry['offer_id']; ?>">
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
<button class="btn js-group-products-trigger" type="button">Select Products</button>
<div class="col-12 col-md-5 roof-xs">
	<div class="form-field row">
		<label for="bundle_percentage_discount">Discount</label>
		<p class="color-gray">Set a discount percentage.</p>
	</div>
	<div class="form-field field-group row floor-xs">
        <input type="text" name="bundle_percentage_discount" id="bundle_percentage_discount" class="js-group-discount-input" placeholder="10" value="<?php echo $isBunldeGroup && $post_meta['bundle_configuration']['discount'] > 0 ? $post_meta['bundle_configuration']['discount'] : ''; ?>">
        <span class="input-addon">%</span>
    </div>

    <div class="form-field row">
		<label for="bundle_group_fixed_price">Fixed Price</label>
		<p class="color-gray">Set a fixed price bundle (overrides percentage discount).</p>
	</div>
	<div class="form-field field-group row floor-xs">
        <label class="input-addon" for="bundle_group_fixed_price">$</label>
        <input type="text" name="bundle_group_fixed_price" id="bundle_group_fixed_price" class="js-group-fixed-price-input" placeholder="9.95" value="<?php echo $isBunldeGroup && $post_meta['bundle_configuration']['price'] > 0 ? $post_meta['bundle_configuration']['price'] : ''; ?>">
    </div>

    <div class="form-field row">
		<label for="bundle_ovveride_cents">Override Cent</label>
		<p class="color-gray">Ensure your bundle price always ends in a specific number (i.e. .99, .95, etc.).</p>
	</div>
	<div class="form-field field-group row floor-xs">
        <label class="input-addon" for="bundle_ovveride_cents">$ XX . </label>
        <input type="text" name="bundle_ovveride_cents" id="bundle_ovveride_cents" class="js-group-cents-override-input" placeholder="95" value="<?php echo  $isBunldeGroup && $post_meta['bundle_configuration']['override_cents'] > 0 ? $post_meta['bundle_configuration']['override_cents'] : ''; ?>">
    </div>
    <span class="group-summary-price">Your customers will pay: $<strong class="js-group-total-price">0.00</strong></span>
</div>