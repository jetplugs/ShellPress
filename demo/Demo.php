<?php
namespace shellpress\v1_3_74\demo;

/**
 * Date: 15.01.2019
 * Time: 21:40
 */

use shellpress\v1_3_74\demo\src\Components\UniversalFrontExample;
use shellpress\v1_3_74\ShellPress;

class Demo extends ShellPress {

	/** @var UniversalFrontExample */
	public $universalFrontExample;

	/**
	 * Called automatically after core is ready.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		$this->universalFrontExample = new UniversalFrontExample( $this );

	}

}