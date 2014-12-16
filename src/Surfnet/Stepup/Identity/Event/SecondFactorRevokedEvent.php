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

namespace Surfnet\Stepup\Identity\Event;

use Surfnet\Stepup\Identity\Value\IdentityId;
use Surfnet\Stepup\Identity\Value\SecondFactorId;

abstract class SecondFactorRevokedEvent extends IdentityEvent
{
    /**
     * @var SecondFactorId
     */
    public $secondFactorId;

    final public function __construct(IdentityId $identityId, SecondFactorId $secondFactorId)
    {
        parent::__construct($identityId);

        $this->secondFactorId = $secondFactorId;
    }

    final public static function deserialize(array $data)
    {
        return new static(
            new IdentityId($data['identity_id']),
            new SecondFactorId($data['second_factor_id'])
        );
    }

    final public function serialize()
    {
        return [
            'identity_id'      => (string) $this->identityId,
            'second_factor_id' => (string) $this->secondFactorId,
        ];
    }
}
