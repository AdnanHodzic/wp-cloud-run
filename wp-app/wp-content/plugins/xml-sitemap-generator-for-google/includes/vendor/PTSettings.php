<?php

namespace GRIM_SG;

class PTSettings {
	public $include       = true;
	public $priority      = 3;
	public $frequency     = 'weekly';
	public $google_news   = false;
	public $image_sitemap = true;
	public $video_sitemap = true;

	// Frequencies
	public static $ALWAYS  = 'always';
	public static $HOURLY  = 'hourly';
	public static $DAILY   = 'daily';
	public static $WEEKLY  = 'weekly';
	public static $MONTHLY = 'monthly';
	public static $YEARLY  = 'yearly';
	public static $NEVER   = 'never';

	/**
	 * DefaultSettings constructor.
	 *
	 * @param int $priority
	 * @param int $frequency
	 */
	public function __construct( $priority = 3, $frequency = 1, $google_news = false, $image_sitemap = false, $video_sitemap = false ) {
		$this->priority      = $priority;
		$this->frequency     = $frequency;
		$this->google_news   = $google_news;
		$this->image_sitemap = $image_sitemap;
		$this->video_sitemap = $video_sitemap;
	}
}
