<?php
namespace shellpress\v1_2_0\src\Handlers;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-11-21
 * Time: 22:14
 */

use shellpress\v1_2_0\src\Shell;

abstract class IHandler {

	/** @var Shell */
	private $shell;

	/**
	 * Handler constructor.
	 *
	 * @param Shell $shell
	 */
	public function __construct( $shell ) {

		$this->shell = $shell;

		$this->onSetUp();

	}

	/**
	 * Returns Shell instance.
	 *
	 * @return Shell
	 */
	protected function shell() {

		return $this->shell;

	}

	/**
	 * Called on handler construction.
	 *
	 * @return void
	 */
	protected abstract function onSetUp();

}