<?php

namespace App\EventListener;


use App\Traits\RegisterDateTimeTrait;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
readonly class RegisterDateTimeListener
{

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($this->isRegistroTrait($entity)) {
            $currentDateTime = new DateTime();
            if (empty($entity->getCreatedAt())) {
                $entity->setCreatedAt($currentDateTime);
            }
            $entity->setUpdatedAt($currentDateTime);
        }
    }

    /**
     * @param object $entity
     * @return bool
     */
    public function isRegistroTrait(object $entity): bool
    {
        $class = $entity::class;
        do {
            if (in_array(RegisterDateTimeTrait::class, class_uses($class))) {
                return true;
            }
        } while ($class = get_parent_class($class));
        return false;
    }
}
