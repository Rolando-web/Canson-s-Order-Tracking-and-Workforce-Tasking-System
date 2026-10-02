<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Driver-aware SQL expressions for bucketing a datetime column.
 *
 * Production runs on PostgreSQL (see the pgsql DSN in docker/entrypoint.sh)
 * while local development uses MySQL. The two dialects have no shared function
 * for "the month this timestamp falls in": DATE(), YEAR(), MONTH() and LPAD()
 * are MySQL-only and raise SQLSTATE 42883 (function does not exist) on
 * PostgreSQL, while to_char() and EXTRACT() are the PostgreSQL spellings.
 *
 * Every date bucketing expression in the app goes through this class so the
 * same query text works on both, instead of the code silently passing its test
 * suite on MySQL and then 500-ing in production.
 *
 * Grouping still happens on the alias the caller assigns, which both dialects
 * accept in a GROUP BY clause.
 */
final class DateBucket
{
    /**
     * 'Y-m-d' for the column's date, used as a day bucket key.
     */
    public static function day(string $column): string
    {
        return self::forDriver(
            sprintf("to_char(%s, 'YYYY-MM-DD')", $column),
            sprintf('DATE(%s)', $column)
        );
    }

    /**
     * 'Y-m' for the column's month, zero padded so it sorts and compares as a
     * plain string key. Identical output to year().'-'.monthNumber() but a
     * single expression, so no padding logic is duplicated in PHP.
     */
    public static function month(string $column): string
    {
        return self::forDriver(
            sprintf("to_char(%s, 'YYYY-MM')", $column),
            sprintf("CONCAT(YEAR(%s), '-', LPAD(MONTH(%s), 2, '0'))", $column, $column)
        );
    }

    /**
     * Four digit year as an integer.
     */
    public static function year(string $column): string
    {
        return self::forDriver(
            sprintf('EXTRACT(YEAR FROM %s)', $column),
            sprintf('YEAR(%s)', $column)
        );
    }

    /**
     * Month number 1-12. Note this is ambiguous across years, so it may only be
     * used to bucket a range that has already been narrowed to known months.
     */
    public static function monthNumber(string $column): string
    {
        return self::forDriver(
            sprintf('EXTRACT(MONTH FROM %s)', $column),
            sprintf('MONTH(%s)', $column)
        );
    }

    /**
     * Day of week with the same numbering on both drivers: 1 = Sunday.
     * MySQL's DAYOFWEEK() is 1-based from Sunday; PostgreSQL's EXTRACT(DOW)
     * is 0-based from Sunday, hence the +1.
     */
    public static function dayOfWeek(string $column): string
    {
        return self::forDriver(
            sprintf('(EXTRACT(DOW FROM %s) + 1)', $column),
            sprintf('DAYOFWEEK(%s)', $column)
        );
    }

    /**
     * Pick between the PostgreSQL and MySQL spellings of the same expression.
     *
     * Not memoised: DB::connection() hands back the already-resolved connection
     * from the manager's cache and getDriverName() only reads a property, so
     * caching it would only risk serving a stale driver after a test or a
     * runtime connection swap.
     */
    private static function forDriver(string $postgres, string $mysql): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? $postgres : $mysql;
    }
}
