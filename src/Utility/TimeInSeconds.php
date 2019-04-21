<?php

namespace SouthCoast\Utility;

class TimeInSeconds
{
    const MINUTE = 60;
    const HOUR = 60 * TimeInSeconds::MINUTE;
    const DAY = 24 * TimeInSeconds::HOUR;
    const WEEK = 7 * TimeInSeconds::DAY;
    const MONTH = 31 * TimeInSeconds::DAY;

    /**
     * Converts the plain text query in seconds.
     *
     * Example:
     *      TimeInSeconds::get('4 days, 23 hours, 48 minutes, 33 seconds');
     *      TimeInSeconds::get('12:14');
     *
     * @param string $query     The plain text query separated by comma's
     * @return int              The time in seconds
     */
    public static function get(string $query): int
    {
        /* Check if it contains the separator */
        if (strpos($query, ',') === false) {
            /* If not, just wrap it in an array */
            $query = [$query];
        } else {
            /* else, Explode it by the comma */
            $query_array = explode(',', $query);
        }

        /* Set the total time to 0 */
        $total = 0;

        /* Loop over the entries */
        foreach ($query_array as $partial) {
            /* Add the wanted period to the current time and subtract the current timestamp  */
            /* This will result in just the time in seconds for the provided period */
            $total += abs(strtotime('now +' . trim($partial)) - time());
        }

        /* Return the total */
        return $total;
    }

    /**
     * Calculates the time in seconds in the provided range
     *
     * Example:
     *      TimeInSeconds::between('12:00', '12:01');
     *      TimeInSeconds::between('2019-01-01 12:00', '2019-01-04 12:01');
     *      TimeInSeconds::between(new DateTime($dateTime_a), new DateTime($dateTime_b));
     *
     * @param mixed $from      The start of the period
     * @param mixed $to        The end of the period
     * @return int              The time in seconds between the range
     */
    public static function between($from, $to): int
    {
        /* Lets create an array for convenience  */
        $date = ['to' => $to, 'from' => $from];

        /* Loop over the dates */
        foreach ($date as $type => &$value) {
            /* Check if its just a time instead of a full date */
            if (preg_match('/^([0-2][0-9]\:[0-5][0-9])(\:[0-5][0-9]|)$/', $value, $matches)) {
                /* If it is, add the current date to it */
                $value = date('Y-m-d') . ' ' . $value;
            }

            /* Check if it's a string */
            if (is_string($value)) {
                /* Create a timestamp from it */
                $value = strtotime($value);
            }

            /* Check if it's a DateTime Object */
            if ($value instanceof DateTime) {
                /* Get it's timestamp */
                $value = $value->getTimestamp();
                continue;
            }
        }

        /* Return the time in between */
        return $date['to'] - $date['from'];
    }
}
