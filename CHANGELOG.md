Changelog
=========

* 1.0.0-beta4 (2015-04-13)

 * `LazyBinding` does not cache resources anymore in case the repository 
   contents changed since the last call
 * removed `NoQueryMatchesException`
 * changed boolean parameter `$required` to integer parameter `$flags` in
   `BindingParameter::__construct()`
 * removed `$code` arguments from static exception factory methods
 * updated to webmozart/glob 2.0

* 1.0.0-beta3 (2015-03-19)

 * replaced `Assert` by webmozart/assert
 * renamed `ResourceDiscovery::find()` to `findByType()`
 * split `ResourceDiscovery::getBindings()` into `findByPath()` and
   `getBindings()` without arguments
 
* 1.0.0-beta2 (2015-01-27)

 * added `NullDiscovery`
 * changed `ResourceDiscovery::find()` and `getBindings()` to throw a
   `NoSuchTypeException` if the type has not been defined
 * removed dependency to beberlei/assert

* 1.0.0-beta (2015-01-12)

 * first release
