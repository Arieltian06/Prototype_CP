<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenjang_pendidikan', function (Blueprint $table) {
            $table->id('id_jenjang');
            $table->string('nama_jenjang', 50)->unique();
            $table->timestamps();
        });

        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id('id_mapel');
            $table->string('nama_mapel', 100);
            $table->foreignId('id_jenjang')->constrained('jenjang_pendidikan', 'id_jenjang')->restrictOnDelete();
            $table->unique(['nama_mapel', 'id_jenjang']);
            $table->timestamps();
        });

        Schema::create('sekolah', function (Blueprint $table) {
            $table->id('id_sekolah');
            $table->string('nama_sekolah', 150);
            $table->text('alamat');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('id_jenjang')->constrained('jenjang_pendidikan', 'id_jenjang')->restrictOnDelete();
            $table->boolean('is_prototype')->default(true);
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id('id_admin');
            $table->string('nama', 100);
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('tempat_belajar', function (Blueprint $table) {
            $table->id('id_tempat');
            $table->string('nama_tempat', 150);
            $table->text('alamat');
            $table->text('deskripsi');
            $table->text('karakteristik');
            $table->text('aktivitas');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('biaya', 12, 2)->default(0);
            $table->string('jam_operasional', 150);
            $table->string('foto')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->index();
            $table->boolean('is_prototype')->default(true);
            $table->timestamps();
        });

        Schema::create('kriteria', function (Blueprint $table) {
            $table->id('id_kriteria');
            $table->string('kode_kriteria', 20)->unique();
            $table->string('nama_kriteria', 150);
            $table->enum('jenis_kriteria', ['benefit', 'cost']);
            $table->decimal('bobot', 12, 6);
            $table->enum('sumber_nilai', ['jenjang', 'mapel', 'jarak', 'manual'])->default('manual');
            $table->timestamps();
        });

        Schema::create('nilai_kesesuaian', function (Blueprint $table) {
            $table->id();
            $table->decimal('nilai', 12, 4)->unique();
            $table->string('label', 100);
            $table->timestamps();
        });

        Schema::create('tempat_mapel', function (Blueprint $table) {
            $table->id('id_tempat_mapel');
            $table->foreignId('id_tempat')->constrained('tempat_belajar', 'id_tempat')->cascadeOnDelete();
            $table->foreignId('id_mapel')->constrained('mata_pelajaran', 'id_mapel')->cascadeOnDelete();
            $table->decimal('nilai_kesesuaian', 12, 4);
            $table->unique(['id_tempat', 'id_mapel']);
            $table->timestamps();
        });

        Schema::create('tempat_jenjang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tempat')->constrained('tempat_belajar', 'id_tempat')->cascadeOnDelete();
            $table->foreignId('id_jenjang')->constrained('jenjang_pendidikan', 'id_jenjang')->cascadeOnDelete();
            $table->decimal('nilai_kesesuaian', 12, 4);
            $table->unique(['id_tempat', 'id_jenjang']);
            $table->timestamps();
        });

        Schema::create('penilaian_tempat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tempat')->constrained('tempat_belajar', 'id_tempat')->cascadeOnDelete();
            $table->foreignId('id_kriteria')->constrained('kriteria', 'id_kriteria')->cascadeOnDelete();
            $table->decimal('nilai', 16, 6);
            $table->unique(['id_tempat', 'id_kriteria']);
            $table->timestamps();
        });

        Schema::create('perhitungan_saw', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('input');
            $table->json('hasil');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['perhitungan_saw', 'penilaian_tempat', 'tempat_jenjang', 'tempat_mapel', 'nilai_kesesuaian', 'kriteria', 'tempat_belajar', 'admins', 'sekolah', 'mata_pelajaran', 'jenjang_pendidikan'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
