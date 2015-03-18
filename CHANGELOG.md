Changelog
=========

* 1.0.0-next (@release_date@)

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
