<?php

namespace GRIM_SG\Vendor;

use GRIM_SG\GoogleNews;
use GRIM_SG\ImageSitemap;
use GRIM_SG\MultilingualSitemap;
use GRIM_SG\VideoSitemap;

class SitemapGenerator {
	public $sitemapFileName = 'sitemap.xml';

	public $sitemapIndexFileName = 'sitemap-index.xml';

	public $robotsFileName = 'robots.txt';

	public $maxURLsPerSitemap = 50000;

	public $createGZipFile = false;

	private $baseURL;

	private $searchEngines = array(
		array(
			'https://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=USERID&url=',
			'https://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=',
		),
		'https://www.google.com/webmasters/tools/ping?sitemap=',
		'https://submissions.ask.com/ping?sitemap=',
		'https://www.bing.com/webmaster/ping.aspx?siteMap=',
	);

	private $urls = array();

	private $sitemaps;

	private $sitemapIndex;

	private $sitemapFullURL;

	/**
	 * Constructor.
	 *
	 * @param string $baseURL You site URL, with / at the end.
	 */
	public function __construct( $baseURL ) {
		$this->baseURL = $baseURL;
	}

	public function addUrls( $urls_array, $callback = 'addUrl', $template = 'sitemap' ) {
		if ( ! is_array( $urls_array ) ) {
			throw new \InvalidArgumentException( 'Array as argument should be given.' );
		}

		if ( sgg_is_sitemap_index( $template ) ) {
			foreach ( $urls_array as $sitemap => $inner_urls ) {
				foreach ( $inner_urls as $url ) {
					$this->{$callback}( $url, $sitemap );
				}
			}
		} else {
			foreach ( $urls_array as $url ) {
				$this->{$callback}( $url );
			}
		}
	}

	/**
	 * Use this to add single URL to Google News Sitemap.
	 */
	public function addNewsUrl( $item ) {
		$url = $item[0] ?? null;

		$this->validateUrl( $url );

		$tmp                     = array();
		$tmp['loc']              = $url;
		$tmp['publication_name'] = $item[1] ?? null;
		$tmp['language']         = $item[2] ?? null;
		$tmp['title']            = $item[3] ?? null;

		if ( isset( $item[4] ) ) {
			$tmp['lastmod'] = $item[4];
		}

		if ( sgg_pro_enabled() ) {
			$tmp['keywords']      = implode( ', ', apply_filters( 'sgg_news_keywords', array(), $item[5] ?? null ) );
			$tmp['stock_tickers'] = implode( ', ', apply_filters( 'sgg_news_stock_tickers', array(), $item[5] ?? null ) );
		}

		$this->urls[] = $tmp;
	}

	/**
	 * Use this to add single URL to Sitemap.
	 */
	public function addMediaUrl( $item ) {
		$url = $item[0] ?? null;

		$this->validateUrl( $url );

		$tmp        = array();
		$tmp['loc'] = $url;

		if ( isset( $item[1] ) ) {
			$tmp['media'] = $item[1];
		}

		$this->urls[] = $tmp;
	}

	/**
	 * Use this to add single URL to Sitemap.
	 */
	public function addUrl( $item, $sitemap = null ) {
		$url = $item[0] ?? null;

		$this->validateUrl( $url );

		$tmp        = array();
		$tmp['loc'] = $url;

		if ( isset( $item[1] ) ) {
			$tmp['lastmod'] = $item[1];
		}
		if ( isset( $item[2] ) ) {
			$tmp['changefreq'] = $item[2];
		}
		if ( isset( $item[3] ) ) {
			$tmp['priority'] = $item[3];
		}

		if ( ! empty( $sitemap ) ) {
			$this->urls[ $sitemap ][] = $tmp;
		} else {
			$this->urls[] = $tmp;
		}
	}

	/**
	 * Validate Sitemap Item URL
	 */
	public function validateUrl( $url ) {
		if ( null === $url ) {
			throw new \InvalidArgumentException( 'URL is mandatory. At least one argument should be given.' );
		}

		$urlLenght = extension_loaded( 'mbstring' ) ? mb_strlen( $url ) : strlen( $url );
		if ( $urlLenght > 2048 ) {
			throw new \InvalidArgumentException(
				"URL lenght can't be bigger than 2048 characters.
                        Note, that precise url length check is guaranteed only using mb_string extension.
                        Make sure Your server allow to use mbstring extension."
			);
		}
	}

	/**
	 * Create sitemap in memory.
	 */
	public function createSitemap( $template = 'sitemap', $headers = array(), $is_xml = true ) {
		if ( ! isset( $this->urls ) ) {
			throw new \BadMethodCallException( 'To create sitemap, call addUrl or addUrls function first.' );
		}

		if ( $this->maxURLsPerSitemap > 50000 ) {
			throw new \InvalidArgumentException( 'More than 50,000 URLs per single sitemap is not allowed.' );
		}

		$settings         = ( new Controller() )->get_settings();
		$stylesheet_path  = apply_filters( 'sitemap_xsl_template_path', 'sitemap-stylesheet.xsl' );
		$stylesheet_url   = sgg_get_sitemap_url( "{$stylesheet_path}?template={$template}", "sitemap_xsl={$template}", false );
		$stylesheet_url   = strtok( $stylesheet_url, '&' ); // remove & query string
		$is_sitemap_index = sgg_is_sitemap_index( $template, $settings );

		$sitemap_urls = $is_sitemap_index
			? $this->urls
			: array_chunk( $this->urls, $this->maxURLsPerSitemap );

		foreach ( $sitemap_urls as $sitemap_key => $sitemap ) {
			$dom    = $this->create_sitemap_dom( $stylesheet_url );
			$urlset = $this->create_sitemap_urlset( $dom, $headers );

			$dom->appendChild( $urlset );

			foreach ( $sitemap as $url ) {
				$url_element = $dom->createElement( 'url' );

				$url_element->appendChild(
					$dom->createElement( 'loc', htmlspecialchars( $url['loc'], ENT_QUOTES, 'UTF-8' ) )
				);

				if ( GoogleNews::$template === $template ) {
					if ( $settings->google_news_old_posts && GoogleNews::is_older_than_48h( $url['lastmod'] ) ) {
						$url_element->appendChild(
							$dom->createElement( 'lastmod', $url['lastmod'] )
						);
					} else {
						$news = $url_element->appendChild( $dom->createElement( 'news:news' ) );

						if ( isset( $url['publication_name'] ) ) {
							$publication = $dom->createElement( 'news:publication' );
							$news->appendChild( $publication );

							$publication->appendChild(
								$dom->createElement( 'news:name', esc_html( $url['publication_name'] ) )
							);
							$publication->appendChild(
								$dom->createElement( 'news:language', $url['language'] )
							);
						}
						if ( isset( $url['lastmod'] ) ) {
							$news->appendChild(
								$dom->createElement( 'news:publication_date', $url['lastmod'] )
							);
						}
						if ( isset( $url['title'] ) ) {
							$news->appendChild(
								$dom->createElement( 'news:title', esc_html( $url['title'] ) )
							);
						}
						if ( sgg_pro_enabled() ) {
							$news->appendChild(
								$dom->createElement( 'news:keywords', $url['keywords'] )
							);
							$news->appendChild(
								$dom->createElement( 'news:stock_tickers', $url['stock_tickers'] )
							);
						}
					}
				} elseif ( ImageSitemap::$template === $template ) {
					if ( ! empty( $url['media'] ) ) {
						foreach ( $url['media'] as $image ) {
							$image_element = $url_element->appendChild( $dom->createElement( 'image:image' ) );
							$image_element->appendChild(
								$dom->createElement( 'image:loc', $image )
							);
						}
					}
				} elseif ( VideoSitemap::$template === $template ) {
					if ( ! empty( $url['media'] ) ) {
						foreach ( $url['media'] as $video ) {
							$video_element = $url_element->appendChild( $dom->createElement( 'video:video' ) );
							$video_element->appendChild(
								$dom->createElement( 'video:thumbnail_loc', esc_url( $video['thumbnail'] ?? '' ) )
							);
							$video_element->appendChild(
								$dom->createElement( 'video:title', esc_html( $video['title'] ?? '' ) )
							);
							$video_element->appendChild(
								$dom->createElement( 'video:description', esc_html( $video['description'] ?? '' ) )
							);
							$video_element->appendChild(
								$dom->createElement( 'video:player_loc', esc_url( $video['player_loc'] ?? '' ) )
							);
							$video_element->appendChild(
								$dom->createElement( 'video:duration', esc_html( $video['duration'] ?? '' ) )
							);
						}
					}
				} else {
					if ( isset( $url['lastmod'] ) ) {
						$url_element->appendChild(
							$dom->createElement( 'lastmod', $url['lastmod'] )
						);
					}
					if ( isset( $url['changefreq'] ) ) {
						$url_element->appendChild(
							$dom->createElement( 'changefreq', $url['changefreq'] )
						);
					}
					if ( isset( $url['priority'] ) ) {
						$url_element->appendChild(
							$dom->createElement( 'priority', $url['priority'] )
						);
					}
				}

				$urlset->appendChild( $url_element );

				if ( $is_sitemap_index && 'sitemap' === $template ) {
					$this->sitemaps[ $sitemap_key ][] = array(
						'loc'     => $url['loc'],
						'lastmod' => $url['lastmod'],
					);
				}
			}

			if ( ! sgg_pro_enabled() || ! $settings->minimize_sitemap ) {
				$dom->formatOutput = true;
			}

			if ( 'sitemap' !== $template || ! $is_sitemap_index ) {
				$ready_sitemap = $dom->saveXML();

				if ( $is_sitemap_index ) {
					$this->sitemaps[ $sitemap_key ][]          = $ready_sitemap;
					$this->sitemaps[ $sitemap_key ]['lastmod'] = $url['lastmod'] ?? gmdate( 'c' );
				} else {
					$this->sitemaps[] = $ready_sitemap;
				}
			}
		}

		if ( null === $this->sitemaps ) {
			$dom    = $this->create_sitemap_dom( $stylesheet_url );
			$urlset = $this->create_sitemap_urlset( $dom, $headers );

			$dom->appendChild( $urlset );

			$this->sitemaps[] = $dom->saveXML();
		}

		if ( count( $this->sitemaps ) > 1000 ) {
			throw new \LengthException( 'Sitemap index can contains 1000 single sitemaps. Perhaps You trying to submit too many URLs.' );
		}

		if ( ! empty( $settings->sitemap_view ) && 'sitemap' === $template && count( $this->sitemaps ) > 0 ) {
			$stylesheet_url   = sgg_get_sitemap_url( "{$stylesheet_path}?template=sitemap-index", 'sitemap_xsl=sitemap-index', false );
			$stylesheet_url   = strtok( $stylesheet_url, '&' ); // remove & query string

			$dom = $this->create_sitemap_dom( $stylesheet_url );

			$sitemapindex = $dom->createElement( 'sitemapindex' );
			$sitemapindex->setAttribute( 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance' );
			$sitemapindex->setAttribute( 'xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd' );
			$sitemapindex->setAttribute( 'xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
			$dom->appendChild( $sitemapindex );

			foreach ( $this->sitemaps as $sitemap_key => $sitemap ) {
				if ( ! empty( $sitemap ) && is_array( $sitemap ) ) {
					if ( 'page' === $sitemap_key && sgg_get_home_url() === ( $sitemap[0]['loc'] ?? '' ) && 1 < count( $sitemap ) ) {
						$sitemap[0] = array(
							'loc'     => $sitemap[0]['loc'],
							'lastmod' => max( $sitemap[0]['lastmod'] ?? '', $sitemap[1]['lastmod'] ?? '' ),
						);

						if ( ! empty( $sitemap[1] ) ) {
							unset( $sitemap[1] );
						}

						$sitemap = array_values( $sitemap );
					}

					foreach ( $sitemap as $index => $url ) {
						$sitemap_element = $dom->createElement( 'sitemap' );
						$sitemap_type    = $is_xml ? 'xml' : 'html';
						$sitemap_number  = 0 < $index ? intval( $index ) + 1 : '';
						$page_param      = 0 < $sitemap_number ? "&page={$sitemap_number}" : '';

						$sitemap_element->appendChild(
							$dom->createElement(
								'loc',
								esc_url( sgg_get_sitemap_url( "{$sitemap_key}-sitemap{$sitemap_number}.{$sitemap_type}", "sitemap_{$sitemap_type}=true&inner_sitemap={$sitemap_key}{$page_param}", false ) )
							)
						);

						$sitemap_element->appendChild(
							$dom->createElement( 'lastmod', $url['lastmod'] ?? gmdate( 'c' ) )
						);

						$sitemapindex->appendChild( $sitemap_element );
					}
				}
			}

			if ( $is_xml ) {
				$additional_sitemaps = apply_filters( 'sgg_additional_index_sitemaps', $settings->custom_sitemaps ?? array() );

				if ( $settings->enable_image_sitemap ) {
					$additional_sitemaps[] = array(
						'url' => sgg_get_sitemap_url( $settings->image_sitemap_url, 'image_sitemap' ),
					);
				}

				if ( $settings->enable_video_sitemap ) {
					$additional_sitemaps[] = array(
						'url' => sgg_get_sitemap_url( $settings->video_sitemap_url, 'video_sitemap' ),
					);
				}

				foreach ( $additional_sitemaps as $additional_sitemap ) {
					$sitemap_element = $dom->createElement( 'sitemap' );
					$sitemap_url     = ! empty( $additional_sitemap['url'] ) ? esc_url( $additional_sitemap['url'] ) : $additional_sitemap;

					if ( ! is_string( $sitemap_url ) ) {
						continue;
					}
					$sitemap_element->appendChild(
						$dom->createElement( 'loc', $sitemap_url )
					);

					$sitemap_element->appendChild(
						$dom->createElement(
							'lastmod',
							! empty( $additional_sitemap['lastmod'] ) ? $additional_sitemap['lastmod'] : gmdate( 'c' )
						)
					);

					$sitemapindex->appendChild( $sitemap_element );
				}
			}

			if ( ! sgg_pro_enabled() || ! $settings->minimize_sitemap ) {
				$dom->formatOutput = true;
			}

			$this->sitemapFullURL = $this->baseURL . $this->sitemapIndexFileName;
			$this->sitemapIndex   = array(
				$this->sitemapIndexFileName,
				$dom->saveXML(),
			);
		} else {
			if ( $this->createGZipFile ) {
				$this->sitemapFullURL = $this->baseURL . $this->sitemapFileName . '.gz';
			} else {
				$this->sitemapFullURL = $this->baseURL . $this->sitemapFileName;
			}

			$this->sitemaps[0] = array(
				$this->sitemapFileName,
				$this->sitemaps[0],
			);
		}
	}


	/**
	 * Create Multilingual Sitemap in memory.
	 */
	public function createMultilingualSitemap( $urls = array(), $is_xml = true ) {
		if ( ! isset( $this->urls ) ) {
			throw new \BadMethodCallException( 'To create sitemap, call addUrl or addUrls function first.' );
		}

		if ( $this->maxURLsPerSitemap > 50000 ) {
			throw new \InvalidArgumentException( 'More than 50,000 URLs per single sitemap is not allowed.' );
		}

		$stylesheet_path = apply_filters( 'sitemap_xsl_template_path', 'sitemap-stylesheet.xsl' );

		$dom = $this->create_sitemap_dom(
			sgg_get_sitemap_url( "{$stylesheet_path}?template=multilingual-sitemap", 'sitemap_xsl=sitemap-index', false )
		);

		$sitemapindex = $dom->createElement( 'sitemapindex' );
		$sitemapindex->setAttribute( 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance' );
		$sitemapindex->setAttribute( 'xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd' );
		$sitemapindex->setAttribute( 'xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
		$dom->appendChild( $sitemapindex );

		foreach ( $urls as $url ) {
			$sitemap_element = $dom->createElement( 'sitemap' );

			$sitemap_element->appendChild(
				$dom->createElement(
					'loc',
					esc_url( $url )
				)
			);

			$sitemap_element->appendChild(
				$dom->createElement( 'lastmod', gmdate( DATE_W3C ) )
			);

			$sitemapindex->appendChild( $sitemap_element );
		}

		if ( ! sgg_pro_enabled() || ! $settings->minimize_sitemap ) {
			$dom->formatOutput = true;
		}

		$this->sitemapFullURL = $this->baseURL . $this->sitemapIndexFileName;
		$this->sitemapIndex   = array(
			$this->sitemapIndexFileName,
			$dom->saveXML(),
		);
	}

	public function create_sitemap_dom( $stylesheet_url ) {
		$generator_info = 'sitemap-generator-url="https://wpgrim.com" sitemap-generator-version="' . GRIM_SG_VERSION . '"';

		$dom = new \DOMDocument( '1.0', 'UTF-8' );

		$dom->appendChild(
			$dom->createProcessingInstruction(
				'xml-stylesheet',
				'type="text/xsl" href="' . $stylesheet_url . '"'
			)
		);

		$dom->appendChild( $dom->createComment( $generator_info ) );

		return $dom;
	}

	public function create_sitemap_urlset( $dom, $headers = array() ) {
		$urlset = $dom->createElement( 'urlset' );

		$urlset->setAttribute( 'xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
		if ( ! empty( $headers ) ) {
			foreach ( $headers as $key => $header ) {
				$urlset->setAttribute( $key, $header );
			}
		}

		return $urlset;
	}

	/**
	 * Returns created sitemaps as array of strings.
	 * Use it You want to work with sitemap without saving it as files.
	 * @return array of strings
	 * @access public
	 */
	public function toArray() {
		if ( isset( $this->sitemapIndex ) ) {
			return array_merge( array( $this->sitemapIndex ), $this->sitemaps );
		} else {
			return $this->sitemaps;
		}
	}

	/**
	 * Will print sitemaps.
	 * @access public
	 */
	public function outputSitemap( $template, $is_xml, $inner_sitemap = null ) {
		add_filter( 'trp_stop_translating_page', '__return_true' );

		ob_get_clean();

		if ( $is_xml ) {
			header( 'Content-Type: text/xml; charset=utf-8' );
		} else {
			ob_start();
		}

		if ( ! empty( $inner_sitemap ) && sgg_is_sitemap_index( $template ) && ! empty( $this->sitemaps[ $inner_sitemap ][0] ) ) {
			echo $this->sitemaps[ $inner_sitemap ][0];
		} else {
			if ( ! empty( $this->sitemapIndex[1] ) ) {
				$template = 'sitemap-index';
				echo $this->sitemapIndex[1];
			} else {
				echo $this->sitemaps[0][1] ?? '';
			}
		}

		if ( ! $is_xml ) {
			$xml_source = ob_get_clean();

			$xml = new \DOMDocument();
			$xml->loadXML( $xml_source );

			ob_start();

			load_template(
				GRIM_SG_PATH . '/templates/xsl/sitemap.php',
				false,
				compact( 'template', 'is_xml' )
			);

			$xsl_content = ob_get_clean();

			$xsl = new \DOMDocument();
			$xsl->loadXML( $xsl_content );

			$proc = new \XSLTProcessor();
			$proc->importStyleSheet( $xsl );

			$dom_tran_obj = $proc->transformToDoc( $xml );

			foreach ( $dom_tran_obj->childNodes as $node ) {
				echo $dom_tran_obj->saveXML( $node ) . "\n";
			}
		}

		if ( ob_get_contents() ) {
			ob_end_flush();
		}
	}
}
