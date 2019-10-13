<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Invoice #<?php echo $order['bt_transaction_id']; ?></title>

	<?php $adminAssetsUrl = str_replace($kanso->Request->environment()->DOCUMENT_ROOT, $kanso->Request->environment()->HTTP_HOST, KANSO_DIR . '/cms/admin/assets'); ?>
	<link rel="shortcut icon"                    href="<?php echo $adminAssetsUrl; ?>/images/favicon.png">
	<link rel="apple-touch-icon" sizes="57x57"   href="<?php echo $adminAssetsUrl; ?>/images/apple-touch-icon.png">
	<link rel="apple-touch-icon" sizes="72x72"   href="<?php echo $adminAssetsUrl; ?>/images/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="114x114" href="<?php echo $adminAssetsUrl; ?>/images/apple-touch-icon-114x114.png">

	<link rel="stylesheet" media="all" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700">
	<link rel="stylesheet" media="all" href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900">
	<link rel="stylesheet" media="all" href="<?php echo $adminAssetsUrl; ?>/css/hubble.css?v=<?php echo $kanso::VERSION; ?>">
	<link rel="stylesheet" media="all" href="<?php echo $adminAssetsUrl; ?>/css/theme.css?v=<?php echo $kanso::VERSION; ?>">
	<link rel="stylesheet" media="all" href="<?php echo str_replace($kanso->Request->environment()->DOCUMENT_ROOT, $kanso->Request->environment()->HTTP_HOST, APP_DIR . '/views/admin/assets/css/styles.css'); ?>?v=<?php echo time(); ?>">
	<style type="text/css">
		@page
		{ 	size: auto;
			margin: 0mm;
		}
		body
		{
		  -webkit-print-color-adjust:exact;
		}
		.invoice-container
		{
			padding-top: 50px;
			padding-bottom: 50px;
			max-width: 860px;
		}
		.invoice-container .logo
		{
			width: 110px;
		    display: block;
		}

		.invoice-container .invoice-info
		{
			margin-bottom: 0;
		}
		.invoice-container .invoice-info dt
		{
			text-align: right !important;
			width: 130px !important;
		}
		.invoice-container .invoice-info dd
		{
			margin-left: 140px !important;
			text-align: right !important;
			margin-bottom: 0 !important;
		}

		.invoice-order-summary
		{
		  color: #444;
		}
		.invoice-order-summary > tbody > tr > th:first-child,
		.invoice-order-summary > tfoot > tr > th:first-child,
		.invoice-order-summary > thead > tr > th:first-child
		{
		  font-weight: 400;
		}
		.invoice-order-summary .divider-border th, .invoice-order-summary .divider-border td
		{
		  border-top: 3px solid #8D8D8D;
		}
		.invoice-order-summary .coupon-row
		{
		  display: none;
		}
		.invoice-order-summary .coupon-row.active
		{
		  display: table-row;
		}
	</style>
</head>
<body>
<div class="container-fluid invoice-container" id="invoice">
	<div class="card pad-30">
		<div class="row">
			<div class="media">
			    <div class="media-left">
			    	<img width="100" src="<?php echo $kanso->Config->get('email.theme.logo_url'); ?>" alt="<?php echo $kanso->Config->get('cms.site_title'); ?>">
			    </div>
			    <div class="media-body gutter-sm"></div>
			    <div class="media-right text-right nowrap">
			        <h1>Invoice</h1>
			        <p class="p3 color-gray-dark">#<?php echo $order['bt_transaction_id']; ?></p>
			    </div>
			</div>
		</div>
		<div class="row">
			 <address>
	        	<?php echo $kanso->Config->get('ecommerce.company_address'); ?>
	        </address>
		</div>
		<div class="card pad-20" style="border: 1px dashed #BFBFBF;">
			<div class="media">
			    <div class="media-left nowrap">
			        <address>
			        	<strong class="p3 block color-gray">Customer:</strong>
			        	<strong><?php echo $address['first_name'] . ' ' . $address['last_name']; ?></strong><br>
			        	<?php echo $address['street_address_1']; ?><br>
			        	<?php echo !empty($address['street_address_2']) ? $address['street_address_2'] . '<br>' : ''; ?>
			        	<?php echo $address['suburb'] . ', ' . $address['state'] . ' ' . $address['zip_code']; ?><br>
			        </address>
			    </div>
			    <div class="media-body gutter-sm"></div>
			    <div class="media-right text-right nowrap">
			       <dl class="dl-horizontal invoice-info" style="margin-bottom: 0">
			       		<dt class="color-gray">Transaction Id : </dt><dd>#<?php echo $order['bt_transaction_id']; ?></dd>
						<dt class="color-gray">Date : </dt><dd><?php echo date('M d, Y', $order['date']); ?></dd>
		                <dt class="color-gray">Payment Method : </dt><dd>Credit Card (<?php echo ucfirst($order['card_type']); ?>)</dd>
		                <dt class="color-gray">Card : </dt><dd> •••• •••• •••• <?php echo $order['card_last_four']; ?></dd>
		                <dt class="color-gray">Total : </dt><dd>$<?php echo $order['total']; ?></dd>
		            </dl>
			    </div>
			</div>
		</div>

		<div class="row roof-xs">
			<table class="table table-hover table-bordered invoice-order-summary">
				<caption>Your order summary:</caption>
				<tbody>
					<thead>
						<tr>
							<th>Items</th>
							<th style="text-align: right">Price</th>
						</tr>
					</thead>
					<?php foreach($order['items'] as $item) : ?>
					<tr>
						<th><span class="strong"><?php echo $item['quantity']; ?>x</span> - <?php echo $item['name'] . ' - ' . $item['offer']; ?></th>
						<td><span class="text-right block">$<?php echo number_format($item['price'], 2, '.', ''); ?></span></td>
					</tr>
					<?php endforeach; ?>
					<?php if (!empty($order['coupon'])) : ?>
					<tr>
						<th><span class="color-success font-bold">Coupon Promotion</span></th>
						<td><span class="color-success font-bold text-right block">-<?php echo $order['coupon']; ?>%</span></td>
					</tr>
					<?php endif; ?>
					<tr class="divider-border">
						<th style="text-align: right;">Sub Total</th>
						<td><span class="text-right block"><span>$<?php echo number_format($order['sub_total'], 2, '.', ''); ?></span></span></td>
					</tr>
					<tr>
						<th style="text-align: right;">Shipping &amp; Handling</th>
						<td><span class="text-right block">$<span><?php echo number_format($order['shipping_costs'], 2, '.', ''); ?></span></span></td>
					</tr>
					<tr>
						<th style="text-align: right;">Incl GST</th>
						<td><span class="text-right block">$<span class="js-gst-costs"><?php echo number_format(((10 / 100) * $order['total']), 2, '.', ''); ?></span></span></td>
					</tr>
					<tr class="divider-border">
						<th style="text-align: right;"><span class="strong color-black">Total</span></th>
						<td><span class="strong text-right block color-black">$<span><?php echo number_format($order['total'], 2, '.', ''); ?></span></span></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script type="text/javascript">
	function printDiv(divName)
	{
     	var printContents = document.getElementById(divName).innerHTML;
     	var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
	}
</script>
</body>
</html>

