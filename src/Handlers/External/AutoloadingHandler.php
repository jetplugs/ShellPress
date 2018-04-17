<?php
namespace shellpress\v1_2_0\src\Handlers\External;

/**
 * Date: 17.04.2018
 * Time: 21:37
 */

use shellpress\v1_2_0\lib\Psr4Autoloader\Psr4AutoloaderClass;
use shellpress\v1_2_0\src\Handlers\IHandler;

class AutoloadingHandler extends IHandler {

	/** @var Psr4AutoloaderClass */
	protected $psr4Autoloader;

	/**
	 * Called on handler construction.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		if( ! class_exists( 'shellpress\v1_2_0\lib\Psr4Autoloader\Psr4AutoloaderClass' ) ){
			require( $this->shell()->getShellPressDir() . '/lib/Psr4Autoloader/Psr4AutoloaderClass.php' );
		}

		$this->psr4Autoloader = new Psr4AutoloaderClass();
		$this->psr4Autoloader->register();
		$this->psr4Autoloader->addNamespace( 'shellpress\v1_2_0', $this->shell()->getShellPressDir() );

	}

	/**
	 * @param string $prefix
	 * @param string $baseDir
	 * @param bool $prepend
	 */
	public function addNamespace( $prefix, $baseDir, $prepend = false ) {

		$this->psr4Autoloader->addNamespace( $prefix, $baseDir, $prepend );

	}

}