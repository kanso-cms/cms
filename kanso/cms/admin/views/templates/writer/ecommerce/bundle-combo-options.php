<p>Combo products</p>
<p class="text-italic color-gray">Customers can select from <strong>any</strong> of these products.</p>
<?php if ($isBunldeCombo) : ?>
	<?php $s = 1; foreach ($post_meta['bundle_configuration']['products'] as $name => $products) :?>
		<div class="row floor-xs">
			<div class="form-field row floor-xs">
		        <label>Selection Name</label>
		        <input type="text" name="bundle_combo_names[]" placeholder="Free Gift 1" value="<?php echo $name; ?>">
		    </div>
		    <div class="table-responsive"><table class="table js-combo-products-table"><thead><tr><th></th><th></th><th></th><th></th><th></th><th></th></tr></thead><tbody>
			    <?php foreach ($products as $entry) :
				    $product = $kanso->PostManager->byId($entry['product_id']);
				    $sku   = $kanso->ProductProvider->sku($entry['product_id'], $entry['sku']);
				    if (!$sku || !$product) continue;
				?>
				<tr class="js-product-entry" data-quantity="<?php echo $entry['quantity']; ?>" data-product-id="<?php echo $entry['product_id']; ?>" data-sku="<?php echo $entry['sku']; ?>" data-product-title="<?php echo $product->title; ?>" data-product-offer="<?php echo $sku['name']; ?>" data-product-price="<?php echo admin_format_price($sku['price']); ?>" data-product-sale-price="<?php echo admin_format_price($sku['sale_price']); ?>">
				    <th>
				        <span class="form-field">
				            <select name="bundle_product_combo_<?php echo $s; ?>_quantities[]" class="js-product-qnty-select">
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
				        <input type="hidden" class="hidden" name="bundle_product_combo_<?php echo $s; ?>_ids[]" value="<?php echo $entry['product_id']; ?>">
				        <input type="hidden" class="hidden" name="bundle_product_offer_combo_<?php echo $s; ?>_ids[]" value="<?php echo $entry['sku']; ?>">
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
    		</tbody></table></div>
		    <?php if ($s > 1) : ?>
		    	<button class="btn btn-danger js-combo-remove-trigger" type="button">Remove selection</button>   
		    <?php endif; ?>
			<button class="btn js-combo-products-trigger" type="button">Select Products</button>
		</div>
	<?php $s++; endforeach; ?>
<?php endif; ?>

<div class="form-field row roof-xs">
	<label>Add Selection</label>
	<p class="color-gray">Add another selection to this bundle</p>
	<button class="btn js-bundle-combo-selection-add-trigger" type="button">Add Selection +</button>
</div>

<div class="row roof-xs">
	<div class="form-field row">
		<label for="bundle_combo_fixed_price">Fixed Price</label>
		<p class="color-gray">Set a fixed price bundle (overrides percentage discount).</p>
	</div>
	<div class="form-field field-group row floor-xs">
        <label class="input-addon" for="bundle_combo_fixed_price">$</label>
        <input type="text" name="bundle_combo_fixed_price" id="bundle_combo_fixed_price" class="js-combo-fixed-price-input" placeholder="9.95" value="<?php echo $isBunldeCombo ? admin_format_price($post_meta['bundle_configuration']['price']) : ''; ?>">
    </div>
</div>