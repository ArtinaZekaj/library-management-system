<?php

namespace Application\Service;

use Application\Entity\SystemLog;
use Application\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class SystemLogService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function log(
        User $actor,
        string $actionType,
        string $entityType,
        int $entityId,
        ?string $details = null
    ): void {
        $log = new SystemLog();

        $log->setActor($actor);
        $log->setActionType($actionType);
        $log->setEntityType($entityType);
        $log->setEntityId($entityId);
        $log->setDetails($details);

        $this->entityManager->persist($log);
    }

    public function getLogs(): array
    {
        $logs = $this->entityManager
            ->getRepository(SystemLog::class)
            ->findBy([], ['timestamp' => 'DESC', 'id' => 'DESC']);

        $result = [];

        foreach ($logs as $log) {
            $result[] = [
                'actor_id' => $log->getActor()->getId(),
                'action_type' => $log->getActionType(),
                'entity_type' => $log->getEntityType(),
                'entity_id' => $log->getEntityId(),
                'timestamp' => $log->getTimestamp()->format(DATE_ATOM),
                'details' => $log->getDetails(),
            ];
        }

        return $result;
    }
}
