<div class="col-12 col-md-8 roof-xs floor-xs">
   <form method="post" class="js-validation-form">
        <p class="color-gray">
            Your author information is used by Kanso to display on articles you write and is 
            linked to your account credentials. You don't need to fill out everything - only 
            your name and slug are mandatory.
        </p>

        <div class="author-avatar-img js-author-avatar-img <?php if (!empty($USER->thumbnail_id)) echo 'active'; ?>">
            <div class="form-field row floor-xs">
                <label>Avatar</label>
                <?php 
                if (!empty($USER->thumbnail_id)) {
                    echo display_thumbnail(the_author_thumbnail($USER->id), 'original', '', '', ''); 
                }
                else {
                    echo '<img src="" >';
                }
                ?>
                <input  type="hidden" name="thumbnail_id" class="js-avatar-id" value="<?php echo $USER->thumbnail_id;?>" />
                <button type="button" class="btn select-img-trigger js-select-img-trigger js-show-media-lib">Select image</button>
                <button type="button" class="btn remove-img-trigger js-remove-img-trigger">Remove image</button>
            </div>
        </div>


        <div class="form-field row floor-sm">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" placeholder="John" value="<?php echo $USER->name; ?>" data-js-required="true">
            <p class="help-danger">* Please enter your name.</p>
        </div>
        
        <div class="form-field row floor-sm">
            <label for="slug">Slug</label>
            <input type="text" name="slug" id="slug" value="<?php echo $USER->slug; ?>" data-js-required="true" class="js-mask-alpha-dash">
            <p class="help-danger">* Please enter a url slug.</p>
        </div>

        <div class="form-field row floor-sm">
            <label for="description">Description</label>
            <textarea name="description" id="description"><?php echo $USER->description; ?></textarea>
        </div>

        <div class="form-field row floor-sm">
            <label for="facebook">FaceBook URL</label>
            <input type="text" name="facebook" id="facebook" placeholder="http://facebook.com/example" value="<?php echo $USER->facebook; ?>" data-js-validation="url">
            <p class="help-danger">* Please enter a valid url.</p>
        </div>

        <div class="form-field row floor-sm">
            <label for="twitter">Twitter URL</label>
            <input type="text" name="twitter" id="twitter" placeholder="http://twitter.com/example" value="<?php echo $USER->twitter; ?>" data-js-validation="url">
            <p class="help-danger">* Please enter a valid url.</p>
        </div>

        <div class="form-field row floor-sm">
            <label for="gplus">Google+ URL</label>
            <input type="text" name="gplus" id="gplus" placeholder="http://plus.google.com/example" value="<?php echo $USER->gplus; ?>" data-js-validation="url">
            <p class="help-danger">* Please enter a valid url.</p>
        </div>

         <div class="form-field row floor-sm">
            <label for="gplus">Instagram URL</label>
            <input type="text" name="instagram" id="instagram" placeholder="http://instagram.com/example" value="<?php echo $USER->instagram; ?>" data-js-validation="url">
            <p class="help-danger">* Please enter a valid url.</p>
        </div>
        
        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
        <input type="hidden" name="form_name" value="author_settings">

        <button type="submit" class="btn btn-success">Update information</button>
    </form>
</div>