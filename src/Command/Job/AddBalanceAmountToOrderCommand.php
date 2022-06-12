<?php

namespace App\Command\Job;

use App\Repository\OrderRepository;
use App\Service\Order\AddBalanceAmount\AddBalanceAmountService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddBalanceAmountToOrderCommand extends Command
{
    protected static $defaultName = 'timcheh:job:add-balance-amount-to-order';

    public function __construct(
        protected AddBalanceAmountService $addBalanceAmountService,
        protected OrderRepository $orderRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Add balance amount to orders');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $orderIds = $this->orderRepository->getOrderIdsForBalanceAmount();

        $this->addBalanceAmountService->addMany($orderIds);

        $io->success('You have successfully added balance amounts to orders');

        return Command::SUCCESS;
    }
}
