	<div class="site-container cleafix">

		<div class="writer active">
		<textarea data-id="<?php echo adminWriterPostId();?>" data-post="<?php echo adminWriterAjaxType();?>" id="writer"><?php echo adminWriterContent();?></textarea>
		</div>

		<div class="reader markdown-body">
		</div>

		<div class="reviewer">

			<div class="panel">
				<div class="row">
					<h6 class="label">Hero Image</h6>
					<div class="row js-hero-drop hero-drop-zone">
						<form class="<?php echo adminHeroDZActive();?>">
							<div class="upload-bar js-upload-bar"><span style="width:0%;" class="progress"></span></div>
							<div class="upload-prompt dz-message">
								<p>Drop image here or click to upload</p>
								<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#image"></use></svg>
							</div>
							<?php echo adminWriterHeroImg();?>
						</form>
					</div>
				</div>

				<form class="ajax-form js-article-form">

					<div class="input-wrap">
						<label class="bold">Title</label>
						<input class="input-default small" type="text" name="title" value="<?php echo adminWriterTheTitle();?>" autocomplete="off"/>
					</div>

					<div class="input-wrap">
						<label class="bold">Category</label>
						<input class="input-default small" type="text" name="category" value="<?php echo adminWriterTheCategory();?>" autocomplete="off"/>
					</div>

					<div class="input-wrap">
						<label class="bold">Tags</label>
						<input class="input-default small" type="text" name="tags" value="<?php echo adminWriterTheTags();?>" autocomplete="off"/>
					</div>

					<div class="input-wrap">
						<label class="bold">Excerpt</label>
						<textarea class="input-default small" type="text" name="excerpt" value="<?php echo adminWriterTheExcerpt();?>"><?php echo adminWriterTheExcerpt();?></textarea>
					</div>

					<div class="input-wrap">
						<label class="bold">Type</label>
						<span class="select-wrap">
							<?php echo adminWriterPostTypeSelect();?>
						</span>
					</div>

					<div class="input-wrap">
						<div class="check-wrap">
							<p class="bold label">Enable Comments</p>
							<input id="commentsCheck" type="checkbox" name="comments" <?php echo adminWriterEnabledComments();?>>
							<label class="checkbox small" for="commentsCheck"></label>
						</div>
					</div>

					<input type="hidden" style="display:none" name="thumbnail" class="js-thumbnail" value="<?php echo adminWriterHeroSrc();?>"/>


					<div class="input-wrap">
						<button type="submit" class="button submit with-spinner">
							Publish
							<span class="spinner1"></span>
							<span class="spinner2"></span>
						</button>
					</div>

				</form>

			</div>
		</div>

	</div>
<div class="writer-footer">
	<div class="view-toggles col col-2">
		<button class="js-raw active tooltipped tooltipped-n" data-tooltip="Write"><svg viewBox="0 0 500 500"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#keyboard"></use></svg></button>
		<button class="js-html tooltipped tooltipped-n" data-tooltip="Read"><svg viewBox="0 0 500 500"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#book-open"></use></svg></button>
		<button class="js-pre-publish tooltipped tooltipped-n" data-tooltip="Review"><svg viewBox="0 0 500 500"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#file-text"></use></svg></button>
		<button class="save-button js-save-post tooltipped tooltipped-n" data-tooltip="Save changes"><svg viewBox="0 0 500 500"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#disk"></use></svg></button>
	</div>
				
	<div class="col col-10 writer-powers">
		<button class="js-insert-h1 tooltipped tooltipped-n" data-tooltip="Toggle heading 1">h1</button>
		<button class="js-insert-h2 tooltipped tooltipped-n" data-tooltip="Toggle heading 2">h2</button>
		<button class="js-insert-h3 tooltipped tooltipped-n" data-tooltip="Toggle heading 3">h3</button>
		<button class="js-insert-h4 tooltipped tooltipped-n" data-tooltip="Toggle heading 4">h4</button>
		<button class="js-insert-h5 tooltipped tooltipped-n" data-tooltip="Toggle heading 5">h5</button>
		<button class="js-insert-h6 tooltipped tooltipped-n" data-tooltip="Toggle heading 6">h6</button>
		<button class="js-insert-list-normal tooltipped tooltipped-n" data-tooltip="Toggle unordered-list">- List</button>
		<button class="js-insert-list-numbered tooltipped tooltipped-n" data-tooltip="Toggle ordered-list">1. List</button>
		<button class="js-insert-bold tooltipped tooltipped-n" data-tooltip="Toggle bold"><svg viewBox="0 0 500 500"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#bold"></use></svg></button>
		<button class="js-insert-italic tooltipped tooltipped-n" data-tooltip="Toggle italics"><svg viewBox="0 0 500 500"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#italic"></use></svg></button>
		<button class="js-insert-strike tooltipped tooltipped-n" data-tooltip="Toggle strike-through"><svg viewBox="0 0 500 500"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#strike-through"></use></svg></span></button>
		<button class="js-insert-link tooltipped tooltipped-n" data-tooltip="Insert link"><svg viewBox="0 0 500 500"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#link"></use></svg></button>
		<button class="js-insert-image tooltipped tooltipped-n" data-tooltip="Insert image"><svg viewBox="0 0 500 500"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#picture"></use></svg></button>
	</div>
</div>