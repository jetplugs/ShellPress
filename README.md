# ShellPress
Simple WordPress framework to normalize software production.
It's still in alpha. Don't use it in production.

# Why I made it
Holy shit. I saw so many people doing obvious mistakes in WordPress plugins, it hurts.

- Programmers use Composer to handle packages. Wow, ok, that's cool, but **not in WordPress**.
Did you think about what will happen, if somebody also used it in his plugin?
In the best scenario, your plugin will be broken, because you use other version of library.

- Use namespaces! Don't prefix every function you have. It's disgusting.
_ShellPress_ comes with built in, easy to use class autoloader.

- When it comes to prefixing, it's pain in the ass.

- I decided to create it as an abstract static class, so you can extend it and use everywhere in your project.

- **NEW!** [Normalized tables!](https://github.com/dualjack/ShellPress/wiki/Ajax-ListTable) ( still alpha )

# Requirements
- PHP 5.3 ( namespacing of course )
- Knwoledge about PS4 http://www.php-fig.org/psr/psr-4/

# Documentation

[GitHub Wiki](https://github.com/dualjack/ShellPress/wiki) - in progress.
