<?php

namespace shellpress\v1_2_2\src\Shared\Components;

/**
 * Date: 26.04.2018
 * Time: 23:07
 */

use shellpress\v1_2_2\ShellPress;
use shellpress\v1_2_2\src\Shell;

abstract class IComponent {

	private $shellPressClassName;

	/**
	 * Component constructors.
	 * You must pass ShellPress Object or class name.
	 *
	 * @param ShellPress|string $shellPress
	 */
	public function __construct( $shellPress ) {

		$this->shellPressClassName = is_object( $shellPress ) ? get_class( $shellPress ) : $shellPress;

		$this->onSetUp();

	}

	/**
	 * Returns ShellPress instance.
	 *
	 * @return ShellPress
	 */
	public function i() {
		return call_user_func( array( $this->shellPressClassName, 'i' ) );
	}

	/**
	 * Returns Shell instance.
	 *
	 * @return Shell
	 */
	public function s() {
		return call_user_func( array( $this->shellPressClassName, 's' ) );
	}

	/**
	 * Called on creation of component.
	 *
	 * @return void
	 */
	abstract protected function onSetUp();

}