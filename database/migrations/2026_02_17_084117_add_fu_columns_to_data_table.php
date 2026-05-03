<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFuColumnsToDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('data', function (Blueprint $table) {
            $table->text('fu1')->nullable();
            $table->text('fu2')->nullable();
            $table->text('fu3')->nullable();
            $table->text('fu4')->nullable();
            $table->text('fu5')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data', function (Blueprint $table) {
            $table->dropColumn(['fu1', 'fu2', 'fu3', 'fu4', 'fu5']);
        });
    }
}
