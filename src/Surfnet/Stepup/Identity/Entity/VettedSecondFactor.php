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

namespace Surfnet\Stepup\Identity\Entity;

use Broadway\EventSourcing\EventSourcedEntity;
use Surfnet\Stepup\DateTime\DateTime;
use Surfnet\Stepup\Exception\DomainException;
use Surfnet\Stepup\Exception\InvalidArgumentException;
use Surfnet\Stepup\Identity\Api\Identity;
use Surfnet\Stepup\Identity\Event\EmailVerifiedEvent;
use Surfnet\Stepup\Identity\Event\CompliedWithVettedSecondFactorRevocationEvent;
use Surfnet\Stepup\Identity\Event\VettedSecondFactorRevokedEvent;
use Surfnet\Stepup\Identity\Value\IdentityId;
use Surfnet\Stepup\Identity\Value\SecondFactorId;
use Surfnet\Stepup\Token\TokenGenerator;

/**
 * A second factor whose possession and its Registrant's identity has been vetted by a Registration Authority.
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateFields)
 */
class VettedSecondFactor extends EventSourcedEntity
{
    /**
     * @var Identity
     */
    private $identity;

    /**
     * @var SecondFactorId
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $secondFactorIdentifier;

    /**
     * @param SecondFactorId $id
     * @param Identity $identity
     * @param string $type
     * @param string $secondFactorIdentifier
     * @return VettedSecondFactor
     */
    public static function create(
        SecondFactorId $id,
        Identity $identity,
        $type,
        $secondFactorIdentifier
    ) {
        $secondFactor = new self();
        $secondFactor->id = $id;
        $secondFactor->identity = $identity;
        $secondFactor->type = $type;
        $secondFactor->secondFactorIdentifier = $secondFactorIdentifier;

        return $secondFactor;
    }

    final public function __construct()
    {
    }

    /**
     * @return SecondFactorId
     */
    public function getId()
    {
        return $this->id;
    }

    public function revoke()
    {
        $this->apply(new VettedSecondFactorRevokedEvent($this->identity->getId(), $this->id));
    }

    public function complyWithRevocation(IdentityId $authorityId)
    {
        $this->apply(
            new CompliedWithVettedSecondFactorRevocationEvent($this->identity->getId(), $this->id, $authorityId)
        );
    }
}
