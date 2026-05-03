<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_menus', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            // $table->unsignedBigInteger('menu_id');
            // $table->index('menu_id');
            $table->boolean('can_access')->default(true);
            $table->timestamps();

            $table->unique(['role', 'menu_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_menus');
    }
}
