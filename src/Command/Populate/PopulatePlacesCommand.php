<?php

namespace App\Command\Populate;

use App\Entity\City;
use App\Entity\District;
use App\Entity\Province;
use Doctrine\ORM\EntityManagerInterface;
use LongitudeOne\Spatial\PHP\Types\Geometry\MultiPolygon;
use LongitudeOne\Spatial\PHP\Types\Geometry\Polygon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulatePlacesCommand extends Command
{
    protected static $defaultName = 'timcheh:populate:places';

    public function __construct(protected EntityManagerInterface $manager, protected string $projectDirectory)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->block('Reading & processing data file...');
        $this->doExecute($io);
        $io->newLine(2);
        $io->block('Importing in database...');

        $this->manager->flush();
        $io->success('You have successfully imported provinces, citie and districts in your database.');

        return Command::SUCCESS;
    }

    private function doExecute(SymfonyStyle $io): void
    {
        $features = json_decode(file_get_contents($this->projectDirectory . "/resources/places.geojson"), true)['features'];
        $progress = $io->createProgressBar(count($features));

        foreach ($features as $feature) {
            $provinceEntity = new Province();
            $provinceEntity
                ->setCode($feature['properties']['name:en'])
                ->setName($this->nameExtractor($feature['properties']['name']))
                ->setCoordinates(new MultiPolygon(array_map(function ($coordinates) {
                    return new Polygon($coordinates);
                }, $feature['geometry']['coordinates'])));


            $this->manager->persist($provinceEntity);
            foreach ($feature['properties']['cities'] as $city) {
                $cityEntity = new City();
                $cityEntity->setProvince($provinceEntity)->setName($this->nameExtractor($city['name']));
                $this->manager->persist($cityEntity);

                foreach ($city['areas'] as $area) {
                    $districtEntity = new District();
                    $districtEntity->setCity($cityEntity)->setName($this->nameExtractor($area['name']));
                    $this->manager->persist($districtEntity);
                }
            }

            $progress->advance();
        }
        $progress->finish();
    }

    public function nameExtractor(string $name): string
    {
        $exploded = explode('-', $name);
        if (count($exploded) === 2) {
            return trim($exploded[1]);
        }

        return $name;
    }
}
