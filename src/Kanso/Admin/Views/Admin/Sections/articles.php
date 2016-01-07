<div class="list-wrap articles-wrap col col-12 right-gutter js-ajax-list-wrap js-articles-wrap" data-list-type="articles">

	<div class="row no-roof list-powers js-list-powers">
		
		<div class="col col-05 right-gutter">
			<div class="check-wrap">
				<input id="articles-select-all" class="js-check-all" type="checkbox" name="articles-select-all">
				<label class="checkbox small" for="articles-select-all"></label>
			</div>
		</div>

		<div class="col col-7 no-gutter">
			<ul class="segmented-buttons ">
				<li class="js-publish">
					<a href="#" class="button">Publish</a>
				</li>
				<li class="js-unpublish">
					<a href="#" class="button">Draft</a>
				</li>
				<li class="js-delete delete">
					<a href="#" class="button red">Delete</a>
				</li>
			</ul>
			<div class="search-wrap">
				<input class="input-default js-search-input small" value="" placeholder="Search">
				<svg class="search-icon" viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#search"></use></svg>
				<a href="#" class="close-icon js-cancel-search">
					<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#cross"></use></svg>
				</a>
			</div>
		</div>

		<div class="col col-4 no-gutter text-right right">
			<a class="expand-list label js-expand-list" href="#">Expand</a>
			<span class="label">Sort</span>
			<div class="button-dropdown align-right text-left js-button-down js-sort-list">
				<a href="#" class="button">Newest
					<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#arrow-down"></use></svg>
				</a>
				<div class="drop">
					<div>
						<a data-sort="title" href="#">Title</a>
						<a data-sort="newest" href="#">Newest</a>
						<a data-sort="oldest" href="#">Oldest</a>
						<a data-sort="category" href="#">Category</a>
						<a data-sort="drafts" href="#">Drafts</a>
						<a data-sort="published" href="#">Published</a>
						<a data-sort="type" href="#">Type</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="js-ajax-list" data-list-name="admin_all_articles">
		
	</div>


	<div class="row large-V-gutter list-nav js-list-nav">

		<div class="js-page-input-wrap page-input-wrap text-left col col-9 no-gutter">
			<span>Page</span>
			<input class="input-default small js-current-page" value="1">
			<span class="js-max-pages">of 1</span>
		</div>

		<div class="text-right col col-3 no-gutter">

			<ul class="segmented-buttons right">
				<li class="js-prev">
					<a class="button" href="#">
						« Previous 
					</a>
				</li>
				<li class="js-next">
					<a class="button" href="#">
						Next »
					</a>
				</li>
			</ul>

		</div>

	</div>

</div>
