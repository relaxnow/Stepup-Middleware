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

class SecondFactorRepository extends EntityRepository
{
    /**
     * @param string $identityId
     * @return SecondFactor[]
     */
    public function findByIdentity($identityId)
    {
        return $this->createQueryBuilder('sf')
            ->where('sf.identity = :identityId')
            ->setParameter('identityId', $identityId)
            ->getQuery()
            ->getResult();
    }
}
