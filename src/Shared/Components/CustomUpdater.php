<?php
namespace shellpress\v1_2_2\src\Shared\Components;

/**
 * Date: 13.05.2018
 * Time: 21:02
 */

abstract class CustomUpdater extends IComponent {

    /** @var string */
    protected $serverUrl;

    /** @var array */
    protected $requestBodyArgs;

    /**
     * Registers update_plugins transient filter.
     *
     * @param string $serverUrl       - URL to server which we will ask for updates.
     * @param array  $requestBodyArgs - POST arguments passed when making request for updates.
     *
     * @return void
     */
    public function setUpdateSource( $serverUrl, $requestBodyArgs = array() ) {

	    $this->serverUrl       = $serverUrl;
	    $this->requestBodyArgs = $requestBodyArgs;

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, '_f_addUpdateInfoToPluginsTransient' ) );

	}

    /**
     * Hides package information from update_plugins transient.
     *
     * @param string $info
     *
     * @return void
     */
    public function disableUpdatePackage() {

        add_filter( 'site_transient_update_plugins', array( $this, '_f_removeUpdatePackageForThisPlugin' ) );

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
	public function _f_addUpdateInfoToPluginsTransient( $transient ) {

        if( ! isset( $transient->response ) ) return $transient;    //  Check, if we have an array set. Sometimes there are errors.

        //  ----------------------------------------
        //  Prepare data for request
        //  ----------------------------------------

		$basename = $this::s()->getPluginBasename();

		$requestBodyArgs    = array(
		    'plugin_basename'   =>  $basename
        );
		$requestBodyArgs    = wp_parse_args( (array) $this->requestBodyArgs, $requestBodyArgs );

		//  ----------------------------------------
		//  Make request
		//  ----------------------------------------

		$response       = wp_remote_post( $this->serverUrl, array(
		    'body'      =>  $requestBodyArgs,
            'timeout'   =>  10,
        ) );
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

    /**
     * @param (object) $transient
     *
     * @return (object)
     */
	public function _f_removeUpdatePackageForThisPlugin( $transient ) {

        if( ! isset( $transient->response ) ) return $transient;

        $basename = $this::s()->getPluginBasename();

        if( isset( $transient->response[ $basename ] ) ){
            $response =& $transient->response[ $basename ];
            $response->package = '';
        }

        return $transient;

    }

}