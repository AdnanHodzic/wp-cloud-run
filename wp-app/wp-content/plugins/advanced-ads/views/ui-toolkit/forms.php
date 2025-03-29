<?php
/**
 * Ui Toolkit - Forms.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

?>
<div class="advads-ui-kit">
	<div class="advads-card">
		<header>
			<h2>Forms</h2>
		</header>
		<div class="advads-card--content">
			<input type="text" placeholder="Enter text" class="advads-input advads-input--text">
			<input type="password" placeholder="Enter password" class="advads-input advads-input--password">
			<input type="email" placeholder="Enter email" class="advads-input advads-input--email">
			<input type="number" placeholder="Enter number" class="advads-input advads-input--number">

			<h4>Checkbox</h4>
			<input type="checkbox" id="advads-checkbox1" class="advads-input advads-input--checkbox">
			<label for="advads-checkbox1" class="advads-label">Checkbox</label>

			<h4>Radio Buttons</h4>
			<input type="radio" id="advads-radio1" name="advads-radioGroup" class="advads-input advads-input--radio">
			<label for="advads-radio1" class="advads-label">Radio 1</label>
			<input type="radio" id="advads-radio2" name="advads-radioGroup" class="advads-input advads-input--radio">
			<label for="advads-radio2" class="advads-label">Radio 2</label>

			<h4>Select Dropdown</h4>
			<select class="advads-select">
				<option value="option1" class="advads-select__option">Option 1</option>
				<option value="option2" class="advads-select__option">Option 2</option>
			</select>

			<h4>Textarea</h4>
			<textarea placeholder="Enter text" class="advads-textarea"></textarea>

			<h4>File Input</h4>
			<input type="file" class="advads-input advads-input--file">

			<h4>Date Input</h4>
			<input type="date" class="advads-input advads-input--date">

		</div>
	</div>
</div>
