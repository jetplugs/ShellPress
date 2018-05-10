<?php
namespace shellpress\v1_2_1\src\Shared\Components;
use TMC_v1_0_3_AdminPageFramework;

/**
 * @author jakubkuranda@gmail.com
 * Date: 14.03.2018
 * Time: 11:22
 */

abstract class LicenseManagerSLM extends IComponent {

    const API_URL       = 'https://themastercut.co';
    const API_SECRET    = '58ba00b52427f3.50566835';

    /** @var string */
    private $_apfClassName;

    /** @var string */
    private $_apfSectionId;

    /** @var string */
    private $_apfPageSlug;

    /** @var string */
    private $_apfPageTab;

    /**
     * Checks SAVED license status.
     * Caution! I does not check status remotely.
     *
     * @return bool
     */
    public function isActive() {

        $key            = $this->getKey();
        $isKeyCorrect   = $this->s()->options->get( 'license/isKeyCorrect', '0' );

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

        $this->s()->options->set( 'license/isKeyCorrect', $value );

    }

    /**
     * Sets license key.
     *
     * @param string $key
     *
     * @return void
     */
    public function setKey( $key ) {

        $this->s()->options->set( 'license/key', $key );

    }

    /**
     * Returns license key.
     *
     * @return string|null
     */
    public function getKey() {

        return $this->s()->options->get( 'license/key' );

    }

    /**
     * Returns UTC last check for license status in mysql datetime format.
     *
     * @return string|null
     */
    public function getLastCheckDatetime() {

        return $this->s()->options->get( 'license/lastCheckDatetime' );

    }

    /**
     * Sets UTC last check for license status in mysql datetime format.
     *
     * @param string|null $datetime
     *
     * @return void
     */
    public function setLastCheckDatetime( $datetime ) {

        $this->s()->options->set( 'license/lastCheckDatetime', $datetime );

    }

    /**
     * Returns UTC key expiration in mysql datetime format.
     *
     * @return string|null
     */
    public function getKeyExpiryDatetime() {

        return $this->s()->options->get( 'license/keyExpiryDatetime' );

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

        $this->s()->options->set( 'license/keyStatus', $status );

    }

    /**
     * Returns key status to show in admin area.
     * It describes, what is going on with key.
     *
     * @return string|null
     */
    public function getKeyStatus() {

        return $this->s()->options->get( 'license/keyStatus' );

    }

    /**
     * Sets UTC key expiration in mysql datetime format.
     *
     * @param string|null $datetime
     *
     * @return void
     */
    public function setKeyExpiryDatetime( $datetime ) {

        $this->s()->options->set( 'license/keyExpiryDatetime', $datetime );

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
            $this->setKeyStatus( __( 'Could not retrieve data from server.' ) );

        } elseif( $responseStatus != '200' ) {

            $this->setKeyExpiryDatetime( null );
            $this->setKeyStatus( __( 'Got unknown info. Please update your key.' ) );

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
                        $this->setKeyStatus( __( 'Current domain is connected with key.' ) );
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
                    $this->setKeyStatus( __( 'Key has expired.' ) );
                    $this->setActive( false );

                    if( $this->isActive() ){
                        $this->onKeyDeactivationCallback();
                    }

                } elseif( $keyStatus === 'blocked' ){

                    $this->setKeyExpiryDatetime( $dateExpiry );
                    $this->setKeyStatus( __( 'Key has been blocked.' ) );
                    $this->setActive( false );

                    if( $this->isActive() ){
                        $this->onKeyDeactivationCallback();
                    }

                } else {

                    //  Active state is not set here, because we don't know what is happening.

                    $this->setKeyExpiryDatetime( $dateExpiry );
                    $this->setKeyStatus( __( 'Got unknown info. Please update your key.' ) );

                }

            } else {

                $errorMessage = array_key_exists( 'message', $responseData ) ? $responseData['message'] : __( 'Got wrong response from server' );

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

            $this->setKeyStatus( __( 'Could not activate your key. Try in a few minutes.' ) );
            $this->setActive( false );

            if( $this->isActive() ){
                $this->onKeyDeactivationCallback();
            }

        } elseif( $responseStatus != '200' ) {

            $this->setKeyStatus( __( 'Got unknown info. Please update your key in a few minutes.' ) );
            $this->setActive( false );

            if( $this->isActive() ){
                $this->onKeyDeactivationCallback();
            }

        } else {

            $responseData = json_decode( $responseBody, true );

            if( array_key_exists( 'result', $responseData ) && $responseData['result'] === 'success' ){

                $this->setKeyStatus( __( 'Key has been activated for this domain.' ) );
                $this->setActive( true );

                if( $this->isActive() ){
                    $this->onKeyActivationCallback();
                }

            } else {

                $errorMessage = array_key_exists( 'message', $responseData ) ? $responseData['message'] : __( 'Could not activate your key.' );

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
     * Returns HTML of license status.
     *
     * @return string
     */
    public function getLicenseStatusHtml() {

        $html = '';

        if( $this->getKey() ){

            $keyStatus          = $this->getKeyStatus();
            $keyExpiryDatetime  = $this->getKeyExpiryDatetime();
            $keyIsActive        = $this->isActive();

            if( $keyStatus ){
                if( $keyIsActive ){
                    $html .= sprintf( '<div style="clear: both; color: #16a085;">%1$s</div>', $keyStatus );
                } else {
                    $html .= sprintf( '<div style="clear: both; color: #e74c3c;">%1$s</div>', $keyStatus );
                }
            }

            if( $keyExpiryDatetime && $keyIsActive ){
                $html .= sprintf( '<div style="clear: both; color: #16a085;">%1$s %2$s</div>', __( 'Valid until:', 'tmc_fbleads' ), $keyExpiryDatetime );
            }

        }

        return $html;

    }

    /**
     * Adds ready-to-use form in defined AdminPageFramework.
     *
     * @param string $apfClassName
     * @param string $apfSectionId
     * @param string $apfPageSlug
     * @param string $apfPageTab
     *
     * @return void
     */
    public function addFormToAPF( $apfClassName, $apfSectionId = 'license', $apfPageSlug = '', $apfPageTab = '' ) {

        $this->_apfClassName = $apfClassName;
        $this->_apfSectionId = $apfSectionId;
        $this->_apfPageSlug = $apfPageSlug;
        $this->_apfPageTab = $apfPageTab;

        add_filter( 'sections_' . $apfClassName,        array( $this, '_f_addSectionToAPF' ) );
        add_filter( 'fields_' . $apfClassName,          array( $this, '_f_addFieldsToAPF' ) );

    }

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

    //  ================================================================================
    //  FILTERS
    //  ================================================================================

    /**
     * Called on form validation.
     * Performs key update if there is change in key value.
     *
     * @param array $newOptions
     * @param array $oldOptions
     * @param TMC_v1_0_3_AdminPageFramework $pageFactory
     *
     * @return array
     */
    public function _f_updateKeyButtonCallbackAPF( $newOptions, $oldOptions, $pageFactory ) {

        if(
            $newOptions[$this->_apfSectionId]['key'] != $oldOptions[$this->_apfSectionId]['key']
            || isset( $_POST['updateKeySubmit'] ) && ! empty( $_POST['updateKeySubmit'] )
        ){

            $key = $newOptions[$this->_apfSectionId]['key'];

            $this->setKey( $key );
            $this->performRemoteKeyUpdate();

            $pageFactory->setSettingNotice( __( 'License key has been updated.' ), 'updated' );

            //  ShellPress options are older than this fresh submitted data.
            //  Actions are performed on ShellPress internal storage, so we return it here for update.

            return $this::s()->options->get();

        }

        return $newOptions;

    }

    /**
     * @param array $sections
     *
     * @return array
     */
    public function _f_addSectionToAPF( $sections ) {

        $sections[$this->_apfSectionId] = array(
            'section_id'            =>  $this->_apfSectionId,
            'page_slug'             =>  $this->_apfPageSlug,
            'tab_slug'              =>  $this->_apfPageTab,
            'order'                 =>  20,
            'title'                 =>  __( 'License' )
        );

        return $sections;

    }

    public function _f_addFieldsToAPF( $fields ) {

        $fields[$this->_apfSectionId] = array(
            'key'               =>  array(
                'field_id'          =>  'key',
                'type'              =>  'text',
                'title'             =>  __( 'Key' ),
                'attributes'            =>  array(
                    'class'                 =>  'regular-text',
                ),
                'after_input'       =>  sprintf( ' <input type="submit" class="button" name="updateKeySubmit" value="%1$s">', __( 'Update key' ) ),
                'after_field'       =>  $this->getLicenseStatusHtml()
            ),
            'keyExpiryDatetime' =>  array(
                'field_id'          =>  'keyExpiryDatetime',
                'type'              =>  'text',
                'title'             =>  'keyExpiryDatetime',
                'hidden'            =>  true,
                'attributes'        =>  array(
                    'disabled'          =>  'disabled'
                )
            ),
            'lastCheckDatetime' =>  array(
                'field_id'          =>  'lastCheckDatetime',
                'type'              =>  'text',
                'title'             =>  'lastCheckDatetime',
                'hidden'            =>  true,
                'attributes'        =>  array(
                    'disabled'          =>  'disabled'
                )
            ),
            'keyStatus'         =>  array(
                'field_id'          =>  'keyStatus',
                'type'              =>  'text',
                'title'             =>  'keyStatus',
                'hidden'            =>  true,
                'attributes'        =>  array(
                    'disabled'          =>  'disabled'
                )
            ),
            'isKeyCorrect'      =>  array(
                'field_id'          =>  'isKeyCorrect',
                'type'              =>  'radio',
                'title'             =>  'isKeyCorrect',
                'label'             =>  array(
                    '1'                 =>  'yes',
                    '0'                 =>  'no'
                ),
                'hidden'            =>  true
            )
        );

        return $fields;

    }

}