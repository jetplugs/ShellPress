<?php
namespace shellpress\v1_1_9\src\Shared\Components;

/**
 * @author jakubkuranda@gmail.com
 * Date: 14.03.2018
 * Time: 11:22
 */

use shellpress\v1_1_9\ShellPress;
use shellpress\v1_1_9\src\Shell;

abstract class LicenseManagerSLM {

    const API_URL       = 'https://themastercut.co';
    const API_SECRET    = '58ba00b52427f3.50566835';

    /** @var Shell */
    protected $shell;

    /**
     * LicenseManagerSLM constructor.
     * Pass your ShellPress instance as reference.
     *
     * @param ShellPress $shellPress
     */
    public function __construct( &$shellPress ) {

        $this->shell = call_user_func( array( $shellPress, 'shell' ) );

        $this->onSetUp();   //  Everything is ready.

    }

    /**
     * Checks SAVED license status.
     * Caution! I does not check status remotely.
     *
     * @return bool
     */
    public function isActive() {

        $key            = $this->getKey();
        $isKeyCorrect   = $this->shell->options->get( 'license/isKeyCorrect', '0' );

        if( ! empty( $key ) && $isKeyCorrect === '1' ){
            return true;
        } else {
            return false;
        }

    }

    /**
     * Sets if key is correct or not.
     *
     * @param bool $isKeyActive
     *
     * @return void
     */
    public function setActive( $isKeyActive ) {

        $value = $isKeyActive ? '1' : '0';

        $this->shell->options->set( 'license/isKeyCorrect', $value );

    }

    /**
     * Sets license key.
     *
     * @param string $key
     *
     * @return void
     */
    public function setKey( $key ) {

        $this->shell->options->set( 'license/key', $key );

    }

    /**
     * Returns license key.
     *
     * @return string|null
     */
    public function getKey() {

        return $this->shell->options->get( 'license/key' );

    }

    /**
     * Returns UTC last check for license status in mysql datetime format.
     *
     * @return string|null
     */
    public function getLastCheckDatetime() {

        return $this->shell->options->get( 'license/lastCheckDatetime' );

    }

    /**
     * Sets UTC last check for license status in mysql datetime format.
     *
     * @param string|null $datetime
     *
     * @return void
     */
    public function setLastCheckDatetime( $datetime ) {

        $this->shell->options->set( 'license/lastCheckDatetime', $datetime );

    }

    /**
     * Returns UTC key expiration in mysql datetime format.
     *
     * @return string|null
     */
    public function getKeyExpiryDatetime() {

        return $this->shell->options->get( 'license/keyExpiryDatetime' );

    }

    /**
     * Sets key status to show in admin area.
     * It should describe, what is going on with key.
     * Example: "Key is not available for this domain".
     *
     * @param string|null $status
     *
     * @return void
     */
    public function setKeyStatus( $status ) {

        $this->shell->options->set( 'license/keyStatus', $status );

    }

    /**
     * Returns key status to show in admin area.
     * It describes, what is going on with key.
     *
     * @return string|null
     */
    public function getKeyStatus() {

        return $this->shell->options->get( 'license/keyStatus' );

    }

    /**
     * Sets UTC key expiration in mysql datetime format.
     *
     * @param string|null $datetime
     *
     * @return void
     */
    public function setKeyExpiryDatetime( $datetime ) {

        $this->shell->options->set( 'license/keyExpiryDatetime', $datetime );

    }

    /**
     * It will check remotely if key is ok and automatically sets all global options.
     * If key is not connected with current domain, it will try to activate it.
     *
     * @uses $this->performRemoteKeyActivation()
     *
     *
     * @return void
     */
    public function performRemoteKeyUpdate() {

        $apiParams = array(
            'slm_action'    =>  'slm_check',
            'secret_key'    =>  $this::API_SECRET,
            'license_key'   =>  $this->getKey()
        );

        $response       = wp_remote_get( add_query_arg( $apiParams, $this::API_URL ), array( 'timeout' => 10, 'sslverify' => false ) );
        $responseStatus = wp_remote_retrieve_response_code( $response );
        $responseBody   = wp_remote_retrieve_body( $response );

        $this->setLastCheckDatetime( current_time( 'mysql', true ) );

        if( is_wp_error( $response ) ){

            $this->setKeyExpiryDatetime( null );
            $this->setKeyStatus( __( 'Could not retrieve data from server.', 'tmc_fbleads' ) );

        } elseif( $responseStatus != '200' ) {

            $this->setKeyExpiryDatetime( null );
            $this->setKeyStatus( __( 'Got unknown info. Please update your key.', 'tmc_fbleads' ) );

        } else {

            $responseData = json_decode( $responseBody, true );

            if( array_key_exists( 'result', $responseData ) && $responseData['result'] === 'success' ){

                //  ----------------------------------------
                //  Prepare result
                //  ----------------------------------------

                $keyStatus          = array_key_exists( 'status', $responseData )               ? (string) $responseData['status'] : null;
                $registeredDomains  = array_key_exists( 'registered_domains', $responseData )   ? (array) $responseData['registered_domains'] : array();
                $dateExpiry         = array_key_exists( 'date_expiry', $responseData )          ? (string) $responseData['date_expiry'] : null;

                //  ----------------------------------------
                //  Process result
                //  ----------------------------------------

                if( $keyStatus === 'active' || $keyStatus === 'pending' ){

                    //  Check if current domain has been connected with key before.

                    if( $this->isCurrentDomainConnected( $registeredDomains ) ){

                        $this->setKeyExpiryDatetime( $dateExpiry );
                        $this->setKeyStatus( __( 'Current domain is connected with key.', 'tmc_fbleads' ) );
                        $this->setActive( true );

                        if( ! $this->isActive() ){
                            $this->onKeyActivationCallback();
                        }

                    } else {

                        $this->setKeyExpiryDatetime( $dateExpiry );

                        $this->performRemoteKeyActivation();  //  It will set status and active state

                    }

                } elseif( $keyStatus === 'expired' ){

                    $this->setKeyExpiryDatetime( $dateExpiry );
                    $this->setKeyStatus( __( 'Key has expired.', 'tmc_fbleads' ) );
                    $this->setActive( false );

                    if( $this->isActive() ){
                        $this->onKeyDeactivationCallback();
                    }

                } elseif( $keyStatus === 'blocked' ){

                    $this->setKeyExpiryDatetime( $dateExpiry );
                    $this->setKeyStatus( __( 'Key has been blocked.', 'tmc_fbleads' ) );
                    $this->setActive( false );

                    if( $this->isActive() ){
                        $this->onKeyDeactivationCallback();
                    }

                } else {

                    //  Active state is not set here, because we don't know what is happening.

                    $this->setKeyExpiryDatetime( $dateExpiry );
                    $this->setKeyStatus( __( 'Got unknown info. Please update your key.', 'tmc_fbleads' ) );

                }

            } else {

                $errorMessage = array_key_exists( 'message', $responseData ) ? $responseData['message'] : __( 'Got wrong response from server', 'tmc_fbleads' );

                $this->setKeyExpiryDatetime( null );
                $this->setKeyStatus( $errorMessage );
                $this->setActive( false );

                if( $this->isActive() ){
                    $this->onKeyDeactivationCallback();
                }

            }

        }

    }

    /**
     * It will activate given key for current domain.
     * It sets key status and active state.
     *
     * @return void
     */
    public function performRemoteKeyActivation() {

        $apiParams = array(
            'slm_action' 		    =>  'slm_activate',
            'secret_key' 		    =>  $this::API_SECRET,
            'license_key' 		    =>  $this->getKey(),
            'registered_domain'     =>  $_SERVER['HTTP_HOST']
        );

        $response       = wp_remote_get( add_query_arg( $apiParams, $this::API_URL ), array( 'timeout' => 10, 'sslverify' => false ) );
        $responseStatus = wp_remote_retrieve_response_code( $response );
        $responseBody   = wp_remote_retrieve_body( $response );

        if( is_wp_error( $response ) ){

            $this->setKeyStatus( __( 'Could not activate your key. Try in a few minutes.', 'tmc_fbleads' ) );
            $this->setActive( false );

            if( $this->isActive() ){
                $this->onKeyDeactivationCallback();
            }

        } elseif( $responseStatus != '200' ) {

            $this->setKeyStatus( __( 'Got unknown info. Please update your key in a few minutes.', 'tmc_fbleads' ) );
            $this->setActive( false );

            if( $this->isActive() ){
                $this->onKeyDeactivationCallback();
            }

        } else {

            $responseData = json_decode( $responseBody, true );

            if( array_key_exists( 'result', $responseData ) && $responseData['result'] === 'success' ){

                $this->setKeyStatus( __( 'Key has been activated for this domain.', 'tmc_fbleads' ) );
                $this->setActive( true );

                if( $this->isActive() ){
                    $this->onKeyActivationCallback();
                }

            } else {

                $errorMessage = array_key_exists( 'message', $responseData ) ? $responseData['message'] : __( 'Could not activate your key.', 'tmc_fbleads' );

                $this->setKeyStatus( $errorMessage );
                $this->setActive( false );

                if( $this->isActive() ){
                    $this->onKeyDeactivationCallback();
                }

            }

        }

    }

    /**
     * Checks if current domain exists in given remote response.
     *
     * @param array $registeredDomainsResponse - array of registered domains data.
     *
     * @return bool
     */
    protected function isCurrentDomainConnected( $registeredDomainsResponse ) {

        foreach( $registeredDomainsResponse as $domainData ) {

            if( array_key_exists( 'registered_domain', $domainData ) && $domainData['registered_domain'] === $_SERVER['HTTP_HOST'] ){
                return true;
            }

        }

        //  Nothing found

        return false;

    }

    /**
     * Called on object creation.
     *
     * @return void
     */
    protected abstract function onSetUp();

    /**
     * Called when key has been activated.
     *
     * @return void
     */
    protected abstract function onKeyActivationCallback();

    /**
     * Called when key has been deactivated.
     *
     * @return void
     */
    protected abstract function onKeyDeactivationCallback();

}