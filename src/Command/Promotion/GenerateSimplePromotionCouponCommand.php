<?php

namespace App\Command\Promotion;

use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Service\Promotion\PromotionCouponFormSubmissionHandler;
use App\Service\Promotion\PromotionFormSubmissionHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class GenerateSimplePromotionCouponCommand extends Command
{
    protected static $defaultName = 'promotion:coupon:generate-simple';

    public function __construct(
        protected PromotionFormSubmissionHandler $promotionFormSubmissionHandler,
        protected PromotionCouponFormSubmissionHandler $promotionCouponFormSubmissionHandler,
        protected EntityManagerInterfac $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate a simple promotion coupon with fixed discount')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of promotion')
            ->addArgument('coupon_code', InputArgument::REQUIRED, 'Coupon code')
            ->addArgument('fixed_amount', InputArgument::REQUIRED, 'Coupon code')
            ->addOption('minimum-basket-total', 'm', InputOption::VALUE_REQUIRED, 'Minimum basket total')
            ->addOption('max-total-usage', 'x', InputOption::VALUE_REQUIRED, 'Usage limit')
            ->addOption('customer-id', 'c', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Customer ids for user based coupon')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fixedAmount = $input->getArgument('fixed_amount');

        if (!is_numeric($fixedAmount)) {
            $io->error('"fixed_amount" argument must be numeric');

            return Command::FAILURE;
        }

        $promotion = new Promotion();

        $this->entityManager->beginTransaction();

        $rules = [];

        if ($minimumBasketTotal = $input->getOption('minimum-basket-total')) {
            $rules[] = [
                'type' => 'minimum_basket_total',
                'configuration' => [
                    'basket_total' => $minimumBasketTotal,
                ],
            ];
        }

        try {
            $promotionForm = $this->promotionFormSubmissionHandler->submit($promotion, [
                'name' => $input->getArgument('name'),
                'priority' => 1,
                'couponBased' => true,
                'enabled' => true,
                'rules' => $rules,
                'actions' => [
                    [
                        'type' => 'fixed_discount',
                        'configuration' => [
                            'amount' => $fixedAmount
                        ],
                    ]
                ]
            ]);

            if (!$promotionForm->isValid()) {
                $this->entityManager->close();
                $this->entityManager->rollback();
                $io->error("There is a problem in creating promotion");

                return Command::FAILURE;
            }

            $promotionCoupon = new PromotionCoupon();
            $promotion->addCoupon($promotionCoupon);

            $couponData = [
                'code' => $input->getArgument('coupon_code'),
                'expiresAt' => (new DateTime('+3 months'))->format('Y-m-d'),
                'perCustomerUsageLimit' => 1,
            ];

            if ($maximumTotalUsage = $input->getOption('max-total-usage')) {
                $couponData['usageLimit'] = $maximumTotalUsage;
            }

            if ($customerIds = (array) $input->getOption('customer-id')) {
                $couponData['customers'] = $customerIds;
            }

            $promotionCouponForm = $this->promotionCouponFormSubmissionHandler->submit($promotionCoupon, $couponData);

            if (!$promotionCouponForm->isValid()) {
                $this->entityManager->close();
                $this->entityManager->rollback();
                $io->error("There is a problem in creating promotion coupon");

                return Command::FAILURE;
            }

            $this->entityManager->commit();
        } catch (Throwable $exception) {
            $this->entityManager->close();
            $this->entityManager->rollback();
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success("Promotion coupon is been created successfully.");

        return Command::SUCCESS;
    }
}
