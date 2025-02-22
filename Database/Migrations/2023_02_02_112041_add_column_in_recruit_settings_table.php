<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('recruit_settings', 'offer_letter_reminder')) {
            Schema::table('recruit_settings', function (Blueprint $table) {
                $table->integer('offer_letter_reminder')->default(null)->after('career_site');
            });
        }

        if (! Schema::hasColumn('recruit_settings', 'job_alert_status')) {
            Schema::table('recruit_settings', function (Blueprint $table) {
                $table->enum('job_alert_status', ['yes', 'no'])->default('no')->after('offer_letter_reminder');
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
