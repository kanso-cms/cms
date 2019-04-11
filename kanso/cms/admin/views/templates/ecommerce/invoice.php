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
	</style>
</head>
<body>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 500 216" class="sr-only"><g id="logo-svg"><path fill="#B5987E" d="M150.5,73.8c-0.3-0.7,0.1-1.4,0.9-1.4h3.9c0.4,0,0.8,0.3,0.9,0.6l12.1,27.3h0.3L180.9,73c0.1-0.3,0.4-0.6,0.9-0.6h3.9c0.8,0,1.3,0.7,0.9,1.4L169.7,111c-0.2,0.3-0.5,0.6-0.9,0.6h-0.6c-0.4,0-0.8-0.3-0.9-0.6L150.5,73.8z"/><path fill="#B5987E" d="M200.5,73.4c0-0.6,0.4-1,1-1h22.1c0.6,0,1,0.5,1,1v3.1c0,0.5-0.4,1-1,1h-17.4v11.3h14.7c0.6,0,1,0.5,1,1V93c0,0.6-0.5,1-1,1h-14.7v11.9h17.4c0.6,0,1,0.5,1,1v3c0,0.6-0.4,1-1,1h-22.1c-0.6,0-1-0.5-1-1V73.4z"/><path fill="#B5987E" d="M241.3,73.4c0-0.6,0.4-1,1-1h12.4c6.7,0,11.5,4.4,11.5,10.2c0,4.2-3.1,7.3-5.7,8.8c2.9,1.2,6.9,3.9,6.9,9c0,6.2-5.1,10.7-12,10.7h-13c-0.6,0-1-0.5-1-1V73.4z M255.3,105.9c3.4,0,6-2.6,6-6c0-3.4-3.2-5.8-6.8-5.8h-7.6v11.8H255.3z M254.4,89.2c3.5,0,5.7-2.6,5.7-5.9c0-3.4-2.2-5.7-5.7-5.7h-7.4v11.6H254.4z"/><path fill="#B5987E" d="M283.9,73.4c0-0.6,0.4-1,1-1H307c0.6,0,1,0.5,1,1v3.1c0,0.5-0.4,1-1,1h-17.4v11.3h14.7c0.6,0,1,0.5,1,1V93c0,0.6-0.5,1-1,1h-14.7v11.9H307c0.6,0,1,0.5,1,1v3c0,0.6-0.4,1-1,1H285c-0.6,0-1-0.5-1-1V73.4z"/><path fill="#B5987E" d="M324.7,72.8c0-0.5,0.5-1,1-1h1.4l24,27.7h0.1V73.4c0-0.6,0.4-1,1-1h3.5c0.6,0,1,0.5,1,1v37.1c0,0.6-0.5,1-1,1h-1.4l-24-28.5h-0.1V110c0,0.6-0.4,1-1,1h-3.5c-0.6,0-1-0.5-1-1V72.8z"/><path fill="#B5987E" d="M370.5,109.6l16.9-37.1c0.2-0.3,0.7-0.6,0.9-0.6h0.5c0.3,0,0.8,0.3,0.9,0.6l16.8,37.1c0.3,0.7-0.1,1.4-0.9,1.4h-3.5c-0.7,0-1-0.3-1.3-0.8l-3.4-7.6h-18c-1.1,2.5-2.3,5-3.4,7.6c-0.2,0.4-0.6,0.8-1.3,0.8h-3.5C370.6,111,370.2,110.3,370.5,109.6z M395.5,97.9l-6.8-15.1h-0.3l-6.7,15.1H395.5z"/><path fill="#B5987E" d="M172.6,143.6c11,0,19.9,8.9,19.9,19.9c0,11-8.8,19.8-19.9,19.8c-11,0-19.8-8.8-19.8-19.8C152.8,152.5,161.6,143.6,172.6,143.6z M172.6,177.9c7.9,0,14.3-6.4,14.3-14.3c0-7.8-6.5-14.4-14.3-14.4c-7.8,0-14.3,6.6-14.3,14.4C158.3,171.5,164.8,177.9,172.6,177.9z"/><path fill="#B5987E" d="M208.6,145.2c0-0.6,0.4-1,1-1h15c6.6,0,12,5.2,12,11.8c0,5.1-3.4,9.3-8.2,11.2l7.6,14c0.4,0.7,0,1.6-0.9,1.6h-4.3c-0.4,0-0.8-0.3-0.9-0.5l-7.3-14.6h-8.3v14.1c0,0.6-0.5,1-1,1h-3.6c-0.6,0-1-0.5-1-1V145.2z M224.3,163c3.6,0,6.7-3,6.7-6.8c0-3.6-3.1-6.6-6.7-6.6h-9.8V163H224.3z"/><path fill="#B5987E" d="M270.7,143.6c5.3,0,9.7,1.9,13.4,5.1c0.4,0.4,0.5,1.1,0.1,1.5c-0.8,0.9-1.7,1.7-2.5,2.6c-0.4,0.5-0.9,0.4-1.4-0.1c-2.6-2.3-6-3.8-9.4-3.8c-7.8,0-13.9,6.6-13.9,14.4c0,7.7,6,14.3,13.9,14.3c4.6,0,7.7-1.7,8.3-1.9v-6.2h-5.4c-0.6,0-1-0.4-1-1v-3.2c0-0.6,0.4-1,1-1h10c0.6,0,1,0.5,1,1c0,4.4,0.1,8.9,0.1,13.4c0,0.3-0.2,0.7-0.4,0.9c0,0-5.7,3.6-13.7,3.6c-11,0-19.9-8.8-19.9-19.8C250.8,152.5,259.7,143.6,270.7,143.6z"/><path fill="#B5987E" d="M296.5,181.4l16.9-37.1c0.2-0.3,0.7-0.6,0.9-0.6h0.6c0.3,0,0.8,0.3,0.9,0.6l16.8,37.1c0.3,0.7-0.1,1.4-0.9,1.4h-3.5c-0.7,0-1-0.3-1.3-0.8l-3.4-7.6h-18c-1.1,2.5-2.3,5-3.4,7.6c-0.2,0.4-0.6,0.8-1.3,0.8h-3.5C296.7,182.8,296.2,182.1,296.5,181.4z M321.5,169.7l-6.8-15.1h-0.3l-6.7,15.1H321.5z"/><path fill="#B5987E" d="M346.5,144.6c0-0.6,0.5-1,1-1h1.4l24,27.7h0.1v-26.1c0-0.6,0.4-1,1-1h3.5c0.6,0,1,0.5,1,1v37.1c0,0.5-0.5,1-1,1h-1.4l-24-28.5h-0.1v26.9c0,0.6-0.4,1-1,1h-3.5c-0.6,0-1-0.5-1-1V144.6z"/><path fill="#B5987E" d="M397.6,145.2c0-0.6,0.5-1,1-1h3.6c0.5,0,1,0.5,1,1v36.5c0,0.6-0.5,1-1,1h-3.6c-0.6,0-1-0.5-1-1V145.2z"/><path fill="#B5987E" d="M439.4,143.6c5.6,0,9.6,1.9,13.4,5.1c0.5,0.4,0.5,1.1,0.1,1.5l-2.4,2.5c-0.4,0.5-0.9,0.5-1.4,0c-2.6-2.3-6.1-3.7-9.6-3.7c-7.9,0-13.8,6.6-13.8,14.4c0,7.7,6,14.3,13.9,14.3c4,0,6.8-1.6,9.5-3.6c0.5-0.4,1-0.3,1.3-0.1l2.5,2.5c0.4,0.4,0.3,1.1-0.1,1.5c-3.8,3.6-8.5,5.4-13.4,5.4c-11,0-19.9-8.8-19.9-19.8C419.5,152.5,428.4,143.6,439.4,143.6z"/><path fill="#B5987E" d="M466.5,177.2c0.5-0.7,0.9-1.5,1.4-2.3c0.5-0.7,1.3-0.9,1.9-0.4c0.3,0.3,4.6,3.8,8.8,3.8c3.8,0,6.2-2.3,6.2-5.1c0-3.3-2.9-5.4-8.3-7.7c-5.6-2.4-10-5.3-10-11.7c0-4.3,3.3-10.2,12.1-10.2c5.5,0,9.7,2.9,10.2,3.3c0.4,0.3,0.9,1,0.3,1.9c-0.4,0.7-0.9,1.4-1.4,2.1c-0.4,0.7-1.2,1-1.9,0.6c-0.4-0.2-4.2-2.8-7.5-2.8c-4.6,0-6.2,2.9-6.2,5c0,3.1,2.4,5.1,7,7c6.4,2.6,11.9,5.6,11.9,12.4c0,5.7-5.1,10.4-12.3,10.4c-6.7,0-11-3.5-11.9-4.4C466.3,178.6,465.9,178.2,466.5,177.2z"/><path fill="#3C2417" d="M75.6,201.7c0.8,0.6,1.9,0.5,2.6-0.2c0,0,0,0,0,0c2.5-3.2,4.5-7.2,6.1-12.3c5.1-16.6,3.4-34.1,0.1-49c0.1-0.1,0.2-0.1,0.2-0.2c1.8-2.6,4.1-4.9,6.8-6.6c-1,3.3-0.3,7.3,2.2,11.3c1.6,2.5,3.3,4.3,5.2,5.5c4.3,2.5,10.2,1.9,14.3-1.5c0.6-0.5,1.1-1,1.6-1.5c3.1-3.7,4.2-9,2.6-13.8c-1.6-4.8-5.6-7.9-10.6-8.1c-0.9,0-1.8,0-2.6,0c2.6-3.6,3.5-8.6,2-13c-1.6-4.8-5.6-7.9-10.6-8.1c-3.4-0.2-6.2,0.2-8.6,1.3c-3.1,1.3-5.5,3.7-6.5,6.6c-1.2,3.4-0.5,7.6,2.1,11.7c1.5,2.5,3.2,4.3,5.2,5.5c0.7,0.4,1.5,0.7,2.3,1c-2.5,1.5-4.7,3.4-6.6,5.6c-0.5-2.1-1-4.1-1.6-6.1l-1-3.7c-1.9-7.1-3.9-14.4-4.3-21.7c-0.4-7.8,1.4-14.3,6-21.8c0.1-0.1,0.1-0.2,0.2-0.3c2,2.2,6.3,2.4,10.8,2.2c7.3-0.3,14.6-2,21.1-5.1c7.1-3.3,13.5-8.2,18.6-14.2c2.2-2.6,4.2-5.4,5.9-8.4c1.8-3.2,2-5.8,0.6-7.8c-0.9-1.2-2.3-1.9-4.3-2.3c-4.7-0.9-9.4,0.3-13,1.4c-10.2,3.1-19.9,8.6-28.1,15.8c-3,2.6-6.2,5.5-8.8,8.7c0.4-4.4,0.1-9-0.3-13c-1.6-16.3-9.3-31.6-21.1-42c-1.6-1.4-3.4-2.8-5.6-3c-1.1-0.1-2.1,0.1-2.9,0.7c-1.8,1.4-1.4,3.9-1.3,4.8l3.9,22.1c1.2,7,2.5,14.2,4.7,21.1c1.6,4.9,3.4,8.6,5.7,11.5c2.8,3.6,6.3,6.2,10.1,7.3c-4.4,7.6-6.2,14.7-5.8,22.7c0.1,1.6,0.2,3.1,0.4,4.6c-0.3-0.7-0.7-1.3-1-1.9c-0.2-0.5-0.5-0.9-0.7-1.3c-2.7-5.4-5.6-10.9-9.3-15.8c-8.1-10.6-20.9-18.5-36.1-22.4c-3.6-0.9-6.4-0.1-7.4,2.3c-0.4,0.9-0.4,1.9-0.3,2.7c0.4,5.8,3.1,11.8,7.6,17.2c3.9,4.6,8.5,8.3,13.7,12.3c5.3,4.1,10.2,7.7,15.8,10.4c5,2.4,9.6,3.7,13.7,3.8c0.8,0,3,0.1,4.3-1.4c0.1-0.1,0.2-0.3,0.3-0.4c0.1-0.2,0.2-0.4,0.3-0.6c0.9,4.4,2.1,8.8,3.2,13.1l1,3.7c2.4,9.1,4.9,19.8,5.4,30.9c-5.3-4-11.3-7.2-17.8-9.1c-2-0.6-4-0.9-5.9-1.3c-1.3-0.2-2.6-0.5-3.8-0.8c2-5.2,2.9-10.8,2.3-15.3c-0.8-6.3-4.4-11.8-9.5-14.4c-1.6-0.8-3.1-1.2-4.5-1.2c-2,0.1-3.8,1-4.7,2.6c-0.6,1-0.8,2.1-0.9,3.2c-0.6,6.6,0.9,13.4,4.2,19.1c-3-1.6-6.1-2.9-9.5-3.6c-5.6-1.2-11.5-0.6-16.7,1.6c-3.9,1.6-6.5,4.1-7.5,7c-1.6,4.8,1.6,9.8,5.4,12.5c6.7,4.9,15,5.1,20.9,4.4c4.7-0.5,8.5-1.7,11.4-3.6c3.1-1.9,5.6-5.2,7.6-8.9c1.6,0.4,3.2,0.7,4.7,1c1.8,0.3,3.7,0.7,5.5,1.2c7,2.1,13.6,5.7,19,10.4c0,7.3-0.8,14.7-3.1,21.9c-1.5,4.7-3.2,8.2-5.4,11.1C74.7,199.8,74.8,201,75.6,201.7z M13.3,154.2c7.2,0.6,14.7,0.2,22-0.2c5-0.3,10.1-0.6,15.1-0.5c0,0.1-0.1,0.1-0.1,0.2c-11.7,4.4-25,4.8-36.9,0.9C13.4,154.4,13.4,154.3,13.3,154.2z M14.2,155.7c11.5,3.5,24.1,3.2,35.3-0.8c-1.3,1.8-2.8,3.3-4.4,4.4c-2.5,1.6-5.7,2.6-9.9,3c-5.2,0.6-12.6,0.4-18.3-3.8C15.9,157.8,15,156.8,14.2,155.7z M49.8,152.5c-4.8,0-9.7,0.2-14.5,0.5c-7.4,0.4-15,0.8-22.3,0.2c-0.3-1-0.4-2.1,0-3c0.8-2.4,3.4-3.9,5.4-4.7c4.5-1.9,9.7-2.4,14.5-1.4C39.1,145.4,44.7,149.1,49.8,152.5z M42.6,126.5c1.4,7.7,5.6,14.7,9.3,20.9l0.9,1.5c-1.6-0.7-3-1.8-4.2-3.3C44.4,140.2,42.2,133.3,42.6,126.5z M54.9,135.2c0.5,3.6-0.2,8.2-1.7,12.5l-0.5-0.8c-4.1-6.9-8.8-14.7-9.6-23.3c0,0,0-0.1,0-0.1c0,0,0,0,0,0c0.3-0.4,0.9-0.6,1.6-0.6c0.8,0,1.7,0.2,2.7,0.8C51.4,125.7,54.2,130.1,54.9,135.2z M70.2,110.9c-14.5-10.1-27.9-21.9-39.7-35.2l-0.3-0.3c-0.6-0.7-1.3-1.4-1.9-2.1c16.5,10.4,30.3,22.5,41.2,36.3C69.7,110,70,110.4,70.2,110.9z M24,70.7c2.1,1.4,3.8,3.4,5.5,5.3l0.3,0.3c11.9,13.3,25.3,25.3,40,35.4c0.2,0.2,0.5,0.3,0.8,0.4c0,0,0,0,0,0c-0.1,0-0.5,0.1-1.3,0c-3.6-0.1-7.7-1.3-12.2-3.4c-5.2-2.5-9.9-6-15.1-10c-5-3.9-9.5-7.4-13.1-11.7c-2.8-3.4-6.3-8.8-6.8-15.1c0-0.2-0.1-0.7,0-1c0,0,0-0.1,0-0.1C22.4,70.7,23.1,70.6,24,70.7z M67.3,105.3C56.4,92.5,42.9,81.1,27,71.4c13.6,3.8,25,11,32.2,20.5C62.4,96,64.9,100.7,67.3,105.3z M81.5,59.4c0.6,5.6,1,13.3-1.1,18.7c0,0,0,0-0.1,0c-3.3-0.8-6.4-3-8.8-6.2c-2-2.6-3.6-5.9-5.1-10.4c-2.2-6.6-3.4-13.7-4.6-20.5l-3.9-22.1c-0.2-1-0.1-1.2-0.1-1.3c1.4,0.3,2.7,1.3,3.7,2.2C72.7,29.5,79.9,44,81.5,59.4z M85.6,79.2l4.5-2.1c15.1-7,30.7-14.2,45.4-22.1c0,0,0,0.1-0.1,0.1C122.4,68.8,104.5,77.5,85.6,79.2z M136.4,53.5c-15.1,8.2-31.1,15.6-46.6,22.8l-4,1.9c0.1-0.1,0.1-0.3,0.2-0.4c0.8-1.3,1.8-2.6,2.8-3.9c2.4-2.8,5.2-5.4,7.9-7.7c7.8-6.8,17.1-12,26.8-15c3.2-1,7.4-2,11.3-1.3c1,0.2,1.7,0.5,2,0.9C137.1,51.2,136.9,52.2,136.4,53.5z M86.9,80c17.2-1.8,33.6-9.4,46.2-21.2C128,66,121,71.9,113,75.6c-6.1,2.8-12.9,4.5-19.7,4.7C89.9,80.5,88,80.3,86.9,80z M99.6,124.4c-2.9,2.4-7.1,2.9-10,1.2c-1.4-0.8-2.7-2.2-3.9-4.2c-1.1-1.8-2.9-5.4-1.7-8.6c0.3-0.7,0.7-1.4,1.2-2c0.8-1,2-1.8,3.3-2.3c1.9-0.8,4.2-1.1,7-1c3.5,0.2,6.1,2.2,7.3,5.6C104,117.2,102.8,121.9,99.6,124.4z M95.1,134c0.3-0.7,0.7-1.4,1.2-2c0.8-1,2-1.8,3.3-2.3c1.9-0.8,4.2-1.1,7-1c3.5,0.2,6.1,2.2,7.3,5.6c1.4,4.1,0.1,8.7-3,11.3c-2.9,2.4-7.1,2.9-10,1.2c-1.4-0.8-2.7-2.2-3.9-4.2C95.7,140.8,94,137.2,95.1,134z"/><path fill="#3C2417" d="M110.9,121.3c1.2,1.9,2.4,3.2,3.9,4.1c3.3,1.9,7.7,1.5,10.8-1.1c0.4-0.3,0.8-0.7,1.2-1.2c2.3-2.8,3.2-6.8,2-10.4c-1.2-3.7-4.2-6-8-6.2c-2.5-0.1-4.6,0.2-6.4,0.9c-2.4,1-4.2,2.8-4.9,5C108.4,115,109,118.2,110.9,121.3z M116.6,122.1c-0.9-0.5-1.8-1.5-2.6-2.8c-0.8-1.2-1.9-3.6-1.2-5.7c0.2-0.5,0.4-0.9,0.8-1.3c0.5-0.6,1.3-1.2,2.1-1.5c1.3-0.5,2.8-0.8,4.8-0.7c3.1,0.2,4.3,2.4,4.7,3.6c0.9,2.7,0.1,5.8-2,7.5C121.3,122.9,118.5,123.3,116.6,122.1z"/></g></svg>
<div class="container-fluid invoice-container" id="invoice">
	<div class="card pad-30">
		<div class="row">
			<div class="media">
			    <div class="media-left">
			        <svg viewBox="0 0 500 216" class="logo"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo-svg"></use></svg>
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

