# ShellPress
Simple WordPress framework to normalize software production.
It's still in alpha. Don't use it in production.

# Why I made it
Holy shit. I saw so many people doing obvious mistakes in WordPress plugins, it hurts.

- Programmers use Composer to handle packages. Wow, ok, that's cool, but **not in WordPress**.
Did you think about what will happen, if somebody used it too in his plugin?
In the best scenario, your plugin will be broken, because you use other version of library.

- Use namespaces! Don't prefix every function you have. It's disgusting.
_ShellPress_ comes with built in, easy to use class autoloader.

- When it comes to prefixing, it's pain in the ass.

- I decided to create it as an abstract static class, so you can extend it and use everywhere in your project.

- **NEW!** Normalized tables! ( still alpha )

# Requirements
- PHP 5.3 ( namespacing of course )
- Knwoledge about PS4 http://www.php-fig.org/psr/psr-4/

# Qucik start

Create new class which extends ShellPress.
```
<?php
namespace myname\pluginname\src;

use shellpress\v1_0_5\ShellPress;

/**
 * App.php
 *
 * Main application class.
 * Extends ShellPress for basic plugin helpers.
 */

if( ! class_exists( 'shellpress\v1_0_5\ShellPress' ) ){

    require_once( dirname( __DIR__ ) . '/lib/ShellPress/ShellPress.php' );

}

class App extends ShellPress {

    /** @var array */
    protected static $sp;   <-- YOU NEED TO DEFINE IT HERE!!!

    /**
     * You can define all of your plugin things inside.
     */
    public static function init() {

        //  ----------------------------------------
        //  Namespaces and importing
        //  ----------------------------------------

        static::autoloader()->addNamespace( 'myname\pluginname', dirname( self::getMainPluginFile() ) );

    }

}
```

Then in your main plugin file, require it and initialize.

```
//  We don't have autoloading yet!

if( ! class_exists( 'myname\pluginname\src\App' ) ){

    require_once( __DIR__ . '/src/App.php' );

    myname\pluginname\src\App::initShellPress( __FILE__, 'pluginname', '1.0.0' );   <-- This will help you later
    myname\pluginname\src\App::init();                                              <-- Here you can do your own stuff

}
```
