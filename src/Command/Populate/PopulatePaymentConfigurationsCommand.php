<?php

namespace App\Command\Populate;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Configuration;
use App\Service\Payment\Gateways\VandarGateway;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulatePaymentConfigurationsCommand extends Command
{
    protected static $defaultName = 'timcheh:populate:payment-configurations';

    public function __construct(protected EntityManagerInterface $manager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Populate default configuration')
             ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
             ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $CPGGatewayConfig = new Configuration();
        $CPGGatewayConfig->setCode(ConfigurationCodeDictionary::CPG_GATEWAY_AVAILABILITY)
                         ->setValue(false);

        $hamrahCardGatewayConfig = new Configuration();
        $hamrahCardGatewayConfig->setCode(ConfigurationCodeDictionary::HAMRAH_CARD_GATEWAY_AVAILABILITY)
                                ->setValue(false);

        $defaultOnlineGatewayConfig = new Configuration();
        $defaultOnlineGatewayConfig->setCode(ConfigurationCodeDictionary::DEFAULT_ONLINE_GATEWAY)
                                   ->setValue(VandarGateway::getName());

        $offlineGatewayAvailability = new Configuration();
        $offlineGatewayAvailability->setCode(ConfigurationCodeDictionary::OFFLINE_GATEWAY_AVAILABILITY)
                                   ->setValue(true);

        $offlineGatewayAvailability = new Configuration();
        $offlineGatewayAvailability->setCode(ConfigurationCodeDictionary::CHECK_INITIAL_INVENTORY_STATUS)
                                   ->setValue(false);

        $this->manager->persist($CPGGatewayConfig);
        $this->manager->persist($hamrahCardGatewayConfig);
        $this->manager->persist($defaultOnlineGatewayConfig);
        $this->manager->persist($offlineGatewayAvailability);
        $this->manager->flush();

        $io->success('You have successfully populated payment configurations!');

        return Command::SUCCESS;
    }
}
