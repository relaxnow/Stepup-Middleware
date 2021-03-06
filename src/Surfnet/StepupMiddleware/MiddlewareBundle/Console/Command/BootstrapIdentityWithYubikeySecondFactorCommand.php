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

use Exception;
use Rhumsaa\Uuid\Uuid;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Identity\Command\BootstrapIdentityWithYubikeySecondFactorCommand
    as BootstrapIdentityWithYubikeySecondFactorIdentityCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

final class BootstrapIdentityWithYubikeySecondFactorCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('middleware:bootstrap:identity-with-yubikey')
            ->setDescription('Creates an identity with a vetted Yubikey second factor')
            ->addArgument('name-id', InputArgument::REQUIRED, 'The NameID of the identity to create')
            ->addArgument('institution', InputArgument::REQUIRED, 'The institution of the identity to create')
            ->addArgument('common-name', InputArgument::REQUIRED, 'The Common Name of the identity to create')
            ->addArgument('email', InputArgument::REQUIRED, 'The e-mail address of the identity to create')
            ->addArgument('preferred-locale', InputArgument::REQUIRED, 'The preferred locale of the identity to create')
            ->addArgument(
                'yubikey',
                InputArgument::REQUIRED,
                'The public ID of the Yubikey. Remove the last 32 characters of a Yubikey OTP to acquire this.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Container $container */
        $container  = $this->getApplication()->getKernel()->getContainer();
        $pipeline   = $container->get('surfnet_stepup_middleware_command_handling.pipeline.transaction_aware_pipeline');
        $eventBus   = $container->get('surfnet_stepup_middleware_command_handling.event_bus.buffered');
        $connection = $container->get('surfnet_stepup_middleware_middleware.dbal_connection_helper');

        $command                  = new BootstrapIdentityWithYubikeySecondFactorIdentityCommand();
        $command->UUID            = (string) Uuid::uuid4();
        $command->identityId      = (string) Uuid::uuid4();
        $command->nameId          = $input->getArgument('name-id');
        $command->institution     = $input->getArgument('institution');
        $command->commonName      = $input->getArgument('common-name');
        $command->email           = $input->getArgument('email');
        $command->preferredLocale = $input->getArgument('preferred-locale');
        $command->secondFactorId  = (string) Uuid::uuid4();
        $command->yubikeyPublicId = $input->getArgument('yubikey');

        $connection->beginTransaction();

        try {
            $command = $pipeline->process($command);
            $eventBus->flush();

            $connection->commit();
        } catch (Exception $e) {
            $output->writeln(sprintf(
                '<error>An Error occurred when trying to bootstrap the identity: "%s"</error>',
                $e->getMessage()
            ));

            $connection->rollBack();

            throw $e;
        }

        $output->writeln(sprintf(
            '<info>Successfully created identity with UUID %s and second factor with UUID %s</info>',
            $command->identityId,
            $command->secondFactorId
        ));
    }
}
