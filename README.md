Puli Resource Discovery
=======================

[![Build Status](https://travis-ci.org/puli/discovery.svg?branch=master)](https://travis-ci.org/puli/discovery)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/puli/discovery/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/puli/discovery/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/1d34f3b8-aafe-49c9-8eb8-df97ac8a1ba3/mini.png)](https://insight.sensiolabs.com/projects/1d34f3b8-aafe-49c9-8eb8-df97ac8a1ba3)
[![Latest Stable Version](https://poser.pugx.org/puli/discovery/v/stable.svg)](https://packagist.org/packages/puli/discovery)
[![Total Downloads](https://poser.pugx.org/puli/discovery/downloads.svg)](https://packagist.org/packages/puli/discovery)
[![Dependency Status](https://www.versioneye.com/php/puli:discovery/1.0.0/badge.svg)](https://www.versioneye.com/php/puli:discovery/1.0.0)

Latest release: none

PHP >= 5.3.9

The Puli Discovery component supports binding of Puli resources to types. Types
can be defined with the `define()` method of the `ResourceBinder`:

```php
use Puli\Discovery\ResourceBinder;

// $repo is a Puli repository
$binder = new ResourceBinder($repo);

$binder->define('acme/xliff-messages');
```

Resources in the repository can be bound to defined types with the `bind()`
method:

```php
$binder->bind('/app/trans/*.xlf', 'acme/xliff-messages');
```

You can define parameters for binding types:

```php
use Puli\Discovery\Binding\BindingParameter;
use Puli\Discovery\Binding\BindingType;

$binder->define(new BindingType('acme/xliff-messages', array(
    new BindingParameter('translationDomain'),
)));

$binder->bind('/app/trans/errors.*.xlf', 'acme/xliff-messages', array(
    'translationDomain' => 'errors',
));
```

The bindings can later be fetched with the `find()` method:

```php
$bindings = $binder->find('acme/xliff-messages');

foreach ($bindings as $binding) {
    foreach ($binding->getResources() as $resource) {
        $translator->add($resource->getLocalPath(), $binding->getParameter('translationDomain'));
    }
}
```

To optimize read performance, you should write the binder to a storage. The
stored object is of type `ResourceDiscoveryInterface` which only features the
methods `find()` and `getBindings()`:

```php
use Puli\Discovery\Storage\PhpDiscoveryStorage;

$storage = new PhpDiscoveryStorage();

// run once
$storage->storeDiscovery($binder, array('path' => '/path/to/discovery.php'));

// run whenever bindings need to be accessed
$discovery = $storage->loadDiscovery($repo, array('path' => '/path/to/discovery.php'));

$bindings = $binder->find('acme/xliff-messages');
// ...
```

Read [Puli at a Glance] if you want to learn more about Puli.

Authors
-------

* [Bernhard Schussek] a.k.a. [@webmozart]
* [The Community Contributors]

Contribute
----------

Contributions to Puli are always welcome!

* Report any bugs or issues you find on the [issue tracker].
* You can grab the source code at Puli’s [Git repository].

Support
-------

If you are having problems, send a mail to bschussek@gmail.com or shout out to
[@webmozart] on Twitter.

License
-------

All contents of this package are licensed under the [MIT license].

[Bernhard Schussek]: http://webmozarts.com
[The Community Contributors]: https://github.com/puli/discovery/graphs/contributors
[Puli at a Glance]: http://puli.readthedocs.org/en/latest/at-a-glance.html
[issue tracker]: https://github.com/puli/puli/issues
[Git repository]: https://github.com/puli/discovery
[@webmozart]: https://twitter.com/webmozart
[MIT license]: LICENSE
