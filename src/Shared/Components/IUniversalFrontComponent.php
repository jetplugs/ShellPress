<?php
namespace shellpress\v1_3_72\src\Shared\Components;

/**
 * @author jakubkuranda@gmail.com
 * Date: 27.02.2019
 * Time: 13:50
 */

use shellpress\v1_3_72\src\Shared\Front\Models\HtmlElement;
use shellpress\v1_3_72\src\Shared\RestModels\UniversalFrontResponse;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

abstract class IUniversalFrontComponent extends IComponent {

	/**
     * Array of form id's to create in future.
     *
	 * @var string[]
	 */
    private $_formIdsToCreate = array();

	/**
	 * Called on creation of component.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		//  ----------------------------------------
		//  Actions
		//  ----------------------------------------

		add_action( 'init',                             array( $this, '_a_registerShortcode' ) );
		add_action( 'rest_api_init',                    array( $this, '_a_initializeRestRoutes' ) );
		add_action( 'wp_enqueue_scripts',               array( $this, '_a_enqueueAssets' ) );
		add_action( 'admin_enqueue_scripts',            array( $this, '_a_enqueueAssets' ) );
		add_action( 'wp_footer',                        array( $this, '_a_createForms' ) );
		add_action( 'admin_footer',                     array( $this, '_a_createForms' ) );

	}

	/**
     * Returns name of shortcode.
     *
	 * @return string
	 */
	public abstract function getShortCodeName();

	/**
     * Returns array of action names to refresh this shortcode on.
     *
	 * @return string[]
	 */
	public abstract function getActionsToRefreshOn();

	/**
	 * Returns array of action names to submit this shortcode on.
	 *
	 * @return string[]
	 */
	public abstract function getActionsToSubmitOn();

	/**
     * Called when front end form is sent to rest API.
     * Returns UniversalFrontResponse object.
     *
     * @param UniversalFrontResponse $universalFrontResponse
	 * @param WP_REST_Request $request
	 *
	 * @return UniversalFrontResponse
	 */
	protected abstract function processUniversalFrontResponse( $universalFrontResponse, $request );

	/**
	 * Returns inner component's HTML based on request.
	 * Hints:
	 * - this method is designed to be used by developers by packing it inside UniversalFrontResponse
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return string
	 */
	public abstract function getInnerHtml( $request );

	/**
	 * Returns only endpoint part of rest route.
	 *
	 * @return string
	 */
	protected function _getRestRouteEndpoint() {

	    return 'universalfrontcomponent/' . sanitize_key( $this->getShortCodeName() );

    }

	/**
     * Returns only namespace part of rest route.
     *
	 * @return string
	 */
    protected function _getRestRouteNamespace() {

	    return 'shellpress/v1';

    }

	/**
     * Returns full URL to rest route.
     *
	 * @return string
	 */
    public function getRestRouteUrl() {

        return get_rest_url( null, sprintf( '%1$s/%2$s', $this->_getRestRouteNamespace(), $this->_getRestRouteEndpoint() ) );

    }

	//  ================================================================================
	//  ACTIONS
	//  ================================================================================

	/**
	 * Called on init.
	 *
	 * @internal
	 *
	 * @return void
	 */
	public function _a_registerShortcode() {

		add_shortcode( $this->getShortCodeName(), array( $this, 'getDisplay' ) );

	}

	/**
	 * Prints out purchase button html.
	 * Called on shortcode call.
	 *
     * @param array $attrs
     * @param string|null $content
     *
	 * @return string
	 */
	public function getDisplay( $attrs = array(), $content = null ) {

		$thisElementId  = $this::s()->getPrefix( uniqid() );
		$thisFormId     = $this::s()->getPrefix( uniqid() );

		$this->_formIdsToCreate[] = $thisFormId;  //  Add form ID for further creation.

		//  ----------------------------------------
		//  Prepare fake request for passing
        //  form data on first shortcode display.
		//  ----------------------------------------

	    $shortcodeData = array(
            'attrs-json'        =>  json_encode( $attrs ),
            'content'           =>  $content,
            'form-id'           =>  $thisFormId,
            'component-id'      =>  $thisElementId,
            'action'            =>  "load"
        );

	    $fakeRequest = new WP_REST_Request();
	    $fakeRequest->set_param( 'sp-universalfront', $shortcodeData );

	    $thisElementJsArgs  = array(
		    'refreshOnActions'  =>  (array) $this->getActionsToRefreshOn(),
            'submitOnActions'   =>  (array) $this->getActionsToSubmitOn()
        );

	    $thisElementClasses = array(
            'sp-universalfront',
            'shortcode-' . $this->getShortCodeName(),
            'is-locked',
            'is-not-initialized'
        );

		//  ----------------------------------------
		//  Prepare display
		//  ----------------------------------------

		ob_start();
		?>

		<div
            class="<?= esc_attr( implode( ' ', $thisElementClasses ) ) ?>"
            id="<?= esc_attr( $thisElementId ) ?>"
            data-form-id="<?= esc_attr( $thisFormId ) ?>"
        >

			<div class="sp-universalfront-loader">
				<div class="sp-universalfront-loader-canvas">
                    <div class="sp-universalfront-loader-spinner"></div>
                </div>
			</div>

			<fieldset form="<?= esc_attr( $thisFormId ) ?>" class="sp-universalfront-fieldset" style="visibility: hidden;" disabled="disabled">

                <input type="hidden" name="sp-universalfront[attrs-json]"   value="<?= esc_attr( $shortcodeData['attrs-json'] ); ?>">
                <input type="hidden" name="sp-universalfront[content]"      value="<?= esc_attr( $shortcodeData['content'] ); ?>">
                <input type="hidden" name="sp-universalfront[form-id]"      value="<?= esc_attr( $shortcodeData['form-id'] ); ?>">
                <input type="hidden" name="sp-universalfront[component-id]" value="<?= esc_attr( $shortcodeData['component-id'] ); ?>">
                <input type="hidden" name="sp-universalfront[action]"       value="<?= esc_attr( $shortcodeData['action'] ) ?>">

                <input type="submit" name="submit" value="submit" style="width:0; height:0; position:absolute; visibility:hidden">

                <div class="sp-universalfront-dynamic-area">

	                <?php echo $this->getInnerHtml( $fakeRequest ); ?>

                </div>

			</fieldset>

            <script>
                if( window.jQuery ) if( window.jQuery ) window.jQuery( document ).ready( function( $ ) {

                    if( $.fn.spUniversalFront ) $( '#<?= $thisElementId ?>' ).spUniversalFront( <?= json_encode( $thisElementJsArgs ) ?> );

                } );
            </script>

		</div>

		<?php
		return ob_get_clean();

	}

	/**
	 * Called on rest_api_init.
	 *
	 * @return void
	 */
	public function _a_initializeRestRoutes() {

		register_rest_route( $this->_getRestRouteNamespace(), $this->_getRestRouteEndpoint(), array(
			'methods'       =>  'POST',
			'callback'      =>  array( $this, '_a_restCallback' )
		) );

	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function _a_restCallback( $request ) {

	    $universalFrontResponse = UniversalFrontResponse::create();
		$universalFrontResponse = $this->processUniversalFrontResponse( $universalFrontResponse, $request );

		return $universalFrontResponse->getPackedResponse();

	}

	/**
	 * Called on wp_enqueue_scripts and admin_enqueue_scripts.
     *
     * @return void
	 */
	public function _a_enqueueAssets() {

	    wp_enqueue_script( 'spUniversalFront', $this::s()->getShellUrl( 'assets/js/universalFront.js' ), array( 'jquery' ), $this::s()->getFullPluginVersion(), true );
	    wp_enqueue_style( 'spUniversalFront', $this::s()->getShellUrl( 'assets/css/UniversalFront/SPUniversalFront.css' ), array(), $this::s()->getFullPluginVersion() );

    }

	/**
     * Called on wp_footer and admin_footer.
     * Creates <form> tags to hook up with universal front components.
     *
	 * @return void
	 */
    public function _a_createForms() {

        foreach( $this->_formIdsToCreate as $formId ){

            $formEl = HtmlElement::create( 'form' );
            $formEl->setAttributes( array(
                'method'    =>  'POST',
                'action'    =>  esc_attr( $this->getRestRouteUrl() ),
                'id'        =>  esc_attr( $formId )
            ) );

            echo $formEl->getDisplay();

        }

    }

}