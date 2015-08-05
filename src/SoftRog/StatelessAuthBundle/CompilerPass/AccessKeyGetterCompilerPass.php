<?php

namespace SoftRog\StatelessAuthBundle\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class AccessKeyGetterCompilerPass implements CompilerPassInterface
{

  public function process(ContainerBuilder $container)
  {
    if (!$container->has('stateless_auth.validator')) {
      return;
    }

    $definition = $container->findDefinition(
            'stateless_auth.validator'
    );

    $taggedServices = $container->findTaggedServiceIds(
            'stateless_auth.access_key_getter'
    );

    if (count($taggedServices) > 1) {
      throw new \Exception('Only one AccessKeyGetter service is allowed');
    }

    if (count($taggedServices) == 1) {
      $definition->addMethodCall('setAccessKeyGetter', array(new Reference(key($taggedServices))));
      $container->setDefinition('stateless_auth.access_key_getter', $container->findDefinition(key($taggedServices)));
    }
  }

}
