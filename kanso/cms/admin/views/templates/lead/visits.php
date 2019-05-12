<?php
use kanso\framework\utility\Humanizer;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;

?>
<section class="cd-container">
	<?php foreach ($visitor->visits() as $visit) : $os = new Os($visit->browser); $browser = new Browser($visit->browser); $actions = $visit->actions(); ?>
	<div class="cd-timeline-block">
		<div class="cd-timeline-dot"></div>
		<div class="cd-timeline-content">
			<div class="card">
			    <div class="card-header">
			    	<span class="color-gray">
			    		<?php echo $visitor->name; ?> Visited <a class="text-ellipses" style="max-width: 300px;display: inline-block;vertical-align: top;" href="<?php echo $visit->page; ?>"><?php echo str_replace($kanso->Request->environment()->HTTP_HOST, '', $visit->page); ?></a>
			    		<span class="float-right"><?php echo date('l F jS Y \a\t h:i A', $visit->date); ?></span>
			    	</span>
			    </div>
			    <div class="card-block">
			        <h5 class="card-title">Details:</h5>
			        <dl class="dl-horizontal">
		        		<dt style="text-align:left;">IP Address:</dt>
					    <dd class="text-ellipses"><?php echo $visit->ip_address; ?></dd>
					    <dt style="text-align:left;">URL:</dt>
					    <dd class="text-ellipses"><a href="<?php echo $visit->page; ?>"><?php echo $visit->page; ?></a></dd>
					    <dt style="text-align:left;">Duration:</dt>
					    <dd class="text-ellipses"><?php echo $visit->end !== 0 ? Humanizer::timeElapsed($visit->end - $visit->date) : 'Unknown'; ?></dd>
					    <dt style="text-align:left;">Interactions:</dt>
					    <dd class="text-ellipses"><?php echo count($visit->actions()); ?></dd>
					    <dt style="text-align:left;">Channel:</dt>
					    <dd class="text-ellipses"><?php echo empty($visit->channel) ? 'Direct' :ucfirst($visit->channel); ?></dd>
		        		<dt style="text-align:left;">Medium</dt>
					    <dd class="text-ellipses"><?php echo empty($visit->medium) ? 'None' : ucfirst($visit->medium); ?></dd>
					    <dt style="text-align:left;">Browser:</dt>
					    <dd class="text-ellipses"><?php echo $browser->getName() . ' ' . $browser->getVersion(); ?></dd>
					    <dt style="text-align:left;">OS:</dt>
					    <dd class="text-ellipses"><?php echo $os->getName(); ?></dd>
					</dl>
					<hr class="divider">
					<?php if (!empty($actions)) : ?>
					<button class="btn btn-info js-collapse" data-collapse-target="interactions-<?php echo $visit->id; ?>">Show Interactions</button>
					<div class="hide-overflow collapsed" id="interactions-<?php echo $visit->id; ?>">
					    <ul>
					    	<?php foreach ($actions as $action) : ?>
						    <li><?php echo $action['action_name']; ?><br>
						    	<span class="color-gray"><?php echo $action['action_description']; ?></span>
						    </li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php else : ?>
					<p class="no-margin">No interactions were tracked during this visit.</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
</section>