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
use Surfnet\Stepup\Identity\Value\Institution;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Command\ListRasCommand;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Entity\Ra;

class RaRepository extends EntityRepository
{
    /**
     * @param string $nameId
     * @return null|\Surfnet\StepupMiddleware\ApiBundle\Identity\Entity\Ra
     */
    public function findByNameId($nameId)
    {
        return $this->findOneBy(['nameId' => $nameId]);
    }

    /**
     * @param Institution $institution
     * @return Ra[]
     */
    public function findByInstitution(Institution $institution)
    {
        return $this->findBy(['institution' => $institution]);
    }

    /**
     * Searches for RAs.
     *
     * @param ListRasCommand $searchRaCommand
     * @return \Doctrine\ORM\Query
     */
    public function createSearchQuery(ListRasCommand $searchRaCommand)
    {
        $queryBuilder = $this
            ->createQueryBuilder('r')
            ->where('r.institution = :institution')
            ->setParameter('institution', $searchRaCommand->institution);

        return $queryBuilder->getQuery();
    }
}