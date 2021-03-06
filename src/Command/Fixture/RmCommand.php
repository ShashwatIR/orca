<?php

namespace Acquia\Orca\Command\Fixture;

use Acquia\Orca\Command\StatusCodes;
use Acquia\Orca\Fixture\Remover;
use Acquia\Orca\Fixture\Facade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Provides a command.
 *
 * @property \Acquia\Orca\Fixture\Facade $facade
 * @property \Acquia\Orca\Fixture\Remover $remover
 */
class RmCommand extends Command {

  protected static $defaultName = 'fixture:rm';

  /**
   * {@inheritdoc}
   */
  public function __construct(Facade $facade, Remover $remover) {
    $this->facade = $facade;
    $this->remover = $remover;
    parent::__construct(self::$defaultName);
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setAliases(['rm'])
      ->setDescription('Removes the test fixture')
      ->setHelp('Removes the entire site build directory and Drupal database.')
      ->addOption('force', 'f', InputOption::VALUE_NONE, 'Remove without confirmation');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    if (!$this->facade->exists()) {
      $output->writeln("Error: No fixture exists at {$this->facade->rootPath()}.");
      return StatusCodes::ERROR;
    }

    /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
    $helper = $this->getHelper('question');
    $question = new ConfirmationQuestion('Are you sure you want to remove the test fixture? ');
    if (
      !$input->getOption('force')
      && ($input->getOption('no-interaction') || !$helper->ask($input, $output, $question))
    ) {
      return StatusCodes::USER_CANCEL;
    }

    $this->remover->remove();
    return StatusCodes::OK;
  }

}
