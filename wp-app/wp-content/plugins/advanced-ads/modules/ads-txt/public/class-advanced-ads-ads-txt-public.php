<?php
/**
 * Display the 'ads.txt' file.
 */
class Advanced_Ads_Ads_Txt_Public {
	const TOP = '# Advanced Ads ads.txt';

	/**
	 * Ads.txt data management class
	 *
	 * @var Advanced_Ads_Ads_Txt_Strategy
	 */
	private $strategy;

	/**
	 * Constructor.
	 */
	public function __construct( $strategy ) {
		$this->strategy = $strategy;
		add_action( 'init', [ $this, 'display' ] );
	}

	/**
	 * Display the 'ads.txt' file on the frontend.
	 */
	public function display() {
		if ( '/ads.txt' === esc_url_raw( $_SERVER['REQUEST_URI'] ) ) {
			$content = $this->prepare_frontend_output();
			if ( $content ) {
				header( 'Content-Type: text/plain; charset=utf-8' );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $content;

				exit;
			}
		}
	}

	/**
	 * Prepare frontend output.
	 *
	 * @return string
	 */
	public function prepare_frontend_output() {
		if ( Advanced_Ads_Ads_Txt_Utils::is_subdir() ) {
			return;
		}
		if ( ! $this->strategy->is_enabled() ) {
			return;
		}

		$content = $this->get_frontend_output();

		if ( $content ) {
			$content = self::TOP . "\n" . $content;
			return $content;
		}

		return '';
	}

	/**
	 * Get output for frontend.
	 *
	 * @return string
	 */
	public function get_frontend_output() {
		if ( $this->strategy->is_all_network() ) {
			$content = $this->prepare_multisite();
		} else {
			$options = $this->strategy->get_options();
			$content = $this->strategy->parse_content( $options );
			$content = apply_filters( 'advanced-ads-ads-txt-content', $content, get_current_blog_id() );
		}
		return $content;
	}

	/**
	 * Prepare content of several blogs for output.
	 *
	 * @param string $domain Domain name.
	 * @return string
	 */
	public function prepare_multisite( $domain = null ) {
		global $current_blog, $wpdb;
		$domain = $domain ? $domain : $current_blog->domain;
		$need_file_on_root_domain = Advanced_Ads_Ads_Txt_Utils::need_file_on_root_domain();

		// Get all sites that include the current domain as part of their domains.
		$sites = get_sites( [
			'search' => $domain,
			'search_columns' => [ 'domain' ],
			'meta_key' => Advanced_Ads_Ads_Txt_Strategy::OPTION,
		] );

		// Uses `subdomain=` variable.
		$referrals = [];
		// Included to the ads.txt file of the current domain.
		$not_refferals = [];

		foreach ( $sites as $site ) {
			if ( (int) $site->blog_id === get_current_blog_id() ) {
				// Current domain, no need to refer.
				$not_refferals[] = $site->blog_id;
				continue;
			}

			if ( $need_file_on_root_domain ) {
				// Subdomains cannot refer to other subdomains.
				$not_refferals[] = $site->blog_id;
				continue;
			}

			if ( '/' !== $site->path ) {
				// We can refer to domains, not domains plus path.
				$not_refferals[] = $site->blog_id;
				continue;
			}

			$referrals[ $site->blog_id ] = $site->domain;
		}

		$o = '';

		if ( $not_refferals ) {
			$results = $wpdb->get_results( sprintf(
				"SELECT blog_id, meta_value FROM $wpdb->blogmeta WHERE meta_key='%s' AND blog_id IN (%s)",
				Advanced_Ads_Ads_Txt_Strategy::OPTION,
				join( ',', array_map( function($value) { return (int)$value; }, $not_refferals ) )
			) );

			$blog_data = [];
			foreach ( $results as $result ) {
				$blog_id = $result->blog_id;

				$options = maybe_unserialize( $result->meta_value );
				$options = $this->strategy->load_default_options( $options );

				$blog_data[ $blog_id ] = $options;
			}

			$blog_data = Advanced_Ads_Ads_Txt_Utils::remove_duplicate_lines( $blog_data, [ 'to_comments' => true ] );

			foreach ( $blog_data as $blog_id => $blog_lines ) {

				$content = $this->strategy->parse_content( $blog_lines );
				if ( $content ) {
					$content = "# blog_id: $blog_id\n" . $content;
				}

				if ( $blog_id === get_current_blog_id() ) {
					// Refer to other subdomains.
					foreach ( $referrals  as $blog_id => $referral ) {
						$content .= "# refer to blog_id: $blog_id\nsubdomain=" . $referral . "\n";
					}
				}

				$content = apply_filters( 'advanced-ads-ads-txt-content', $content, $blog_id );
				$o .= $content . "\n";
			}


		}
		return $o;
	}

}
