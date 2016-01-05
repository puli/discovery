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
use Webmozart\Json\JsonDecoder;
use Webmozart\Json\JsonEncoder;

/**
 * A resource discovery backed by a JSON file.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class JsonDiscovery extends AbstractEditableDiscovery
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $json;

    /**
     * @var JsonEncoder
     */
    private $encoder;

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
     * @param string               $path         The path to the JSON file.
     * @param BindingInitializer[] $initializers The binding initializers to
     *                                           apply to newly created or
     *                                           unserialized bindings.
     */
    public function __construct($path, array $initializers = array())
    {
        Assert::stringNotEmpty($path, 'The path to the JSON file must be a non-empty string. Got: %s');

        parent::__construct($initializers);

        $this->path = $path;
        $this->encoder = new JsonEncoder();
        $this->encoder->setPrettyPrinting(true);
        $this->encoder->setEscapeSlash(false);
    }

    /**
     * {@inheritdoc}
     */
    public function addBindingType(BindingType $type)
    {
        if (null === $this->json) {
            $this->load();
        }

        if (isset($this->json['keysByTypeName'][$type->getName()])) {
            throw DuplicateTypeException::forTypeName($type->getName());
        }

        $key = $this->json['nextKey']++;

        $this->json['keysByTypeName'][$type->getName()] = $key;

        $this->typesByKey[$key] = $type;

        // Use integer keys to reduce storage space
        // (compared to fully-qualified class names)
        $this->json['typesByKey'][$key] = serialize($type);

        $this->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function removeBindingType($typeName)
    {
        Assert::stringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        if (null === $this->json) {
            $this->load();
        }

        if (!isset($this->json['keysByTypeName'][$typeName])) {
            return;
        }

        $key = $this->json['keysByTypeName'][$typeName];

        if (!isset($this->bindingsByKey[$key])) {
            // no initialize, since we're removing this anyway
            $this->loadBindingsForKey($key, false);
        }

        // Remove all binding UUIDs for this binding type
        foreach ($this->bindingsByKey[$key] as $binding) {
            unset($this->json['keysByUuid'][$binding->getUuid()->toString()]);
        }

        unset($this->typesByKey[$key]);
        unset($this->bindingsByKey[$key]);

        unset($this->json['keysByTypeName'][$typeName]);
        unset($this->json['typesByKey'][$key]);
        unset($this->json['bindingsByKey'][$key]);

        $this->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function removeBindingTypes()
    {
        if (null === $this->json) {
            $this->load();
        }

        $this->typesByKey = array();
        $this->bindingsByKey = array();

        $this->json['keysByTypeName'] = array();
        $this->json['keysByUuid'] = array();
        $this->json['typesByKey'] = array();
        $this->json['bindingsByKey'] = array();
        $this->json['nextKey'] = 0;

        $this->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function hasBindingType($typeName)
    {
        Assert::stringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        if (null === $this->json) {
            $this->load();
        }

        return isset($this->json['keysByTypeName'][$typeName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBindingType($typeName)
    {
        Assert::stringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        if (null === $this->json) {
            $this->load();
        }

        if (!isset($this->json['keysByTypeName'][$typeName])) {
            throw NoSuchTypeException::forTypeName($typeName);
        }

        $key = $this->json['keysByTypeName'][$typeName];

        if (!isset($this->typesByKey[$key])) {
            $this->typesByKey[$key] = unserialize($this->json['typesByKey'][$key]);
        }

        return $this->typesByKey[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function hasBindingTypes()
    {
        if (null === $this->json) {
            $this->load();
        }

        return count($this->json['keysByTypeName']) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindingTypes()
    {
        if (null === $this->json) {
            $this->load();
        }

        foreach ($this->json['keysByTypeName'] as $key) {
            if (!isset($this->typesByKey[$key])) {
                $this->typesByKey[$key] = unserialize($this->json['typesByKey'][$key]);
            }
        }

        ksort($this->typesByKey);

        return $this->typesByKey;
    }

    /**
     * {@inheritdoc}
     */
    public function addBinding(Binding $binding)
    {
        if (null === $this->json) {
            $this->load();
        }

        $typeName = $binding->getTypeName();

        if (!isset($this->json['keysByTypeName'][$typeName])) {
            throw NoSuchTypeException::forTypeName($typeName);
        }

        if (isset($this->json['keysByUuid'][$binding->getUuid()->toString()])) {
            // Ignore duplicates
            return;
        }

        $key = $this->json['keysByTypeName'][$typeName];

        if (!isset($this->bindingsByKey[$key])) {
            $this->loadBindingsForKey($key);
        }

        $this->initializeBinding($binding);

        $this->bindingsByKey[$key][] = $binding;

        $this->json['keysByUuid'][$binding->getUuid()->toString()] = $key;
        $this->json['bindingsByKey'][$key] = serialize($this->bindingsByKey[$key]);

        $this->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function removeBinding(Uuid $uuid)
    {
        if (null === $this->json) {
            $this->load();
        }

        $uuidString = $uuid->toString();

        if (!isset($this->json['keysByUuid'][$uuidString])) {
            return;
        }

        $key = $this->json['keysByUuid'][$uuidString];

        if (!isset($this->bindingsByKey[$key])) {
            $this->loadBindingsForKey($key);
        }

        foreach ($this->bindingsByKey[$key] as $i => $binding) {
            if ($binding->getUuid()->equals($uuid)) {
                unset($this->bindingsByKey[$key][$i]);
            }
        }

        $this->reindexBindingsForKey($key);

        unset($this->json['keysByUuid'][$uuidString]);

        $this->json['bindingsByKey'][$key] = serialize($this->bindingsByKey[$key]);

        $this->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function hasBinding(Uuid $uuid)
    {
        if (null === $this->json) {
            $this->load();
        }

        return isset($this->json['keysByUuid'][$uuid->toString()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBinding(Uuid $uuid)
    {
        if (null === $this->json) {
            $this->load();
        }

        if (!isset($this->json['keysByUuid'][$uuid->toString()])) {
            throw NoSuchBindingException::forUuid($uuid);
        }

        $key = $this->json['keysByUuid'][$uuid->toString()];

        if (!isset($this->bindingsByKey[$key])) {
            $this->loadBindingsForKey($key);
        }

        foreach ($this->bindingsByKey[$key] as $binding) {
            if ($binding->getUuid()->equals($uuid)) {
                return $binding;
            }
        }

        // This does not happen except if someone plays with the JSON file
        // contents (or there's a bug here..)
        throw new RuntimeException('The discovery is corrupt. Please rebuild it.');
    }

    /**
     * {@inheritdoc}
     */
    public function findBindings($typeName, Expression $expr = null)
    {
        Assert::stringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        if (null === $this->json) {
            $this->load();
        }

        if (!isset($this->json['keysByTypeName'][$typeName])) {
            return array();
        }

        $key = $this->json['keysByTypeName'][$typeName];

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
        if (null === $this->json) {
            $this->load();
        }

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
        if (null === $this->json) {
            $this->load();
        }

        $this->bindingsByKey = array();

        $this->json['bindingsByKey'] = array();
        $this->json['keysByUuid'] = array();

        $this->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function removeBindingsThatMatch(Expression $expr)
    {
        if (null === $this->json) {
            $this->load();
        }

        $this->loadAllBindings();

        foreach ($this->bindingsByKey as $key => $bindingsForKey) {
            foreach ($bindingsForKey as $i => $binding) {
                if ($expr->evaluate($binding)) {
                    unset($this->bindingsByKey[$key][$i]);
                    unset($this->json['keysByUuid'][$binding->getUuid()->toString()]);
                }
            }

            $this->reindexBindingsForKey($key);

            $this->json['bindingsByKey'][$key] = serialize($this->bindingsByKey[$key]);
        }

        $this->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function removeBindingsWithTypeName($typeName)
    {
        if (null === $this->json) {
            $this->load();
        }

        if (!isset($this->json['keysByTypeName'][$typeName])) {
            return;
        }

        $key = $this->json['keysByTypeName'][$typeName];

        if (!isset($this->bindingsByKey[$key])) {
            // no initialize, since we're removing this anyway
            $this->loadBindingsForKey($key, false);
        }

        foreach ($this->bindingsByKey[$key] as $binding) {
            unset($this->json['keysByUuid'][$binding->getUuid()->toString()]);
        }

        unset($this->bindingsByKey[$key]);
        unset($this->json['bindingsByKey'][$key]);

        $this->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function removeBindingsWithTypeNameThatMatch($typeName, Expression $expr)
    {
        if (null === $this->json) {
            $this->load();
        }

        if (!isset($this->json['keysByTypeName'][$typeName])) {
            return;
        }

        $key = $this->json['keysByTypeName'][$typeName];

        if (!isset($this->bindingsByKey[$key])) {
            $this->loadBindingsForKey($key);
        }

        foreach ($this->bindingsByKey[$key] as $i => $binding) {
            if ($expr->evaluate($binding)) {
                unset($this->bindingsByKey[$key][$i]);
                unset($this->json['keysByUuid'][$binding->getUuid()->toString()]);
            }
        }

        $this->reindexBindingsForKey($key);

        $this->json['bindingsByKey'][$key] = serialize($this->bindingsByKey[$key]);

        $this->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function hasAnyBinding()
    {
        if (null === $this->json) {
            $this->load();
        }

        return count($this->json['keysByUuid']) > 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function hasBindingsThatMatch(Expression $expr)
    {
        if (null === $this->json) {
            $this->load();
        }

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
        if (null === $this->json) {
            $this->load();
        }

        if (!isset($this->json['keysByTypeName'][$typeName])) {
            return false;
        }

        $key = $this->json['keysByTypeName'][$typeName];

        return false !== array_search($key, $this->json['keysByUuid'], true);
    }

    protected function hasBindingsWithTypeNameThatMatch($typeName, Expression $expr)
    {
        if (null === $this->json) {
            $this->load();
        }

        if (!$this->hasBindingsWithTypeName($typeName)) {
            return false;
        }

        $key = $this->json['keysByTypeName'][$typeName];

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
        foreach ($this->json['keysByTypeName'] as $key) {
            if (!isset($this->bindingsByKey[$key])) {
                $this->bindingsByKey[$key] = isset($this->json['bindingsByKey'][$key])
                    ? unserialize($this->json['bindingsByKey'][$key])
                    : array();
                $this->initializeBindings($this->bindingsByKey[$key]);
            }
        }
    }

    private function loadBindingsForKey($key, $initialize = true)
    {
        $this->bindingsByKey[$key] = isset($this->json['bindingsByKey'][$key])
            ? unserialize($this->json['bindingsByKey'][$key])
            : array();

        if ($initialize) {
            $this->initializeBindings($this->bindingsByKey[$key]);
        }
    }

    private function reindexBindingsForKey($key)
    {
        $this->bindingsByKey[$key] = array_values($this->bindingsByKey[$key]);

        $this->json['bindingsByKey'][$key] = serialize($this->bindingsByKey[$key]);
    }

    /**
     * Loads the JSON file.
     */
    private function load()
    {
        $decoder = new JsonDecoder();
        $decoder->setObjectDecoding(JsonDecoder::ASSOC_ARRAY);

        $this->json = file_exists($this->path)
            ? $decoder->decodeFile($this->path)
            : array();

        if (!isset($this->json['keysByTypeName'])) {
            $this->json['keysByTypeName'] = array();
            $this->json['keysByUuid'] = array();
            $this->json['typesByKey'] = array();
            $this->json['bindingsByKey'] = array();
            $this->json['nextKey'] = 0;
        }
    }

    /**
     * Writes the JSON file.
     */
    private function flush()
    {
        $this->encoder->encodeFile($this->json, $this->path);
    }
}
