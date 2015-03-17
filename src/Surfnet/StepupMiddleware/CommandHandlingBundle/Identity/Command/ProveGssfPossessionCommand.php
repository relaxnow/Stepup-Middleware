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

namespace Surfnet\StepupMiddleware\CommandHandlingBundle\Identity\Command;

use Surfnet\StepupMiddleware\CommandHandlingBundle\Command\AbstractCommand;
use Symfony\Component\Validator\Constraints as Assert;

class ProveGssfPossessionCommand extends AbstractCommand
{
    /**
     * The ID of an existing identity.
     *
     * @Assert\NotBlank(message="stepup.command.prove_gssf_possession.identity_id.must_not_be_blank")
     * @Assert\Type(type="string", message="stepup.command.prove_gssf_possession.identity_id.must_be_string")
     *
     * @var string
     */
    public $identityId;

    /**
     * The ID of the second factor to create.
     *
     * @Assert\NotBlank(message="stepup.command.prove_gssf_possession.second_factor_id.must_not_be_blank")
     * @Assert\Type(type="string", message="stepup.command.prove_gssf_possession.second_factor_id.must_be_string")
     *
     * @var string
     */
    public $secondFactorId;

    /**
     * The phone number
     *
     * @Assert\NotBlank(message="stepup.command.prove_gssf_possession.stepup_provider.must_not_be_blank")
     * @Assert\Type(type="string", message="stepup.command.prove_gssf_possession.stepup_provider.must_be_string")
     *
     * @var string
     */
    public $stepupProvider;

    /**
     * The phone number
     *
     * @Assert\NotBlank(message="stepup.command.prove_gssf_possession.gssf_id.must_not_be_blank")
     * @Assert\Type(type="string", message="stepup.command.prove_gssf_possession.gssf_id.must_be_string")
     *
     * @var string
     */
    public $gssfId;
}