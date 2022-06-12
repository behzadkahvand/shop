<?php

/**
 * User: amir
 * Date: 12/2/20
 * Time: 11:54 AM
 */

namespace App\Service\Promotion;

use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Form\Promotion\PromotionCouponType;
use App\Form\Promotion\PromotionType;
use App\Service\Customer\CustomerService;
use App\Service\Promotion\DTO\PromotionCouponDTO;
use App\Service\Promotion\Exception\CouponHasEmptyCodeException;
use App\Service\Utils\CsvService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class PromotionCouponFormSubmissionHandler
{
    private EntityManagerInterface $entityManager;

    private FormFactoryInterface $formFactory;

    private CsvService $csvService;

    private CustomerService $customerService;

    private PromotionCouponService $couponService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        CsvService $csvService,
        CustomerService $customerService,
        PromotionCouponService $couponService
    ) {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->csvService = $csvService;
        $this->customerService = $customerService;
        $this->couponService = $couponService;
    }

    public function submit(PromotionCoupon $promotionCoupon, $data)
    {
        $dto = new PromotionCouponDTO();
        $form = $this->formFactory->create(PromotionCouponType::class, $dto);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $customersCsv = $form->get('customersCsv')->getData();
            if ($customersCsv instanceof UploadedFile) {
                $customers = $this->getCustomerCollectionFromFile($customersCsv);
                foreach ($customers as $customer) {
                    $dto->addCustomer($customer);
                }
            }

            $this->couponService->updateFromDTO($promotionCoupon, $dto);

            $this->entityManager->flush();
        }

        return $form;
    }

    private function getCustomerCollectionFromFile(UploadedFile $customersCsv)
    {
        $customers = $this->csvService->getFirstColumnFromUploadedFile($customersCsv);

        return  $this->customerService->getCustomersByMobileList($customers);
    }
}
