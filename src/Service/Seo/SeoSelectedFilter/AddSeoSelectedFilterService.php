<?php

namespace App\Service\Seo\SeoSelectedFilter;

use App\DTO\Admin\Seo\SeoSelectedFilterData;
use App\Entity\Seo\SeoSelectedFilter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class AddSeoSelectedFilterService
{
    protected SeoSelectedFilterFactory $factory;

    protected EntityManagerInterface $manager;

    public function __construct(SeoSelectedFilterFactory $factory, EntityManagerInterface $manager)
    {
        $this->factory = $factory;
        $this->manager = $manager;
    }

    public function perform(SeoSelectedFilterData $data): SeoSelectedFilter
    {
        $this->manager->beginTransaction();

        try {
            $seoSelectedFilter = $this->factory->getSeoSelectedFilter($data->getEntity());

            $seoSelectedFilter
                ->setEntity($data->getEntity())
                ->setCategory($data->getCategory())
                ->setTitle($data->getTitle())
                ->setDescription($data->getDescription())
                ->setMetaDescription($data->getMetaDescription())
                ->setStarred($data->isStarred());

            $this->manager->persist($seoSelectedFilter);
            $this->manager->flush();
            $this->manager->commit();
        } catch (Exception $e) {
            $this->manager->close();
            $this->manager->rollBack();

            throw $e;
        }

        return $seoSelectedFilter;
    }
}
