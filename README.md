# Penoaks Framework

[![StyleCI](https://styleci.io/repos/7548986/shield?style=flat)](https://styleci.io/repos/7548986)
[![Build Status](https://travis-ci.org/penoaks/framework.svg)](https://travis-ci.org/penoaks/framework)
[![Total Downloads](https://poser.pugx.org/penoaks/framework/d/total.svg)](https://packagist.org/packages/penoaks/framework)
[![Latest Stable Version](https://poser.pugx.org/penoaks/framework/v/stable.svg)](https://packagist.org/packages/penoaks/framework)
[![Latest Unstable Version](https://poser.pugx.org/penoaks/framework/v/unstable.svg)](https://packagist.org/packages/penoaks/framework)
[![License](https://poser.pugx.org/penoaks/framework/license.svg)](https://packagist.org/packages/penoaks/framework)

> **Note:** This repository contains the core code of the Penoaks Framework. If you want to build an application using Penoaks 5, visit the main [Penoaks repository](https://github.com/penoaks/penoaks).

## Getting Started

Penoaks Framework uses Composer to manage dependencies. So, before using Penoaks Framework, make sure you have Composer installed on your machine.

### Dependencies

Be sure you have the following dependencies installed.

* PHP >= 5.5.9
* OpenSSL PHP Extension
* PDO PHP Extension
* Mbstring PHP Extension
* Tokenizer PHP Extension

### Install with Composer

You can simply install Penoaks Framework by issuing the Composer `create-project` command in your terminal:

```bash
composer create-project --prefer-dist penoaks/framework framework
```

You can also alternatively install the Framework by simply cloning our repository and issuing the Composer `update` command in your terminal.

```bash
composer update
```

### Your First Project

With Penoaks Framework, no application or user files are kept in the framework directory. Instead views, controllers, etc, are kept in the `src` (customizable) directory found in your webroot. Because of this you can simply update your installation by deleting the old framework directory and reinstalling. You should only need the `index.php` and `.htaccess` files to setup proper routing from there. Be sure you have the `mod_rewrite` apache module enabled.

`.htaccess`

```
<IfModule mod_rewrite.c>
	<IfModule mod_negotiation.c>
		Options -MultiViews
	</IfModule>

	RewriteEngine On

	# Ignore Let's Encrypt Challenges
	RewriteRule ^.well-known - [L]

	# Redirect Trailing Slashes If Not A Folder...
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)/$ /$1 [L,R=301]

	# Pass Request to Penoaks Framework
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule ^ index.php [L]

	# Handle Authorization Header
	RewriteCond %{HTTP:Authorization} .
	RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
</IfModule>
```

`index.php`

```php
<?php
$fw = require( "framework/Constructor.php" ); // Replace 'framework' with the location of Penoaks Framework.

/*
 * init() function sets up a basic framework instance and loads Composer classes.
 * The first argument sets the application directory.
 * The second argument (optional) sets the 'config' directory. Not defined (or null), will set it as 'config' under the application directory.
 */
$fw->init( __DIR__ );

/*
 * join() function takes over the current request and returns a response.
 * Be sure that no data was output before this, else problems could arise.
 */
$fw->join();
```

Because of Penoaks Frameworks unique way of handling the webroot, it's possible for the `framework` and `src` directories to be located anywhere on your machine that is writable by your Apache user. While it's not recommended, it's also possible to use the same framework install for multiple virtual hosts. In the event that you do keep your `framework` and/or `src` directory with your webroot, be sure to disallow access using various methods that can be found online with a simple search.

An example application can be found under the example directory.

## Penoaks PHP Framework

Penoaks Framework is the continuation of the simplified framework by Chiori-chan, called Chiori Framework. Forked from Laravel, it's a framework with expressive, elegant syntax. And with the added additions by Chiori-chan, the framework is all the more powerful and robust. ENJOY!

If you're looking for more power and persistence in a framework, checkout [Chiori-chan's Web Server](https://github.com/ChioriGreene/ChioriWebServer). Written in Java, it features an easy to understand API and runs with the power of Groovy as a scripting language.

## History

Starting out almost 7 years ago, Joel Greene (aka. Chiori-chan) was looking for a simplified yet powerful framework to build his web applications upon. Many of the frameworks at the time either offer more than what he needed or had miles of documentation to read through to even get started, he turned to writing his own. Just recently picking up PHP, including having a limited knowledge of PHP, he ended up with the framework he needed. It featured plugins, events, an easy to use templating engine, built-in user and session management, and much more.

But just a few versions in Joel had a dilemma, PHP greatly lacked persistence and surefire ways of minimizing the amount of code needed with each request. Having recently developed his first Android Application, he decided to give Java a try. But to his dismay, simplified web development in Java was (and still is) extremely rare and what open-sourced projects that were on the market, required some extensive knowledge of XML, life cycles, and worst of all, used (very heavy) Configuration over Convention that was very error prone. And this is not also to mention the lack of debugging and the need for Java to be always compiled. Moving his framework to Java and going through much trial and error, the biggest headache being finding a scripting language similar to PHP. Chiori-chan's Web Server was born. It features much of same stuff from the old PHP framework plus more, such as CSRF tokens, a Task Manager (for background processes), on-the-fly Image Manipulator, Built-in CSS and JS compression, Groovy Scripting Language (Using an extendable scripting engine), Node based Permission System, and so much more.

Today, Joel Greene is the owner, alongside his wife Rachel, of Penoaks Publishing Co. and live in Kansas City, Missouri. Recently with the need of a simple PHP framework for a side-project of Penoaks, Joel resurrected the idea of maintaining a PHP framework after he gave the Laravel framework a try. Finding issues with Laravel's handling of models and errors but liking other things about Laravel, he instead decided to fork Laravel and Penoaks Framework was born.

## Official Documentation

Documentation for the framework can be found on the [Penoaks website](http://penoaks.com/development/framework).

## Contributing

Thank you for considering contributing to the Penoaks Framework! The full contribution guide is a work in progress but you can get started with a fork and pull request.

## Security Vulnerabilities

If you discover a security vulnerability within Penoaks, please send an e-mail to Chiori-chan at me@chiorichan.com. All security vulnerabilities will be promptly addressed.

## License

The Penoaks Framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
