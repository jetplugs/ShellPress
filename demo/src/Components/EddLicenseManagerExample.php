<?php
namespace shellpress\v1_3_77\demo\src\Components;

use shellpress\v1_3_77\src\Shared\Components\IUniversalFrontComponentEDDLicenser;

/**
 * @author jakubkuranda@gmail.com
 * Date: 16.09.2019
 * Time: 11:34
 */
class EddLicenseManagerExample extends IUniversalFrontComponentEDDLicenser {


	/**
	 * Called on basic set up, just before everything else.
	 *
	 * @return void
	 */
	public function onSetUpComponent() {

		$this->setApiUrl( 'https://easydigitaldownloads.com' );
		$this->setProductId( '5' );

	}

}