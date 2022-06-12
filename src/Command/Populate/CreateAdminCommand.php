<?php

namespace App\Command\Populate;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'timcheh:admins:create';

    public function __construct(
        protected EntityManagerInterface $manager,
        protected UserPasswordHasherInterface $hasher,
        protected AdminRepository $adminRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('createdBy', InputArgument::REQUIRED, 'Email of creator')
            ->addOption('user', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'comma separated values consist of: email,password,mobile,first_name,last_name')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'a csv file of admin users consist of: email,password,mobile,first_name,last_name')
            ->setDescription('Create admin users');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $callback = static function ($v) {
            return array_combine(
                ['email', 'password', 'mobile', 'first_name', 'last_name'],
                array_pad(str_getcsv($v), 5, null)
            );
        };

        if ($admins = $input->getOption('user')) {
            $admins = array_map($callback, $admins);
        } elseif ($file = $input->getOption('file')) {
            $file = realpath($file);

            if (!is_file($file)) {
                throw new InvalidArgumentException("{$file} is not a file");
            }

            $admins = array_map($callback, array_map('trim', file($file)));
        } else {
            $admins = [];
        }

        if (empty($admins)) {
            $io->info('No user provided!');

            return Command::SUCCESS;
        }

        if ($invalidEmails = $this->emailsThatAlreadyExist($admins)) {
            $io->error(sprintf('Email already exist, %s', $invalidEmails));

            return Command::FAILURE;
        }

        $i         = 0;
        $createdAt = new DateTime();
        $createdBy = $input->getArgument('createdBy');

        foreach ($admins as $row) {
            $admin = new Admin();
            $admin->setName($row['first_name'])
                  ->setFamily($row['last_name'])
                  ->setMobile($row['mobile'])
                  ->setEmail($row['email'])
                  ->setCreatedBy($createdBy)
                  ->setUpdatedBy($createdBy)
                  ->setCreatedAt($createdAt)
                  ->setUpdatedAt($createdAt)
                  ->setPassword($this->hasher->hashPassword($admin, $row['password']));

            $this->manager->persist($admin);

            if (100 === $i) {
                $this->manager->flush();
                $this->manager->clear();
                $i = 0;
            }
        }

        $this->manager->flush();
        $this->manager->clear();

        $io->success(sprintf('You have successfully created %d admins', count($admins)));

        return Command::SUCCESS;
    }

    private function emailsThatAlreadyExist(array $admins): false|string
    {
        $emails = array_map(fn(array $admin): string => $admin['email'], $admins);
        $existingAdmins = $this->adminRepository->findBy(['email' => $emails]);
        if (count($existingAdmins) > 0) {
            $invalidEmails = array_map(fn(Admin $admin): string => $admin->getEmail(), $existingAdmins);
            return implode(', ', $invalidEmails);
        }

        return false;
    }
}
