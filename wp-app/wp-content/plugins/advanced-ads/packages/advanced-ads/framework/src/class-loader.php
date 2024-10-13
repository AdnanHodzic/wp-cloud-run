<?php
/**
 * Class that manages loading integrations.
 *
 * @package AdvancedAds\Framework
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework;

use ReflectionClass;

defined( 'ABSPATH' ) || exit;

/**
 * Loader class
 */
class Loader {

	/**
	 * The registered integrations.
	 *
	 * @var string[]
	 */
	protected $integrations = [];

	/**
	 * The registered integrations.
	 *
	 * @var string[]
	 */
	protected $initializers = [];

	/**
	 * The registered routes.
	 *
	 * @var string[]
	 */
	protected $routes = [];

	/**
	 * Hold containers
	 *
	 * @var array
	 */
	protected $containers = [];

	/**
	 * Override magic method
	 *
	 * @param string $name Name of property.
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		if ( array_key_exists( $name, $this->containers ) ) {
			return $this->containers[ $name ];
		}

		// phpcs:disable
		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE
		);
		// phpcs:enable

		return null;
	}

	/**
	 * Loads all registered classes if their conditionals are met.
	 *
	 * @return void
	 */
	public function load() {
		$this->load_initializers();

		if ( ! \did_action( 'init' ) ) {
			\add_action( 'init', [ $this, 'load_integrations' ] );
		} else {
			$this->load_integrations();
		}

		\add_action( 'rest_api_init', [ $this, 'load_routes' ] );
	}

	/**
	 * Register as.
	 *
	 * @param string $register_as Register the container as.
	 * @param string $class_name  The class name of the registery to be loaded.
	 * @param string $alias       The class alias.
	 * @param array  $args        The constructor arguments.
	 *
	 * @return void
	 */
	private function register( $register_as, $class_name, $alias = '', $args = null ) {
		if ( ! empty( $args ) ) {
			$class_name = [ $class_name, $args ];
		}

		if ( '' === $alias ) {
			$this->{$register_as}[] = $class_name;
		} else {
			$this->{$register_as}[ $alias ] = $class_name;
		}
	}

	/**
	 * Register an integration.
	 *
	 * @param string $integration The class name of the integration to be loaded.
	 * @param string $alias       The class alias.
	 * @param array  $args        The constructor arguments.
	 *
	 * @return void
	 */
	public function register_integration( $integration, $alias = '', $args = null ) {
		$this->register( 'integrations', $integration, $alias, $args );
	}

	/**
	 * Register an initializer.
	 *
	 * @param string $initializer The class name of the initializer to be loaded.
	 * @param string $alias       The class alias.
	 * @param array  $args        The constructor arguments.
	 *
	 * @return void
	 */
	public function register_initializer( $initializer, $alias = '', $args = null ) {
		$this->register( 'initializers', $initializer, $alias, $args );
	}

	/**
	 * Register a route.
	 *
	 * @param string $router The class name of the route to be loaded.
	 * @param string $alias  The class alias.
	 * @param array  $args   The constructor arguments.
	 *
	 * @return void
	 */
	public function register_route( $router, $alias = '', $args = null ) {
		$this->register( 'routes', $router, $alias, $args );
	}

	/**
	 * Loads all registered initializers if their conditionals are met.
	 *
	 * @return void
	 */
	protected function load_initializers() {
		foreach ( $this->initializers as $alias => $class ) {
			$this->create_container( $class, 'initialize', $alias );
		}
	}

	/**
	 * Loads all registered integrations if their conditionals are met.
	 *
	 * @return void
	 */
	public function load_integrations() {
		foreach ( $this->integrations as $alias => $class ) {
			$this->create_container( $class, 'hooks', $alias );
		}
	}

	/**
	 * Loads all registered routes if their conditionals are met.
	 *
	 * @return void
	 */
	public function load_routes() {
		foreach ( $this->routes as $alias => $class ) {
			$this->create_container( $class, 'register_routes', $alias );
		}
	}

	/**
	 * Create container if needed.
	 *
	 * @param string $data   Class data.
	 * @param string $method Method to execute.
	 * @param string $alias  Class alias.
	 *
	 * @return void
	 */
	private function create_container( $data, $method, $alias ): void {
		$class_name = is_string( $data ) ? $data : $data[0];
		$arguments  = is_string( $data ) ? [] : $data[1];

		if ( ! \class_exists( $class_name, true ) ) {
			return;
		}

		$container = new ReflectionClass( $class_name );
		$container = $container->newInstanceArgs( $arguments );
		if ( null === $container ) {
			return;
		}

		$container->$method();

		if ( is_string( $alias ) ) {
			$this->containers[ $alias ] = $container;
		}
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param bool|string $value Constant value.
	 *
	 * @return void
	 */
	protected function define( $name, $value ): void {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
}
