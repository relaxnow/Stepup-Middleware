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

namespace Surfnet\StepupMiddleware\CommandHandlingBundle\SensitiveData;

use Broadway\Serializer\SerializableInterface;
use Surfnet\Stepup\Identity\Value\CommonName;
use Surfnet\Stepup\Identity\Value\DocumentNumber;
use Surfnet\Stepup\Identity\Value\Email;
use Surfnet\Stepup\Identity\Value\SecondFactorIdentifier;
use Surfnet\Stepup\Identity\Value\SecondFactorIdentifierFactory;
use Surfnet\StepupBundle\Value\SecondFactorType;

class SensitiveData implements SerializableInterface
{
    /**
     * @var CommonName|null
     */
    private $commonName;

    /**
     * @var Email|null
     */
    private $email;

    /**
     * @var SecondFactorIdentifier|null
     */
    private $secondFactorIdentifier;

    /**
     * @var SecondFactorType|null
     */
    private $secondFactorType;

    /**
     * @var DocumentNumber|null
     */
    private $documentNumber;

    /**
     * @param CommonName $commonName
     * @return SensitiveData
     */
    public function withCommonName(CommonName $commonName)
    {
        $clone = clone $this;
        $clone->commonName = $commonName;

        return $clone;
    }

    /**
     * @param Email $email
     * @return SensitiveData
     */
    public function withEmail(Email $email)
    {
        $clone = clone $this;
        $clone->email = $email;

        return $clone;
    }

    /**
     * @param SecondFactorIdentifier $secondFactorIdentifier
     * @param SecondFactorType       $secondFactorType
     * @return SensitiveData
     */
    public function withSecondFactorIdentifier(
        SecondFactorIdentifier $secondFactorIdentifier,
        SecondFactorType $secondFactorType
    ) {
        $clone = clone $this;
        $clone->secondFactorType = $secondFactorType;
        $clone->secondFactorIdentifier = $secondFactorIdentifier;

        return $clone;
    }

    /**
     * @param DocumentNumber $documentNumber
     * @return SensitiveData
     */
    public function withDocumentNumber(DocumentNumber $documentNumber)
    {
        $clone = clone $this;
        $clone->documentNumber = $documentNumber;

        return $clone;
    }

    /**
     * Returns an instance in which all sensitive data is forgotten.
     *
     * @return SensitiveData
     */
    public function forget()
    {
        $forgotten = new self();
        $forgotten->secondFactorType = $this->secondFactorType;

        return $forgotten;
    }

    /**
     * @return CommonName
     */
    public function getCommonName()
    {
        return $this->commonName ?: CommonName::unknown();
    }

    /**
     * @return Email
     */
    public function getEmail()
    {
        return $this->email ?: Email::unknown();
    }

    /**
     * @return SecondFactorIdentifier
     */
    public function getSecondFactorIdentifier()
    {
        return $this->secondFactorIdentifier ?: SecondFactorIdentifierFactory::unknownForType($this->secondFactorType);
    }

    /**
     * @return DocumentNumber
     */
    public function getDocumentNumber()
    {
        return $this->documentNumber ?: DocumentNumber::unknown();
    }

    public static function deserialize(array $data)
    {
        $self = new self;

        if (isset($data['common_name'])) {
            $self->commonName = new CommonName($data['common_name']);
        }

        if (isset($data['email'])) {
            $self->email = new Email($data['email']);
        }

        if (isset($data['second_factor_type'])) {
            $self->secondFactorType = new SecondFactorType($data['second_factor_type']);
        }

        if (isset($data['second_factor_identifier'])) {
            $self->secondFactorIdentifier =
                SecondFactorIdentifierFactory::forType($self->secondFactorType, $data['second_factor_identifier']);
        }

        if (isset($data['document_number'])) {
            $self->documentNumber = new DocumentNumber($data['document_number']);
        }

        return $self;
    }

    public function serialize()
    {
        return array_filter([
            'common_name'              => $this->commonName,
            'email'                    => $this->email,
            'second_factor_type'       => $this->secondFactorType,
            'second_factor_identifier' => $this->secondFactorIdentifier,
            'document_number'          => $this->documentNumber,
        ]);
    }
}
