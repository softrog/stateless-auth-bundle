<?php

namespace SoftRog\StatelessAuthBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use SoftRog\StatelessAuthBundle\Authentication\Authenticator;

class AnnotationDriver
{

  const STATE_LESS_ANNOTATION = 'SoftRog\StatelessAuthBundle\Annotations\StatelessAuth';

  /** @var Reader */
  private $reader;

  /** @var Authenticator */
  private $authenticator;

  /** @var RequestStack */
  private $requestStack;

  public function __construct(Reader $reader, Authenticator $authenticator)
  {
    $this->reader = $reader;
    $this->authenticator = $authenticator;
  }

  public function setRequestStack(RequestStack $requestStack)
  {
    $this->requestStack = $requestStack;
  }

  /**
   * This event will fire during any controller call
   */
  public function onKernelController(FilterControllerEvent $event)
  {
    if (!is_array($controller = $event->getController())) {
      return;
    }

    $class = new \ReflectionClass(get_class($controller[0])); // get controller

    $this->handleStatelessAuth($class);
  }

  protected function handleStatelessAuth($class)
  {
    $annotation = $this->reader->getClassAnnotation($class, self::STATE_LESS_ANNOTATION);
    //echo $this->authenticator->create(); exit;
    if ($annotation && !$this->authenticator->validate()) {
      throw new AccessDeniedHttpException('Invalid authentication');
    }

    return true;
  }

}
