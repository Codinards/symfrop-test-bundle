<?php

namespace Njeaner\Symfrop\Command;

use Directory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 0.0.1
 */
#[AsCommand(
    name: 'symfrop:entities',
    description: 'Create all symfrop bundle entities (User, Role, Action).',
)]
class EntityCommand extends Command
{
    public function __construct(private KernelInterface $kernel)
    {
        parent::__construct();
    }
    public static function getCommandName(): string
    {
        return 'symfrop:entities';
    }

    public static function getCommandDescription(): string
    {
        return 'Create all symfrop bundle entities (User, Role, Action).';
    }

    public function configure(): void
    {
        $this
            ->addArgument('auth_folder', InputArgument::OPTIONAL, sprintf('The folder to store auth entities (e.g. <fg=yellow>%s</>)', 'Auth'), 'Auth')
            ->addOption('overwrite', InputOption::VALUE_OPTIONAL)
            ->setHelp(file_get_contents(__DIR__ . '/Resources/help/MakeSymfropEntities.txt'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $auth_dir = $input->getArgument('auth_folder');
        $overWrite = $input->getOption('overwrite');
        $authFolder = $this->kernel->getProjectDir()
            . DIRECTORY_SEPARATOR . 'src'
            . DIRECTORY_SEPARATOR . 'Entity'
            . DIRECTORY_SEPARATOR . $auth_dir;

        if (file_exists($authFolder)) {
            if (
                (file_exists($authFolder . DIRECTORY_SEPARATOR . 'user.php')
                    || file_exists($authFolder . DIRECTORY_SEPARATOR . 'action.php')
                    || file_exists($authFolder . DIRECTORY_SEPARATOR . 'role.php'))
                && !$overWrite
            ) {
                $io->error(
                    'The directory '
                        . $authFolder
                        . ' already exists with User.php, Action.php or Role.php entity files'
                );
                return Command::FAILURE;
            }
        } else {
            mkdir($authFolder);
        }
        foreach (['User.txt', 'Role.txt', 'Action.txt'] as $file) {
            copy(
                dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Entity' . DIRECTORY_SEPARATOR . $file,
                $authFolder . DIRECTORY_SEPARATOR . str_replace('.txt', '.php', $file)
            );
        }

        $io->info(
            "Symfrop auth entity Files have been created in Entity" . DIRECTORY_SEPARATOR . $auth_dir . " directory\n\n"
                . "Created:"
                . "\n\tEntity" . DIRECTORY_SEPARATOR . $auth_dir . DIRECTORY_SEPARATOR . "User.php"
                . "\n\tEntity" . DIRECTORY_SEPARATOR . $auth_dir . DIRECTORY_SEPARATOR . "Role.php"
                . "\n\tEntity" . DIRECTORY_SEPARATOR . $auth_dir . DIRECTORY_SEPARATOR . "Action.php"
        );
        return Command::SUCCESS;
    }
}
