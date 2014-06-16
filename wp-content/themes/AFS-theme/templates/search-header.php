<div class="hero orange-hero">
	<div class="container">
		<form role="search" method="get" class="search-form form-inline" action="<?php echo home_url('/'); ?>">
          <input type="hidden" name="category" value="<?php echo get_query_var( 'search_category' ); ?>">
		  <div class="search-wrap">
		    <input type="search" value="<?php echo (is_search()) ? get_search_query() : ''; ?>" placeholder="search by project title, contractor name or keywords..." name="s" class="search-field lg-input white">
		    <div class="addon-icon">
			    <span class="add-on"><i class="fa fa-map-marker"></i></span>
			    <input type="text" title="Location" name="location" value="<?php echo (is_search()) ? get_query_var( 'search_location' ) : ''; ?>" class="location-field lg-input white" placeholder="zipcode">
			</div>
		      <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
		  </div>
		  <div class="search-meta">
		  	<div class="custom-checkbox">
						<input type="checkbox" value="1" <?php echo ( (is_search() && get_query_var( 'search_job_postings_filter' )) || ( !is_search() && !isset($_GET['precheck_contractor']) ) ) ? 'checked="checked"' : ''; ?> name="job_postings_filter" id="job_postings_filter" class="white"/>
						<label for="job_postings_filter"></label><span class="field-meta">job postings</span>
			</div>
			<div class="custom-checkbox">
						<input type="checkbox" value="1" <?php echo ( (is_search() && get_query_var( 'search_contractors_filter' )) || ( !is_search() && !isset($_GET['precheck_job']) ) ) ? 'checked="checked"' : ''; ?> name="contractors_filter" id="contractors_filter" class="white"/>
						<label for="contractors_filter"></label><span class="field-meta">contractors</span>
			</div>
			 <div class="addon-icon">
			    <span class="add-on"><i class="fa fa-truck"></i></span>
					<label class="custom-select">
					<select name="distance">
							  <option <?php echo (is_search() && get_query_var( 'search_distance' ) == 'nationwide' ) ? 'selected="selected"' : ''; ?>>nationwide</option>
							  <option <?php echo (is_search() && get_query_var( 'search_distance' ) == '25' ) ? 'selected="selected"' : ''; ?> value="25">25 miles</option>
							  <option <?php echo ( (is_search() && get_query_var( 'search_distance' ) == '50') || (!is_search()) ) ? 'selected="selected"' : ''; ?> value="50">50 miles</option>
							  <option <?php echo (is_search() && get_query_var( 'search_distance' ) == '100' ) ? 'selected="selected"' : ''; ?> value="100">100 miles</option>
							  <option <?php echo (is_search() && get_query_var( 'search_distance' ) == '150' ) ? 'selected="selected"' : ''; ?> value="150">150 miles</option>
							  <option <?php echo (is_search() && get_query_var( 'search_distance' ) == '250' ) ? 'selected="selected"' : ''; ?> value="250">250 miles</option>
							  <option <?php echo (is_search() && get_query_var( 'search_distance' ) == '500' ) ? 'selected="selected"' : ''; ?> value="500">500 miles</option>
					</select>
					</label>
			</div>
		 </div>
		</form>
	</div>
</div>