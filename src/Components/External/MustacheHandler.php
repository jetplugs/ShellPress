<?php
namespace shellpress\v1_3_7\src\Components\External;

/**
 * Date: 30.05.2018
 * Time: 21:36
 */

use Mustache_Engine;
use shellpress\v1_3_7\src\Shared\Components\IComponent;

class MustacheHandler extends IComponent {

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

		if( ! $this->engine ){

			//  Construct Mustache instance
			$this->engine           = new Mustache_Engine();
			$this->isInitialized    = true;

		}

		return $this->engine;

	}

	/**
	 * Returns file contents if given string is absolute or relative path to template file.
	 *
	 * @param string $string
	 * @param string $fileExtension
	 *
	 * @return string
	 */
	protected function maybeGetTemplateContents( $string, $fileExtension = '.mustache' ) {

		$fileExtensionLength    = strlen( $fileExtension );
		$lastCharacters         = substr( $string, -$fileExtensionLength );

		//  Check, if last characters are like supported file type.

		if( $lastCharacters === $fileExtension ){

			if( file_exists( $string ) ){

				//  File exists. Get it.
				return file_get_contents( $string );

			} else if( file_exists( $this::s()->getPath( $string ) ) ) {

				//  File from relative path exists. Get it.
				return file_get_contents( $this::s()->getPath( $string ) );

			}

		}

		//  This is not a file. Just return given string.

		return $string;

	}

	/**
	 * @param string $template
	 * @param mixed $data
	 */
	public function render( $template, $data ) {

		$template = $this->maybeGetTemplateContents( $template );

		return $this->getEngine()->render( $template, $data );

	}

}