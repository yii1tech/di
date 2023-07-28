<p align="center">
    <a href="https://github.com/yii1tech" target="_blank">
        <img src="https://avatars.githubusercontent.com/u/134691944" height="100px">
    </a>
    <h1 align="center">Yii1 Dependency Injection Extension</h1>
    <br>
</p>

This extension provides Dependency Injection support for Yii1 application.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://img.shields.io/packagist/v/yii1tech/di.svg)](https://packagist.org/packages/yii1tech/di)
[![Total Downloads](https://img.shields.io/packagist/dt/yii1tech/di.svg)](https://packagist.org/packages/yii1tech/di)
[![Build Status](https://github.com/yii1tech/di/workflows/build/badge.svg)](https://github.com/yii1tech/di/actions)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii1tech/di
```

or add

```json
"yii1tech/di": "*"
```

to the "require" section of your composer.json.


Usage
-----

This extension provides Dependency Injection support for Yii1 application.


### Container Setup <span id="container-setup"></span>

This extension introduces static facade class `\yii1tech\di\DI`, which provides global access to PSR container nad injector.
All dependency injection features provided in this extension relies on this facade.
The dependency injection container should be set at the Yii bootstrap stage before the application run.
For example:

```php
<?php
// file '/public/index.php'
require __DIR__ . '../vendor/autoload.php';
// ...

// setup DI container:
yii1tech\di\DI::setContainer(
    yii1tech\di\Container::new()
        ->config(CDbConnection::class, [
            'connectionString' => 'sqlite::memory:',
        ])
        ->lazy(ICache::class, function (Psr\Container\ContainerInterface $container) {
            $cache = new CDbCache();
            $cache->setDbConnection($container->get(CDbConnection::class))
            
            $cache->init();
            
            return $cache;
        })
        // ...
);

// create and run Yii application:
Yii::createWebApplication($config)->run();
```

Instead of creating container instance right away, you may specify a PHP callback, which will instantiate it.
For example:

```php
<?php
// file '/public/index.php'
require __DIR__ . '../vendor/autoload.php';
// ...

// setup DI container:
yii1tech\di\DI::setContainer(function () {
    return ContainerFactory::create();
});

// create and run Yii application:
Yii::createWebApplication($config)->run();

// ...
class ContainerFactory
{
    public static function create(): \Psr\Container\ContainerInterface
    {
        $container = yii1tech\di\Container::new();
        // fill up container
        
        return $container;
    }
}
```


### Dependency Injection for Application Components <span id="di-for-application-components"></span>

This extension allows configuration of the Application (Service Locator) components via DI container.
In order to make it work you need use one of application classes provided within this extension - feature will not function
automatically on the standard ones.

Following classes are provided:

- `\yii1tech\di\web\WebApplication` - DI aware Web Application.
- `\yii1tech\di\console\ConsoleApplication` - DI aware Console Application.
- `\yii1tech\di\base\Module` - DI aware Module.
- `\yii1tech\di\web\WebModule` - DI aware Web Module.

If you use your own custom application class, you may apply DI component resolution ot it using `\yii1tech\di\base\ResolvesComponentViaDI` trait.

For example:

```php
<?php
// file '/public/index.php'
require __DIR__ . '../vendor/autoload.php';
// ...
// setup DI container:
yii1tech\di\DI::setContainer(/* ... */);

// create and run Yii DI-aware application:
Yii::createApplication(yii1tech\di\web\WebApplication::class, $config)->run();
```

Being DI-aware application will resolve configured components via DI Container according to their configured class.
This allows you moving configuration into DI Container from Service Locator without breaking anything.
For example:

```php
<?php
// ...
// setup DI container:
yii1tech\di\DI::setContainer(
    yii1tech\di\Container::new()
        ->lazy(CDbConnection::class, function () {
            $db = new CDbConnection();
            $db->connectionString = 'mysql:host=127.0.0.1;dbname=example';
            $db->username = 'container_user';
            $db->password = 'secret';
            
            $db->init();
            
            return $db;
        })
        ->lazy(ICache::class, function (Psr\Container\ContainerInterface $container) {
            $cache = new CDbCache();
            $cache->setDbConnection($container->get(CDbConnection::class));
            
            $cache->init();
            
            return $cache;
        })
);

$config = [
    'components' => [
        'db' => [ // component 'db' will be fetched from container using ID 'CDbConnection'
            'class' => CDbConnection::class,
        ],
        'cache' => [ // component 'cache' will be fetched from container using ID 'ICache'
            'class' => ICache::class,
        ],
        'format' => [ // if component has no matching definition in container - it will be resolved in usual way
            'class' => CFormatter::class,
            'dateFormat' => 'Y/m/d',
        ],
    ],
];

// create and run Yii DI-aware application:
Yii::createApplication(yii1tech\di\web\WebApplication::class, $config)->run();
//...
$db = Yii::app()->getComponent('db');
var_dump($db->username); // outputs 'container_user'

$cache = Yii::app()->getComponent('cache');
var_dump(get_class($cache)); // outputs 'CDbCache'
```

**Heads up!** Be careful while moving Yii component definitions into DI container: remember to invoke method `init()` manually on them.
Yii often places crucial initialization logic into component's `init()`, if you omit its invocation you may receive broken component instance
while fetching it directly from container.

**Heads up!** If you move application component config to DI container, make sure to clean up its original configuration at Service Locator.
Any "property value" definition remaining, will be applied to the object fetched from container. It may be useful hen you define container
entity via factory, but in most cases it will cause you trouble.


### Dependency Injection in Web Application <span id="di-in-web-application"></span>


### Dependency Injection in Console Application <span id="di-in-console-application"></span>


### External (3rd party) Container Usage <span id="external-contatiner-usage"></span>

