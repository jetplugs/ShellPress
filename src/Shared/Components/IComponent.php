<?php

namespace shellpress\v1_3_1\src\Shared\Components;

/**
 * Date: 26.04.2018
 * Time: 23:07
 */

use shellpress\v1_3_1\ShellPress;
use shellpress\v1_3_1\src\Shell;

abstract class IComponent {

	private $shellPressClassName;

	/**
	 * Component constructors.
	 * You must pass ShellPress Object or class name.
	 *
	 * @param ShellPress|string $shellPress
	 * @param string|null       $setupHook  Hook on which onSetUp method will be called.
	 * @param int               $priority   Priority of hook.
	 */
	public function __construct( $shellPress, $setupHook = null, $priority = 5 ) {

		$this->shellPressClassName = is_object( $shellPress ) ? get_class( $shellPress ) : $shellPress;

		/**
		 * @since 1_3_1
		 * When $setupHook is set, onSetUp method will be called at it.
		 * When not, it will be called immediately.
		 */
		if( $setupHook ){
			add_action( $setupHook, array( $this, 'onSetUp' ), $priority );
		} else {
			$this->onSetUp();
		}

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