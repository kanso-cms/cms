<div class="col-12 col-md-8 roof-sm floor-sm">

    <!-- BATCH IMPORT ARTICLES -->
    <form class="row floor-md" method="post" enctype="multipart/form-data">
        <div class="form-field row">
            <label>Batch import articles</label>
            <p class="color-gray">
                Use this tool to batch import articles into your Kanso installation by uploading a valid JSON file. 
                Find out more <strong><a href="http://kanso-cms.github.io/docs/0.0.01/admin-panel/tools/#importing">here</a></strong> on how to create a valid JSON file for importing.
            </p>
        </div>
        <div class="form-field field-group file-field js-file-field row floor-xs">
            <label class="input-addon" for="import_articles">
                .JSON
            </label>
            <input type="text" class="js-file-text file-text" disabled>
            <button type="button" class="btn btn-upload">
                <span class="upload-cover">
                    <input type="file" class="js-file-input" name="import_articles" id="import_articles" accept=".json">
                </span>
                <span class="glyph-icon glyph-icon-upload"></span>
            </button>
        </div>
        <input type="hidden" name="form_name" value="batch_articles">
        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
        <button type="submit" class="btn btn-success">Import Articles</button>
    </form>

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
        <button type="submit" class="btn btn-danger">Restore Kanso</button>
    </form>

</div>