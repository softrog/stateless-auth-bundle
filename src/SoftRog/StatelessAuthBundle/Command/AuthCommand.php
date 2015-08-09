<?php

namespace SoftRog\StatelessAuthBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use SoftRog\StatelessAuth\Authentication\Generator;

class AuthCommand extends ContainerAwareCommand
{

  protected function configure()
  {
    $this
      ->setName('auth:generate:token')
      ->setAliases(['generate:auth:token'])
      ->setDescription('Generates a valid auth hmac token')
      ->addArgument('accessKeyId', InputArgument::REQUIRED)
      ->addArgument('accessKey', InputArgument::OPTIONAL)
      ->addOption('headers', 'H', InputOption::VALUE_OPTIONAL,
        'The headers that will be used in json',
        sprintf('{\'host\':\'%s\'}', gethostname())
      )
      ->addOption('algorithm', 'a', InputOption::VALUE_OPTIONAL, '', 'sha256')
      ->addOption('num-first-iterations', 'f', InputOption::VALUE_OPTIONAL, '', 10)
      ->addOption('num-second-iterations', 'd', InputOption::VALUE_OPTIONAL, '', 10)
      ->addOption('num-final-iterations', 'l', InputOption::VALUE_OPTIONAL, '', 100)
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $headers = json_decode($input->getOption('headers'), true);

    if (empty($headers)) {
      $message = sprintf('Invalid json format for headers (\'%s\')', $input->getArgument('headers'));
      throw new \Exception($message);
    }

    if (empty($input->getArgument('accessKey'))) {
      $accessKey = $this->findAccessKey($input->getArgument('accessKeyId'));
    } else {
      $accessKey = $input->getArgument('accessKey');
    }

    $config = [
        'id' => $input->getArgument('accessKeyId'),
        'key' => $accessKey,
        'algorithm' => $input->getOption('algorithm'),
        'num_first_iterations' => $input->getOption('num-first-iterations'),
        'num_second_iterations' => $input->getOption('num-second-iterations'),
        'num_final_iterations' => $input->getOption('num-final-iterations'),
        'signed_headers' => implode(';', array_keys($headers)),
    ];

    $generator = new Generator($config);
    $output->writeln(sprintf('<info>%s</info>', $generator->generate($headers)));
  }

  protected function findAccessKey($accessKeyId)
  {
    $getter = $this->getContainer()->get('stateless_auth.access_key_getter');
    $accessKey = $getter->get($accessKeyId);

    if (empty($accessKey)) {
      throw new \Exception(
          sprintf('Impossible to find accessKey for accessKeyId \'%s\'', $accessKeyId)
      );
    }

    return $accessKey;
  }

}
