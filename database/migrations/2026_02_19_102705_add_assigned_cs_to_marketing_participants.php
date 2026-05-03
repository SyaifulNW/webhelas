<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssignedCsToMarketingParticipants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketing_participants', function (Blueprint $table) {
            $table->string('assigned_cs')->nullable()->after('is_transferred');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketing_participants', function (Blueprint $table) {
            $table->dropColumn('assigned_cs');
        });
    }
}
