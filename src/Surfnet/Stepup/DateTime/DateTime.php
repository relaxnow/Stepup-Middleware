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

namespace Surfnet\Stepup\DateTime;

use DateInterval;
use DateTime as CoreDateTime;
use Surfnet\Stepup\Exception\InvalidArgumentException;

class DateTime
{
    /**
     * The 'c' format, expanded in separate format characters. This string can also be used with
     * `DateTime::createFromString()`.
     */
    const FORMAT = 'Y-m-d\\TH:i:sP';

    /**
     * Allows for mocking of time.
     *
     * @var self|null
     */
    private static $now;

    /**
     * @var CoreDateTime
     */
    private $dateTime;

    /**
     * @return self
     */
    public static function now()
    {
        return self::$now ?: new self(new CoreDateTime);
    }

    /**
     * @param string $dateTime A date-time string formatted using `self::FORMAT` (eg. '2014-11-26T15:20:43+01:00').
     * @return self
     */
    public static function fromString($dateTime)
    {
        if (!is_string($dateTime)) {
            InvalidArgumentException::invalidType('string', 'dateTime', $dateTime);
        }

        return new self(CoreDateTime::createFromFormat(self::FORMAT, $dateTime));
    }

    /**
     * @param CoreDateTime|null $dateTime
     */
    public function __construct(CoreDateTime $dateTime = null)
    {
        $this->dateTime = $dateTime ?: new CoreDateTime();
    }

    /**
     * @param string $intervalSpec
     * @return DateTime
     */
    public function add($intervalSpec)
    {
        $dateTime = clone $this->dateTime;
        $dateTime->add(new DateInterval($intervalSpec));

        return new self($dateTime);
    }

    /**
     * @param DateTime $dateTime
     * @return boolean
     */
    public function comesAfter(DateTime $dateTime)
    {
        return $this->dateTime > $dateTime->dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return boolean
     */
    public function comesAfterOrIsEqual(DateTime $dateTime)
    {
        return $this->dateTime >= $dateTime->dateTime;
    }

    /**
     * @return string An ISO 8601 representation of this DateTime.
     */
    public function __toString()
    {
        return $this->dateTime->format(self::FORMAT);
    }
}
