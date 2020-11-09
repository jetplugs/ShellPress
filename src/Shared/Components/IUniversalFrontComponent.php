<?php
namespace shellpress\v1_3_87\src\Shared\Components;

/**
 * @author jakubkuranda@gmail.com
 * Date: 27.02.2019
 * Time: 13:50
 */

use shellpress\v1_3_87\src\Shared\Front\Models\HtmlElement;
use shellpress\v1_3_87\src\Shared\RestModels\UniversalFrontResponse;
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

		$this->onSetUpComponent();

		//  ----------------------------------------
		//  Actions
		//  ----------------------------------------

		add_action( 'init',                             array( $this, '_a_registerShortcode' ) );
		add_action( 'wp_enqueue_scripts',               array( $this, '_a_enqueueScripts' ) );
		add_action( 'admin_enqueue_scripts',            array( $this, '_a_enqueueScripts' ) );
		add_filter( 'style_loader_tag',                 array( $this, '_f_styleLoaderTag' ), 10, 4 );
		add_action( 'rest_api_init',                    array( $this, '_a_initializeRestRoutes' ) );
		add_action( 'wp_footer',                        array( $this, '_a_createForms' ) );
		add_action( 'admin_footer',                     array( $this, '_a_createForms' ) );

	}

	/**
	 * Called on basic set up, just before everything else.
	 *
	 * @return void
	 */
	public abstract function onSetUpComponent();

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
		$jsPluginName   = 'spUniversalFront_' . $this::s()->getShellVersion( true );
		$jsPluginUrl    = $this::s()->getShellUrl( 'assets/js/universalFront.js?ver=' . $this::s()->getShellVersion() );
		$cssUrl         = $this::s()->getShellUrl( 'assets/css/UniversalFront/SPUniversalFront.css?ver=' . $this::s()->getShellVersion() );

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
			'action'            =>  "load",
			'eventName'         =>  ''
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

                    <div class="sp-universalfront-loader-progress-bar">
                        <div class="sp-universalfront-loader-progress-bar-strip"></div>
                    </div>

                </div>
            </div>

            <fieldset form="<?= esc_attr( $thisFormId ) ?>" class="sp-universalfront-fieldset" style="visibility: hidden;" disabled="disabled">

                <input type="hidden" name="sp-universalfront[attrs-json]"   value="<?= esc_attr( $shortcodeData['attrs-json'] ); ?>">
                <input type="hidden" name="sp-universalfront[content]"      value="<?= esc_attr( $shortcodeData['content'] ); ?>">
                <input type="hidden" name="sp-universalfront[form-id]"      value="<?= esc_attr( $shortcodeData['form-id'] ); ?>">
                <input type="hidden" name="sp-universalfront[component-id]" value="<?= esc_attr( $shortcodeData['component-id'] ); ?>">
                <input type="hidden" name="sp-universalfront[action]"       value="<?= esc_attr( $shortcodeData['action'] ) ?>">
                <input type="hidden" name="sp-universalfront[eventName]"    value="<?= esc_attr( $shortcodeData['eventName'] ) ?>">

                <input type="submit" name="submit" value="submit" style="width:0; height:0; position:absolute; visibility:hidden">

                <div class="sp-universalfront-dynamic-area">

					<?php echo $this->getInnerHtml( $fakeRequest ); ?>

                </div>

            </fieldset>

            <script>
                (function( $ ){
                    if( typeof $ === "undefined" ) return;

                    var runOnReady = function(){

                        let x = function($){
                            if( $.fn.<?= $jsPluginName ?> ){
                                $( '#<?= $thisElementId ?>' ).<?= $jsPluginName ?>( <?= json_encode( $thisElementJsArgs ) ?> );
                            }
                        };

                        switch (document.readyState) {
                            case "interactive":
                            case "complete":
                                x( $ );
                                break;
                            default:
                                $( document ).ready( x );
                                break;
                        }

                    };

                    if( $.fn.<?= $jsPluginName ?> ){

                        runOnReady();

                    } else {

                        if( window.isDownloading_<?= $jsPluginName ?> ){

                            $( document.body ).on( "downloaded_<?= $jsPluginName ?>", function(){
                                runOnReady();
                            } );

                        } else {

                            window.isDownloading_<?= $jsPluginName ?> = true;

                            jQuery.ajax( {
                                type:       "GET",
                                url:        "<?= $jsPluginUrl ?>",
                                success:    function(){

                                    $( document.body ).trigger( "downloaded_<?= $jsPluginName ?>" );
                                    runOnReady();

                                },
                                dataType:   "script",
                                cache:      true
                            } );

                        }

                    }

                })( window.jQuery );
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
			'methods'               =>  'POST',
			'callback'              =>  array( $this, '_a_restCallback' ),
            'permission_callback'   =>  '__return_true'
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

	/**
     * Enqueue styles and scripts.
	 *
	 * @return void
	 * Called on wp_enqueue_scripts, admin_enqueue_scripts.
	 */
	public function _a_enqueueScripts() {

	    wp_enqueue_style( 'SPUniversalFront.css', $this::s()->getShellUrl( 'assets/css/UniversalFront/SPUniversalFront.css' ), array(), $this::s()->getShellVersion(), 'all' );

	}

	/**
     * Called on style_loader_tag.
     * Adds lazy load.
     *
	 * @param string $html
	 * @param string $handle
	 * @param string $href
	 * @param string $media
     *
     * @return string
	 */
	public function _f_styleLoaderTag( $html, $handle, $href, $media ) {

	    //  TODO - lazy loading.
        if( $handle === 'SPUniversalFront.css' ){

        }

	    return $html;

	}

}