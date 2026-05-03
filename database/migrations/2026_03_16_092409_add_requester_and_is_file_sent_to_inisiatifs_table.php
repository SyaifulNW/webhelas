<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequesterAndIsFileSentToInisiatifsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inisiatifs', function (Blueprint $table) {
            $table->unsignedBigInteger('requester_id')->nullable()->after('pic');
            $table->boolean('is_file_sent')->default(false)->after('requester_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inisiatifs', function (Blueprint $table) {
            $table->dropColumn(['requester_id', 'is_file_sent']);
        });
    }
}
