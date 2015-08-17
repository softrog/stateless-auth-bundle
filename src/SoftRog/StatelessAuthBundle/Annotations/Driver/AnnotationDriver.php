<?php

namespace SoftRog\StatelessAuthBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader;
use SoftRog\StatelessAuth\Authentication;
use SoftRog\StatelessAuthBundle\Exception\AuthorizationHeaderNotFoundException;
use SoftRog\StatelessAuthBundle\Exception\InvalidAuthorizationException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class AnnotationDriver
{

  const STATE_LESS_ANNOTATION = 'SoftRog\StatelessAuthBundle\Annotations\Authenticated';

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

    if (!empty($this->reader->getClassAnnotation($class, self::STATE_LESS_ANNOTATION))) {
      $this->handleAuthenticated($class);
    }
  }

  protected function handleAuthenticated($class)
  {
    $request = $this->requestStack->getCurrentRequest();
    $headers = $request->headers->all();
    $token = $request->headers->get('Authorization');

    if (empty($token)) {
      throw new AuthorizationHeaderNotFoundException();
    }

    if (!$this->validator->validate($token, $headers)) {
      throw new InvalidAuthorizationException();
    }
  }

}
