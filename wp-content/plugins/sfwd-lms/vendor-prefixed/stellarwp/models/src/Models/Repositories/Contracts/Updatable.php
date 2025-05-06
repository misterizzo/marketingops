<?php

namespace StellarWP\Learndash\StellarWP\Models\Repositories\Contracts;

use StellarWP\Learndash\StellarWP\Models\Contracts\Model;

interface Updatable {
	/**
	 * Inserts a model record.
	 *
	 * @since 1.0.0
	 *
	 * @param Model $model
	 *
	 * @return Model
	 */
	public function update( Model $model ) : Model;
}
