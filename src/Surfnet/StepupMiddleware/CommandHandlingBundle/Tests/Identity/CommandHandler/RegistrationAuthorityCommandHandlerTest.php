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

namespace Surfnet\StepupMiddleware\CommandHandlingBundle\Tests\Identity\CommandHandler;

use Broadway\CommandHandling\CommandHandlerInterface;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventStore\EventStoreInterface;
use Surfnet\Stepup\IdentifyingData\Value\IdentifyingDataId;
use Surfnet\Stepup\Identity\Event\IdentityAccreditedAsRaaEvent;
use Surfnet\Stepup\Identity\Event\IdentityAccreditedAsRaEvent;
use Surfnet\Stepup\Identity\Event\IdentityCreatedEvent;
use Surfnet\Stepup\Identity\Event\RegistrationAuthorityInformationAmendedEvent;
use Surfnet\Stepup\Identity\Event\YubikeySecondFactorBootstrappedEvent;
use Surfnet\Stepup\Identity\EventSourcing\IdentityRepository;
use Surfnet\Stepup\Identity\Value\ContactInformation;
use Surfnet\Stepup\Identity\Value\IdentityId;
use Surfnet\Stepup\Identity\Value\Institution;
use Surfnet\Stepup\Identity\Value\Location;
use Surfnet\Stepup\Identity\Value\NameId;
use Surfnet\Stepup\Identity\Value\RegistrationAuthorityRole;
use Surfnet\Stepup\Identity\Value\SecondFactorId;
use Surfnet\Stepup\Identity\Value\YubikeyPublicId;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Identity\Command\AccreditIdentityCommand;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Identity\Command\AmendRegistrationAuthorityInformationCommand;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Identity\CommandHandler\RegistrationAuthorityCommandHandler;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Tests\CommandHandlerTest;

class RegistrationAuthorityCommandHandlerTest extends CommandHandlerTest
{
    /**
     * Create a command handler for the given scenario test case.
     *
     * @param EventStoreInterface $eventStore
     * @param EventBusInterface   $eventBus
     *
     * @return CommandHandlerInterface
     */
    protected function createCommandHandler(EventStoreInterface $eventStore, EventBusInterface $eventBus)
    {
        $aggregateFactory = new PublicConstructorAggregateFactory();

        return new RegistrationAuthorityCommandHandler(
            new IdentityRepository($eventStore, $eventBus, $aggregateFactory)
        );
    }

    /**
     * @test
     * @group                    command-handler
     * @group                    ra-command-handler
     * @expectedException        \Surfnet\Stepup\Exception\DomainException
     * @expectedExceptionMessage An Identity may only be accredited within its own institution
     */
    public function an_identity_cannot_be_accredited_for_another_institution_then_its_own()
    {
        $command                     = new AccreditIdentityCommand();
        $command->identityId         = static::uuid();
        $command->institution        = 'Babelfish Inc.';
        $command->role               = 'ra';
        $command->location           = 'Somewhere behind you';
        $command->contactInformation = 'Call me Maybe';

        $identityId           = new IdentityId($command->identityId);
        $institution          = new Institution('A Different Institution than the Command holds');
        $nameId               = new NameId(md5('someNameId'));
        $identifyingDataId    = IdentifyingDataId::fromIdentityId($identityId);
        $secondFactorId       = new SecondFactorId(static::uuid());
        $secondFactorPublicId = new YubikeyPublicId('ccccvfeghijk');

        $this->scenario
            ->withAggregateId($command->identityId)
            ->given([
                new IdentityCreatedEvent(
                    $identityId,
                    $institution,
                    $nameId,
                    $identifyingDataId
                ),
                new YubikeySecondFactorBootstrappedEvent(
                    $identityId,
                    $nameId,
                    $institution,
                    $identifyingDataId,
                    $secondFactorId,
                    $secondFactorPublicId
                )
            ])
            ->when($command);
    }

    /**
     * @test
     * @group                    command-handler
     * @group                    ra-command-handler
     * @expectedException        \Surfnet\Stepup\Exception\DomainException
     * @expectedExceptionMessage An Identity must have at least one vetted second factor before it can be accredited
     */
    public function an_identity_cannot_be_accredited_when_it_does_not_have_a_vetted_second_factor()
    {
        $command                     = new AccreditIdentityCommand();
        $command->identityId         = static::uuid();
        $command->institution        = 'Babelfish Inc.';
        $command->role               = 'ra';
        $command->location           = 'Somewhere behind you';
        $command->contactInformation = 'Call me Maybe';

        $identityId           = new IdentityId($command->identityId);
        $institution          = new Institution($command->institution);
        $nameId               = new NameId(md5('someNameId'));
        $identifyingDataId    = IdentifyingDataId::fromIdentityId($identityId);

        $this->scenario
            ->withAggregateId($command->identityId)
            ->given([
                new IdentityCreatedEvent(
                    $identityId,
                    $institution,
                    $nameId,
                    $identifyingDataId
                ),
            ])
            ->when($command);
    }


    /**
     * @test
     * @group                    command-handler
     * @group                    ra-command-handler
     * @expectedException        \Surfnet\Stepup\Exception\DomainException
     * @expectedExceptionMessage Cannot accredit Identity as it has already been accredited
     */
    public function an_identity_cannot_be_accredited_when_it_already_has_been_accredited()
    {
        $command                     = new AccreditIdentityCommand();
        $command->identityId         = static::uuid();
        $command->institution        = 'Babelfish Inc.';
        $command->role               = 'ra';
        $command->location           = 'Somewhere behind you';
        $command->contactInformation = 'Call me Maybe';

        $identityId           = new IdentityId($command->identityId);
        $institution          = new Institution($command->institution);
        $nameId               = new NameId(md5('someNameId'));
        $identifyingDataId    = IdentifyingDataId::fromIdentityId($identityId);
        $secondFactorId       = new SecondFactorId(static::uuid());
        $secondFactorPublicId = new YubikeyPublicId('ccccvfeghijk');

        $this->scenario
            ->withAggregateId($command->identityId)
            ->given(
                [
                    new IdentityCreatedEvent(
                        $identityId,
                        $institution,
                        $nameId,
                        $identifyingDataId
                    ),
                    new YubikeySecondFactorBootstrappedEvent(
                        $identityId,
                        $nameId,
                        $institution,
                        $identifyingDataId,
                        $secondFactorId,
                        $secondFactorPublicId
                    ),
                    new IdentityAccreditedAsRaEvent(
                        $identityId,
                        $nameId,
                        $institution,
                        new RegistrationAuthorityRole(RegistrationAuthorityRole::ROLE_RAA),
                        new Location('Somewhere behind you'),
                        new ContactInformation('Call me maybe')
                    )
                ]
            )
            ->when($command);
    }

    /**
     * @test
     * @group                    command-handler
     * @group                    ra-command-handler
     * @expectedException        \Surfnet\StepupMiddleware\CommandHandlingBundle\Exception\RuntimeException
     */
    public function an_identity_cannot_be_accredited_with_an_invalid_role()
    {

        $command                     = new AccreditIdentityCommand();
        $command->identityId         = static::uuid();
        $command->institution        = 'Babelfish Inc.';
        $command->role               = 'A role that does not exist';
        $command->location           = 'Somewhere behind you';
        $command->contactInformation = 'Call me Maybe';

        $identityId           = new IdentityId($command->identityId);
        $institution          = new Institution($command->institution);
        $nameId               = new NameId(md5('someNameId'));
        $identifyingDataId    = IdentifyingDataId::fromIdentityId($identityId);
        $secondFactorId       = new SecondFactorId(static::uuid());
        $secondFactorPublicId = new YubikeyPublicId('ccccvfeghijk');

        $this->scenario
            ->withAggregateId($command->identityId)
            ->given(
                [
                    new IdentityCreatedEvent(
                        $identityId,
                        $institution,
                        $nameId,
                        $identifyingDataId
                    ),
                    new YubikeySecondFactorBootstrappedEvent(
                        $identityId,
                        $nameId,
                        $institution,
                        $identifyingDataId,
                        $secondFactorId,
                        $secondFactorPublicId
                    )
                ]
            )
            ->when($command);
    }

    /**
     * @test
     * @group                    command-handler
     * @group                    ra-command-handler
     */
    public function an_identity_can_be_accredited_with_ra_role()
    {
        $command                     = new AccreditIdentityCommand();
        $command->identityId         = static::uuid();
        $command->institution        = 'Babelfish Inc.';
        $command->role               = 'ra';
        $command->location           = 'Somewhere behind you';
        $command->contactInformation = 'Call me Maybe';

        $identityId           = new IdentityId($command->identityId);
        $institution          = new Institution($command->institution);
        $nameId               = new NameId(md5('someNameId'));
        $identifyingDataId    = IdentifyingDataId::fromIdentityId($identityId);
        $secondFactorId       = new SecondFactorId(static::uuid());
        $secondFactorPublicId = new YubikeyPublicId('ccccvfeghijk');

        $this->scenario
            ->withAggregateId($command->identityId)
            ->given(
                [
                    new IdentityCreatedEvent(
                        $identityId,
                        $institution,
                        $nameId,
                        $identifyingDataId
                    ),
                    new YubikeySecondFactorBootstrappedEvent(
                        $identityId,
                        $nameId,
                        $institution,
                        $identifyingDataId,
                        $secondFactorId,
                        $secondFactorPublicId
                    )
                ]
            )
            ->when($command)
            ->then([
                new IdentityAccreditedAsRaEvent(
                    $identityId,
                    $nameId,
                    $institution,
                    new RegistrationAuthorityRole(RegistrationAuthorityRole::ROLE_RA),
                    new Location($command->location),
                    new ContactInformation($command->contactInformation)
                )
            ]);
    }

    /**
     * @test
     * @group                    command-handler
     * @group                    ra-command-handler
     */
    public function an_identity_can_be_accredited_with_raa_role()
    {
        $command                     = new AccreditIdentityCommand();
        $command->identityId         = static::uuid();
        $command->institution        = 'Babelfish Inc.';
        $command->role               = 'raa';
        $command->location           = 'Somewhere behind you';
        $command->contactInformation = 'Call me Maybe';

        $identityId           = new IdentityId($command->identityId);
        $institution          = new Institution($command->institution);
        $nameId               = new NameId(md5('someNameId'));
        $identifyingDataId    = IdentifyingDataId::fromIdentityId($identityId);
        $secondFactorId       = new SecondFactorId(static::uuid());
        $secondFactorPublicId = new YubikeyPublicId('ccccvfeghijk');

        $this->scenario
            ->withAggregateId($command->identityId)
            ->given(
                [
                    new IdentityCreatedEvent(
                        $identityId,
                        $institution,
                        $nameId,
                        $identifyingDataId
                    ),
                    new YubikeySecondFactorBootstrappedEvent(
                        $identityId,
                        $nameId,
                        $institution,
                        $identifyingDataId,
                        $secondFactorId,
                        $secondFactorPublicId
                    )
                ]
            )
            ->when($command)
            ->then(
                [
                    new IdentityAccreditedAsRaaEvent(
                        $identityId,
                        $nameId,
                        $institution,
                        new RegistrationAuthorityRole(RegistrationAuthorityRole::ROLE_RAA),
                        new Location($command->location),
                        new ContactInformation($command->contactInformation)
                    )
                ]
            );
    }

    /**
     * @test
     * @group                    command-handler
     * @group                    ra-command-handler
     */
    public function a_registration_authoritys_information_can_be_amended()
    {
        $command                     = new AmendRegistrationAuthorityInformationCommand();
        $command->identityId         = static::uuid();
        $command->location           = 'New York';
        $command->contactInformation = '131 West 3rd Street, NY';

        $identityId           = new IdentityId($command->identityId);
        $institution          = new Institution('Blue Note');
        $nameId               = new NameId(md5('someNameId'));
        $identifyingDataId    = IdentifyingDataId::fromIdentityId($identityId);
        $secondFactorId       = new SecondFactorId(static::uuid());
        $secondFactorPublicId = new YubikeyPublicId('ccccvfeghijk');

        $this->scenario
            ->withAggregateId($command->identityId)
            ->given(
                [
                    new IdentityCreatedEvent(
                        $identityId,
                        $institution,
                        $nameId,
                        $identifyingDataId
                    ),
                    new YubikeySecondFactorBootstrappedEvent(
                        $identityId,
                        $nameId,
                        $institution,
                        $identifyingDataId,
                        $secondFactorId,
                        $secondFactorPublicId
                    ),
                    new IdentityAccreditedAsRaaEvent(
                        $identityId,
                        $nameId,
                        $institution,
                        new RegistrationAuthorityRole(RegistrationAuthorityRole::ROLE_RAA),
                        new Location('Somewhere behind you'),
                        new ContactInformation('Call me Maybe')
                    ),
                ]
            )
            ->when($command)
            ->then(
                [
                    new RegistrationAuthorityInformationAmendedEvent(
                        $identityId,
                        $institution,
                        $nameId,
                        new Location($command->location),
                        new ContactInformation($command->contactInformation)
                    )
                ]
            );
    }

    /**
     * @test
     * @group                    command-handler
     * @group                    ra-command-handler
     * @expectedException        \Surfnet\Stepup\Exception\DomainException
     * @expectedExceptionMessage Cannot amend registration authority information: identity is not a registration authority
     */
    public function an_identitys_registration_authority_information_cannot_be_amended()
    {
        $command                     = new AmendRegistrationAuthorityInformationCommand();
        $command->identityId         = static::uuid();
        $command->location           = 'New York';
        $command->contactInformation = '131 West 3rd Street, NY';

        $identityId           = new IdentityId($command->identityId);
        $institution          = new Institution('Blue Note');
        $nameId               = new NameId(md5('someNameId'));
        $identifyingDataId    = IdentifyingDataId::fromIdentityId($identityId);
        $secondFactorId       = new SecondFactorId(static::uuid());
        $secondFactorPublicId = new YubikeyPublicId('ccccvfeghijk');

        $this->scenario
            ->withAggregateId($command->identityId)
            ->given(
                [
                    new IdentityCreatedEvent(
                        $identityId,
                        $institution,
                        $nameId,
                        $identifyingDataId
                    ),
                    new YubikeySecondFactorBootstrappedEvent(
                        $identityId,
                        $nameId,
                        $institution,
                        $identifyingDataId,
                        $secondFactorId,
                        $secondFactorPublicId
                    ),
                ]
            )
            ->when($command);
    }
}