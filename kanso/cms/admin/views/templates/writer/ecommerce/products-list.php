<ul class="list-unstyled bundle-product-list js-bundle-product-list hidden">
	<?php if ($kanso->Ecommerce) : foreach ($kanso->Ecommerce->products()->all() as $product) : $offers = $kanso->Ecommerce->products()->offers($product->id); ?>
		<?php foreach ($offers as $productOffer) :?>
		<li data-quantity="1" data-product-id="<?php echo $product->id; ?>" data-offer-id="<?php echo $productOffer['offer_id']; ?>" data-product-title="<?php echo $product->title; ?>" data-product-offer="<?php echo $productOffer['name']; ?>" data-product-image="<?php echo the_post_thumbnail_src($product->id); ?>" data-product-price="<?php echo number_format($productOffer['price'], 2, '.', ''); ?>"  data-product-sale-price="<?php echo number_format($productOffer['sale_price'], 2, '.', ''); ?>">
			<strong><?php echo $product->title; ?></strong> - 
			<span class="color-gray"><?php echo $productOffer['name']; ?></span> 
			(<del class="color-gray">$<?php echo number_format($productOffer['price'], 2, '.', ''); ?></del> 
			<span class="color-success">$<?php echo number_format($productOffer['sale_price'], 2, '.', ''); ?>)</span>
		</li>
	<?php endforeach; endforeach; endif; ?>
</ul>