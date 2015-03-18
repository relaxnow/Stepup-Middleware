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

namespace Surfnet\StepupMiddleware\MiddlewareBundle\Console\Command;

use Doctrine\DBAL\Connection;
use Exception;
use Rhumsaa\Uuid\Uuid;
use Surfnet\StepupMiddleware\CommandHandlingBundle\EventHandling\BufferedEventBus;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Identity\Command\BootstrapIdentityWithYubikeySecondFactorCommand
    as BootstrapIdentityWithYubikeySecondFactorIdentityCommand;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Pipeline\Pipeline;
use Surfnet\StepupMiddleware\MiddlewareBundle\Service\DBALConnectionHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class BootstrapIdentityWithYubikeySecondFactorCommand extends Command
{
    /**
     * @var \Surfnet\StepupMiddleware\CommandHandlingBundle\Pipeline\Pipeline
     */
    private $pipeline;

    /**
     * @var BufferedEventBus
     */
    private $eventBus;

    /**
     * @var DBALConnectionHelper
     */
    private $connectionHelper;

    public function __construct(Pipeline $pipeline, BufferedEventBus $eventBus, DBALConnectionHelper $connectionHelper)
    {
        parent::__construct(null);

        $this->pipeline = $pipeline;
        $this->eventBus = $eventBus;
        $this->connectionHelper = $connectionHelper;
    }


    protected function configure()
    {
        $this
            ->setName('middleware:bootstrap:identity-with-yubikey')
            ->setDescription('Creates an identity with a vetted Yubikey second factor')
            ->addArgument('name-id', InputArgument::REQUIRED, 'The NameID of the identity to create')
            ->addArgument('institution', InputArgument::REQUIRED, 'The institution of the identity to create')
            ->addArgument('common-name', InputArgument::REQUIRED, 'The Common Name of the identity to create')
            ->addArgument('email', InputArgument::REQUIRED, 'The e-mail address of the identity to create')
            ->addArgument(
                'yubikey',
                InputArgument::REQUIRED,
                'The public ID of the Yubikey. Remove the last 32 characters of a Yubikey OTP to acquire this.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command                  = new BootstrapIdentityWithYubikeySecondFactorIdentityCommand();
        $command->UUID            = (string) Uuid::uuid4();
        $command->identityId      = (string) Uuid::uuid4();
        $command->nameId          = $input->getArgument('name-id');
        $command->institution     = $input->getArgument('institution');
        $command->commonName      = $input->getArgument('common-name');
        $command->email           = $input->getArgument('email');
        $command->secondFactorId  = (string) Uuid::uuid4();
        $command->yubikeyPublicId = $input->getArgument('yubikey');

        $this->connectionHelper->beginTransaction();

        try {
            $command = $this->pipeline->process($command);
            $this->eventBus->flush();

            $this->connectionHelper->commit();
        } catch (Exception $e) {
            $output->writeln(sprintf(
                '<error>An Error occurred when trying to bootstrap the identity: "%s"</error>',
                $e->getMessage()
            ));

            $this->connectionHelper->rollBack();

            throw $e;
        }

        $output->writeln(
            sprintf(
                '<info>Successfully created identity with UUID %s and second factor with UUID %s</info>',
                $command->identityId,
                $command->secondFactorId
            )
        );
    }
}
