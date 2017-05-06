<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Kanso Application Error</title>
	<?php include 'style.css.php';?>
</head>
	<body>
		<div class="interstitial-wrapper">
			<div class="icon" alt="" style=""></div>
			<div class="content">
				<h1><?php echo $errName; ?></h1>
				<code class="error-msg"><?php echo $errmsg;?></code>
				<div class="error-desc">
					<p>
						<span class="uppercase"><?php echo $errtype; ?> [<?php echo $errcode;?>]</span> 
						thrown in <strong><?php echo $errfile;?></strong> 
						on line <strong><?php echo $errline; ?></strong>.
					</p>
				</div>

				<div class="row">
					<button type="button" onclick="location.reload()" class="button">Reload</button>
				</div>

				<div class="code-block row">
					<pre><code>
						<?php foreach ($errFileLines as $lineno => $linecode) : ?>
							<div class="line <?php if ($lineno === $errline) echo 'error';?>">
								<span class="lineno"><?php echo $lineno;?></span>
								<span class="linecode"><?php echo htmlspecialchars($linecode);?></span>
							</div>
						<?php endforeach; ?>
					</code></pre>
				</div>

				<div class="row">
					<h2>Full Details</h2>
					<dl class="dl-horizontal">
					   
					    <dt>Type: </dt>
					    <dd><code><?php echo $errtype; ?> [<?php echo $errcode;?>]</code></dd>

					    <dt>URL: </dt>
					    <dd><code><?php echo $errUrl; ?></code></dd>

					    <dt>File: </dt>
					    <dd><code><?php echo $errfile; ?></code></dd>

					    <dt>Line: </dt>
					    <dd><code><?php echo $errline; ?></code></dd>

					    <dt>Class: </dt>
					    <dd><code><?php echo $errClass; ?></code></dd>

					    <dt>Message: </dt>
					    <dd><code><?php echo $errmsg; ?></code></dd>

					    <dt>IP Address: </dt>
					    <dd><code><?php echo $clientIP; ?></code></dd>

					    <dt>Date: </dt>
					    <dd><code><?php echo date('l jS \of F Y h:i:s A', $errtime);?></code></dd>
					</dl>
				</div>

				<div class="row">
					<h2>Trace</h2>
					<ul>
						<?php foreach ($errTrace as $file) : ?>
							<li><code><?php echo $file; ?></code></li>
						<?php endforeach; ?>
					</ul>
				</div>

				

			</div>
		</div>

	</body>

</html>
