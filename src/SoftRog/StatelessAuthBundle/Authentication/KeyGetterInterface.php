<?php

namespace SoftRog\StatelessAuthBundle\Authentication;

interface KeyGetterInterface
{
  /**
   * Get accessKey
   *
   * @param string $accessKeyId
   * @return string Returns the accessKey
   */
  public function get($accessKeyId);
}
