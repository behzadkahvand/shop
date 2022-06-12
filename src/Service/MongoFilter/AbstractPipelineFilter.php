<?php

namespace App\Service\MongoFilter;

use App\Service\Pipeline\AbstractPipelinePayload;

abstract class AbstractPipelineFilter
{
    abstract protected function filterName(): string;

    /**
     * @param FilterPayload $payload
     */
    public function __invoke(AbstractPipelinePayload $payload)
    {
        if (!$this->validate($payload)) {
            return $payload;
        }

        return $this->doInvoke($payload);
    }

    abstract protected function doInvoke(FilterPayload $filterPayload);

    protected function validate(FilterPayload $filterPayload): bool
    {
        return isset($filterPayload->getRequestFilters()['filter'][$this->filterName()]);
    }

    public static function getTag(): string
    {
        return "app.pipeline_stage.mongo_query_builder";
    }
}
