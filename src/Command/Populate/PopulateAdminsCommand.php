<?php

namespace App\Command\Populate;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PopulateAdminsCommand extends Command
{
    protected static $defaultName = 'timcheh:populate:admins';

    public function __construct(
        protected EntityManagerInterface $manager,
        protected UserPasswordHasherInterface $hasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Creating default admins.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->block('Creating default admins');

        $admin = new Admin();

        $admin->setName("Farhad")
              ->setFamily("Zand")
              ->setMobile("09120724013")
              ->setEmail("f.zand@lendo.ir")
              ->setPassword($this->hasher->hashPassword($admin, '12345678'));

        $this->manager->persist($admin);
        $this->manager->flush();

        $io->success('You have successfully populated default admins');

        return Command::SUCCESS;
    }
}
