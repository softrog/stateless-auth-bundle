<?php

namespace SoftRog\StatelessAuthBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Mardy\Hmac\Manager;
use Mardy\Hmac\Adapters\Hash;

class AuthCommand extends ContainerAwareCommand
{

  protected function configure()
  {
    $this
            ->setName('auth:generate:token')
            ->setAliases(['generate:auth:token'])
            ->setDescription('Generates a valid auth hmac token')
            ->addArgument('id', InputArgument::REQUIRED)
            ->addArgument('key', InputArgument::REQUIRED)
            ->addArgument('signer_headers', InputArgument::REQUIRED, 'The headers that will be used (e.g. "host;user-agent"')
            ->addArgument('data', InputArgument::REQUIRED, 'The resulting data of concatenating the signed headers')
            ->addOption('algorithm', 'a', InputArgument::OPTIONAL, '', 'sha256')
            ->addOption('num-first-iterations', 'f', InputOption::VALUE_OPTIONAL, '', 10)
            ->addOption('num-second-iterations', 'd', InputOption::VALUE_OPTIONAL, '', 10)
            ->addOption('num-final-iterations', 'l', InputOption::VALUE_OPTIONAL, '', 100)
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $config = [
        'algorithm' => $input->getOption('algorithm'),
        'num-first-iterations' => $input->getOption('num-first-iterations'),
        'num-second-iterations' => $input->getOption('num-second-iterations'),
        'num-final-iterations' => $input->getOption('num-final-iterations')
    ];

    $time = time();
    $this->manager = new Manager(new Hash);
    $this->manager->config($config);
    $this->manager->key($input->getArgument('key'));
    $this->manager->data($input->getArgument('data'));
    $this->manager->time($time);
    $this->manager->encode();

    $hmac = $this->manager->toArray();

    if ($hmac != null) {
      $output->writeln(sprintf('HMAC-%s Credential=%s/%s, SignedHeaders=%s, Signature=%s',
        strtoupper($input->getOption('algorithm')),
        $input->getArgument('id'),
        $time,
        $input->getArgument('signer_headers'),
        $hmac['hmac']
      ));

      return true;
    }

    throw new \Exception('Error generating the token, make sure that all data is set correctly.');
  }

}
