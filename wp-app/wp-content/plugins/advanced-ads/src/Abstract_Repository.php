<?php

namespace Advanced_Ads;

/**
 * Abstract object repository/factory to hold object instances within a request.
 */
abstract class Abstract_Repository {
	/**
	 * Get the object from the repository. Create and add it, if it doesn't exist.
	 *
	 * @param int $id The object id to look for.
	 */
	abstract public static function get( int $id );

	/**
	 * Whether the object is in the repository.
	 *
	 * @param int $id The object id to look for.
	 *
	 * @return bool
	 */
	protected static function has( int $id ): bool {
		return array_key_exists( $id, static::$repo );
	}
}
