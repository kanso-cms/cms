<div class="col-12 col-md-8 roof-sm floor-sm">

    <!-- RESTORE DEFAULTS -->
    <form method="post">
        <div class="form-field row">
            <label>Restore defaults</label>
            <p class="color-danger font-bolder">
                Use this tool to restore Kanso's database to its original state. 
                Warning, this will delete all data associated with your Kanso installation 
                including all your articles, tags, categories, accounts, passwords and
                immediately log you out of the admin panel. Use with caution.
            </p>
        </div>
        <input type="hidden" name="form_name" value="restore_kanso">
        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
        <button type="submit" class="btn btn-danger js-restore-kanso-trigger">Restore Kanso</button>
    </form>

</div>