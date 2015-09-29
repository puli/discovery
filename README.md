The Puli Discovery Component
============================

[![Build Status](https://travis-ci.org/puli/discovery.svg?branch=master)](https://travis-ci.org/puli/discovery)
[![Build status](https://ci.appveyor.com/api/projects/status/wmg14bydks4xwqs2/branch/master?svg=true)](https://ci.appveyor.com/project/webmozart/discovery/branch/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/puli/discovery/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/puli/discovery/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/puli/discovery/v/stable.svg)](https://packagist.org/packages/puli/discovery)
[![Total Downloads](https://poser.pugx.org/puli/discovery/downloads.svg)](https://packagist.org/packages/puli/discovery)
[![Dependency Status](https://www.versioneye.com/php/puli:discovery/1.0.0/badge.svg)](https://www.versioneye.com/php/puli:discovery/1.0.0)

Latest release: [1.0.0-beta7](https://packagist.org/packages/puli/discovery#1.0.0-beta7)

PHP >= 5.3.9

The [Puli] Discovery Component supports binding of Puli resources to *binding
types*. Binding types can be defined with the `defineType()` method of the 
[`EditableDiscovery`] interface:

```php
use Puli\Discovery\InMemoryDiscovery;

// $repo is a Puli repository
$discovery = new InMemoryDiscovery($repo);

$discovery->defineType('doctrine/xml-mapping');
```

Resources in the repository can then be bound to the defined type with `bind()`:

```php
$discovery->bind('/app/config/doctrine/*.xml', 'doctrine/xml-mapping');
```

With `findByType()`, you can later retrieve all the bindings for the type:

```php
foreach ($discovery->findByType('doctrine/xml-mapping') as $binding) {
    foreach ($binding->getResources() as $resource) {
        // do something...
    }
}
```

The following [`ResourceDiscovery`] implementations are currently supported:

* [`InMemoryDiscovery`]
* [`KeyValueStoreDiscovery`]
* [`NullDiscovery`]

Read the [Resource Discovery] guide in the Puli documentation to learn more
about resource discovery.

Authors
-------

* [Bernhard Schussek] a.k.a. [@webmozart]
* [The Community Contributors]

Installation
------------

Follow the [Getting Started] guide to install Puli in your project.

Documentation
-------------

Read the [Puli Documentation] to learn more about Puli.

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

[Puli]: http://puli.io
[Bernhard Schussek]: http://webmozarts.com
[The Community Contributors]: https://github.com/puli/discovery/graphs/contributors
[Resource Discovery]: http://docs.puli.io/en/latest/discovery.html
[Getting Started]: http://docs.puli.io/en/latest/getting-started.html
[Puli Documentation]: http://docs.puli.io/en/latest/index.html
[issue tracker]: https://github.com/puli/issues/issues
[Git repository]: https://github.com/puli/discovery
[@webmozart]: https://twitter.com/webmozart
[MIT license]: LICENSE
[`EditableDiscovery`]: http://api.puli.io/latest/class-Puli.Discovery.Api.EditableDiscovery.html
[`ResourceDiscovery`]: http://api.puli.io/latest/class-Puli.Discovery.Api.ResourceDiscovery.html
[`InMemoryDiscovery`]: http://api.puli.io/latest/class-Puli.Discovery.InMemoryDiscovery.html
[`KeyValueStoreDiscovery`]: http://api.puli.io/latest/class-Puli.Discovery.KeyValueStoreDiscovery.html
[`NullDiscovery`]: http://api.puli.io/latest/class-Puli.Discovery.NullDiscovery.html
