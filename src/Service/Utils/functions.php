<?php

use App\Dictionary\CurrencyDictionary;
use App\Exceptions\CurrencyIsNotValidException;
use App\Messaging\Messages\Command\AsyncMessage;

if (!function_exists('camel_case')) {
    function camel_case(string $string): string
    {
        return lcfirst(pascal_case($string));
    }
}

if (!function_exists('pascal_case')) {
    function pascal_case(string $string): string
    {
        return str_replace([' ', '_', '-'], '', ucwords(strtolower($string), ' _-'));
    }
}

if (!function_exists('snake_case')) {
    function snake_case(string $string): string
    {
        $camelCased = preg_replace('~(?<=\\w)([A-Z])~u', '_$1', $string);

        if ($camelCased === null) {
            throw new RuntimeException(
                sprintf(
                    'preg_replace returned null for value "%s"',
                    $string
                )
            );
        }

        return mb_strtolower($camelCased);
    }
}

if (!function_exists('always_true_function')) {
    function always_true_function(): bool
    {
        return true;
    }
}

if (!function_exists('always_false_function')) {
    function always_false_function(): bool
    {
        return false;
    }
}

if (!function_exists('to_date_time')) {
    function to_date_time($datetime): DateTime
    {
        if ($datetime instanceof DateTimeInterface) {
            return to_date_time($datetime->format('Y-m-d H:i:s'));
        }

        if (is_string($datetime)) {
            try {
                return new DateTime($datetime);
            } catch (Exception) {
                throw new InvalidArgumentException('Given value is not a valid date time format.');
            }
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unable to create a date time object. expected %s or string got %s',
                DateTimeInterface::class,
                gettype($datetime)
            )
        );
    }
}

if (!function_exists('to_date_time_immutable')) {
    function to_date_time_immutable($datetime): DateTimeImmutable
    {
        if ($datetime instanceof DateTimeInterface) {
            return to_date_time_immutable($datetime->format('Y-m-d H:i:s'));
        }

        if (is_string($datetime)) {
            try {
                return new DateTimeImmutable($datetime);
            } catch (Exception) {
                throw new InvalidArgumentException('Given value is not a valid date time format.');
            }
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unable to create a date time object. expected %s or string got %s',
                DateTimeInterface::class,
                gettype($datetime)
            )
        );
    }
}

if (!function_exists('async_message')) {
    function async_message(object $message): AsyncMessage
    {
        return AsyncMessage::wrap($message);
    }
}

if (!function_exists('retry')) {
    function retry(callable $closure, int $times = 3, bool $sleep = true, int $sleepTimeInMillisecond = 100)
    {
        while ($times) {
            try {
                return $closure();
            } catch (Throwable $e) {
                $times--;

                if ($sleep) {
                    usleep(1000 * $sleepTimeInMillisecond);
                }

                if (0 === $times) {
                    throw $e;
                }
            }
        }
    }
}

if (!function_exists('change_amount_base_currency')) {
    function change_amount_base_currency(int $amount, string $currency): int
    {
        if (!in_array($currency, CurrencyDictionary::toArray())) {
            throw new CurrencyIsNotValidException();
        }

        return $currency === CurrencyDictionary::RIAL ? $amount * 10 : $amount;
    }
}

if (! function_exists('str_slug')) {
    function str_slug(string $title, string $separator = '-')
    {
        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

        // Replace @ with the word 'at'
        $title = str_replace('@', $separator . 'at' . $separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title, 'UTF-8'));

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

        return trim($title, $separator);
    }
}

if (! function_exists('is_even')) {
    function is_even(int $value): bool
    {
        return $value % 2 === 0;
    }
}

if (! function_exists('is_odd')) {
    function is_odd(int $value): bool
    {
        return ! is_even($value);
    }
}

if (! function_exists('sentry_catch_exception')) {
    function sentry_catch_exception(Throwable $exception): void
    {
        \Sentry\captureException($exception);
    }
}

if (!function_exists('array_flatten')) {
    function array_flatten(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, array_flatten($value));
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}

if (!function_exists('array_cross_diff')) {
    function array_cross_diff(array $array1, array $array2): array
    {
        return [...array_diff($array1, $array2), ...array_diff($array2, $array1)];
    }
}

if (!function_exists('strip_base64_encoded_img')) {
    function strip_base64_encoded_img(string $text): string
    {
        return preg_replace('#data:image/([a-zA-Z]*);base64,([^\"]*)#', '', $text);
    }
}

if (!function_exists('calc_discount')) {
    function calc_discount(int $initialPrice, int $finalPrice): float
    {
        if ($initialPrice === 0) {
            return 0;
        }

        return (($initialPrice - $finalPrice) / $initialPrice) * 100;
    }
}

if (!function_exists('days_to_milliseconds')) {
    function days_to_milliseconds(int $days): int
    {
        return $days * 86400000;
    }
}

if (!function_exists('class_basename')) {
    function class_basename(object $entity): string
    {
        return basename(str_replace('\\', '/', get_class($entity)));
    }
}
