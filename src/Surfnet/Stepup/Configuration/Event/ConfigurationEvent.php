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

namespace Surfnet\Stepup\Configuration\Event;

use Broadway\Serializer\SerializableInterface;
use Surfnet\Stepup\Configuration\Configuration;
use Symfony\Component\Form\Exception\LogicException;

abstract class ConfigurationEvent implements SerializableInterface
{
    /**
     * @var string
     */
    public $id;

    public function __construct($id)
    {
        if ($id !== Configuration::CONFIGURATION_ID) {
            throw new LogicException('Configuration Events must use the fixed Configuration::CONFIGURATION_ID as id');
        }

        $this->id = $id;
    }
}