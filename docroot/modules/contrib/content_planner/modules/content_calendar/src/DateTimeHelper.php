<?php

namespace Drupal\content_calendar;

use Drupal\Core\Datetime\DateHelper;

/**
 * Implements DateTimeHelper class.
 */
abstract class DateTimeHelper {

  /**
   * Regex for mysql date only values.
   */
  const FORMAT_MYSQL_DATE_ONLY_REGEX = '\d{4}\-\d{2}\-\d{2}';

  /**
   * Format value for mysql date only values.
   */
  const FORMAT_MYSQL_DATE_ONLY = 'Y-m-d';

  /**
   * Get Month label by its number.
   *
   * @param int $number
   *   Number of a month.
   *
   * @return bool|mixed
   *   Return label of the month.
   */
  public static function getMonthLabelByNumber($number) {

    if (is_numeric($number) && ($number >= 1 && $number <= 12)) {

      $month_labels = [
        1 => t('January'),
        2 => t('February'),
        3 => t('March'),
        4 => t('April'),
        5 => t('May'),
        6 => t('June'),
        7 => t('July'),
        8 => t('August'),
        9 => t('September'),
        10 => t('October'),
        11 => t('November'),
        12 => t('December'),
      ];

      return $month_labels[$number];
    }

    return FALSE;
  }

  /**
   * Get the count of days in a given month of a given year.
   *
   * @param int $month
   *   The month to display in the calendar.
   * @param int $year
   *   The year to display in the calendar.
   *
   * @return int
   *   Return the days count of the month.
   */
  public static function getDayCountInMonth($month, $year) {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
  }

  /**
   * Get the first day of a given month and year.
   *
   * @param int $month
   *   The month to display in the calendar.
   * @param int $year
   *   The year to display in the calendar.
   *
   * @return \DateTime
   *   Return the first day of the month.
   */
  public static function getFirstDayOfMonth($month, $year) {
    $datetime = new \DateTime();

    $datetime->setDate($year, $month, 1);
    $datetime->setTime(0, 0, 0);

    return $datetime;
  }

  /**
   * Get the last day of a given month and year.
   *
   * @param int $month
   *   The month to display in the calendar.
   * @param int $year
   *   The year to display in the calendar.
   *
   * @return \DateTime
   *   Return the last day of the month.
   */
  public static function getLastDayOfMonth($month, $year) {

    $datetime = new \DateTime();

    $datetime->setDate($year, $month, 1);
    $datetime->setTime(23, 59, 59);

    $datetime->modify('last day of this month');

    return $datetime;
  }

  /**
   * Convert unix timestamp to Datetime object.
   *
   * @param int $unix_timestamp
   *   Timestamp integer.
   *
   * @return \DateTime
   *   Return datetime.
   */
  public static function convertUnixTimestampToDatetime(int $unix_timestamp) {

    $datetime = new \DateTime();
    $datetime->setTimestamp($unix_timestamp);

    return $datetime;
  }

  /**
   * Check is a given string is a date of the MySQL Date Only format.
   *
   * @param string $value
   *   Value string.
   *
   * @return false|int
   *   Return int or false.
   */
  public static function dateIsMySqlDateOnly($value) {
    return preg_match("/" . self::FORMAT_MYSQL_DATE_ONLY_REGEX . "/", $value);
  }

  /**
   * Gets the weekdays based on the first weekday set in Regional settings.
   *
   * @return array
   *   Returns an array with the translated days of week.
   */
  public static function getWeekdays() {
    $weekdays = DateHelper::weekDays(TRUE);
    // We reset the keys to keep the same order for them.
    return array_values(DateHelper::weekDaysOrdered($weekdays));
  }

  /**
   * Gets the day of week for the given datetime object.
   *
   * This is done by respecting the first day of week configuration from Drupal.
   *
   * @param \DateTime $dateTime
   *   The datetime object.
   *
   * @return false|int|string
   *   Returns the representative number for the weekday for the given date.
   */
  public static function getDayOfWeekByDate(\DateTime $dateTime) {
    $weekdays = self::getWeekdays();
    $dayName = t($dateTime->format('l'));
    return array_search($dayName, $weekdays, false) + 1;
  }

}
