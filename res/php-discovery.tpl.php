<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\ResourceBindingInterface;

/**
 * @var string|null                $namespace         The namespace without trailing "\".
 * @var string                     $className         The simple class name.
 * @var BindingType[]              $types             All binding types.
 * @var ResourceBindingInterface[] $bindingsById      All bindings by ID.
 * @var int[][]                    $idsByType         All binding IDs by type.
 * @var int[][]                    $idsByResourcePath All binding IDs by resource path.
 */

?>
// This file was generated automatically by Puli's PhpDiscoveryStorage

<?php if (isset($namespace)): ?>
namespace <?php echo $namespace ?>;

<?php endif ?>
use Puli\Discovery\Binding\BindingParameter;
use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\LazyBinding;
use Puli\Discovery\Binding\NoSuchTypeException;
use Puli\Discovery\ResourceDiscoveryInterface;
use Puli\Repository\ResourceRepository;

class <?php echo $className ?> implements ResourceDiscoveryInterface
{
    private $repo;

    private $typeIndex = array(
<?php foreach ($idsByType as $typeName => $ids): ?>
        '<?php echo $typeName ?>' => array(
<?php foreach ($ids as $id): ?>
            <?php echo $id ?>,
<?php endforeach ?>
        ),
<?php endforeach ?>
    );

    private $resourcePathIndex = array(
<?php foreach ($idsByResourcePath as $resourcePath => $ids): ?>
        '<?php echo $resourcePath ?>' => array(
<?php foreach ($ids as $id): ?>
            <?php echo $id ?>,
<?php endforeach ?>
        ),
<?php endforeach ?>
    );

    private $types = array(
<?php foreach ($types as $type): ?>
        '<?php echo $type->getName() ?>' => null,
<?php endforeach ?>
    );

    private $bindings = array(
<?php foreach ($bindingsById as $id => $binding): ?>
        <?php echo $id ?> => null,
<?php endforeach ?>
    );

    public function __construct(ResourceRepository $repo)
    {
        $this->repo = $repo;
    }

    public function isDefined($typeName)
    {
        return array_key_exists($typeName, $this->types);
    }

    public function getType($typeName)
    {
        if (!array_key_exists($typeName, $this->types)) {
            throw new NoSuchTypeException(sprintf(
                'The binding type "%s" has not been defined.',
                $typeName
            ));
        }

        if (!isset($this->types[$typeName])) {
            $this->types[$typeName] = $this->loadType($typeName);
        }

        return $this->types[$typeName];
    }

    public function getTypes()
    {
        foreach ($this->types as $typeName => $type) {
            if (null === $type) {
                $this->types[$typeName] = $this->loadType($typeName);
            }
        }

        return $this->types;
    }

    public function find($typeName)
    {
        return $this->getBindingsByType($typeName);
    }

    public function getBindings($resourcePath = null, $typeName = null)
    {
        if (null === $resourcePath && null === $typeName) {
            return $this->getAllBindings();
        }

        if (null === $resourcePath) {
            return $this->getBindingsByType($typeName);
        }

        if (null === $typeName) {
            return $this->getBindingsByResourcePath($resourcePath);
        }

        return $this->getBindingsByResourcePathAndType($resourcePath, $typeName);
    }

    private function getAllBindings()
    {
        foreach ($this->bindings as $id => $binding) {
            if (null === $binding) {
                $this->bindings[$id] = $this->loadBinding($id);
            }
        }

        return $this->bindings;
    }

    private function getBindingsByType($typeName)
    {
        if (!array_key_exists($typeName, $this->typeIndex)) {
            return array();
        }

        $bindings = array();

        if (isset($this->typeIndex[$typeName])) {
            foreach ($this->typeIndex[$typeName] as $id) {
                if (!isset($this->bindings[$id])) {
                    $this->bindings[$id] = $this->loadBinding($id);
                }

                $bindings[] = $this->bindings[$id];
            }
        }

        return $bindings;
    }

    private function getBindingsByResourcePath($resourcePath)
    {
        if (!array_key_exists($resourcePath, $this->resourcePathIndex)) {
            return array();
        }

        $bindings = array();

        if (isset($this->resourcePathIndex[$resourcePath])) {
            foreach ($this->resourcePathIndex[$resourcePath] as $id) {
                if (!isset($this->bindings[$id])) {
                    $this->bindings[$id] = $this->loadBinding($id);
                }

                $bindings[] = $this->bindings[$id];
            }
        }

        return $bindings;
    }

    private function getBindingsByResourcePathAndType($resourcePath, $typeName)
    {
        if (!array_key_exists($typeName, $this->typeIndex)) {
            return array();
        }

        if (!array_key_exists($resourcePath, $this->resourcePathIndex)) {
            return array();
        }

        $bindings = array();

        if (isset($this->resourcePathIndex[$resourcePath])) {
            foreach ($this->resourcePathIndex[$resourcePath] as $id) {
                if (!isset($this->bindings[$id])) {
                    $this->bindings[$id] = $this->loadBinding($id);
                }

                if ($typeName === $this->bindings[$id]->getType()->getName()) {
                    $bindings[] = $this->bindings[$id];
                }
            }
        }

        return $bindings;
    }

    private function loadType($typeName)
    {
        switch ($typeName) {
<?php foreach ($types as $type): ?>
            case '<?php echo $type->getName() ?>':
                return new BindingType('<?php echo $type->getName() ?>'<?php if ($type->getParameters()): ?>, array(
<?php foreach ($type->getParameters() as $parameter): ?>
                    new BindingParameter('<?php echo $parameter->getName() ?>', <?php var_export($parameter->getMode()) ?>, <?php var_export($parameter->getDefaultValue()) ?>),
<?php endforeach ?>
                )<?php endif ?>);
<?php endforeach ?>
            default:
                return null;
        }
    }

    private function loadBinding($id)
    {
        switch ($id) {
<?php foreach ($bindingsById as $id => $binding): ?>
            case <?php echo $id ?>:
                return new LazyBinding('<?php echo $binding->getPath() ?>', $this->repo, $this->getType('<?php echo $binding->getType()->getName() ?>')<?php if ($binding->getParameters()): ?>, array(
<?php foreach ($binding->getParameters() as $parameter => $value): ?>
                    '<?php echo $parameter ?>' => <?php var_export($value) ?>,
<?php endforeach ?>
                )<?php endif ?>);
<?php endforeach ?>
            default:
                return null;
        }
    }
}
