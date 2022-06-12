<?php

namespace App\Form;

use App\DTO\Customer\OrderAddressData;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Repository\CustomerRepository;
use Closure;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CustomerOrderAddressType extends AbstractType
{
    private Security $security;

    /**
     * @var CustomerRepository
     */
    private CustomerRepository $customerRepository;

    public function __construct(Security $security, CustomerRepository $customerRepository)
    {
        $this->security = $security;
        $this->customerRepository = $customerRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customerAddress', EntityType::class, [
                'class' => CustomerAddress::class,
                'constraints' => [
                    new Callback([
                        'callback' => $this->getCustomerAddressValidator(),
                        'groups' => $options['validation_groups'],
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderAddressData::class,
            'allow_extra_fields' => true
        ]);
    }

    private function getCustomerAddressValidator(): Closure
    {
        return function (CustomerAddress $payload, ExecutionContextInterface $context) {
            $user = $this->security->getUser();

            if (!$user instanceof Customer) {
                throw new \LogicException('This form should be used in customer context!');
            }

            if ($payload->getCustomer()->getId() == $user->getId()) {
                return;
            }

            $context->buildViolation('The value you selected is not belong to current user.')
                ->atPath('customerAddress')
                ->addViolation();
        };
    }
}
