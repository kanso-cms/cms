<?php $user= $ADMIN_INCLUDES->user();?>
<div class="col-12 col-md-8 roof-xs floor-xs">
   <form method="post" class="js-validation-form">
        <p class="color-gray">
            Your author information is used by Kanso to display on articles you write and is 
            linked to your account credentials. You don't need to fill out everything - only 
            your name and slug are mandatory.
        </p>
        <div class="form-field row floor-sm">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" placeholder="John" value="<?php echo $user['name']; ?>" data-js-required="true">
            <p class="help-danger">* Please enter your name.</p>
        </div>
        
        <div class="form-field row floor-sm">
            <label for="slug">Slug</label>
            <input type="text" name="slug" id="slug" value="<?php echo $user['slug']; ?>" data-js-required="true" class="js-mask-alpha-dash">
            <p class="help-danger">* Please enter a url slug.</p>
        </div>

        <div class="form-field row floor-sm">
            <label for="description">Description</label>
            <textarea name="description" id="description"><?php echo $user['description']; ?></textarea>
        </div>

        <div class="form-field row floor-sm">
            <label for="facebook">FaceBook URL</label>
            <input type="text" name="facebook" id="facebook" placeholder="http://facebook.com/example" value="<?php echo $user['facebook']; ?>" data-js-validation="url">
            <p class="help-danger">* Please enter a valid url.</p>
        </div>

        <div class="form-field row floor-sm">
            <label for="twitter">Twitter+ URL</label>
            <input type="text" name="twitter" id="twitter" placeholder="http://twitter.com/example" value="<?php echo $user['twitter']; ?>" data-js-validation="url">
            <p class="help-danger">* Please enter a valid url.</p>
        </div>

        <div class="form-field row floor-sm">
            <label for="gplus">Google+ URL</label>
            <input type="text" name="gplus" id="gplus" placeholder="http://plus.google.com/example" value="<?php echo $user['gplus']; ?>" data-js-validation="url">
            <p class="help-danger">* Please enter a valid url.</p>
        </div>
        
        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
        <input type="hidden" name="form_name" value="author_settings">

        <button type="submit" class="btn btn-success">Update information</button>
    </form>
</div>