<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery;

use Puli\Discovery\Api\Binding\Binding;
use Puli\Discovery\Api\Binding\Initializer\BindingInitializer;
use Puli\Discovery\Api\Binding\NoSuchBindingException;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Api\Type\DuplicateTypeException;
use Puli\Discovery\Api\Type\NoSuchTypeException;
use Rhumsaa\Uuid\Uuid;
use RuntimeException;
use Webmozart\Assert\Assert;
use Webmozart\Expression\Expression;
use Webmozart\KeyValueStore\Api\KeyValueStore;

/**
 * A resource discovery that stores the bindings in a key-value store.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class KeyValueStoreDiscovery extends AbstractEditableDiscovery
{
    /**
     * @var KeyValueStore
     */
    private $store;

    /**
     * Stores the integer key that will be used when adding the next binding type.
     *
     * Synchronized with the entry "::nextKey" in the store.
     *
     * @var int
     */
    private $nextKey;

    /**
     * Stores an integer "key" for each binding type name.
     *
     * Contains each key only once.
     *
     * Synchronized with the entry "::keysByTypeName" in the store.
     *
     * @var int[]
     */
    private $keysByTypeName;

    /**
     * Stores the corresponding keys for each binding UUID.
     *
     * Potentially contains keys zero or multiple times.
     *
     * Synchronized with the entry "::keysByUuid" in the store.
     *
     * @var int[]
     */
    private $keysByUuid;

    /**
     * Stores the binding type for each key.
     *
     * Synchronized with the entries "t:<key>" in the store.
     *
     * @var BindingType[]
     */
    private $typesByKey = array();

    /**
     * Stores the bindings for each key.
     *
     * Synchronized with the entries "b:<key>" in the store.
     *
     * @var Binding[][]
     */
    private $bindingsByKey = array();

    /**
     * Creates a new resource discovery.
     *
     * @param KeyValueStore        $store        The key-value store used to
     *                                           store the bindings and the
     *                                           binding types.
     * @param BindingInitializer[] $initializers The binding initializers to
     *                                           apply to newly created or
     *                                           unserialized bindings.
     */
    public function __construct(KeyValueStore $store, array $initializers = array())
    {
        parent::__construct($initializers);

        $this->store = $store;
        $this->keysByTypeName = $store->get('::keysByTypeName', array());
        $this->keysByUuid = $store->get('::keysByUuid', array());
        $this->nextKey = $store->get('::nextKey', 0);
    }

    /**
     * {@inheritdoc}
     */
    public function addBindingType(BindingType $type)
    {
        if (isset($this->keysByTypeName[$type->getName()])) {
            throw DuplicateTypeException::forTypeName($type->getName());
        }

        $key = $this->nextKey++;

        $this->keysByTypeName[$type->getName()] = $key;

        // Use integer keys to reduce storage space
        // (compared to fully-qualified class names)
        $this->typesByKey[$key] = $type;

        $this->store->set('::keysByTypeName', $this->keysByTypeName);
        $this->store->set('::nextKey', $this->nextKey);
        $this->store->set('t:'.$key, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function removeBindingType($typeName)
    {
        Assert::stringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        if (!isset($this->keysByTypeName[$typeName])) {
            return;
        }

        $key = $this->keysByTypeName[$typeName];

        if (!isset($this->bindingsByKey[$key])) {
            // no initialize, since we're removing this anyway
            $this->loadBindingsForKey($key, false);
        }

        // Remove all binding UUIDs for this binding type
        foreach ($this->bindingsByKey[$key] as $binding) {
            unset($this->keysByUuid[$binding->getUuid()->toString()]);
        }

        unset($this->keysByTypeName[$typeName]);
        unset($this->typesByKey[$key]);
        unset($this->bindingsByKey[$key]);

        $this->store->remove('t:'.$key);
        $this->store->remove('b:'.$key);
        $this->store->set('::keysByTypeName', $this->keysByTypeName);
        $this->store->set('::keysByUuid', $this->keysByUuid);
    }

    /**
     * {@inheritdoc}
     */
    public function removeBindingTypes()
    {
        $this->keysByTypeName = array();
        $this->keysByUuid = array();
        $this->typesByKey = array();
        $this->bindingsByKey = array();
        $this->nextKey = 0;

        $this->store->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function hasBindingType($typeName)
    {
        Assert::stringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        return isset($this->keysByTypeName[$typeName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBindingType($typeName)
    {
        Assert::stringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        if (!isset($this->keysByTypeName[$typeName])) {
            throw NoSuchTypeException::forTypeName($typeName);
        }

        $key = $this->keysByTypeName[$typeName];

        if (!isset($this->typesByKey[$key])) {
            $this->typesByKey[$key] = $this->store->get('t:'.$key);
        }

        return $this->typesByKey[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function hasBindingTypes()
    {
        return count($this->keysByTypeName) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindingTypes()
    {
        $keysToFetch = array();

        foreach ($this->keysByTypeName as $key) {
            if (!isset($this->typesByKey[$key])) {
                $keysToFetch[] = 't:'.$key;
            }
        }

        $types = $this->store->getMultiple($keysToFetch);

        foreach ($types as $prefixedKey => $type) {
            $this->typesByKey[substr($prefixedKey, 2)] = $type;
        }

        ksort($this->typesByKey);

        return $this->typesByKey;
    }

    /**
     * {@inheritdoc}
     */
    public function addBinding(Binding $binding)
    {
        $typeName = $binding->getTypeName();

        if (!isset($this->keysByTypeName[$typeName])) {
            throw NoSuchTypeException::forTypeName($typeName);
        }

        if (isset($this->keysByUuid[$binding->getUuid()->toString()])) {
            // Ignore duplicates
            return;
        }

        $key = $this->keysByTypeName[$typeName];

        if (!isset($this->bindingsByKey[$key])) {
            $this->loadBindingsForKey($key);
        }

        $this->initializeBinding($binding);

        $this->keysByUuid[$binding->getUuid()->toString()] = $key;
        $this->bindingsByKey[$key][] = $binding;

        $this->store->set('b:'.$key, $this->bindingsByKey[$key]);
        $this->store->set('::keysByUuid', $this->keysByUuid);
    }

    /**
     * {@inheritdoc}
     */
    public function removeBinding(Uuid $uuid)
    {
        $uuidString = $uuid->toString();

        if (!isset($this->keysByUuid[$uuidString])) {
            return;
        }

        $key = $this->keysByUuid[$uuidString];

        if (!isset($this->bindingsByKey[$key])) {
            $this->loadBindingsForKey($key);
        }

        foreach ($this->bindingsByKey[$key] as $i => $binding) {
            if ($binding->getUuid()->equals($uuid)) {
                unset($this->bindingsByKey[$key][$i]);
            }
        }

        // Reindex array
        $this->bindingsByKey[$key] = array_values($this->bindingsByKey[$key]);

        unset($this->keysByUuid[$uuidString]);

        $this->store->set('b:'.$key, $this->bindingsByKey[$key]);
        $this->store->set('::keysByUuid', $this->keysByUuid);
    }

    /**
     * {@inheritdoc}
     */
    public function hasBinding(Uuid $uuid)
    {
        return isset($this->keysByUuid[$uuid->toString()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBinding(Uuid $uuid)
    {
        if (!isset($this->keysByUuid[$uuid->toString()])) {
            throw NoSuchBindingException::forUuid($uuid);
        }

        $key = $this->keysByUuid[$uuid->toString()];

        if (!isset($this->bindingsByKey[$key])) {
            $this->loadBindingsForKey($key);
        }

        foreach ($this->bindingsByKey[$key] as $binding) {
            if ($binding->getUuid()->equals($uuid)) {
                return $binding;
            }
        }

        // This does not happen except if someone plays with the key-value store
        // contents (or there's a bug here..)
        throw new RuntimeException('The discovery is corrupt. Please rebuild it.');
    }

    /**
     * {@inheritdoc}
     */
    public function findBindings($typeName, Expression $expr = null)
    {
        Assert::stringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        if (!isset($this->keysByTypeName[$typeName])) {
            return array();
        }

        $key = $this->keysByTypeName[$typeName];

        if (!isset($this->bindingsByKey[$key])) {
            $this->loadBindingsForKey($key);
        }

        $bindings = $this->bindingsByKey[$key];

        if (null !== $expr) {
            $bindings = $this->filterBindings($bindings, $expr);
        }

        return $bindings;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings()
    {
        $this->loadAllBindings();

        $bindings = array();

        foreach ($this->bindingsByKey as $bindingsForKey) {
            $bindings = array_merge($bindings, $bindingsForKey);
        }

        return $bindings;
    }

    /**
     * {@inheritdoc}
     */
    protected function removeAllBindings()
    {
        $this->bindingsByKey = array();
        $this->keysByUuid = array();

        // Iterate $keysByTypeName which does not contain duplicate keys
        foreach ($this->keysByTypeName as $key) {
            $this->store->remove('b:'.$key);
        }

        $this->store->remove('::keysByUuid');
    }

    /**
     * {@inheritdoc}
     */
    protected function removeBindingsThatMatch(Expression $expr)
    {
        $this->loadAllBindings();

        foreach ($this->bindingsByKey as $key => $bindingsForKey) {
            foreach ($bindingsForKey as $i => $binding) {
                if ($expr->evaluate($binding)) {
                    unset($this->bindingsByKey[$key][$i]);
                    unset($this->keysByUuid[$binding->getUuid()->toString()]);
                }
            }

            // Reindex array
            $this->bindingsByKey[$key] = array_values($this->bindingsByKey[$key]);

            $this->store->set('b:'.$key, $this->bindingsByKey[$key]);
        }

        $this->store->set('::keysByUuid', $this->keysByUuid);
    }

    /**
     * {@inheritdoc}
     */
    protected function removeBindingsWithTypeName($typeName)
    {
        if (!isset($this->keysByTypeName[$typeName])) {
            return;
        }

        $key = $this->keysByTypeName[$typeName];

        if (!isset($this->bindingsByKey[$key])) {
            // no initialize, since we're removing this anyway
            $this->loadBindingsForKey($key, false);
        }

        foreach ($this->bindingsByKey[$key] as $binding) {
            unset($this->keysByUuid[$binding->getUuid()->toString()]);
        }

        unset($this->bindingsByKey[$key]);

        $this->store->remove('b:'.$key);
        $this->store->set('::keysByUuid', $this->keysByUuid);
    }

    /**
     * {@inheritdoc}
     */
    protected function removeBindingsWithTypeNameThatMatch($typeName, Expression $expr)
    {
        if (!isset($this->keysByTypeName[$typeName])) {
            return;
        }

        $key = $this->keysByTypeName[$typeName];

        if (!isset($this->bindingsByKey[$key])) {
            $this->loadBindingsForKey($key);
        }

        foreach ($this->bindingsByKey[$key] as $i => $binding) {
            if ($expr->evaluate($binding)) {
                unset($this->bindingsByKey[$key][$i]);
                unset($this->keysByUuid[$binding->getUuid()->toString()]);
            }
        }

        // Reindex array
        $this->bindingsByKey[$key] = array_values($this->bindingsByKey[$key]);

        $this->store->set('b:'.$key, $this->bindingsByKey[$key]);
        $this->store->set('::keysByUuid', $this->keysByUuid);
    }

    /**
     * {@inheritdoc}
     */
    protected function hasAnyBinding()
    {
        return count($this->keysByUuid) > 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function hasBindingsThatMatch(Expression $expr)
    {
        $this->loadAllBindings();

        foreach ($this->bindingsByKey as $bindingsForKey) {
            foreach ($bindingsForKey as $binding) {
                if ($expr->evaluate($binding)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function hasBindingsWithTypeName($typeName)
    {
        if (!isset($this->keysByTypeName[$typeName])) {
            return false;
        }

        $key = $this->keysByTypeName[$typeName];

        return false !== array_search($key, $this->keysByUuid, true);
    }

    protected function hasBindingsWithTypeNameThatMatch($typeName, Expression $expr)
    {
        if (!$this->hasBindingsWithTypeName($typeName)) {
            return false;
        }

        $key = $this->keysByTypeName[$typeName];

        if (!isset($this->bindingsByKey[$key])) {
            $this->loadBindingsForKey($key);
        }

        foreach ($this->bindingsByKey[$key] as $binding) {
            if ($expr->evaluate($binding)) {
                return true;
            }
        }

        return false;
    }

    private function loadAllBindings()
    {
        $keysToFetch = array();

        foreach ($this->keysByTypeName as $key) {
            if (!isset($this->bindingsByKey[$key])) {
                $keysToFetch[] = 'b:'.$key;
            }
        }

        $fetchedBindings = $this->store->getMultiple($keysToFetch);

        foreach ($fetchedBindings as $key => $bindingsForKey) {
            $this->bindingsByKey[$key] = $bindingsForKey ?: array();
            $this->initializeBindings($this->bindingsByKey[$key]);
        }
    }

    private function loadBindingsForKey($key, $initialize = true)
    {
        $this->bindingsByKey[$key] = $this->store->get('b:'.$key, array());

        if ($initialize) {
            $this->initializeBindings($this->bindingsByKey[$key]);
        }
    }
}
