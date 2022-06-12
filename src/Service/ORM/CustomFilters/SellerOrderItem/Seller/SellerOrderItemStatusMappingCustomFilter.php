<?php

namespace App\Service\ORM\CustomFilters\SellerOrderItem\Seller;

use App\Dictionary\SellerOrderItemStatusMappingDictionary;
use App\Service\ORM\CustomFilters\CustomFilterInterface;
use App\Service\Seller\SellerOrderItem\Exceptions\InvalidSellerOrderItemStatusException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SellerOrderItemStatusMappingCustomFilter
 */
final class SellerOrderItemStatusMappingCustomFilter implements CustomFilterInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Request $request): void
    {
        $queryParams = $request->query->all();

        if (!isset($queryParams['filter']['status'])) {
            $queryParams['filter']['status'] = ['in' => implode(',', $this->getDefaultStatuses())];

            $request->query->replace($queryParams);

            return;
        }

        $givenStatus = $queryParams['filter']['status'];

        if (is_string($givenStatus)) {
            $queryParams['filter']['status'] = ['in' => implode(',', $this->getMappedStatuses($givenStatus))];

            $request->query->replace($queryParams);

            return;
        }

        $statuses = collect(explode(',', current($givenStatus)))->map(fn(string $v) => trim($v))
                                                                ->filter()
                                                                ->values()
                                                                ->flatMap(function (string $status) {
                                                                    return $this->getMappedStatuses($status);
                                                                })
                                                                ->unique()
                                                                ->toArray();

        $queryParams['filter']['status'] = ['in' => implode(',', $statuses)];

        $request->query->replace($queryParams);
    }

    private function getDefaultStatuses(): array
    {
        return array_merge(
            ...array_values($this->mapToGroups(SellerOrderItemStatusMappingDictionary::getDefaultStatuses()))
        );
    }

    private function getMappedStatuses(string $status): array
    {
        $mappedStatuses = $this->mapToGroups(SellerOrderItemStatusMappingDictionary::toArray());

        if (!isset($mappedStatuses[$status])) {
            throw new InvalidSellerOrderItemStatusException();
        }

        return $mappedStatuses[$status];
    }

    private function mapToGroups(array $statuses): array
    {
        return collect($statuses)->mapToGroups(fn(string $value, string $key) => [$value => $key])
                                 ->toArray();
    }
}
