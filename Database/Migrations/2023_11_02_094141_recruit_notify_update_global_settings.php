<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::whenTableDoesntHaveColumn('recruit_global_settings', 'notify_update', function (Blueprint $table) {
            $table->boolean('notify_update')->default(1)->after('supported_until');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
