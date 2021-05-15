<?php

namespace Elderbraum\WooProductSet;

interface Installable
{
	/**
	 * Make a class instance.
	 *
	 * @return self
	 */
	public static function install();
}
