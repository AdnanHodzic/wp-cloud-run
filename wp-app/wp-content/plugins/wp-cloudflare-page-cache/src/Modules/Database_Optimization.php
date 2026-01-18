<?php

namespace SPC\Modules;

use SPC\Constants;
use SPC\Services\Settings_Store;

class Database_Optimization implements Module_Interface {

	/**
	 * Event intervals
	 * @var array<string, array<string, callable>> $intervals
	 */
	private $intervals = array();

	const NEVER   = 'never';
	const DAILY   = 'daily';
	const WEEKLY  = 'weekly';
	const MONTHLY = 'monthly';

	/**
	 * Database_Optimization constructor.
	 *
	 * Initializes the intervals array with database optimization callbacks.
	 */
	public function __construct() {
		$this->intervals = array(
			Constants::SETTING_ALL_TRANSIENT_INTERVAL   => array(
				'callback' => array( $this, 'remove_transients' ),
			),
			Constants::SETTING_POST_REVISION_INTERVAL   => array(
				'callback' => array( $this, 'remove_revision_posts' ),
			),
			Constants::SETTING_AUTO_DRAFT_POST_INTERVAL => array(
				'callback' => array( $this, 'remove_draft_posts' ),
			),
			Constants::SETTING_TRASHED_POST_INTERVAL    => array(
				'callback' => array( $this, 'remove_trashed_posts' ),
			),
			Constants::SETTING_SPAM_COMMENT_INTERVAL    => array(
				'callback' => array( $this, 'remove_spam_comments' ),
			),
			Constants::SETTING_TRASHED_COMMENT_INTERVAL => array(
				'callback' => array( $this, 'remove_trashed_comments' ),
			),
			Constants::SETTING_OPTIMIZE_TABLE_INTERVAL  => array(
				'callback' => array( $this, 'optimize_database_table' ),
			),
		);
	}

	/**
	 * Get the schedule options.
	 *
	 * @return array<string, string>
	 */
	public static function get_schedule_options() {
		return array(
			self::NEVER   => __( 'Never', 'wp-cloudflare-page-cache' ),
			self::DAILY   => __( 'Daily', 'wp-cloudflare-page-cache' ),
			self::WEEKLY  => __( 'Weekly', 'wp-cloudflare-page-cache' ),
			self::MONTHLY => __( 'Monthly', 'wp-cloudflare-page-cache' ),
		);
	}

	/**
	 * Initialize the module.
	 *
	 * Sets up action hooks for all database optimization intervals.
	 *
	 * @return void
	 */
	public function init() {

		foreach ( $this->intervals as $interval => $args ) {
			$hook = $this->event_hook( $interval );
			add_action( $hook, $args['callback'] );

			// For backward compatibility.
			$_hook = str_replace( 'swcfpc_', '', $hook );
			add_action( $_hook, array( $this, 'legacy_action_scheduler' ) );
		}
	}

	/**
	 * Setup cron for database optimization actions.
	 *
	 * Schedules or unschedules recurring actions based on settings.
	 *
	 * @return void
	 */
	public function setup_cron() {
		$settings = Settings_Store::get_instance();

		foreach ( $this->intervals as $interval => $args ) {
			$recurrence = $settings->get( $interval );
			if ( $recurrence === 'never' ) {
				as_unschedule_action( $this->event_hook( $interval ), [], Constants::ACTION_SCHEDULER_GROUP );
			} else {
				$hook = $this->event_hook( $interval );
				$this->schedule_event( $hook, $recurrence );
			}
		}
	}


	/**
	 * Schedule the event.
	 *
	 * @param string $hook hook for event schedule.
	 * @param string $recurrence recurrence for the event.
	 * @return void
	 */
	private function schedule_event( $hook, $recurrence ) {
		if ( $recurrence === self::NEVER ) {
			as_unschedule_action( $hook, [], Constants::ACTION_SCHEDULER_GROUP );
			return;
		}
		$recurrence_time = $this->recurrence_time( $recurrence );
		if ( as_has_scheduled_action( $hook, [], Constants::ACTION_SCHEDULER_GROUP ) ) {
			$action = as_get_scheduled_actions(
				[
					'hook'   => $hook,
					'group'  => Constants::ACTION_SCHEDULER_GROUP,
					'status' => 'pending',
				]
			);
			//Here we check if the action is already scheduled and if the recurrence is different, we unschedule it
			if ( ! empty( $action ) ) {
				$action = reset( $action );
				if ( $action->get_schedule()->get_recurrence() !== $recurrence_time ) {
					as_unschedule_action( $hook, [], Constants::ACTION_SCHEDULER_GROUP );
				}
			}
		}
		as_schedule_recurring_action( time(), $recurrence_time, $hook, [], Constants::ACTION_SCHEDULER_GROUP, true );
	}

	/**
	 * Get the time for the recurrence.
	 *
	 * @param string $recurrence The recurrence interval (monthly, weekly, daily).
	 * @return int The time in seconds for the recurrence interval.
	 */
	public function recurrence_time( string $recurrence ): int {
		switch ( $recurrence ) {
			case self::MONTHLY:
				return MONTH_IN_SECONDS;
			case self::WEEKLY:
				return WEEK_IN_SECONDS;
			case self::DAILY:
				return DAY_IN_SECONDS;
			case self::NEVER:
				return 0;
			default:
				return DAY_IN_SECONDS;
		}
	}


	/**
	 * Create event hook.
	 *
	 * Transforms the interval setting name into a hook name.
	 *
	 * @param string $hook The original hook name.
	 * @return string The transformed hook name.
	 */
	private function event_hook( $hook ) {
		return 'swcfpc_' . str_replace( '_interval', '', $hook );
	}

	/**
	 * Execute paginated database query with callback processing.
	 *
	 * @param string   $query_template SQL query template with %d placeholders for LIMIT and OFFSET.
	 * @param callable $callback       Function to process each batch of results.
	 * @param int      $batch_size     Number of records to process per batch.
	 * @return int Total number of items processed.
	 */
	private function paginated_query( $query_template, $callback, $batch_size = 100 ) {
		global $wpdb;
		$offset = 0;
		$total  = 0;
		do {
			$query = $wpdb->get_col(
				$wpdb->prepare(
					$query_template, //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$batch_size,
					$offset
				)
			);

			if ( $query ) {
				$total += call_user_func( $callback, $query );
			}

			$offset += $batch_size;
		} while ( count( $query ) === $batch_size );

		return $total;
	}

	/**
	 * Optimize entire database.
	 *
	 * Executes all database optimization methods.
	 *
	 * @return string Success or error message.
	 * @throws \Exception When database optimization fails.
	 */
	public function remove_all() {
		try {
			foreach ( $this->intervals as $interval ) {
				call_user_func( $interval['callback'] );
			}

			return __( 'Database optimized successfully', 'wp-cloudflare-page-cache' );
		} catch ( \Exception $e ) {
			return sprintf(
				// translators: %s is error.
				__( 'There is an error while optimizing database. Error: %s', 'wp-cloudflare-page-cache' ),
				$e->getMessage(),
			);
		}
	}

	/**
	 * Remove post's revisions.
	 *
	 * Removes all post revisions from the database in batches.
	 *
	 * @return string Success or error message with count of removed revisions.
	 * @throws \Exception When revision removal fails.
	 */
	public function remove_revision_posts() {
		try {
			global $wpdb;

			$posts = $this->paginated_query(
				"SELECT ID FROM $wpdb->posts WHERE post_type = 'revision' LIMIT %d OFFSET %d",
				function ( $batch ) {
					$count = 0;
					foreach ( $batch as $id ) {
						$count += wp_delete_post_revision( intval( $id ) ) instanceof \WP_Post ? 1 : 0;
					}
					return $count;
				}
			);

			return sprintf(
				// translators: %d is number of removed post's revisions.
				__( '%d posts revision removed.', 'wp-cloudflare-page-cache' ),
				$posts
			);
		} catch ( \Exception $e ) {
			return sprintf(
				// translators: %s is error.
				__( 'There is an error while removing the post revision. Error: %s', 'wp-cloudflare-page-cache' ),
				$e->getMessage()
			);
		}
	}

	/**
	 * Remove draft posts.
	 *
	 * Removes all auto-draft posts from the database in batches.
	 *
	 * @return string Success or error message with count of removed drafts.
	 * @throws \Exception When draft removal fails.
	 */
	public function remove_draft_posts() {
		try {
			global $wpdb;

			$posts = $this->paginated_query(
				"SELECT ID FROM $wpdb->posts WHERE post_status = 'auto-draft' LIMIT %d OFFSET %d",
				function ( $batch ) {
					$count = 0;
					foreach ( $batch as $id ) {
						$count += wp_delete_post( intval( $id ), true ) instanceof \WP_Post ? 1 : 0;
					}
					return $count;
				}
			);

			return sprintf(
				// translators: %d is number of removed auto draft posts.
				__( '%d auto-draft posts removed.', 'wp-cloudflare-page-cache' ),
				$posts
			);
		} catch ( \Exception $e ) {
			return sprintf(
				// translators: %s is error.
				__( 'There is an error while removing auto draft posts. Error: %s', 'wp-cloudflare-page-cache' ),
				$e->getMessage(),
			);
		}
	}

	/**
	 * Remove trashed posts.
	 *
	 * Removes all trashed posts from the database in batches.
	 *
	 * @return string Success or error message with count of removed posts.
	 * @throws \Exception When trashed post removal fails.
	 */
	public function remove_trashed_posts() {
		try {
			global $wpdb;

			$posts = $this->paginated_query(
				"SELECT ID FROM $wpdb->posts WHERE post_status = 'trash' LIMIT %d OFFSET %d",
				function ( $batch ) {
					$count = 0;
					foreach ( $batch as $id ) {
						$count += wp_delete_post( intval( $id ), true ) instanceof \WP_Post ? 1 : 0;
					}
					return $count;
				}
			);

			return sprintf(
				// translators: %d is number of removed trashed posts.
				__( '%d trashed posts removed.', 'wp-cloudflare-page-cache' ),
				$posts,
			);
		} catch ( \Exception $e ) {
			return sprintf(
				// translators: %s is error.
				__( 'There is an error while removing trashed post. Error: %s', 'wp-cloudflare-page-cache' ),
				$e->getMessage(),
			);
		}
	}

	/**
	 * Remove spam comments.
	 *
	 * Removes all spam comments from the database in batches.
	 *
	 * @return string Success or error message with count of removed comments.
	 * @throws \Exception When spam comment removal fails.
	 */
	public function remove_spam_comments() {
		try {
			global $wpdb;

			$comments = $this->paginated_query(
				"SELECT comment_ID FROM $wpdb->comments WHERE comment_approved = 'spam' LIMIT %d OFFSET %d",
				function ( $batch ) {
					$count = 0;
					foreach ( $batch as $id ) {
						$count += wp_delete_comment( intval( $id ), true );
					}
					return $count;
				}
			);

			return sprintf(
				// translators: %d is number of removed spam comments.
				__( '%d spam comments removed.', 'wp-cloudflare-page-cache' ),
				$comments
			);
		} catch ( \Exception $e ) {
			return sprintf(
				// translators: %s is error.
				__( 'There is an error while removing spam comments. Error: %s', 'wp-cloudflare-page-cache' ),
				$e->getMessage()
			);
		}
	}

	/**
	 * Remove trashed comments.
	 *
	 * Removes all trashed and post-trashed comments from the database in batches.
	 *
	 * @return string Success or error message with count of removed comments.
	 * @throws \Exception When trashed comment removal fails.
	 */
	public function remove_trashed_comments() {
		try {
			global $wpdb;

			$comments = $this->paginated_query(
				"SELECT comment_ID FROM $wpdb->comments WHERE (comment_approved = 'trash' OR comment_approved = 'post-trashed') LIMIT %d OFFSET %d",
				function ( $batch ) {
					$count = 0;
					foreach ( $batch as $id ) {
						$count += wp_delete_comment( intval( $id ), true );
					}
					return $count;
				}
			);

			return sprintf(
				// translators: %d is number of removed trashed comments.
				__( '%d trashed comments removed.', 'wp-cloudflare-page-cache' ),
				$comments
			);
		} catch ( \Exception $e ) {
			return sprintf(
				// translators: %s is error.
				__( 'There is an error while removing trashed comments. Error: %s', 'wp-cloudflare-page-cache' ),
				$e->getMessage()
			);
		}
	}

	/**
	 * Remove transients.
	 *
	 * Removes all transients and site transients from the database in batches.
	 *
	 * @return string Success or error message with count of removed transients.
	 * @throws \Exception When transient removal fails.
	 */
	public function remove_transients() {
		try {
			global $wpdb;

			$transient_like      = $wpdb->esc_like( '_transient_' ) . '%';
			$site_transient_like = $wpdb->esc_like( '_site_transient_' ) . '%';

			$transients = $this->paginated_query(
				"SELECT option_name FROM $wpdb->options WHERE option_name LIKE '$transient_like' OR option_name LIKE '$site_transient_like' LIMIT %d OFFSET %d",
				function ( $batch ) {
					$count = 0;
					foreach ( $batch as $transient ) {
						if ( strpos( $transient, '_site_transient_' ) !== false ) {
							$count += (int) delete_site_transient( str_replace( '_site_transient_', '', $transient ) );
						} else {
							$count += (int) delete_transient( str_replace( '_transient_', '', $transient ) );
						}
					}
					return $count;
				}
			);

			return sprintf(
				// translators: %d is number of removed transients.
				__( '%d transients removed.', 'wp-cloudflare-page-cache' ),
				$transients,
			);
		} catch ( \Exception $e ) {
			return sprintf(
				// translators: %s is error.
				__( 'There is an error while removing transients. Error: %s', 'wp-cloudflare-page-cache' ),
				$e->getMessage(),
			);
		}
	}

	/**
	 * Optimize database tables.
	 *
	 * Optimizes non-InnoDB database tables that have free space.
	 *
	 * @return string Success or error message with count of optimized tables.
	 * @throws \Exception When table optimization fails.
	 */
	public function optimize_database_table() {
		try {
			$tables = 0;
			if ( defined( 'DB_NAME' ) ) {
				global $wpdb;

				$query = $wpdb->get_results( "SELECT table_name AS table_name, data_free AS data_free FROM information_schema.tables WHERE table_schema = '" . esc_sql( DB_NAME ) . "' and Engine <> 'InnoDB' and data_free > 0" );

				if ( $query ) {
					foreach ( $query as $table ) {
						$results = $wpdb->get_results( 'OPTIMIZE TABLE ' . esc_sql( $table->table_name ) );

						foreach ( $results as $result ) {
							if ( isset( $result->Msg_text ) && 'OK' === $result->Msg_text ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
								++$tables;
							}
						}
					}
				}
			}
			if ( $tables > 0 ) {
				return sprintf(
				// translators: %d is the number of optimized database tables.
					_n( '%d database table optimized.', '%d database tables optimized.', $tables, 'wp-cloudflare-page-cache' ),
					$tables
				);
			} else {
				return __( 'No database tables are eligible for optimization.', 'wp-cloudflare-page-cache' );
			}
		} catch ( \Exception $e ) {
			return sprintf(
				// translators: %s is error.
				__( 'There is an error while optimizing table. Error: %s', 'wp-cloudflare-page-cache' ),
				$e->getMessage()
			);
		}
	}

	/**
	 * Remove cron events.
	 *
	 * Unschedules all database optimization cron events.
	 *
	 * @return void
	 */
	public function delete_events() {
		foreach ( $this->intervals as $interval => $args ) {
			$hook = $this->event_hook( $interval );
			as_unschedule_action( $hook, [], Constants::ACTION_SCHEDULER_GROUP );
		}
	}

	/**
	 * Legacy Action Scheduler compatibility.
	 *
	 * Maps Action Scheduler hooks to legacy hooks for backward compatibility.
	 *
	 * @return void
	 */
	public function legacy_action_scheduler() {
		if ( ! $this->is_action_scheduler() ) {
			return;
		}

		$current_hook = current_action();
		do_action( 'swcfpc_' . $current_hook );
	}

	/**
	 * Check if current action is from Action Scheduler.
	 *
	 * @return bool True if current action is from Action Scheduler, false otherwise.
	 */
	private function is_action_scheduler() {
		return function_exists( 'as_has_scheduled_action' ) && doing_action( 'action_scheduler_run_queue' );
	}
}
