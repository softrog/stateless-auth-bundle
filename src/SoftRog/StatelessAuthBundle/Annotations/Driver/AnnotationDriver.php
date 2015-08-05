<?php

namespace SoftRog\StatelessAuthBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use SoftRog\StatelessAuth\Authentication;
use Symfony\Component\HttpFoundation\RequestStack;

class AnnotationDriver
{

  const STATE_LESS_ANNOTATION = 'SoftRog\StatelessAuthBundle\Annotations\StatelessAuth';

  /** @var Reader */
  private $reader;

  /** @var Authentication\Validator */
  private $validator;

  /** @var RequestStack */
  private $requestStack;

  public function __construct(Reader $reader)
  {
    $this->reader = $reader;
  }

  public function setValidator(Authentication\Validator $validator)
  {
    $this->validator = $validator;
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

    $request = $this->requestStack->getCurrentRequest();
    $headers = $request->headers->all();
    $token = $request->headers->get('Authorization');

    if ($annotation && !$this->validator->validate($token, $headers)) {
      throw new AccessDeniedHttpException('Invalid authentication');
    }

    return true;
  }

}
