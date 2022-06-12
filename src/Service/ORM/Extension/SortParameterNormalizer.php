<?php

namespace App\Service\ORM\Extension;

/**
 * Class SortParameterNormalizer.
 */
final class SortParameterNormalizer implements \Iterator
{
    /**
     * @var int
     */
    private $position;

    /**
     * @var array
     */
    private $sortData;

    /**
     * SortParameterNormalizer constructor.
     *
     * @param array $sortData
     */
    public function __construct(array $sortData)
    {
        $this->position = 0;
        $this->sortData = $sortData;
    }

    /**
     * @param array $sortData
     *
     * @return array
     */
    public static function toArray(array $sortData): array
    {
        return iterator_to_array(new static(array_values($sortData)));
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->extractSortData($this->sortData[$this->position]);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return isset($this->sortData[$this->position]);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @param $sortData
     *
     * @return array
     */
    private function extractSortData($sortData): array
    {
        $isHyphened = '-' === $sortData[0];

        return [
            'field'            => $isHyphened ? substr($sortData, 1) : $sortData,
            'direction'        => $isHyphened ? 'DESC' : 'ASC',
            'direction_prefix' => $isHyphened ? '-' : '',
        ];
    }
}
