<?php


namespace App\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheInvalidationListener
{
    private $cache;

    public function __construct(TagAwareCacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->invalidateCache($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->invalidateCache($args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->invalidateCache($args);
    }

    private function invalidateCache(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        $tags = [];
        if ($entity instanceof \App\Entity\Zone) {
            $tags = ['zones_tag', 'zones_tag_array'];
        } elseif ($entity instanceof \App\Entity\ProductLine) {
            $tags = ['productLines_tag', 'productLines_tag_array'];
        } elseif ($entity instanceof \App\Entity\Category) {
            $tags = ['categories_tag', 'categories_tag_array'];
        } elseif ($entity instanceof \App\Entity\Button) {
            $tags = ['buttons_tag', 'buttons_tag_array'];
        } elseif ($entity instanceof \App\Entity\User) {
            $tags = ['users_tag', 'users_tag_array'];
        } elseif ($entity instanceof \App\Entity\Upload) {
            $tags = ['uploads_tag', 'uploads_tag_array'];
        } elseif ($entity instanceof \App\Entity\Incident) {
            $tags = ['incidents_tag', 'incidents_tag_array'];
        } elseif ($entity instanceof \App\Entity\IncidentCategory) {
            $tags = ['incidentCategories_tag', 'incidentCategories_tag_array'];
        } elseif ($entity instanceof \App\Entity\Department) {
            $tags = ['departments_tag', 'departments_tag_array'];
        } elseif ($entity instanceof \App\Entity\Validation) {
            $tags = ['validations_tag', 'validations_tag_array'];
        } elseif ($entity instanceof \App\Entity\Team) {
            $tags = ['teams_tag', 'teams_tag_array'];
        } elseif ($entity instanceof \App\Entity\Uap) {
            $tags = ['uaps_tag', 'uaps_tag_array'];
        } elseif ($entity instanceof \App\Entity\Operator) {
            $tags = ['operators_tag', 'operators_tag_array'];
        }

        if (!empty($tags)) {
            $this->cache->invalidateTags($tags);
        }
    }
}
