<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add the indexes that the dashboard, sales and inventory read paths need.
 *
 * Every index here was added in response to a predicate that appears on a hot
 * page. Foreign key columns are deliberately absent: MySQL already builds an
 * index for each one, so adding a second would only cost write throughput.
 *
 * The recurring theme is that the original queries wrapped their date column in
 * a function. `whereDate('created_at', $d)` compiles to
 * `DATE(created_at) = '...'`, which cannot use an index on `created_at` no
 * matter how many are added, so the database fell back to a full scan on every
 * dashboard load. The indexes below are what make the rewritten range
 * predicates in DashboardController and SalesController fast; on their own,
 * against the old queries, they would change nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── orders ──────────────────────────────────────────────────────────
        // The single busiest table. Both the dashboard trend chart and the
        // sales report filter on created_at, and the dashboard also filters on
        // status. Without these, each of those was a full table scan.
        Schema::table('orders', function (Blueprint $table) {
            $table->index('created_at', 'orders_created_at_index');
        });

        // Leading status serves the three dashboard status counts; created_at
        // is carried along so a status-scoped date range is still a single
        // index seek rather than a filter applied after the lookup.
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'orders_status_created_at_index');
        });

        // The schedule page and dispatch board both filter by delivery_date
        // independent of created_at.
        Schema::table('orders', function (Blueprint $table) {
            $table->index('delivery_date', 'orders_delivery_date_index');
        });

        // ── products ────────────────────────────────────────────────────────
        // Low-stock and out-of-stock counters on the dashboard, plus the same
        // filter in the inventory views. `status` is an ENUM here, so this
        // index is small and stays cached in memory.
        Schema::table('products', function (Blueprint $table) {
            $table->index('status', 'products_status_index');
        });

        // ── order_phase_items ───────────────────────────────────────────────
        // The top-products widget groups by name on every dashboard load. A
        // name index lets that GROUP BY stream from the index instead of
        // buffering a full scan, and the same column backs product search.
        Schema::table('order_phase_items', function (Blueprint $table) {
            $table->index('name', 'opi_name_index');
        });

        // ── order_phases ────────────────────────────────────────────────────
        // Status counts per phase drive the order progress page.
        Schema::table('order_phases', function (Blueprint $table) {
            $table->index('status', 'op_status_index');
        });

        // ── assignments ─────────────────────────────────────────────────────
        // employee_id is already indexed as a foreign key, but every employee
        // query filters on employee_id *and* status together, and the existing
        // single-column index cannot serve the second predicate.
        Schema::table('assignments', function (Blueprint $table) {
            $table->index(['employee_id', 'status'], 'assignments_employee_status_index');
        });

        // The dispatch board lists work by status alone across all employees.
        Schema::table('assignments', function (Blueprint $table) {
            $table->index('status', 'assignments_status_index');
        });

        // ── stock movements ─────────────────────────────────────────────────
        // Both history pages paginate newest-first by created_at.
        Schema::table('stock_in', function (Blueprint $table) {
            $table->index('created_at', 'stock_in_created_at_index');
        });

        Schema::table('stock_out', function (Blueprint $table) {
            $table->index('created_at', 'stock_out_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('stock_out', function (Blueprint $table) {
            $table->dropIndex('stock_out_created_at_index');
        });

        Schema::table('stock_in', function (Blueprint $table) {
            $table->dropIndex('stock_in_created_at_index');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('assignments_status_index');
        });

        // The (employee_id, status) index needs its own dance, because MySQL
        // quietly dropped the auto-created employee_id foreign key index when
        // this migration added a composite whose leading column is also
        // employee_id. The constraint was left pointing at the composite, so
        // dropping it now fails with error 1553.
        //
        // Detach the constraint, drop the composite, then restore the
        // constraint. Re-adding the foreign key makes MySQL recreate
        // assignments_employee_id_foreign on its own, so no explicit index
        // call is needed here -- adding one leaves a redundant duplicate
        // alongside the one MySQL creates.
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('assignments_employee_status_index');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->foreign('employee_id')->references('User_Id')->on('users')->cascadeOnDelete();
        });

        Schema::table('order_phases', function (Blueprint $table) {
            $table->dropIndex('op_status_index');
        });

        Schema::table('order_phase_items', function (Blueprint $table) {
            $table->dropIndex('opi_name_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_delivery_date_index');
            $table->dropIndex('orders_status_created_at_index');
            $table->dropIndex('orders_created_at_index');
        });
    }
};
