<?php
namespace shellpress\v1_4_0\src\Shared\Components;

/**
 * @author jakubkuranda@gmail.com
 * Date: 14.03.2018
 * Time: 11:22
 */

/**
 * @deprecated
 */
abstract class IComponentLicenseManagerSLM extends IComponent {

    /**
     * Checks SAVED license status.
     * Caution! I does not check status remotely.
     *
     * @return bool
     */
    public function isActive() {
		return false;
    }

    /**
     * Sets if key is correct or not.
     *
     * @param bool $isKeyActive
     *
     * @return void
     */
    public function setActive( $isKeyActive ) {

    }

    /**
     * Sets license key.
     *
     * @param string $key
     *
     * @return void
     */
    public function setKey( $key ) {
		
    }

    /**
     * Returns license key.
     *
     * @return string|null
     */
    public function getKey() {

        return '';

    }

    /**
     * Returns UTC last check for license status in mysql datetime format.
     *
     * @return string|null
     */
    public function getLastCheckDatetime() {

        return null;

    }

    /**
     * Sets UTC last check for license status in mysql datetime format.
     *
     * @param string|null $datetime
     *
     * @return void
     */
    public function setLastCheckDatetime( $datetime ) {
		
    }

    /**
     * Returns UTC key expiration in mysql datetime format.
     *
     * @return string|null
     */
    public function getKeyExpiryDatetime() {

        return null;

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
		
    }

    /**
     * Returns key status to show in admin area.
     * It describes, what is going on with key.
     *
     * @return string|null
     */
    public function getKeyStatus() {

        return null;

    }

    /**
     * Sets UTC key expiration in mysql datetime format.
     *
     * @param string|null $datetime
     *
     * @return void
     */
    public function setKeyExpiryDatetime( $datetime ) {
		
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

    }

    /**
     * It will activate given key for current domain.
     * It sets key status and active state.
     *
     * @return void
     */
    public function performRemoteKeyActivation() {

    }

}