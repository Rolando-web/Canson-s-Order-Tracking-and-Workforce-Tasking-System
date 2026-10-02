<?php

use App\Support\DateBucket;
use Illuminate\Support\Facades\DB;

/**
 * Point the default connection at a driver without opening it.
 *
 * DateBucket only asks the connection which driver it is, which is answered
 * from config, so this exercises the real dispatch path on a machine that has
 * only one PDO driver installed. Nothing here runs a query.
 */
function useDriver(string $driver): void
{
    config([
        'database.connections.driver_probe' => [
            'driver' => $driver,
            'host' => '127.0.0.1',
            'database' => 'probe',
            'username' => 'probe',
            'password' => 'probe',
        ],
    ]);

    DB::setDefaultConnection('driver_probe');
    DB::purge('driver_probe');
}

afterEach(function () {
    DB::setDefaultConnection(config('database.default'));
    DB::purge('driver_probe');
});

test('MySQL produces DATE-style expressions', function () {
    useDriver('mysql');

    expect(DateBucket::day('created_at'))->toBe('DATE(created_at)')
        ->and(DateBucket::month('created_at'))
        ->toBe("CONCAT(YEAR(created_at), '-', LPAD(MONTH(created_at), 2, '0'))")
        ->and(DateBucket::year('created_at'))->toBe('YEAR(created_at)')
        ->and(DateBucket::monthNumber('created_at'))->toBe('MONTH(created_at)')
        ->and(DateBucket::dayOfWeek('created_at'))->toBe('DAYOFWEEK(created_at)');
});

test('PostgreSQL produces to_char and EXTRACT expressions', function () {
    useDriver('pgsql');

    expect(DateBucket::day('created_at'))->toBe("to_char(created_at, 'YYYY-MM-DD')")
        ->and(DateBucket::month('created_at'))->toBe("to_char(created_at, 'YYYY-MM')")
        ->and(DateBucket::year('created_at'))->toBe('EXTRACT(YEAR FROM created_at)')
        ->and(DateBucket::monthNumber('created_at'))->toBe('EXTRACT(MONTH FROM created_at)')
        ->and(DateBucket::dayOfWeek('created_at'))->toBe('(EXTRACT(DOW FROM created_at) + 1)');
});

test('qualified column names are carried through on both drivers', function () {
    foreach (['mysql', 'pgsql'] as $driver) {
        useDriver($driver);

        expect(DateBucket::day('orders.created_at'))->toContain('orders.created_at')
            ->and(DateBucket::month('orders.created_at'))->toContain('orders.created_at');
    }
});

test('month bucket yields the same Y-m key shape on both drivers', function () {
    useDriver('pgsql');

    // The PHP side compares bucket keys against Carbon's format('Y-m'), so the
    // expression has to be zero padded to two digits on both dialects.
    expect(DateBucket::month('created_at'))->toBe("to_char(created_at, 'YYYY-MM')");
});
