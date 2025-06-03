<?php

namespace App\EventSubscriber;

use App\Entity\Setting;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;

class InvalidateMaintenanceCacheSubscriber implements EventSubscriberInterface
{
    public function __construct(protected CacheInterface $cache)
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityUpdatedEvent::class => 'onBeforeEntityUpdated'
        ];
    }

    public function onBeforeEntityUpdated(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof Setting) 
        {
            return;
        }

        if ($entity->getSettingKey() === 'maintenance') {
            $this->cache->delete('maintenance_mode');
        }
    }
}