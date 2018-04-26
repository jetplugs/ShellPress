<?php

namespace shellpress\v1_2_1\src\Shared\Components;

/**
 * Date: 26.04.2018
 * Time: 23:07
 */

use shellpress\v1_2_1\ShellPress;
use shellpress\v1_2_1\src\Shell;

abstract class IComponent {

	/** @var Shell */
	private $shell;

	/**
	 * Component constructors.
	 * You must pass Shell object from your ShellPress instance.
	 *
	 * @param Shell $shell
	 */
	public function __construct( &$shell ) {

		$this->shell = $shell;

		$this->onSetUp();

	}

	/**
	 * Returns Shell instance.
	 *
	 * @return Shell
	 */
	protected function s() {

		return $this->shell;

	}

	/**
	 * Called on creation of component.
	 *
	 * @return void
	 */
	abstract protected function onSetUp();

}