<?php
namespace shellpress\v1_2_1\src\Shared\Components;

/**
 * Date: 13.05.2018
 * Time: 21:02
 */

abstract class CustomUpdater extends IComponent {

    /** @var string */
    protected $serverUrl;

    /** @var array */
    protected $requestArgs;

    /**
     * @param string $serverUrl     - URL to server which we will ask for updates.
     * @param array $requestArgs    - arguments passed when making request for updates.
     */
    public function setUpdateSource( $serverUrl, $requestArgs = array() ) {

	    $this->serverUrl = $serverUrl;
	    $this->requestArgs = $requestArgs;

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, '_f_modifyUpdatePluginsTransient' ) );

	}

	//  ================================================================================
	//  FILTERS
	//  ================================================================================

	/**
	 * @param object $transient
	 *
     * @internal
     *
	 * @return object
	 */
	public function _f_modifyUpdatePluginsTransient( $transient ) {

        if( ! isset( $transient->response ) ) return $transient;    //  Check, if we have an array set. Sometimes there are errors.

        //  ----------------------------------------
        //  Prepare data for request
        //  ----------------------------------------

		$basename = plugin_basename( $this::s()->getMainPluginFile() );

		$requestArgs    = array(
		    'plugin_basename'   =>  $basename
        );
		$requestArgs    = wp_parse_args( (array) $this->requestArgs, $requestArgs );
		$requestUrl     = add_query_arg( $requestArgs, $this->serverUrl );

		//  ----------------------------------------
		//  Make request
		//  ----------------------------------------

		$response       = wp_remote_request( $requestUrl, array() );
		$responseBody   = wp_remote_retrieve_body( $response );
		$responseCode   = wp_remote_retrieve_response_code( $response );

		if( ! is_wp_error( $response ) && $responseCode === 200 ){

            $encoded = json_decode( $responseBody );

            if( $encoded && isset( $encoded->new_version ) && version_compare( $encoded->new_version, $this::s()->getPluginVersion(), '>' ) ){

                $transient->response[ $basename ] = (object) $encoded;

            } else {

                unset( $transient->response[ $basename ]);
                return $transient;

            }

        }

        return $transient;

	}

}