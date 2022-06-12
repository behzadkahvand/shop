<?php

namespace App\Command\Job;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\Notification\DTOs\Customer\RateAndReview\RateAndReviewSmsNotificationDTO;
use App\Service\Notification\NotificationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RateAndReviewSmsCommand extends Command
{
    protected static $defaultName = 'timcheh:job:sms:rate-and-review';

    public function __construct(
        protected NotificationService $notificationService,
        protected CustomerRepository $customerRepository
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDescription(
            'This job will send a sms notification to user and invite him/her to rate on purchased product'
        );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->findCustomersWithDeliveredShipmentSince2DayAgo() as $customer) {
            $notification = new RateAndReviewSmsNotificationDTO($customer, 'https://zaya.io/t45bl');

            $this->notificationService->send($notification);
        }

        $io->success('You have successfully send sms notification for product rate and review!');

        return Command::SUCCESS;
    }

    /**
     * @return Customer[]
     */
    private function findCustomersWithDeliveredShipmentSince2DayAgo(): iterable
    {
        yield from $this->customerRepository->findCustomersWithDeliveredShipmentSince2DayAgo();
    }
}
