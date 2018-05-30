<?php
namespace shellpress\v1_2_3\src\Components\External;

/**
 * Date: 30.05.2018
 * Time: 21:36
 */

use Mustache_Autoloader;
use Mustache_Engine;
use shellpress\v1_2_3\src\Shared\Components\IComponent;

class MustacheHandler extends IComponent {

	/** @var bool */
	protected $isInitialized = false;

	/** @var Mustache_Engine */
	protected $engine;

	/**
	 * Called on creation of component.
	 *
	 * @return void
	 */
	protected function onSetUp() {}

	/**
	 * Returns Mustache template engine instance.
	 * If not yet, it will instantiate engine class.
	 *
	 * @return Mustache_Engine
	 */
	protected function getEngine() {

		if( ! $this->isInitialized ){

			//  Mustache autoloader

			$this::s()->requireFile( 'lib/ShellPress/lib/Mustache/Autoloader.php', 'Mustache_Autoloader' );
			Mustache_Autoloader::register();

			//  Construct Mustache instance

			$this->engine           = new Mustache_Engine();
			$this->isInitialized    = true;

		}

		return $this->engine;

	}

	/**
	 * @param string $template
	 * @param mixed $data
	 */
	public function render( $template, $data ) {

		return $this->getEngine()->render( $template, $data );

	}

}