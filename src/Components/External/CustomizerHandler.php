<?php
namespace shellpress\v1_2_1\src\Components\External;

/**
 * Date: 26.04.2018
 * Time: 20:13
 */

use shellpress\v1_2_1\src\Components\Models\CustomizerSection;
use shellpress\v1_2_1\src\Shared\Components\IComponent;
use WP_Customize_Color_Control;
use WP_Customize_Manager;

class CustomizerHandler extends IComponent {

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

		foreach( $this->sections as $section ){
			$section->register( $wpCustomize );
		}

		//  TODO - clean this shit up.

		$wpCustomize->add_setting( 'tttest' , array(
			'default'   => '#000000',
			'transport' => 'refresh',
		) );

		$wpCustomize->add_control( new WP_Customize_Color_Control( $wpCustomize, 'tttest', array(
			'label'      => __( 'Header Color', 'mytheme' ),
			'section'    => 'test'
		) ) );

	}

}