<?php

namespace shellpress\v1_3_88\demo\src\Components;

use shellpress\v1_3_88\src\Shared\Components\IUniversalFrontComponent;
use shellpress\v1_3_88\src\Shared\RestModels\UniversalFrontResponse;
use WP_REST_Request;

/**
 * @author jakubkuranda@gmail.com
 * Date: 07.08.2020
 * Time: 13:08
 */
class FileUploaderExample extends IUniversalFrontComponent {

	/**
	 * Called on basic set up, just before everything else.
	 *
	 * @return void
	 */
	public function onSetUpComponent() {
		// TODO: Implement onSetUpComponent() method.
	}

	/**
	 * Returns name of shortcode.
	 *
	 * @return string
	 */
	public function getShortCodeName() {
		return sanitize_key( __CLASS__ );
	}

	/**
	 * Returns array of action names to refresh this shortcode on.
	 *
	 * @return string[]
	 */
	public function getActionsToRefreshOn() {
		return array();
	}

	/**
	 * Returns array of action names to submit this shortcode on.
	 *
	 * @return string[]
	 */
	public function getActionsToSubmitOn() {
		return array();
	}

	/**
	 * Called when front end form is sent to rest API.
	 * Returns UniversalFrontResponse object.
	 *
	 * @param UniversalFrontResponse $universalFrontResponse
	 * @param WP_REST_Request        $request
	 *
	 * @return UniversalFrontResponse
	 */
	protected function processUniversalFrontResponse( $universalFrontResponse, $request ) {

		$universalFrontResponse->setReplacementHtml( $this->getInnerHtml( $request ) );

		return $universalFrontResponse;

	}

	/**
	 * Returns inner component's HTML based on request.
	 * Hints:
	 * - this method is designed to be used by developers by packing it inside UniversalFrontResponse
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return string
	 */
	public function getInnerHtml( $request ) {

		ob_start();
		?>
		<input type="file" multiple="multiple" name="files[]">
		<button type="submit" class="button btn btn-primary">Wyślij</button>

		<?php echo $this::s()->utility->getFormattedVarExport( $_FILES ); ?>

		<?php
		return ob_get_clean();

	}
}