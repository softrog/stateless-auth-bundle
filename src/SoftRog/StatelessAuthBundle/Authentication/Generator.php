<?php

namespace SoftRog\StatelessAuthBundle\Authentication;

use Mardy\Hmac\Manager;
use Mardy\Hmac\Adapters\Hash;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Generator implements ContainerAwareInterface
{

  /** @ContainerInterface */
  private $container;

  /** @var Manager */
  private $manager;

  /**
   * {@inheritdoc}
   */
  public function setContainer(ContainerInterface $container = null)
  {
    $this->container = $container;
  }

  /**
   * Generate a valid token.
   *
   * @param array $headers
   * @return string|null
   * @throws \SoftRog\StatelessAuthBundle\Exception\InvalidStatelessAuthCredentialsException
   */
  public function generate($headers)
  {
    if (!$this->container->hasParameter('stateless_auth.id') || !$this->container->hasParameter('stateless_auth.key')) {
      throw new \SoftRog\StatelessAuthBundle\Exception\InvalidStatelessAuthCredentialsException();
    }

    $this->reset($this->container->getParameter('stateless_auth.algorithm'));

    $data = "";
    $signedHeaders = $this->container->getParameter('stateless_auth.signed_headers');
    foreach (explode(';', $signedHeaders) as $signedHeader) {
      $data .= is_array($headers[$signedHeader])? implode(';', $headers[$signedHeader]) : $headers[$signedHeader];
    }

    $time = time();

    $this->manager->key($this->container->getParameter('stateless_auth.key'));
    $this->manager->data($data);
    $this->manager->time($time);
    $this->manager->encode();

    $hmac = $this->manager->toArray();

    if ($hmac != null) {
      return sprintf('HMAC-%s Credential=%s/%s, SignedHeaders=%s, Signature=%s',
              strtoupper($this->container->getParameter('stateless_auth.algorithm')),
              $this->container->getParameter('stateless_auth.id'),
              $time,
              $signedHeaders,
              $hmac['hmac']
      );
    }

    return null;
  }

  /**
   * Reset the manager
   *
   * @param type $algorithm
   */
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
