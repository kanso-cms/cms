<?php

use kanso\framework\utility\Humanizer;

?>
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