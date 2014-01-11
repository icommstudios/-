<div class="hero orange-hero">
	<div class="container">
		<form role="search" method="get" class="search-form form-inline" action="<?php echo home_url('/'); ?>">
		  <div class="search-wrap">
		    <input type="search" placeholder="search by project title, contractor name or keywords..." name="s" class="search-field lg-input white">
		    <div class="addon-icon">
			    <span class="add-on"><i class="fa fa-map-marker"></i></span>
			    <input type="text" title="zipcode" name="zipcode" class="location-field lg-input white" placeholder="zipcode">
			</div>
		      <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
		  </div>
		  <div class="search-meta">
		  	<div class="custom-checkbox">
						<input type="checkbox" value="None" name="job_postings_filter" id="job_postings_filter" class="white"/>
						<label for="job_postings_filter"></label><span class="field-meta">job postings</span>
			</div>
			<div class="custom-checkbox">
						<input type="checkbox" value="None" name="contractors_filter" id="contractors_filter" class="white"/>
						<label for="contractors_filter"></label><span class="field-meta">contractors</span>
			</div>
			 <div class="addon-icon">
			    <span class="add-on"><i class="fa fa-truck"></i></span>
					<label class="custom-select">
					<select>
							  <option>nationwide</option>
							  <option>25 miles</option>
							  <option selected>50 miles</option>
							  <option>100 miles</option>
							  <option>150 miles</option>
							  <option>250 miles</option>
							  <option>500 miles</option>
					</select>
					</label>
			</div>
		 </div>
		</form>
	</div>
</div>