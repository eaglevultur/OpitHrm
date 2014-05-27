<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Component\Utils;

/**
 * The Utils class is a helper class for all class in the project.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage Component
 */
class Utils
{

    /**
     * Get all values from specific key in a multidimensional array
     *
     * @param $key string
     * @param $arr array
     * @return array
     */
    public static function arrayValueRecursive($key, array $arr)
    {
        $val = array();
        array_walk_recursive($arr, function ($v, $k) use ($key, &$val) {
            if ($k == $key) {
                array_push($val, $v);
            }
        });

        return $val;
    }

    /**
     * Grouping an array collection by a counter
     *
     * @param  array   $collection
     * @param  integer $division
     * @return array   the grouped collection array
     */
    public static function groupingArrayByCounter($collection, $division)
    {
        $result = array();
        $counter = 0;
        $index = 0;

        // Grouping collection by counter
        // The elements of collection will be ordered into subarrays by the division number.
        foreach ($collection as $data) {

            if ($counter % $division == 0) {
                ++$index;
            }
            $result[$index][] = $data;
            $counter++;
        }

        return $result;
    }

    /**
     * Extracts and returns a class basename
     *
     * @param  object $obj
     * @return string The class basename
     */
    public static function getClassBasename($obj)
    {
        $classname = get_class($obj);

        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return $classname;
    }

    /**
     * Collects error messages from a symfony form object
     *
     * @param  \Symfony\Component\Form\Form $form
     * @return array                        An array containing form error messages
     */
    public static function getErrorMessages(\Symfony\Component\Form\Form $form, $i = null)
    {
        $errors = array();
        if (null === $i) {
            $i = 0;
        }
        foreach ($form->getErrors() as $error) {
            $template = $error->getMessageTemplate();
            $parameters = $error->getMessageParameters();

            foreach ($parameters as $var => $value) {
                $template = str_replace($var, $value, $template);
            }

            $errors[$i] = $template;
            $i++;
        }
        if ($form->count()) {
            foreach ($form as $child) {
                if (!$child->isValid()) {
                    $errors = array_merge($errors, self::getErrorMessages($child, $i));
                }
            }
        }

        return $errors;
    }

    /**
     *  Validate date
     *
     * @link http://hu1.php.net/checkdate#113205
     * @param  date    $date
     * @param  string  $format the format of the date
     * @return boolean true or false
     */
    public static function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $default = \DateTime::createFromFormat($format, $date);

        return $default && $default->format($format) == $date;
    }

    /**
     * Validate the currency string.
     * Check the currencies' lenght, only alphabetic or not, and separated by comma or not.
     *
     * @param  string  $currencyString this contains the currencies.
     * @return boolean return true if the passed currency string is valid
     */
    public static function validateCurrencyCodesString($currencyString)
    {
        $currencies = explode(',', $currencyString);

        foreach ($currencies as $currency) {
            // If currency's length is not equel to 3 or it is not alphebitc return with false.
            if (3 != strlen($currency) || !ctype_alpha($currency)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all dates between two datetime.
     *
     * @param  \DateTime $sDate the start date
     * @param  \DateTime $eDate the end date
     * @return array     of datetimes
     */
    public static function diff_days($sDate, $eDate)
    {
        $startDate = clone $sDate;
        $endDate = clone $eDate;
        $days = array();

        // Collect the days of month.
        while ($startDate->getTimestamp() < $endDate->getTimestamp()) {
            $days[] = clone $startDate;
            $startDate->add(new \DateInterval("P1D"));
        }

        return $days;
    }
}