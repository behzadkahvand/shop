<?php

namespace App\Service\Notification;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Throwable;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class NotificationService
{
    protected NotificationRepository $notificationRepository;

    protected Environment $templateEngine;

    protected MessageBusInterface $messenger;

    public function __construct(
        NotificationRepository $notificationRepository,
        Environment $templateEngine,
        MessageBusInterface $messenger,
        private CacheInterface $cache,
        private int $cacheLifeTime,
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->templateEngine         = $templateEngine;
        $this->messenger              = $messenger;
    }

    public function send(AbstractNotificationDTO $notificationDTO): void
    {
        $notification  = $this->getNotification($notificationDTO);
        $defaultLoader = $this->templateEngine->getLoader();

        $key = $this->getKey($notification);
        $this->templateEngine->setLoader(new ArrayLoader([
            $key => $notification->getTemplate(),
        ]));

        try {
            $this->messenger->dispatch($notificationDTO->getMessage($this->templateEngine, $key));
        } catch (Throwable $e) {
        } finally {
            $this->templateEngine->setLoader($defaultLoader);
        }
    }

    private function createNotificationWithDefaultTemplate(
        AbstractNotificationDTO $notificationDTO
    ): Notification {
        return (new Notification())
            ->setTemplate($notificationDTO::getDefaultTemplate())
            ->setSection($notificationDTO::getSection())
            ->setCode($notificationDTO::getCode())
            ->setNotificationType($notificationDTO::getNotificationType());
    }

    private function getKey(Notification $notification): string
    {
        return md5(implode('|', [
            $notification->getNotificationType(),
            $notification->getSection(),
            $notification->getCode(),
            $notification->getTemplate(),
        ]));
    }

    /**
     * @param AbstractNotificationDTO $notificationDTO
     *
     * @return Notification
     */
    private function getNotification(AbstractNotificationDTO $notificationDTO): Notification
    {
        return $this->cache->get(
            $this->getCacheKey($notificationDTO),
            function (ItemInterface $item) use ($notificationDTO) {
                /** @var Notification $notification */
                $notification = $this->notificationRepository->findOneBy([
                    'code'             => $notificationDTO::getCode(),
                    'section'          => $notificationDTO::getSection(),
                    'notificationType' => $notificationDTO::getNotificationType(),
                ]);

                if (!$notification) {
                    $notification = $this->createNotificationWithDefaultTemplate($notificationDTO);
//                    throw new \Exception(sprintf(
//                        "Notification message with code:%s section:%s type:%s not found!",
//                        $notificationDTO::getCode(),
//                        $notificationDTO::getSection(),
//                        $notificationDTO::getNotificationType()
//                    ));
                }

                $item->expiresAfter($this->cacheLifeTime); // 8 Hour

                return $notification;
            }
        );
    }

    private function getCacheKey(AbstractNotificationDTO $notificationDTO): string
    {
        return sprintf(
            "cache_notification_code_%s_section_%s_type_%s",
            $notificationDTO::getCode(),
            $notificationDTO::getSection(),
            $notificationDTO::getNotificationType()
        );
    }
}
