<?php

namespace SoftRog\StatelessAuthBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class StatelessAuthExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $generatorDefinition = new Definition();
        $generatorDefinition->setClass('SoftRog\\StatelessAuth\\Authentication\\Generator');
        $generatorDefinition->addArgument($config);
        $container->setDefinition('stateless_auth.generator', $generatorDefinition);

        $validatorDefinition = new Definition();
        $validatorDefinition->setClass('SoftRog\\StatelessAuth\\Authentication\\Validator');
        $validatorDefinition->addArgument($config);
        $container->setDefinition('stateless_auth.validator', $validatorDefinition);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

    }
}
