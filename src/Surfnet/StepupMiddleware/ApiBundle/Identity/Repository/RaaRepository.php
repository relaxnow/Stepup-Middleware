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
use Surfnet\StepupMiddleware\ApiBundle\Exception\InvalidArgumentException;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Query\RaaQuery;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Entity\Raa;

class RaaRepository extends EntityRepository
{
    public function getAllNameIdsRegisteredFor($institution)
    {
        $query = $this
            ->getEntityManager()
            ->createQuery("
                SELECT
                    raa.nameId
                FROM
                    Surfnet\\StepupMiddleware\\ApiBundle\\Identity\\Entity\\Raa raa
                WHERE
                    raa.institution = :institution
            ");

        $query->setParameter('institution', $institution);

        $result = $query->getScalarResult();
        $scalarList = array_map(function ($value) {
            return $value['nameId'];
        }, $result);

        return $scalarList;
    }

    /**
     * @param array $raaCollection
     */
    public function saveAll(array $raaCollection)
    {
        $invalid = [];
        foreach ($raaCollection as $index => $raa) {
            if (!$raa instanceof Raa) {
                $invalid[$index] = $raa;
            }
        }

        if (count($invalid)) {
            $invalidIndications = [];
            foreach ($invalid as $index => $value) {
                $invalidIndications[] = sprintf(
                    '"%s" at index "%d"',
                    is_object($value) ? get_class($value) : gettype($value)
                );
            }

            throw new InvalidArgumentException(sprintf(
                'Expected array of Raa Objects, got %s',
                implode(', ', $invalidIndications)
            ));
        }

        $entityManager = $this->getEntityManager();

        foreach ($raaCollection as $raa) {
            $entityManager->persist($raa);
        }

        $entityManager->flush();
    }

    /**
     * @param RaaQuery $query
     * @return \Doctrine\ORM\Query
     */
    public function createSearchQuery(RaaQuery $query)
    {
        $queryBuilder = $this
            ->createQueryBuilder('r')
            ->where('r.institution = :institution')
            ->setParameter('institution', $query->institution);

        if ($query->nameId) {
            $queryBuilder
                ->andWhere('r.nameId = :nameId')
                ->setParameter('nameId', $query->nameId);
        }

        return $queryBuilder->getQuery();
    }

    /**
     * @param string $nameId
     * @return null|\Surfnet\StepupMiddleware\ApiBundle\Identity\Entity\Raa
     */
    public function findByNameId($nameId)
    {
        return $this->findOneBy(['nameId' => $nameId]);
    }

    /**
     * @param Institution $institution
     * @return Raa[]
     */
    public function findByInstitution(Institution $institution)
    {
        return $this->findBy(['institution' => $institution]);
    }
}
