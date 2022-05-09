<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('company_id')->after('room_id');
            $table->string('title',255)->after('end');
            $table->enum('status',[0,1,2])->after('description')->default(1)->comment('0 = отмена, 1 = в работе, 2 = завершен');
        });

        Schema::table('booking_users', function (Blueprint $table) {
            $table->enum('status',[0,1,2])->after('booking_id')->default(1)->comment(
                '0 = отказал, 1 = в работе, 2 = принял');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('company_id','title', 'status');
        });

        Schema::table('booking_users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

}
