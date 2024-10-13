<?php printf(
	wp_kses(
		/* translators: 1: the plugin name that is managing the Auto ads code. */
		__( 'Advanced Ads detected that <strong>%s</strong> is managing the Auto ads code and will therefore not add it.', 'advanced-ads' ),
		[
			'strong' => [],
		]
	),
	'Borlabs Cookies'
);

