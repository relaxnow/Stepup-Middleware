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

namespace Surfnet\StepupMiddleware\CommandHandlingBundle\Pipeline;

use Broadway\CommandHandling\CommandBusInterface;
use Psr\Log\LoggerInterface;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Command\Command;

class DispatchStage implements Stage
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface     $logger
     * @param CommandBusInterface $commandBus
     */
    public function __construct(LoggerInterface $logger, CommandBusInterface $commandBus)
    {
        $this->logger = $logger;
        $this->commandBus = $commandBus;
    }

    public function process(Command $command)
    {
        $this->logger->debug(sprintf('Dispatching command "%s" for handling', $command));

        $this->commandBus->dispatch($command);

        $this->logger->debug(sprintf('Command "%s" has been handled', $command));
        return $command;
    }
}
