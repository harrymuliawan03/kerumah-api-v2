<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKontrakansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kontrakans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('alamat');
            $table->string('provinsi', 100);
            $table->string('kota', 100);
            $table->integer('kode_pos')->nullable();
            $table->integer('jml_unit');
            $table->string('kode_unit', 5);
            $table->enum('periode_pembayaran', ['year', 'month']);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->timestamp('deleted_at')->nullable(); // Soft delete column
            $table->timestamps();

            $table->foreign('user_id')->on('users')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kontrakans');
    }
}
