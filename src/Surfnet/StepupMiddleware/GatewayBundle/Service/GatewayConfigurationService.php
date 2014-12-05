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

namespace Surfnet\StepupMiddleware\GatewayBundle\Service;

use Broadway\ReadModel\Projector;
use Doctrine\Common\Collections\ArrayCollection;
use Surfnet\StepupMiddleware\GatewayBundle\Entity\SamlEntity;
use Surfnet\StepupMiddleware\GatewayBundle\Entity\SamlEntityRepository;

class GatewayConfigurationService extends Projector
{
    /**
     * @var \Surfnet\StepupMiddleware\GatewayBundle\Entity\SamlEntityRepository
     */
    private $samlEntityRepository;

    public function __construct(SamlEntityRepository $samlEntityRepository)
    {
        $this->samlEntityRepository = $samlEntityRepository;
    }

    /**
     * @param array $serviceProviderConfigurations
     */
    public function updateServiceProviders(array $serviceProviderConfigurations)
    {
        $spConfigurations = new ArrayCollection();
        foreach ($serviceProviderConfigurations as $configuration) {
            $newConfiguration = $configuration;
            unset($newConfiguration['entity_id']);

            $spConfigurations->add(SamlEntity::createServiceProvider($configuration['entity_id'], $newConfiguration));
        }

        $this->samlEntityRepository->replaceAll($spConfigurations);
    }
}