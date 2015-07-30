<?php

namespace SoftRog\StatelessAuthBundle\AccessKeyGetter;

use SoftRog\StatelessAuthBundle\AccessKeyGetter\AccessKeyGetterInterface;
use SoftRog\StatelessAuthBundle\AccessKeyGetter\Exception\AccessKeyNotFoundException;
use Symfony\Component\DependencyInjection\ContainerAware;

class DoctrineAccessKeyGetter extends ContainerAware implements AccessKeyGetterInterface
{

  /**
   * Get the accessKey for $accessKeyId
   *
   * @param string $accessKeyId
   * @return string
   * @throws AccessKeyNotFoundException
   */
  public function get($accessKeyId)
  {
    $em = $this->container->get('doctrine')->getManager();
    $entity = $em->getRepository('StatelessAuthBundle:AccessKey')->findOneByAccessKeyId($accessKeyId);

    if ($entity) {
      return $entity->getAccessKey();
    }

    throw new AccessKeyNotFoundException($accessKeyId);
  }

}
