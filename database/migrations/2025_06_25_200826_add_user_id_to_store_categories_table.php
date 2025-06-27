<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_categories', function (Blueprint $table) {
            // Add user_id as nullable first to avoid conflict
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });

        // Now fill NULL user_ids with a valid user or remove those rows
        DB::table('store_categories')->whereNull('user_id')->delete();

        // Now add foreign key constraint safely
        Schema::table('store_categories', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('store_categories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
