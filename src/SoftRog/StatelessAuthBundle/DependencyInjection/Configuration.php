<?php

namespace SoftRog\StatelessAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{

  /**
   * {@inheritdoc}
   */
  public function getConfigTreeBuilder()
  {
    $treeBuilder = new TreeBuilder();
    $rootNode = $treeBuilder->root('stateless_auth');

    $rootNode
      ->children()
        ->scalarNode('id')->end()
        ->scalarNode('key')->end()
        ->scalarNode('signed_headers')->defaultValue('Host')->end()
        ->enumNode('algorithm')->values(['sha1', 'md5', 'sha256'])->defaultValue('sha256')->end()
        ->integerNode('ttl')->defaultValue(300)->end()
        ->integerNode('num_first_iterations')->defaultValue(10)->end()
        ->integerNode('num_second_iterations')->defaultValue(10)->end()
        ->integerNode('num_final_iterations')->defaultValue(100)->end()
      ->end();

    return $treeBuilder;
  }

}
