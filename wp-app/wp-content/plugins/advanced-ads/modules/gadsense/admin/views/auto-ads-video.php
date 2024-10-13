<div id="advads-auto-ads-links">
	<a id="advads-auto-ads-video-link" style="cursor: pointer;">
		<span class="dashicons dashicons-format-video"></span>&nbsp;<?php
		esc_attr_e( 'How to enable Auto ads in 30 seconds (video tutorial)', 'advanced-ads' ); ?>
	</a>
</div>
<script>
	(function ($) {
		var $videoLink = $('#advads-auto-ads-video-link');
		$videoLink.click(function () {
			$('<br class="clear"/><br/><iframe src="https://player.vimeo.com/video/381874350" width="640" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>').appendTo('#advads-auto-ads-links');
			$(this).remove();
		})
			.children('.dashicons').css('line-height', $videoLink.css('line-height'));
	})(jQuery)
</script>