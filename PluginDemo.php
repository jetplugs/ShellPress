<?php
/**
 * Plugin Name:     ShellPress Demo
 * Description:     Development demo for ShellPress framework.
 * Version:         1.0.4
 * Plugin URI:      https://themastercut.co
 * Author:          TheMasterCut.co
 * License:         GPL-2.0+
 * Text Domain:     shellpress
 * Domain Path:     /langugages
 */

//  ----------------------------------------
//  Requirements
//  ----------------------------------------

require __DIR__ . '/vendor/autoload.php';   //  Composer

$requirementChecker = new ShellPress_RequirementChecker();

$checkPHP   = $requirementChecker->checkPHPVersion( '5.3', 'ShellPress Demo requires PHP version >= 5.3' );
$checkWP    = $requirementChecker->checkWPVersion( '4.8', 'ShellPress Demo requires WP version >= 4.8' );

if( ! $checkPHP || ! $checkWP ) return;

//  ----------------------------------------
//  ShellPress
//  ----------------------------------------

\shellpress\v1_3_87\demo\Demo::initShellPress( __FILE__, 'spdemo', '1.0.4' );   //  <--- Remember to always change version here
