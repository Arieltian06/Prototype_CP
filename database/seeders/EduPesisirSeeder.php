<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\JenjangPendidikan;
use App\Models\Kriteria;
use App\Models\MataPelajaran;
use App\Models\NilaiKesesuaian;
use App\Models\Sekolah;
use App\Models\TempatBelajar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EduPesisirSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Admin::updateOrCreate(['email' => 'admin@edupesisir.test'], [
                'nama' => 'Admin EduPesisir', 'password' => Hash::make('password123'),
            ]);

            $levels = collect(['SD', 'SMP', 'SMA'])->mapWithKeys(fn ($name) => [
                $name => JenjangPendidikan::firstOrCreate(['nama_jenjang' => $name]),
            ]);
            foreach ([1 => 'Sangat Rendah', 2 => 'Rendah', 3 => 'Cukup', 4 => 'Sesuai', 5 => 'Sangat Sesuai'] as $value => $label) {
                NilaiKesesuaian::updateOrCreate(['nilai' => $value], ['label' => $label]);
            }

            foreach ([
                ['C1', 'Kesesuaian Jenjang', 'benefit', 0.30, 'jenjang'],
                ['C2', 'Kesesuaian Mata Pelajaran', 'benefit', 0.45, 'mapel'],
                ['C3', 'Jarak', 'cost', 0.25, 'jarak'],
            ] as [$code, $name, $type, $weight, $source]) {
                Kriteria::updateOrCreate(['kode_kriteria' => $code], [
                    'nama_kriteria' => $name, 'jenis_kriteria' => $type, 'bobot' => $weight, 'sumber_nilai' => $source,
                ]);
            }

            $subjectsByLevel = [
                'SD' => ['IPA', 'Matematika', 'IPS', 'Olahraga', 'Seni'],
                'SMP' => ['IPA', 'Matematika', 'IPS', 'Olahraga', 'Seni', 'Sejarah'],
                'SMA' => ['IPA', 'Matematika', 'IPS', 'Olahraga', 'Seni', 'Sejarah', 'Biologi'],
            ];
            $subjects = collect();
            foreach ($subjectsByLevel as $levelName => $names) {
                foreach ($names as $name) {
                    $subjects->push(MataPelajaran::firstOrCreate([
                        'nama_mapel' => $name, 'id_jenjang' => $levels[$levelName]->getKey(),
                    ]));
                }
            }

            $schools = [
                ['SD', 'SD Pesisir 01 (Demo)', -3.8051000, 102.2730000],
                ['SD', 'SD Pesisir 02 (Demo)', -3.7860000, 102.2670000],
                ['SMP', 'SMP Pesisir 01 (Demo)', -3.8110000, 102.2860000],
                ['SMP', 'SMP Pesisir 02 (Demo)', -3.7750000, 102.2720000],
                ['SMA', 'SMA Pesisir 01 (Demo)', -3.8240000, 102.2930000],
                ['SMA', 'SMA Pesisir 02 (Demo)', -3.7920000, 102.2780000],
            ];
            foreach ($schools as [$level, $name, $latitude, $longitude]) {
                Sekolah::updateOrCreate(['nama_sekolah' => $name], [
                    'alamat' => 'Lokasi simulasi sekolah di Kota Bengkulu. Nama dan koordinat bukan data sekolah resmi.',
                    'latitude' => $latitude, 'longitude' => $longitude,
                    'id_jenjang' => $levels[$level]->getKey(), 'is_prototype' => true,
                ]);
            }

            $places = [
                [
                    'nama' => 'Pantai Panjang', 'gambar' => 'pantai-panjang',
                    'alamat' => 'Kawasan Pantai Panjang, Kota Bengkulu — titik contoh.',
                    'lat' => -3.8186000, 'lng' => 102.2825000, 'biaya' => 0,
                    'deskripsi' => 'Pantai terbuka sebagai contoh ruang pembelajaran kontekstual tentang ekosistem pesisir, gelombang, dan hubungan manusia dengan lingkungan.',
                    'karakteristik' => 'Ekosistem pantai · pengamatan alam · ruang terbuka',
                    'aktivitas' => "Mengamati bentuk gelombang dari area aman.\nMencatat komponen ekosistem pesisir.\nMengukur panjang bayangan dan membuat grafik hasil pengamatan.\nMendiskusikan kebersihan pantai bersama pendamping.",
                    'jenjang' => ['SD' => 5, 'SMP' => 4, 'SMA' => 4],
                    'mapel' => ['IPA' => 5, 'Matematika' => 4, 'IPS' => 4, 'Olahraga' => 5, 'Seni' => 4, 'Sejarah' => 2, 'Biologi' => 4],
                ],
                [
                    'nama' => 'Pantai Jakat', 'gambar' => 'pantai-jakat',
                    'alamat' => 'Kawasan Pantai Jakat, Kota Bengkulu — titik contoh.',
                    'lat' => -3.7799000, 'lng' => 102.2527000, 'biaya' => 0,
                    'deskripsi' => 'Contoh lokasi pengamatan kehidupan masyarakat pesisir dan lingkungan pantai untuk menghubungkan pembelajaran sosial dengan fenomena alam.',
                    'karakteristik' => 'Kehidupan pesisir · interaksi sosial · lingkungan pantai',
                    'aktivitas' => "Membuat peta sederhana kawasan pesisir.\nMengamati aktivitas masyarakat tanpa mengganggu pekerjaan.\nMenyusun laporan tentang hubungan lingkungan dan mata pencaharian.\nMembuat sketsa bentang pantai.",
                    'jenjang' => ['SD' => 4, 'SMP' => 5, 'SMA' => 4],
                    'mapel' => ['IPA' => 4, 'Matematika' => 3, 'IPS' => 5, 'Olahraga' => 4, 'Seni' => 4, 'Sejarah' => 3, 'Biologi' => 4],
                ],
                [
                    'nama' => 'Kawasan Mangrove Pesisir', 'gambar' => 'mangrove',
                    'alamat' => 'Kawasan pesisir Kota Bengkulu — area mangrove simulasi untuk prototype.',
                    'lat' => -3.8808000, 'lng' => 102.3005000, 'biaya' => 5000,
                    'deskripsi' => 'Area contoh untuk memahami adaptasi tumbuhan, keanekaragaman hayati, dan fungsi vegetasi pesisir. Titik lokasi ini merupakan simulasi, bukan penetapan akses kawasan.',
                    'karakteristik' => 'Ekologi mangrove · biodiversitas · konservasi pesisir',
                    'aktivitas' => "Mengidentifikasi bentuk akar dan daun dari jalur aman.\nMencatat interaksi organisme tanpa mengambil sampel.\nMembahas peran vegetasi dalam mengurangi abrasi.\nMembuat poster konservasi pesisir.",
                    'jenjang' => ['SD' => 3, 'SMP' => 5, 'SMA' => 5],
                    'mapel' => ['IPA' => 5, 'Matematika' => 3, 'IPS' => 4, 'Olahraga' => 2, 'Seni' => 3, 'Sejarah' => 2, 'Biologi' => 5],
                ],
                [
                    'nama' => 'Kawasan Benteng Marlborough', 'gambar' => 'benteng',
                    'alamat' => 'Kawasan pesisir sekitar Benteng Marlborough, Kota Bengkulu — titik contoh.',
                    'lat' => -3.7875000, 'lng' => 102.2511000, 'biaya' => 10000,
                    'deskripsi' => 'Kawasan sejarah pesisir sebagai konteks contoh pembelajaran perubahan sosial, warisan budaya, dan bentuk arsitektur. Materi lapangan perlu disusun bersama pendidik.',
                    'karakteristik' => 'Sejarah pesisir · warisan budaya · arsitektur',
                    'aktivitas' => "Menyusun pertanyaan pengamatan sejarah lokal.\nMenggambar bentuk geometri pada arsitektur.\nMembuat garis waktu berdasarkan sumber yang diverifikasi guru.\nMendiskusikan pelestarian warisan budaya.",
                    'jenjang' => ['SD' => 4, 'SMP' => 5, 'SMA' => 5],
                    'mapel' => ['IPA' => 2, 'Matematika' => 4, 'IPS' => 5, 'Olahraga' => 2, 'Seni' => 4, 'Sejarah' => 5, 'Biologi' => 2],
                ],
                [
                    'nama' => 'Tapak Paderi', 'gambar' => 'tapak-paderi',
                    'alamat' => 'Kawasan Tapak Paderi, Kota Bengkulu — titik contoh.',
                    'lat' => -3.7848000, 'lng' => 102.2490000, 'biaya' => 0,
                    'deskripsi' => 'Ruang pesisir sebagai contoh titik pertemuan pembelajaran seni, pengamatan bentang alam, dan hubungan masyarakat dengan kawasan pantai.',
                    'karakteristik' => 'Bentang pesisir · seni visual · observasi sosial',
                    'aktivitas' => "Membuat sketsa perspektif lanskap.\nMencatat bentuk dan tekstur objek pesisir.\nMembahas penggunaan ruang publik.\nMenyusun cerita visual tentang lingkungan setempat.",
                    'jenjang' => ['SD' => 5, 'SMP' => 4, 'SMA' => 4],
                    'mapel' => ['IPA' => 4, 'Matematika' => 3, 'IPS' => 4, 'Olahraga' => 4, 'Seni' => 5, 'Sejarah' => 4, 'Biologi' => 3],
                ],
                [
                    'nama' => 'Ruang Belajar Pesisir', 'gambar' => 'ruang-pesisir',
                    'alamat' => 'Area ruang publik pesisir Kota Bengkulu — lokasi simulasi.',
                    'lat' => -3.8334000, 'lng' => 102.2918000, 'biaya' => 0,
                    'deskripsi' => 'Konsep ruang publik untuk kegiatan belajar luar kelas, diskusi, dan proyek sederhana yang menggunakan lingkungan pesisir sebagai sumber belajar.',
                    'karakteristik' => 'Belajar kolaboratif · proyek siswa · ruang publik',
                    'aktivitas' => "Diskusi kelompok tentang sampah pesisir.\nMembuat tabel pengamatan lingkungan.\nMengerjakan proyek seni dari bahan yang disiapkan guru.\nMelakukan aktivitas gerak ringan bersama pendamping.",
                    'jenjang' => ['SD' => 5, 'SMP' => 4, 'SMA' => 3],
                    'mapel' => ['IPA' => 4, 'Matematika' => 5, 'IPS' => 4, 'Olahraga' => 5, 'Seni' => 4, 'Sejarah' => 2, 'Biologi' => 3],
                ],
            ];

            foreach ($places as $data) {
                $place = TempatBelajar::updateOrCreate(['nama_tempat' => $data['nama']], [
                    'alamat' => $data['alamat'],
                    'deskripsi' => $data['deskripsi'].' Data Prototype: koordinat, biaya, jam, deskripsi, aktivitas, dan nilai kesesuaian belum diverifikasi; bukan informasi resmi kunjungan.',
                    'karakteristik' => $data['karakteristik'], 'aktivitas' => $data['aktivitas'],
                    'latitude' => $data['lat'], 'longitude' => $data['lng'], 'biaya' => $data['biaya'],
                    'jam_operasional' => 'Simulasi 08.00–17.00 WIB (belum diverifikasi)',
                    'foto' => 'images/'.$data['gambar'].'.svg', 'status' => 'aktif', 'is_prototype' => true,
                ]);
                $place->jenjang()->sync($levels->mapWithKeys(fn ($level, $name) => [
                    $level->getKey() => ['nilai_kesesuaian' => $data['jenjang'][$name]],
                ])->all());
                $place->mapel()->sync($subjects->mapWithKeys(fn ($subject) => [
                    $subject->getKey() => ['nilai_kesesuaian' => $data['mapel'][$subject->nama_mapel]],
                ])->all());
            }
        });
    }
}
