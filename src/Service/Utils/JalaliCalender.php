<?php

namespace App\Service\Utils;

use Psr\Log\InvalidArgumentException;

class JalaliCalender
{
    /**
     * @param $gy
     * @param $gm
     * @param $gd
     *
     * @return string
     */
    public static function toJalali($gy, $gm, $gd)
    {
        return self::d2j(self::g2d($gy, $gm, $gd));
    }

    /**
     * @param $jy
     *
     * @return array
     */
    public static function jalaliCal($jy)
    {
        $breaks = [
            -61,
            9,
            38,
            199,
            426,
            686,
            756,
            818,
            1111,
            1181,
            1210,
            1635,
            2060,
            2097,
            2192,
            2262,
            2324,
            2394,
            2456,
            3178,
        ];

        $breaksCount = count($breaks);

        $gy = $jy + 621;
        $leapJ = -14;
        $jp = $breaks[0];

        if ($jy < $jp || $jy >= $breaks[$breaksCount - 1]) {
            throw new InvalidArgumentException('Invalid Jalali year : ' . $jy);
        }

        $jump = 0;

        for ($i = 1; $i < $breaksCount; ++$i) {
            $jm = $breaks[$i];
            $jump = $jm - $jp;

            if ($jy < $jm) {
                break;
            }

            $leapJ += self::div($jump, 33) * 8 + self::div(self::mod($jump, 33), 4);

            $jp = $jm;
        }

        $n = $jy - $jp;

        $leapJ += self::div($n, 33) * 8 + self::div(self::mod($n, 33) + 3, 4);

        if (self::mod($jump, 33) === 4 && $jump - $n === 4) {
            ++$leapJ;
        }

        $leapG = self::div($gy, 4) - self::div((self::div($gy, 100) + 1) * 3, 4) - 150;

        $march = 20 + $leapJ - $leapG;

        if ($jump - $n < 6) {
            $n = $n - $jump + self::div($jump + 4, 33) * 33;
        }

        $leap = self::mod(self::mod($n + 1, 33) - 1, 4);

        if ($leap === -1) {
            $leap = 4;
        }

        return [
            'leap' => $leap,
            'gy' => $gy,
            'march' => $march,
        ];
    }

    /**
     * @param $a
     * @param $b
     *
     * @return bool
     */
    public static function div($a, $b)
    {
        return ~~($a / $b);
    }

    /**
     * @param $a
     * @param $b
     *
     * @return float|int
     */
    public static function mod($a, $b)
    {
        return $a - ~~($a / $b) * $b;
    }

    /**
     * @param $jdn
     *
     * @return array
     */
    public static function d2g($jdn)
    {
        $j = 4 * $jdn + 139361631;
        $j += self::div(self::div(4 * $jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
        $i = self::div(self::mod($j, 1461), 4) * 5 + 308;

        $gd = self::div(self::mod($i, 153), 5) + 1;
        $gm = self::mod(self::div($i, 153), 12) + 1;
        $gy = self::div($j, 1461) - 100100 + self::div(8 - $gm, 6);

        return [$gy, $gm, $gd];
    }

    /**
     * @param $gy
     * @param $gm
     * @param $gd
     *
     * @return bool|int
     */
    public static function g2d($gy, $gm, $gd)
    {
        return (
                self::div(($gy + self::div($gm - 8, 6) + 100100) * 1461, 4)
                + self::div(153 * self::mod($gm + 9, 12) + 2, 5)
                + $gd - 34840408
            ) - self::div(self::div($gy + 100100 + self::div($gm - 8, 6), 100) * 3, 4) + 752;
    }

    /**
     * @param $jdn
     *
     * @return string
     */
    public static function d2j($jdn)
    {
        $gy = self::d2g($jdn)[0];
        $jy = $gy - 621;
        $jCal = self::jalaliCal($jy);
        $jdn1f = self::g2d($gy, 3, $jCal['march']);

        $k = $jdn - $jdn1f;

        if ($k >= 0) {
            if ($k <= 185) {
                $jm = 1 + self::div($k, 31);
                $jd = self::mod($k, 31) + 1;

                return self::appendZero([$jy, $jm, $jd]);
            }

            $k -= 186;
        } else {
            --$jy;
            $k += 179;

            if ($jCal['leap'] === 1) {
                ++$k;
            }
        }

        $jm = 7 + self::div($k, 30);
        $jd = self::mod($k, 30) + 1;

        return self::appendZero([$jy, $jm, $jd]);
    }

    /**
     * @param $date
     *
     * @return string
     */
    public static function appendZero($date)
    {
        if ($date[1] < 10) {
            $date[1] = '0' . $date[1];
        }
        if ($date[2] < 10) {
            $date[2] = '0' . $date[2];
        }

        return implode('/', $date);
    }
}
