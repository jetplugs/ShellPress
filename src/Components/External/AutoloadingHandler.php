<?php
namespace shellpress\v1_3_71\src\Components\External;

/**
 * Date: 17.04.2018
 * Time: 21:37
 */

use shellpress\v1_3_71\lib\Psr4Autoloader\Psr4AutoloaderClass;
use shellpress\v1_3_71\src\Shared\Components\IComponent;

class AutoloadingHandler extends IComponent {

	/** @var Psr4AutoloaderClass */
	private $_psr4Autoloader;

	/**
	 * Called on handler construction.
	 *
	 * @return void
	 */
	protected function onSetUp() {}

	/**
	 * @return Psr4AutoloaderClass
	 */
	protected function getAutoloader() {

		if( ! $this->_psr4Autoloader ){

			if ( ! class_exists( 'shellpress\v1_3_71\lib\Psr4Autoloader\Psr4AutoloaderClass' ) ) {
				require( $this->s()->getShellPressDir() . '/lib/Psr4Autoloader/Psr4AutoloaderClass.php' );
			}

			$this->_psr4Autoloader = new Psr4AutoloaderClass();
			$this->_psr4Autoloader->register();

		}

		return $this->_psr4Autoloader;

	}

	/**
	 * @param string $prefix
	 * @param string $baseDir
	 * @param bool   $prepend
	 */
	public function addNamespace( $prefix, $baseDir, $prepend = false ) {

		$this->getAutoloader()->addNamespace( $prefix, $baseDir, $prepend );

	}

}