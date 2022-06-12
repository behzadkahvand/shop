<?php

namespace App\Service\OrderShipment;

use App\Dictionary\FileHandlerPresenterModels;
use App\Dictionary\ShipmentTrackingCodeStatus;
use App\DTO\Admin\TrackingCodeSheetFileError;
use App\Entity\Admin;
use App\Entity\OrderShipment;
use App\Entity\ShipmentTrackingCodeUpdate;
use App\Repository\OrderShipmentRepository;
use App\Repository\ShipmentTrackingCodeUpdateRepository;
use App\Service\File\CSV\TrackingCodeRow;
use App\Service\File\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ShipmentTrackingCodeUpdateService
{
    private FileService $fileService;

    private EntityManagerInterface $manager;

    private OrderShipmentRepository $orderShipmentRepository;

    private ShipmentTrackingCodeUpdateRepository $shipmentTrackingCodeUpdateRepository;

    public function __construct(
        ShipmentTrackingCodeUpdateRepository $shipmentTrackingCodeUpdateRepository,
        FileService $fileService,
        EntityManagerInterface $manager,
        OrderShipmentRepository $orderShipmentRepository
    ) {
        $this->fileService                          = $fileService;
        $this->manager                              = $manager;
        $this->orderShipmentRepository              = $orderShipmentRepository;
        $this->shipmentTrackingCodeUpdateRepository = $shipmentTrackingCodeUpdateRepository;
    }

    public function saveUploadedSheetFile(string $fullFilePath): ShipmentTrackingCodeUpdate
    {
        $trackingCodeUpdate = (new ShipmentTrackingCodeUpdate())
            ->setFilePath($fullFilePath);

        $this->manager->persist($trackingCodeUpdate);
        $this->manager->flush();

        return $trackingCodeUpdate;
    }

    public function processBatchUpdateTrackingCodes(int $trackingCodeID): void
    {
        $storedItem = $this->shipmentTrackingCodeUpdateRepository->findOneBy([
            'id'     => $trackingCodeID,
            'status' => ShipmentTrackingCodeStatus::PENDING,
        ]);

        if (!$storedItem) {
            throw new Exception(sprintf(
                "ShipmentTrackingCodeUpdate with id %d and status PENDING not found!",
                $trackingCodeID
            ));
        }

        $rows = $this->getFileRows($storedItem);

        $errors = collect([]);

        try {
            $counter = 0;
            $rows->each(function (TrackingCodeRow $row) use ($errors, $counter) {
                $this->processRow($row, $errors);

                $counter++;
                if ($counter == 500) {
                    $this->manager->flush();
                    $this->manager->clear();
                    $counter = 0;
                }
            });
        } catch (Exception $exception) {
            $errors->add(
                (new TrackingCodeSheetFileError($exception->getMessage()))
                    ->toArray()
            );
        }

        $storedItem->setErrors($errors->toArray())
                   ->setStatus(ShipmentTrackingCodeStatus::PROCESSED);

        $this->manager->flush();
        $this->manager->clear();
    }

    protected function getFileRows(ShipmentTrackingCodeUpdate $storedItem): \Tightenco\Collect\Support\LazyCollection
    {
        return $this->fileService->create(
            $storedItem->getFilePath(),
            FileHandlerPresenterModels::TRACKING_CODE
        )->getRows();
    }

    protected function processRow(TrackingCodeRow $row, \Tightenco\Collect\Support\Collection $errors): void
    {
        try {
            /** @var OrderShipment $orderShipment */
            $orderShipment = $this->orderShipmentRepository->findShipmentForUpdateOrderTracking($row->orderShipmentId);
            if (!$orderShipment) {
                throw new Exception(sprintf(
                    "orderShipment با شناسه %d که orderTracking آن خالی باشد و از نوع غیر express باشد وجود ندارد.",
                    $row->orderShipmentId
                ));
            }

            $orderShipment->setTrackingCode($row->trackingCode);
        } catch (Exception $ex) {
            $errors->add(
                (new TrackingCodeSheetFileError("آیتم شماره {$row->lineNumber}, {$ex->getMessage()}"))
                    ->toArray()
            );
        }
    }

    public function getAdminUploadSheetResult(Admin $admin): array
    {
        $adminUploadSheetErrors = $this->shipmentTrackingCodeUpdateRepository->getAdminUploadSheetErrors($admin->getUsername());

        return [
            'status' => empty($adminUploadSheetErrors),
            'errors' => $adminUploadSheetErrors,
        ];
    }
}
