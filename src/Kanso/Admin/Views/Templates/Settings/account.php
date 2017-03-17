<div class="col-12 col-md-8 roof-xs floor-xs">
    <form method="post" class="js-validation-form">
        <p class="color-gray">
            Your administrator settings are used to login to your Kanso control panel. 
            Keep your credentials in a safe place.
        </p>
        <div class="form-field row floor-sm">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" placeholder="John" value="<?php echo $USER->username;?>" data-js-required="true" class="js-mask-alpha-dash">
            <p class="help-danger">* Please enter a valid username.</p>
        </div>
        
        <div class="form-field row floor-sm">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="Howard" value="<?php echo $USER->email;?>" data-js-required="true" data-js-validation="email">
            <p class="help-danger">* Please enter a valid email address.</p>
        </div>

        <div class="form-field row floor-sm">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="" data-js-validation="password">
            <p class="help-danger">* Passwords must include a number or special character.</p>
        </div>

        <div class="form-field row floor-sm">
            <span class="checkbox checkbox-primary">
                <input type="checkbox" name="email_notifications" id="email_notifications" <?php if ($USER->email_notifications === 1) echo 'checked';?>/>
                <label for="email_notifications">Email notifications</label>
            </span>
            <p class="color-gray">
                Receive email notifications whenever a new comment is made.
            </p>
        </div>
        
        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
        <input type="hidden" name="form_name" value="account_settings">

        <button type="submit" class="btn btn-success">Update Settings</button>
    </form>
</div>