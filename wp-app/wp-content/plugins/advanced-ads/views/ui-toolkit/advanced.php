<?php
/**
 * Ui Toolkit - Advanced Components.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

?>
<div class="advads-ui-kit">
	<div class="advads-card">
		<header>
			<h2>Advanced Components</h2>
		</header>
		<div class="advads-card--content">

			<h4>Tabs</h4>
			<div class="advads-tabs">
				<button class="advads-tabs__button" onclick="openTab('advads-tab1')">Tab 1</button>
				<button class="advads-tabs__button" onclick="openTab('advads-tab2')">Tab 2</button>
				<div id="advads-tab1" class="advads-tabs__content">Content for Tab 1</div>
				<div id="advads-tab2" class="advads-tabs__content">Content for Tab 2</div>
			</div>

			<h4>Cards</h4>
			<div class="advads-card">
				<header>
					<h2>Advanced Components</h2>
				</header>
				<div class="advads-card--content">
					<p>This is a card component.</p>
				</div>
			</div>

			<h4>Sidebar</h4>
			<div class="advads-sidebar">
				<a href="#home" class="advads-sidebar__link">Home</a>
				<a href="#services" class="advads-sidebar__link">Services</a>
				<a href="#contact" class="advads-sidebar__link">Contact</a>
				<a href="#about" class="advads-sidebar__link">About</a>
			</div>

			<h4>Modal Without Header and Footer</h4>
			<div class="advads-modal" tabindex="-1" role="dialog">
				<div class="advads-modal__dialog" role="document">
					<div class="advads-modal__content">
						<div class="advads-modal__body">
							<!-- Modal body content goes here -->
						</div>
					</div>
				</div>
			</div>

			<h4>Modal With Header Only</h4>
			<div class="advads-modal" tabindex="-1" role="dialog">
				<div class="advads-modal__dialog" role="document">
					<div class="advads-modal__content">
						<div class="advads-modal__header">
							<h5 class="advads-modal__title">Modal title</h5>
							<button type="button" class="advads-modal__close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="advads-modal__body">
							<!-- Modal body content goes here -->
						</div>
					</div>
				</div>
			</div>

			<h4>Modal With Header and Footer</h4>
			<div class="advads-modal" tabindex="-1" role="dialog">
				<div class="advads-modal__dialog" role="document">
					<div class="advads-modal__content">
						<div class="advads-modal__header">
							<h5 class="advads-modal__title">Modal title</h5>
							<button type="button" class="advads-modal__close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="advads-modal__body">
							<!-- Modal body content goes here -->
						</div>
						<div class="advads-modal__footer">
							<button type="button" class="advads-modal__button advads-modal__button--secondary" data-dismiss="modal">Close</button>
							<button type="button" class="advads-modal__button advads-modal__button--primary">Save changes</button>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>
