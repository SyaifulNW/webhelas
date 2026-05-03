<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFu9To12ColumnsToSalesplansTable extends Migration
{
    public function up()
    {
        Schema::table('salesplans', function (Blueprint $table) {
            // Add missing rtl_at for 9 and 10
            if (!Schema::hasColumn('salesplans', 'fu9_rtl_at')) {
                $table->timestamp('fu9_rtl_at')->nullable()->after('fu9_at');
            }
            if (!Schema::hasColumn('salesplans', 'fu10_rtl_at')) {
                $table->timestamp('fu10_rtl_at')->nullable()->after('fu10_at');
            }

            // Add columns for FU 11
            $table->text('fu11_hasil')->nullable()->after('fu10_at');
            $table->text('fu11_tindak_lanjut')->nullable()->after('fu11_hasil');
            $table->timestamp('fu11_at')->nullable()->after('fu11_tindak_lanjut');
            $table->timestamp('fu11_rtl_at')->nullable()->after('fu11_at');

            // Add columns for FU 12
            $table->text('fu12_hasil')->nullable()->after('fu11_rtl_at');
            $table->text('fu12_tindak_lanjut')->nullable()->after('fu12_hasil');
            $table->timestamp('fu12_at')->nullable()->after('fu12_tindak_lanjut');
            $table->timestamp('fu12_rtl_at')->nullable()->after('fu12_at');
        });
    }

    public function down()
    {
        Schema::table('salesplans', function (Blueprint $table) {
            $table->dropColumn([
                'fu9_rtl_at', 'fu10_rtl_at',
                'fu11_hasil', 'fu11_tindak_lanjut', 'fu11_at', 'fu11_rtl_at',
                'fu12_hasil', 'fu12_tindak_lanjut', 'fu12_at', 'fu12_rtl_at'
            ]);
        });
    }
}
