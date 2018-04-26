<?php
namespace shellpress\v1_2_1\src\Handlers\External;

/**
 * Date: 26.04.2018
 * Time: 20:13
 */

use shellpress\v1_2_1\src\Handlers\IHandler;
use shellpress\v1_2_1\src\Handlers\Models\CustomizerSection;
use WP_Customize_Manager;

class CustomizerHandler extends IHandler {

	/** @var CustomizerSection[] */
	public $sections = array();

	/**
	 * Called on handler construction.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		add_action( 'customize_register',           array( $this, '_a_customizerCallback' ) );

	}

	/**
	 * @param string $slug
	 * @param string $title
	 *
	 * @return CustomizerSection
	 */
	public function addSection( $slug, $title = '' ) {

		return $this->sections[$slug] = new CustomizerSection( $slug, $title );

	}

	//  ================================================================================
	//  ACTIONS
	//  ================================================================================

	/**
	 * @param WP_Customize_Manager $wpCustomize
	 */
	public function _a_customizerCallback( $wpCustomize ) {



	}

}