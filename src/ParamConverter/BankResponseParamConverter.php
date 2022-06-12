<?php

namespace App\ParamConverter;

use App\Repository\TransactionRepository;
use App\Service\Payment\Response\Bank\BankResponseFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BankResponseParamConverter
 */
final class BankResponseParamConverter implements ParamConverterInterface
{
    /**
     * @var BankResponseFactory
     */
    private BankResponseFactory $bankResponseFactory;
    /**
     * @var TransactionRepository
     */
    private TransactionRepository $transactionRepository;

    /**
     * BankResponseParamConverter constructor.
     *
     * @param BankResponseFactory $bankResponseFactory
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(BankResponseFactory $bankResponseFactory, TransactionRepository $transactionRepository)
    {
        $this->bankResponseFactory = $bankResponseFactory;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @inheritDoc
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $identifier = $request->attributes->get('identifier');

        $gateway = $this->transactionRepository->getGatewayByIdentifier($identifier);
        if (is_null($gateway)) {
            return;
        }

        $requestData = array_merge($request->request->all(), $request->query->all());

        $request->attributes->set('bankResponse', $this->bankResponseFactory->create($gateway, $requestData));
    }

    /**
     * @inheritDoc
     */
    public function supports(ParamConverter $configuration)
    {
        return 'bankResponse' === $configuration->getName();
    }
}
