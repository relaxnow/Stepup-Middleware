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

namespace Surfnet\StepupMiddleware\ApiBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Thrown when a client provided invalid command input to the application.
 */
class BadCommandRequestException extends RuntimeException
{
    /**
     * @var string[]
     */
    private $errors;

    /**
     * @param string $message
     * @param ConstraintViolationListInterface $violations
     * @return self
     */
    public static function withViolations($message, ConstraintViolationListInterface $violations)
    {
        $violationStrings = self::convertViolationsToStrings($violations);
        $message = sprintf('%s (%s)', $message, join('; ', $violationStrings));

        return new self($violationStrings, $message);
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @return string[]
     */
    private static function convertViolationsToStrings(ConstraintViolationListInterface $violations)
    {
        $violationStrings = [];

        foreach ($violations as $violation) {
            /** @var ConstraintViolationInterface $violation */
            $violationStrings[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
        }

        return $violationStrings;
    }

    /**
     * @param string[] $errors
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(
        array $errors,
        $message = 'JSON could not be reconstituted into valid object.',
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }

    /**
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
