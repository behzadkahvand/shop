<?php

namespace App\Command\Populate;

use App\Dictionary\CityDictionary;
use App\Entity\City;
use App\Repository\CityRepository;
use App\Repository\ProvinceZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulateFixZonesCommand extends Command
{
    protected static $defaultName = 'timcheh:populate:fix-zones';

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ProvinceZoneRepository $provinceZoneRepository,
        protected CityRepository $cityRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Convert provinces zones defined in timcheh:populate:zones command to city zone');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $confirmed = $io->confirm('WARNING! You are about to remove all province zones and all the provinces in that zones. Are you sure?', false);

        if (!$confirmed) {
            $io->error('Fixing zones cancelled!');

            return 3;
        }

        $expressCityIds = array_map(function (City $city) {
            return $city->getId();
        }, $this->cityRepository->findBy(['name' => CityDictionary::EXPRESS_CITIES]));

        $nonExpressCities = $this->cityRepository->createQueryBuilder('city')
                                                 ->select('city.id')
                                                 ->where('city.id NOT IN (:expressCities)')
                                                 ->setParameter('expressCities', $expressCityIds)
                                                 ->getQuery()
                                                 ->getResult();

        $nonExpressCityIds = array_column($nonExpressCities, 'id');
        $expressZoneId     = $this->provinceZoneRepository->findOneBy(['code' => 'tehran'])->getId();
        $nonExpressZoneId  = $this->provinceZoneRepository->findOneBy(['code' => 'provinces-excluding-tehran'])->getId();
        $pivotTableQuery   = 'INSERT INTO city_zone_city (city_zone_id, city_id) VALUES ';

        foreach ($expressCityIds as $expressCityId) {
            $pivotTableQuery .= sprintf('(%d, %d), ', $expressZoneId, $expressCityId);
        }

        foreach ($nonExpressCityIds as $nonExpressCityId) {
            $pivotTableQuery .= sprintf('(%d, %d), ', $nonExpressZoneId, $nonExpressCityId);
        }

        $pivotTableQuery = trim($pivotTableQuery, ', ');

        $connection = $this->entityManager->getConnection();

        $connection->beginTransaction();

        try {
            $query = $connection->prepare("DELETE FROM province_zones");
            $query->executeQuery();

            $query = $connection->prepare("INSERT INTO city_zones (id) VALUES ({$expressZoneId}), ({$nonExpressZoneId})");
            $query->executeQuery();

            $query = $connection->prepare($pivotTableQuery);
            $query->executeQuery();

            $query = $connection->prepare('UPDATE zones SET code = ?, name = ?, dtype = ? WHERE id = ?');
            $query->bindValue(1, 'express');
            $query->bindValue(2, 'اکسپرس');
            $query->bindValue(3, 'city');
            $query->bindValue(4, $expressZoneId);
            $query->executeQuery();

            $query = $connection->prepare('UPDATE zones SET code = ?, name = ?, dtype = ? WHERE id = ?');
            $query->bindValue(1, 'none-express');
            $query->bindValue(2, 'غیر اکسپرس');
            $query->bindValue(3, 'city');
            $query->bindValue(4, $nonExpressZoneId);
            $query->executeQuery();

            $query = $connection->prepare('DELETE FROM province_zone_province');
            $query->executeQuery();

            $connection->commit();
        } catch (\Throwable $e) {
            $connection->close();
            $connection->rollBack();

            throw $e;
        }

        $io->success('Zones fixed successfully');

        return Command::SUCCESS;
    }
}
