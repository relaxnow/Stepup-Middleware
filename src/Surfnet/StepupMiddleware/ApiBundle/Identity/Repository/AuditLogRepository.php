<?php

/**
 * Copyright 2014 SURFnet bv
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\StepupMiddleware\ApiBundle\Identity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Command\SearchSecondFactorAuditLogCommand;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Entity\AuditLogEntry;

class AuditLogRepository extends EntityRepository
{
    /**
     * An array of event FQCNs that pertain to second factors (verification, vetting, revocation etc.).
     *
     * @var string[]
     */
    private static $secondFactorEvents = [
        'Surfnet\Stepup\Identity\Event\YubikeySecondFactorBootstrappedEvent',
        'Surfnet\Stepup\Identity\Event\GssfPossessionProvenEvent',
        'Surfnet\Stepup\Identity\Event\PhonePossessionProvenEvent',
        'Surfnet\Stepup\Identity\Event\YubikeyPossessionProvenEvent',
        'Surfnet\Stepup\Identity\Event\EmailVerifiedEvent',
        'Surfnet\Stepup\Identity\Event\SecondFactorVettedEvent',
        'Surfnet\Stepup\Identity\Event\UnverifiedSecondFactorRevokedEvent',
        'Surfnet\Stepup\Identity\Event\VerifiedSecondFactorRevokedEvent',
        'Surfnet\Stepup\Identity\Event\VettedSecondFactorRevokedEvent',
        'Surfnet\Stepup\Identity\Event\CompliedWithUnverifiedSecondFactorRevocationEvent',
        'Surfnet\Stepup\Identity\Event\CompliedWithVerifiedSecondFactorRevocationEvent',
        'Surfnet\Stepup\Identity\Event\CompliedWithVettedSecondFactorRevocationEvent',
    ];

    /**
     * @param AuditLogEntry $entry
     */
    public function save(AuditLogEntry $entry)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($entry);
        $entityManager->flush();
    }

    /**
     * @param SearchSecondFactorAuditLogCommand $command
     * @return Query
     */
    public function createSecondFactorSearchQuery(SearchSecondFactorAuditLogCommand $command)
    {
        $queryBuilder = $this
            ->createQueryBuilder('al')
            ->where('al.identityInstitution = :identityInstitution')
            ->setParameter('identityInstitution', $command->identityInstitution)
            ->andWhere('al.identityId = :identityId')
            ->andWhere('al.event IN (:secondFactorEvents)')
            ->setParameter('identityId', $command->identityId)
            ->setParameter('secondFactorEvents', self::$secondFactorEvents);

        switch ($command->orderBy) {
            case 'secondFactorId':
            case 'secondFactorType':
            case 'event':
            case 'recordedOn':
            case 'actorId':
                $queryBuilder->orderBy(sprintf('al.%s', $command->orderBy), $command->orderDirection === 'desc' ? 'DESC' : 'ASC');
                break;
        }

        return $queryBuilder->getQuery();
    }
}