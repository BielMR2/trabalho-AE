<?php

namespace App\EventListener;

use App\Traits\RegisterUserTrait;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDoctrineListener(event: Events::prePersist)]
readonly class RegisterUserListener
{
    public function __construct(
        private Security $security
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($this->isRegistroTrait($entity)) {
            $user = $this->security->getUser();
            if ($user && method_exists($entity, 'getCreatedBy') && empty($entity->getCreatedBy())) {
                $entity->setCreatedBy($user);
            }
            if ($user && method_exists($entity, 'setUpdatedBy')) {
                $entity->setUpdatedBy($user);
            }
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
            if (in_array(RegisterUserTrait::class, class_uses($class))) {
                return true;
            }
        } while ($class = get_parent_class($class));
        return false;
    }
}
