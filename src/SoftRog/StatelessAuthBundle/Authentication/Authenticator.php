<?php

namespace SoftRog\StatelessAuthBundle\Authentication;

use Mardy\Hmac\Manager;
use Mardy\Hmac\Adapters\Hash;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Authenticator implements ContainerAwareInterface
{

  /** @ContainerInterface */
  private $container;

  /** @var Manager */
  private $manager;

  /** @var KeyGetterInterface */
  private $keyGetter;

  public function setContainer(ContainerInterface $container = null)
  {
    $this->container = $container;

    if ($container->hasParameter('stateless_auth.key_getter_class')) {
      $keyGetterClass = $container->getParameter('stateless_auth.key_getter_class');
      $this->keyGetter = new $keyGetterClass();

      if ($this->keyGetter instanceof ContainerAwareInterface) {
        $this->keyGetter->setContainer($container);
      }
    }
  }

  public function validate()
  {
    if (!$this->keyGetter instanceof \SoftRog\StatelessAuthBundle\AccessKeyGetter\AccessKeyGetterInterface) {
      throw new \SoftRog\StatelessAuthBundle\AccessKeyGetter\Exception\InvalidAccessKeyGetterException();
    }

    $header = $this->getRequest()->headers->get('Authorization');

    $pattern = "/^HMAC-(?<algorithm>[^ ]+)\s*Credential=(?<id>[^\/]+)\/(?<time>\d+),\s*SignedHeaders=(?<signed_headers>[^,]+),\s*Signature=(?<signature>[^\s]+)\s*$/";
    if ($header && preg_match($pattern, $header, $matches)) {
      $headers = $this->getRequest()->headers;
      $data = array_reduce(explode(';', $matches['signed_headers']), function ($carry, $item) use ($headers) {
        return $carry . $headers->get($item);
      });

      file_put_contents('/tmp/authenticator.log', var_export($data, true), FILE_APPEND);
      $algorithm = $matches['algorithm'];
      $id = $matches['id'];
      $key = $this->keyGetter->get($id);
      $time = $matches['time'];
      $hmac = $matches['signature'];

      $this->reset($algorithm);
      $this->manager->ttl($this->container->getParameter('stateless_auth.ttl'));
      $this->manager->key($key);
      $this->manager->data($data);
      $this->manager->time($time);

      if ($this->manager->isValid($hmac)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Get current request
   *
   * @return Request
   */
  private function getRequest()
  {
    return $this->container->get('request_stack')->getCurrentRequest();
  }

  private function reset($algorithm)
  {
    $config = [
        'algorithm' => $algorithm,
        'num-first-iterations' => $this->container->getParameter('stateless_auth.num_first_iterations'),
        'num-second-iterations' => $this->container->getParameter('stateless_auth.num_second_iterations'),
        'num-final-iterations' => $this->container->getParameter('stateless_auth.num_final_iterations')
    ];

    $this->manager = new Manager(new Hash);
    $this->manager->config($config);
    $this->manager->ttl($this->container->getParameter('stateless_auth.ttl'));
  }

}
