<?php

namespace SoftRog\StatelessAuthBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

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

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('stateless_auth.algorithm', $config['algorithm']);
        $container->setParameter('stateless_auth.ttl', $config['ttl']);
        $container->setParameter('stateless_auth.num_first_iterations', $config['num_first_iterations']);
        $container->setParameter('stateless_auth.num_second_iterations', $config['num_second_iterations']);
        $container->setParameter('stateless_auth.num_final_iterations', $config['num_final_iterations']);
        $container->setParameter('stateless_auth.key_getter_class', $config['key_getter_class']);
    }
}
