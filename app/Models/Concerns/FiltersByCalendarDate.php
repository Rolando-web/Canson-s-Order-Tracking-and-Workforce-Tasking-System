<?php

namespace App\Models\Concerns;

use Illuminate\Support\Carbon;

/**
 * Sargable calendar-date filters for models with a `created_at` timestamp.
 *
 * These exist to replace whereDate() and whereYear()+whereMonth() calls.
 *
 * whereDate('created_at', $d) compiles to `DATE(created_at) = '...'`. Wrapping
 * an indexed column in a function makes the predicate non-sargable: no index
 * can answer the comparison, so the planner falls back to a full scan and reads
 * every row only to discard most of them. A BETWEEN over raw timestamps
 * compares the column directly, which the created_at index can seek.
 *
 * The month form also fixes a latent correctness bug that the whereYear() +
 * whereMonth() pair invites: pairing it with only a month number and no year
 * constraint matches that month number in every year at once.
 *
 * Always prefer these scopes over the equivalent whereDate() form.
 */
trait FiltersByCalendarDate
{
    /**
     * Restrict to a single calendar day.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDay($query, $date)
    {
        return $query->whereBetween($this->qualifyCreatedAt(), [
            $date->copy()->startOfDay(),
            $date->copy()->endOfDay(),
        ]);
    }

    /**
     * Restrict to a single calendar month, year included.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMonth($query, $date)
    {
        return $query->whereBetween($this->qualifyCreatedAt(), [
            $date->copy()->startOfMonth(),
            $date->copy()->endOfMonth(),
        ]);
    }

    /**
     * Apply a date filter taken from a request query string.
     *
     * Parsing is strict on purpose. Carbon::parse() would roll '2026-02-30'
     * over into March and quietly return the wrong day's rows, and it throws on
     * outright garbage. Neither matches what whereDate() did: MySQL coerced a
     * bad value to a zero date and matched no rows at all.
     *
     * So anything that is not a real calendar date yields a query that matches
     * nothing. Returning the unfiltered query instead would be far worse: a
     * junk ?date= parameter would silently show the entire list rather than an
     * empty one.
     *
     * 'Y-m-d' is what an <input type="date"> submits. MySQL's own parser also
     * accepts the compact 'Ymd' form, so that is honoured too rather than
     * silently becoming an empty result set later.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByDay($query, ?string $day)
    {
        if (blank($day)) {
            return $query;
        }

        $date = false;

        foreach (['!Y-m-d', '!Ymd'] as $format) {
            $candidate = \DateTimeImmutable::createFromFormat($format, $day);
            $errors = \DateTimeImmutable::getLastErrors();

            // getLastErrors() returns false when there were no errors at all,
            // which is the success case. A non-false value carrying warnings
            // means the input was rejected or rolled over into another date.
            $rejected = $candidate === false
                || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0));

            if (! $rejected) {
                $date = $candidate;
                break;
            }
        }

        if ($date === false) {
            return $query->whereRaw('1 = 0');
        }

        return $this->scopeForDay($query, Carbon::instance($date));
    }

    /**
     * The timestamp column, qualified for joins.
     */
    protected function qualifyCreatedAt(): string
    {
        return $this->getTable().'.created_at';
    }
}
