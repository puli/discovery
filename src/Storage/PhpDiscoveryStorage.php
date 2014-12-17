<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Storage;

use Puli\Discovery\ResourceDiscovery;
use Puli\Repository\ResourceRepository;

/**
 * Generates a resource discovery as PHP file.
 *
 * The storage supports the following options:
 *
 *  * "path": The path of the stored file. This option is mandatory.
 *  * "namespace": The namespace of the generated class without trailing "\".
 *    This is option `null` by default, meaning that the class is generated in
 *    the global namespace.
 *  * "className" The class name (without namespace) of the generated class.
 *    The class name is "GeneratedPhpDiscovery" by default.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PhpDiscoveryStorage implements DiscoveryStorage
{
    /**
     * @var array
     */
    private static $defaultOptions = array(
        'namespace' => null,
        'className' => 'GeneratedPhpDiscovery',
    );

    /**
     * {@inheritdoc}
     */
    public function storeDiscovery(ResourceDiscovery $discovery, array $options = array())
    {
        if (!isset($options['path'])) {
            throw new \InvalidArgumentException('The "path" option is missing.');
        }

        if (!file_exists($dir = dirname($options['path']))) {
            mkdir($dir, 0777, true);
        }

        $vars = array_replace(
            self::$defaultOptions,
            $options,
            $this->createVariables($discovery)
        );

        $template = $this->generateTemplate($vars);

        file_put_contents($options['path'], $template);
    }

    /**
     * {@inheritdoc}
     */
    public function loadDiscovery(ResourceRepository $repo, array $options = array())
    {
        if (!isset($options['path'])) {
            throw new \InvalidArgumentException('The "path" option is missing.');
        }

        $options = array_replace(self::$defaultOptions, $options);
        $className = $options['namespace'].'\\'.$options['className'];

        if (!file_exists($options['path'])) {
            throw new LoadingException(sprintf(
                'Could not load resource discovery: The file %s was not found.',
                $options['path']
            ));
        }

        require_once $options['path'];

        // false: disable autoloading
        if (!class_exists($className, false)) {
            throw new LoadingException(sprintf(
                'Could not load resource discovery: The file %s did not '.
                'an implementation of %s.',
                $options['path'],
                $className
            ));
        }

        return new $className($repo);
    }

    private function generateTemplate($vars)
    {
        extract($vars);

        ob_start();

        require __DIR__.'/../../res/php-discovery.tpl.php';

        return "<?php\n\n".ob_get_clean();
    }

    private function createVariables(ResourceDiscovery $discovery)
    {
        $vars = array(
            'bindingsById' => array(),
            'idsByType' => array(),
            'idsByResourcePath' => array(),
            'types' => $discovery->getTypes(),
        );

        $nextId = 0;

        foreach ($discovery->getBindings() as $binding) {
            $id = $nextId++;
            $typeName = $binding->getType()->getName();

            if (!isset($vars['idsByType'][$typeName])) {
                $vars['idsByType'][$typeName] = array();
            }

            $vars['bindingsById'][$id] = $binding;
            $vars['idsByType'][$typeName][] = $id;

            foreach ($binding->getResources() as $resource) {
                $resourcePath = $resource->getPath();

                if (!isset($vars['idsByResourcePath'][$resourcePath])) {
                    $vars['idsByResourcePath'][$resourcePath] = array();
                }

                $vars['idsByResourcePath'][$resourcePath][] = $id;

            }
        }

        return $vars;
    }
}
