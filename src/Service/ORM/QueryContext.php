<?php

namespace App\Service\ORM;

/**
 * Class QueryContext
 */
class QueryContext
{
    /**
     * @var array
     */
    private $context;

    /**
     * @var string
     */
    private $rootAlias;

    /**
     * @var callable
     */
    private $joinStorage;

    /**
     * QueryContext constructor.
     *
     * @param array $context
     * @param string $rootAlias
     */
    public function __construct(array $context, string $rootAlias, callable $joinStorage)
    {
        $this->context                    = $context;
        $this->rootAlias                  = $rootAlias;
        $this->context['aliases']['root'] = $rootAlias;
        $this->joinStorage                = $joinStorage;
    }

    /**
     * @return bool
     */
    public function hasFilters(): bool
    {
        return isset($this->context['filter']) && !empty($this->context['filter']);
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->hasFilters() ? $this->context['filter'] : [];
    }

    /**
     * @return string
     */
    public function getRootAlias(): string
    {
        return $this->context['aliases']['root'];
    }

    /**
     * @return string
     */
    public function getCurrentAlias(): string
    {
        return $this->context['aliases']['current'] ?? $this->context['aliases']['root'];
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function hasAlias(string $from, string $to): bool
    {
        return isset($this->context['aliases'][$from][$to]);
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return string|null
     */
    public function getAlias(string $from, string $to): ?string
    {
        return $this->hasAlias($from, $to) ? $this->context['aliases'][$from][$to] : null;
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $alias
     */
    public function setAlias(string $from, string $to, string $alias): void
    {
        $this->context['aliases'][$from][$to] = $alias;

        ($this->joinStorage)($from, $to, $alias);
    }

    /**
     * @param string $alias
     */
    public function changeCurrentAlias(string $alias)
    {
        $this->context['aliases']['current'] = $alias;
    }

    /**
     *
     */
    public function unsetCurrentAlias(): void
    {
        unset($this->context['aliases']['current']);
    }

    /**
     * @param array $filters
     *
     * @return QueryContext
     */
    public function withFilters(array $filters): QueryContext
    {
        $context           = $this->context;
        $context['filter'] = $filters;

        return new QueryContext($context, $this->rootAlias, $this->joinStorage);
    }

    /**
     * @return bool
     */
    public function hasSort(): bool
    {
        return isset($this->context['sort']) && !empty($this->context['sort']);
    }

    /**
     * @return array
     */
    public function getSorts(): array
    {
        return $this->hasSort() ? $this->context['sort'] : [];
    }

    /**
     * @param array $sorts
     *
     * @return QueryContext
     */
    public function withSorts(array $sorts): QueryContext
    {
        $context         = $this->context;
        $context['sort'] = $sorts;

        return new QueryContext($context, $this->rootAlias, $this->joinStorage);
    }
}
