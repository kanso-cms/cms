<?php
use kanso\framework\Utility\Humanizer;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;

?>
<!-- PAGE WRAP -->
<div class="dash-wrap js-dash-wrap">

	<!-- HEADING -->
	<section class="page-heading">
		<h1>Lead Profile</h1>
	</section>

	<!-- PROFILE -->
	<section class="lead-profile">

		<!-- DETAILS -->
		<div class="col col-4">
			<div class="card">
			    <div class="card-block">
		       		<span class="avatar">
						<img src="http://www.gravatar.com/avatar/fd876f8cd6a58277fc664d47ea10ad19?s=64&amp;d=mm" width="40" height="40">
					</span>
		       		<h4 class="card-title roof-xs"><?php echo empty($visitor->name) ? 'Unknown Name' : $visitor->name; ?></h4>
		        	<h6 class="card-subtitle color-gray"><?php echo empty($visitor->email) ? 'Unknown Email' : $visitor->email; ?></h6>
		        	<p class="card-text">Browses using <?php echo $browser; ?> on <?php echo $os; ?>.<br><?php echo !$location ? '' : $location['location']; ?></p>
		        	<?php if ($location) : ?>
			        <iframe width="300" height="150" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" style="width:100%" src="https://maps.google.com/maps?q=<?php echo $location['lat']; ?>,<?php echo $location['long']; ?>&z=10&output=embed"></iframe>
			    	<?php endif; ?>
			    </div>
			</div>

			<div class="clearfix roof-xs"></div>

			<div class="card">
			    <div class="card-block">
		       		<h5 class="card-title roof-xs">About <?php echo $visitor->name; ?></h5>
		        	<dl class="dl-horizontal">
		        		<dt style="text-align:left;">ID:</dt>
					    <dd><?php echo $visitor->id; ?></dd>
					    <dt style="text-align:left;">Email:</dt>
					    <dd><?php echo empty($visitor->email) ? 'Unknown' : $visitor->email; ?></dd>
					    <dt style="text-align:left;">Status:</dt>
					    <dd><?php echo ucfirst($visitor->grade(null, true)); ?></dd>
					    <dt style="text-align:left;">Last Active:</dt>
					    <dd><?php echo Humanizer::timeAgo($visitor->last_active); ?> ago</dd>
					    <dt style="text-align:left;">Visit Count:</dt>
					    <dd><?php echo $visitor->countVisits(); ?></dd>
					    <dt style="text-align:left;">Initial Channel:</dt>
					    <dd><?php echo ucfirst($visitor->channel()); ?></dd>
					    <dt style="text-align:left;">Initial Medium:</dt>
					    <dd><?php echo ucfirst($visitor->medium()); ?></dd>
					    <dt style="text-align:left;">Made Purchase:</dt>
					    <dd><?php echo  !$visitor->made_purchase ? 'No' : 'Yes'; ?></dd>
					</dl>			        
			    </div>
			</div>
		</div>	
			
		<!-- VISITS -->
		<div class="col col-8 gutter-xs floor-md">

			<section class="cd-container">
				<?php foreach ($visitor->visits() as $visit) : $os = new Os($visit->browser); $browser = new Browser($visit->browser); $actions = $visit->actions(); ?>
				<div class="cd-timeline-block">
					<div class="cd-timeline-dot"></div>
					<div class="cd-timeline-content">
						<div class="card">
						    <div class="card-header">
						    	<span class="color-gray">
						    		<?php echo $visitor->name; ?> Visited <a href="<?php echo $visit->page; ?>"><?php echo str_replace($kanso->Request->environment()->HTTP_HOST, '', $visit->page); ?></a>
						    		<span class="float-right"><?php echo date('l F jS Y \a\t h:i A', $visit->date); ?></span>
						    	</span>
						    </div>
						    <div class="card-block">
						        <h5 class="card-title">Details:</h5>
						        <dl class="dl-horizontal">
					        		<dt style="text-align:left;">IP Address:</dt>
								    <dd><?php echo $visit->ip_address; ?></dd>
								    <dt style="text-align:left;">URL:</dt>
								    <dd><a href="<?php echo $visit->page; ?>"><?php echo $visit->page; ?></a></dd>
								    <dt style="text-align:left;">Duration:</dt>
								    <dd><?php echo $visit->end !== 0 ? $visit->end - $visit->date . ' seconds' : 'Unknown'; ?></dd>
								    <dt style="text-align:left;">Interactions:</dt>
								    <dd><?php echo count($visit->actions()); ?></dd>
								    <dt style="text-align:left;">Channel:</dt>
								    <dd><?php echo empty($visit->channel) ? 'Direct' :ucfirst($visit->channel); ?></dd>
					        		<dt style="text-align:left;">Medium</dt>
								    <dd><?php echo empty($visit->medium) ? 'None' : ucfirst($visit->medium); ?></dd>
								    <dt style="text-align:left;">Browser:</dt>
								    <dd><?php echo $browser->getName() . ' ' . $browser->getVersion(); ?></dd>
								    <dt style="text-align:left;">OS:</dt>
								    <dd><?php echo $os->getName(); ?></dd>
								</dl>
								<hr class="divider">
								<?php if (!empty($actions)) : ?>
								<button class="btn btn-info js-collapse" data-collapse-target="interactions-<?php echo $visit->id; ?>">Show Interactions</button>
								<div class="hide-overflow collapsed" id="interactions-<?php echo $visit->id; ?>">
								    <ul>
								    	<?php foreach ($actions as $action) : ?>
									    <li><?php echo $action['action_name']; ?><br>
									    	<span class="color-gray"><?php echo $action['description']; ?></span>
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
		</div>
	</section>
</div>