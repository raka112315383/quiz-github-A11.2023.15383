<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('periksa', function (Blueprint $table) {
            $table->string('bukti_pembayaran')->nullable()->after('biaya_periksa');
            $table->string('status_pembayaran')->default('belum_bayar')->after('bukti_pembayaran');
            $table->dateTime('tgl_bayar')->nullable()->after('status_pembayaran');
            $table->dateTime('tgl_konfirmasi')->nullable()->after('tgl_bayar');
            $table->text('catatan_pembayaran')->nullable()->after('tgl_konfirmasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periksa', function (Blueprint $table) {
            $table->dropColumn([
                'bukti_pembayaran',
                'status_pembayaran',
                'tgl_bayar',
                'tgl_konfirmasi',
                'catatan_pembayaran',
            ]);
        });
    }
};
