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
 * @version 1.0.0
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

        $repositoryFolder = $this->kernel->getProjectDir()
            . DIRECTORY_SEPARATOR . 'src'
            . DIRECTORY_SEPARATOR . 'Repository'
            . DIRECTORY_SEPARATOR . $auth_dir;

        if (!file_exists($authFolder)) {
            mkdir($authFolder);
        }

        if (!file_exists($repositoryFolder)) {
            mkdir($repositoryFolder);
        }

        foreach (['User', 'Role', 'Action'] as $file) {
            $content = str_replace('$authFolder', $auth_dir ? ('\\' . $auth_dir) : '', file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Entity' . DIRECTORY_SEPARATOR . $file . '.txt'));
            file_put_contents($authFolder . DIRECTORY_SEPARATOR . $file . '.php', $content);
            $content = str_replace('$authFolder', ($auth_dir ? ('\\' . $auth_dir) : ''), file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Repository' . DIRECTORY_SEPARATOR . $file . 'Repository.txt'));
            $content = str_replace('$namespace$', 'App\Entity' . ($auth_dir ? ('\\' . $auth_dir) : '') . '\\' . $file, $content);
            $content = str_replace('$entity_name$', $file, $content);
            file_put_contents($repositoryFolder . DIRECTORY_SEPARATOR . $file . 'Repository.php', $content);
        }

        $io->info(
            "Symfrop auth entity Files have been created in Entity" . DIRECTORY_SEPARATOR . $auth_dir . " directory\n\n"
                . "Created:"
                . "\n\tApp\Entity\\" . $auth_dir .  "\User.php\n"
                . "\tApp\Entity\\" . $auth_dir .  "\Role.php\n"
                . "\tApp\Entity\\" . $auth_dir .  "\Action.php\n"
                . "\tApp\Repository\\" . $auth_dir .  "\UserRepository.php\n"
                . "\tApp\Repository\\" . $auth_dir .  "\RoleRepository.php\n"
                . "\tApp\Repository\\" . $auth_dir .  "\ActionRepository.php\n"
        );
        return Command::SUCCESS;
    }
}
