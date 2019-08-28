<?php
namespace shellpress\v1_3_76\src\Shared\Components;

/**
 * @author jakubkuranda@gmail.com
 * Date: 14.03.2018
 * Time: 11:22
 */

use WP_Upgrader;

/**
 * This little fucker is a ready-to-use component, you can extend
 * and register some predefined helpers.
 *
 * Checkout registerAPFForm(), registerAutomaticChecker() and registerNotices().
 * It's all designed to work with remote SLM host and AdminPageFramework.
 *
 * This class will be deprecated in the future.
 */
abstract class IComponentLicenseManagerSLM extends IComponent {

    const API_URL       = 'https://themastercut.co';
    const API_SECRET    = '58ba00b52427f3.50566835';

    /** @var string */
    private $_apfSectionId = 'license';

    /** @var string */
    private $_apfPageSlug;

    /** @var string */
    private $_apfPageTab;

    /** @var string */
    private $_licenseCheckCronJobName;

    /**
     * Checks SAVED license status.
     * Caution! I does not check status remotely.
     *
     * @return bool
     */
    public function isActive() {

        $key            = $this->getKey();
        $isKeyCorrect   = $this->s()->options->get( $this->_apfSectionId . '/isKeyCorrect', '0' );

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

        $this->s()->options->set( $this->_apfSectionId . '/isKeyCorrect', $value );

    }

    /**
     * Sets license key.
     *
     * @param string $key
     *
     * @return void
     */
    public function setKey( $key ) {

        $this->s()->options->set( $this->_apfSectionId . '/key', $key );

    }

    /**
     * Returns license key.
     *
     * @return string|null
     */
    public function getKey() {

        return $this->s()->options->get( $this->_apfSectionId . '/key' );

    }

    /**
     * Returns UTC last check for license status in mysql datetime format.
     *
     * @return string|null
     */
    public function getLastCheckDatetime() {

        return $this->s()->options->get( $this->_apfSectionId . '/lastCheckDatetime' );

    }

    /**
     * Sets UTC last check for license status in mysql datetime format.
     *
     * @param string|null $datetime
     *
     * @return void
     */
    public function setLastCheckDatetime( $datetime ) {

        $this->s()->options->set( $this->_apfSectionId . '/lastCheckDatetime', $datetime );

    }

    /**
     * Returns UTC key expiration in mysql datetime format.
     *
     * @return string|null
     */
    public function getKeyExpiryDatetime() {

        return $this->s()->options->get( $this->_apfSectionId . '/keyExpiryDatetime' );

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

        $this->s()->options->set( $this->_apfSectionId . '/keyStatus', $status );

    }

    /**
     * Returns key status to show in admin area.
     * It describes, what is going on with key.
     *
     * @return string|null
     */
    public function getKeyStatus() {

        return $this->s()->options->get( $this->_apfSectionId . '/keyStatus' );

    }

    /**
     * Sets UTC key expiration in mysql datetime format.
     *
     * @param string|null $datetime
     *
     * @return void
     */
    public function setKeyExpiryDatetime( $datetime ) {

        $this->s()->options->set( $this->_apfSectionId . '/keyExpiryDatetime', $datetime );

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
     * Adds ready-to-use form in defined AdminPageFramework instance.
     *
     * @param string $apfClassName
     * @param string $pageSlug
     * @param string $pageTab
     * @param string $sectionId
     *
     * @return void
     */
    public function registerAPFForm( $apfClassName, $pageSlug = '', $pageTab = '', $sectionId = 'license' ) {

        $this->_apfSectionId    = $sectionId;
        $this->_apfPageSlug     = $pageSlug;
        $this->_apfPageTab      = $pageTab;

        //  Prepare hook name.

        if( $pageSlug && $pageTab ){

        	$loadHookName = 'load_' . $pageSlug . '_' . $pageTab;

        } else if( $pageSlug ){

        	$loadHookName = 'load_' . $pageSlug;

        } else {

        	$loadHookName = 'load_' . $apfClassName;

        }

        //  Add filters.

        add_filter( $loadHookName,                  array( $this, '_a_addSectionToAPF' ) );
        add_filter( $loadHookName,                  array( $this, '_a_addFieldsToAPF' ) );

        add_filter( 'validation_' . $apfClassName,  array( $this, '_f_updateKeyButtonCallbackAPF' ), 10, 3 );

    }

	/**
	 * Adds new cron job for checking license.
	 * Supports plugin activation, deactivation and upgrade.
	 *
	 * @param string $cronJobName
	 * @param string $cronScheduleName
	 *
	 * @return void
	 */
    public function registerAutomaticChecker( $cronJobName = '', $cronScheduleName = 'daily' ) {

    	//  Prepare cron job name.

	    $this->_licenseCheckCronJobName = $cronJobName ? $cronJobName : $this::s()->getPrefix( '_slm_license_check' );

	    //  Add cron job callback.

	    add_action( $this->_licenseCheckCronJobName,            array( $this, '_a_doCheckLicenseCronCallback' ) );

	    //  Register cron job on plugin activation, deactivation and upgrade.

	    $this::s()->event->addOnActivate(                       array( $this, '_a_registerCronJob' ) );
	    $this::s()->event->addOnDeactivate(                     array( $this, '_a_unregisterCronJob' ) );

	    add_action( 'upgrader_process_complete',                array( $this, '_a_handlePluginUpdateCronJobs' ), 10, 2 );

    }

	/**
	 * Just adds some notices if key has been set by user and
	 * there is something wrong with license.
	 *
	 * @return void
	 */
    public function registerNotices() {

	    add_action( 'admin_notices',                            array( $this, '_a_displayLicenseNotification' ) );

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
     * @param mixed $pageFactory
     *
     * @return array
     */
    public function _f_updateKeyButtonCallbackAPF( $newOptions, $oldOptions, $pageFactory ) {

    	$newKey = isset( $newOptions[$this->_apfSectionId]['key'] ) ? $newOptions[$this->_apfSectionId]['key'] : '';
    	$oldKey = isset( $oldOptions[$this->_apfSectionId]['key'] ) ? $newOptions[$this->_apfSectionId]['key'] : '';

	    if(
		    $newKey != $oldKey
		    || isset( $_POST['updateKeySubmit'] ) && ! empty( $_POST['updateKeySubmit'] )
	    ){

		    $this->setKey( $newKey );
		    $this->performRemoteKeyUpdate();

		    $pageFactory->setSettingNotice( __( 'License key has been updated.' ), 'updated' );

		    //  ShellPress options are older than this fresh submitted data.
		    //  Actions are performed on ShellPress internal storage, so we return it here for update.

		    return $this::s()->options->get();

	    }

        return $newOptions;

    }

	//  ================================================================================
	//  Actions
	//  ================================================================================

	/**
	 * Adds setting section on load of apf page and tab.
	 *
	 * @param mixed $pageFactory
	 */
	public function _a_addSectionToAPF( $pageFactory ) {

		$pageFactory->addSettingSection(
			array(
				'section_id'            =>  $this->_apfSectionId,
				'page_slug'             =>  $this->_apfPageSlug,
				'tab_slug'              =>  $this->_apfPageTab,
				'order'                 =>  20,
				'title'                 =>  __( 'License' )
			)
		);

	}

	/**
	 * Adds setting fields on load of apf page and tab.
	 *
	 * @param mixed $pageFactory
	 */
	public function _a_addFieldsToAPF( $pageFactory ) {

		$pageFactory->addSettingFields(
			array(
				'field_id'          =>  'key',
				'section_id'        =>  $this->_apfSectionId,
				'type'              =>  'password',
				'title'             =>  __( 'Key' ),
				'attributes'            =>  array(
					'class'                 =>  'regular-text',
				),
				'after_input'       =>  sprintf( ' <input type="submit" class="button" name="updateKeySubmit" value="%1$s">', __( 'Update key' ) ),
				'after_field'       =>  $this->getLicenseStatusHtml()
			),
			array(
				'field_id'          =>  'keyExpiryDatetime',
				'section_id'        =>  $this->_apfSectionId,
				'type'              =>  'text',
				'title'             =>  'keyExpiryDatetime',
				'hidden'            =>  true,
				'attributes'        =>  array(
					'disabled'          =>  'disabled'
				)
			),
			array(
				'field_id'          =>  'lastCheckDatetime',
				'section_id'        =>  $this->_apfSectionId,
				'type'              =>  'text',
				'title'             =>  'lastCheckDatetime',
				'hidden'            =>  true,
				'attributes'        =>  array(
					'disabled'          =>  'disabled'
				)
			),
			array(
				'field_id'          =>  'keyStatus',
				'section_id'        =>  $this->_apfSectionId,
				'type'              =>  'text',
				'title'             =>  'keyStatus',
				'hidden'            =>  true,
				'attributes'        =>  array(
					'disabled'          =>  'disabled'
				)
			),
			array(
				'field_id'          =>  'isKeyCorrect',
				'section_id'        =>  $this->_apfSectionId,
				'type'              =>  'radio',
				'title'             =>  'isKeyCorrect',
				'label'             =>  array(
					'1'                 =>  'yes',
					'0'                 =>  'no'
				),
				'hidden'            =>  true
			)
		);

	}

	/**
	 * Display error notice if passed license is inactive.
	 * Called on admin_notices hook.
	 *
	 * @return void
	 */
	public function _a_displayLicenseNotification() {

		if( $this->getKey() && ! $this->isActive() ){    //  If key is set and is inactive.

			$adminPageUrl = admin_url( 'options-general.php' );
			$adminPageUrl = add_query_arg(
				array(
					'page'      =>  $this->_apfPageSlug,
					'tab'       =>  $this->_apfPageTab
				),
				$adminPageUrl
			);

			$pluginData = get_plugin_data( $this::s()->getMainPluginFile() );
			$pluginName = isset( $pluginData['Name'] ) ? $pluginData['Name'] : __( 'plugin', 'tmc_sp' );

			printf( '<div class="notice notice-error"><p>%1$s %2$s</p></div>',
				sprintf( __( 'Your license for %1$s is inactive.', 'tmc_sp' ), $pluginName ),
				sprintf( '<a href="%1$s">%2$s</a>', $adminPageUrl, __( 'Please remove license to dismiss this message.', 'tmc_sp' ) )
			);

		}

	}

	/**
	 * If plugin was updated, plugin activation hook is not called.
	 * This method fixes it!
	 *
	 * Called on upgrader_process_complete.
	 *
	 * @param WP_Upgrader $upgrader
	 * @param array $options
	 *
	 * @return void
	 */
	public function _a_handlePluginUpdateCronJobs( $upgrader, $options ) {

		if( $options['action'] == 'update' && $options['type'] == 'plugin' ){   //  Updating plugin.

			$pluginName = plugin_basename( $this::s()->getMainPluginFile() );

			if( is_array( $options['plugins'] ) && in_array( $pluginName, $options['plugins'] ) ){  //  Updating THIS plugin

				add_action( 'init', array( $this, '_a_registerCronJob' ) );

			}

		}

	}

	/**
	 * Called on plugin activation.
	 *
	 * @return void
	 */
	public function _a_registerCronJob() {

		if( ! wp_next_scheduled( $this->_licenseCheckCronJobName ) ){

			wp_schedule_event( time(), 'daily', $this->_licenseCheckCronJobName );

			$this::s()->log->info( 'Added cron job: ' . $this->_licenseCheckCronJobName );

		}

	}

	/**
	 * Called on plugin deactivation.
	 *
	 * @return void
	 */
	public function _a_unregisterCronJob() {

		wp_clear_scheduled_hook( $this->_licenseCheckCronJobName );

	}

	/**
	 * Does remote requests and checks given license key.
	 *
	 * @return void
	 */
	public function _a_doCheckLicenseCronCallback() {

		$this::s()->log->info( 'START LICENSE CHECK CRON JOB' );

		$key = $this->getKey();

		if( $key ){
			$this->performRemoteKeyUpdate();
			$this::s()->options->flush();
		}

	}

}