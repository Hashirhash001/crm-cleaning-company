    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up()
        {
            Schema::table('customers', function (Blueprint $table) {
                // Index for filtering
                $table->index('branch_id');
                $table->index('priority');
                $table->index('is_active');
                $table->index('created_at');

                // Composite index for common queries
                $table->index(['branch_id', 'is_active']);
                $table->index(['branch_id', 'priority']);

                // Index for search
                $table->index('name');
                $table->index('phone');
                $table->index('customer_code');
            });

            Schema::table('jobs', function (Blueprint $table) {
                $table->index(['customer_id', 'status']);
                $table->index('status');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex('customers_branch_id_index');
                $table->dropIndex('customers_priority_index');
                $table->dropIndex('customers_is_active_index');
                $table->dropIndex('customers_created_at_index');
                $table->dropIndex('customers_branch_id_is_active_index');
                $table->dropIndex('customers_branch_id_priority_index');
                $table->dropIndex('customers_name_index');
                $table->dropIndex('customers_phone_index');
                $table->dropIndex('customers_customer_code_index');
            });
        }
    };
