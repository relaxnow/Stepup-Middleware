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

namespace Surfnet\StepupMiddleware\ApiBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Value\SecondFactorStatus;

/**
 * Custom Doctrine Type for the four possible statuses a second factor can be in: unverified, verified, vetted and
 * revoked.
 */
class SecondFactorStatusType extends Type
{
    const NAME = 'stepup_second_factor_status';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getIntegerTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (SecondFactorStatus::unverified()->equals($value)) {
            return 0;
        } elseif (SecondFactorStatus::verified()->equals($value)) {
            return 10;
        } elseif (SecondFactorStatus::vetted()->equals($value)) {
            return 20;
        } elseif (SecondFactorStatus::revoked()->equals($value)) {
            return 30;
        }

        throw new ConversionException(
            sprintf(
                "Encountered illegal second factor status of type %s '%s', expected it to be a SecondFactorStatus instance",
                is_object($value) ? get_class($value) : gettype($value),
                is_scalar($value) ? (string) $value : ''
            )
        );
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === '0') {
            return SecondFactorStatus::unverified();
        } elseif ($value === '10') {
            return SecondFactorStatus::verified();
        } elseif ($value === '20') {
            return SecondFactorStatus::vetted();
        } elseif ($value === '30') {
            return SecondFactorStatus::revoked();
        }

        throw new ConversionException(
            sprintf(
                "Encountered illegal second factor status of type %s '%s', expected it to be one of [0,10,20,30]",
                is_object($value) ? get_class($value) : gettype($value),
                is_scalar($value) ? (string) $value : ''
            )
        );
    }

    public function getName()
    {
        return self::NAME;
    }
}
