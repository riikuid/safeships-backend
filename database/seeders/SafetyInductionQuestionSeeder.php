<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionPackage;
use Illuminate\Database\Seeder;

class SafetyInductionQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat paket soal
        $packageA = QuestionPackage::create([
            'name' => 'Paket A',
            'type' => 'standard',
        ]);

        $packageB = QuestionPackage::create([
            'name' => 'Paket B',
            'type' => 'standard',
        ]);

        $packageEasy = QuestionPackage::create([
            'name' => 'Paket Mudah',
            'type' => 'easy',
        ]);

        // Soal untuk Paket A
        $questionsPackageA = [
            [
                'text' => 'Apa yang harus dilakukan sebelum memasuki area kerja berbahaya?',
                'options' => [
                    'A' => 'Memakai alat pelindung diri (APD)',
                    'B' => 'Berlari untuk mempercepat pekerjaan',
                    'C' => 'Mengabaikan tanda peringatan',
                    'D' => 'Tidak melapor ke supervisor',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa fungsi helm keselamatan di tempat kerja?',
                'options' => [
                    'A' => 'Melindungi kepala dari benturan',
                    'B' => 'Sebagai aksesori fashion',
                    'C' => 'Menjaga suhu tubuh',
                    'D' => 'Meningkatkan penglihatan',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Tanda peringatan berwarna kuning di area kerja biasanya menunjukkan?',
                'options' => [
                    'A' => 'Perhatian terhadap bahaya',
                    'B' => 'Area aman untuk beristirahat',
                    'C' => 'Zona bebas asap',
                    'D' => 'Tempat parkir kendaraan',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa yang harus dilakukan jika terjadi kebocoran bahan kimia?',
                'options' => [
                    'A' => 'Segera laporkan ke supervisor',
                    'B' => 'Abaikan jika kecil',
                    'C' => 'Coba bersihkan tanpa pelindung',
                    'D' => 'Pindahkan bahan kimia ke tempat lain',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Mengapa penting untuk mengikuti prosedur lockout-tagout?',
                'options' => [
                    'A' => 'Mencegah kecelakaan saat perawatan mesin',
                    'B' => 'Mempercepat proses perbaikan',
                    'C' => 'Mengurangi konsumsi listrik',
                    'D' => 'Menjaga kebersihan mesin',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa yang harus dilakukan jika mendengar alarm kebakaran?',
                'options' => [
                    'A' => 'Evakuasi ke titik kumpul',
                    'B' => 'Tetap bekerja sampai selesai',
                    'C' => 'Mencari sumber alarm',
                    'D' => 'Menyembunyikan diri di ruangan',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Alat pelindung diri yang wajib digunakan di area konstruksi adalah?',
                'options' => [
                    'A' => 'Sepatu safety dan helm',
                    'B' => 'Kacamata biasa',
                    'C' => 'Pakaian olahraga',
                    'D' => 'Topi biasa',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa tujuan dari pelatihan safety induction?',
                'options' => [
                    'A' => 'Meningkatkan kesadaran keselamatan',
                    'B' => 'Mempercepat proses kerja',
                    'C' => 'Mengurangi jumlah karyawan',
                    'D' => 'Meningkatkan penjualan',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Jika ada kabel listrik yang terbuka, apa yang harus dilakukan?',
                'options' => [
                    'A' => 'Laporkan ke petugas listrik',
                    'B' => 'Tutup dengan kain',
                    'C' => 'Biarkan saja',
                    'D' => 'Sentuh untuk memeriksa',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa yang dimaksud dengan zona bahaya di tempat kerja?',
                'options' => [
                    'A' => 'Area dengan risiko tinggi',
                    'B' => 'Tempat untuk beristirahat',
                    'C' => 'Ruang penyimpanan alat',
                    'D' => 'Area kantor administrasi',
                ],
                'correct_answer' => 'A',
            ],
        ];

        // Soal untuk Paket B
        $questionsPackageB = [
            [
                'text' => 'Apa yang harus dilakukan saat menggunakan alat berat?',
                'options' => [
                    'A' => 'Ikuti prosedur operasi standar',
                    'B' => 'Gunakan tanpa pelatihan',
                    'C' => 'Abaikan petunjuk keselamatan',
                    'D' => 'Operasikan dengan cepat',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa fungsi sarung tangan keselamatan?',
                'options' => [
                    'A' => 'Melindungi tangan dari bahan berbahaya',
                    'B' => 'Meningkatkan kekuatan tangan',
                    'C' => 'Menjaga tangan tetap hangat',
                    'D' => 'Sebagai hiasan',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Tanda larangan merokok di area kerja menunjukkan?',
                'options' => [
                    'A' => 'Risiko kebakaran',
                    'B' => 'Area untuk merokok',
                    'C' => 'Zona bebas bahaya',
                    'D' => 'Tempat istirahat',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa yang harus dilakukan jika terjadi kecelakaan kerja?',
                'options' => [
                    'A' => 'Laporkan ke supervisor segera',
                    'B' => 'Abaikan jika ringan',
                    'C' => 'Sembunyikan kejadian',
                    'D' => 'Tunggu sampai selesai shift',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Mengapa penting memeriksa alat sebelum digunakan?',
                'options' => [
                    'A' => 'Mencegah kerusakan atau kecelakaan',
                    'B' => 'Mempercepat pekerjaan',
                    'C' => 'Mengurangi biaya perawatan',
                    'D' => 'Menambah masa pakai alat',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa yang harus dilakukan saat melihat tumpahan minyak?',
                'options' => [
                    'A' => 'Laporkan dan tandai area',
                    'B' => 'Lewati tanpa tindakan',
                    'C' => 'Bersihkan tanpa pelindung',
                    'D' => 'Abaikan jika kecil',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Alat pelindung telinga digunakan untuk?',
                'options' => [
                    'A' => 'Melindungi dari kebisingan',
                    'B' => 'Meningkatkan pendengaran',
                    'C' => 'Menjaga suhu telinga',
                    'D' => 'Sebagai hiasan',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa yang dimaksud dengan prosedur evakuasi?',
                'options' => [
                    'A' => 'Rencana keluar saat darurat',
                    'B' => 'Jadwal kerja harian',
                    'C' => 'Pemeriksaan alat',
                    'D' => 'Pelatihan karyawan baru',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Jika ada bau gas di area kerja, apa yang harus dilakukan?',
                'options' => [
                    'A' => 'Evakuasi dan laporkan',
                    'B' => 'Nyalakan api untuk memeriksa',
                    'C' => 'Tetap bekerja',
                    'D' => 'Tutup hidung dengan kain',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa tujuan dari tanda keselamatan di tempat kerja?',
                'options' => [
                    'A' => 'Memberi peringatan dan panduan',
                    'B' => 'Dekorasi area kerja',
                    'C' => 'Menandai tempat parkir',
                    'D' => 'Menunjukkan ruang kantor',
                ],
                'correct_answer' => 'A',
            ],
        ];

        // Soal untuk Paket Mudah
        $questionsPackageEasy = [
            [
                'text' => 'Apa yang harus dipakai saat bekerja di area konstruksi?',
                'options' => [
                    'A' => 'Helm dan sepatu safety',
                    'B' => 'Sandal biasa',
                    'C' => 'Topi kain',
                    'D' => 'Baju olahraga',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa yang harus dilakukan jika melihat api kecil?',
                'options' => [
                    'A' => 'Gunakan alat pemadam kebakaran',
                    'B' => 'Tuang air biasa',
                    'C' => 'Abaikan saja',
                    'D' => 'Sembunyikan api',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Mengapa kita harus mematuhi tanda keselamatan?',
                'options' => [
                    'A' => 'Untuk mencegah kecelakaan',
                    'B' => 'Untuk mempercepat kerja',
                    'C' => 'Untuk dekorasi',
                    'D' => 'Untuk mengisi waktu',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa yang harus dilakukan sebelum menggunakan mesin?',
                'options' => [
                    'A' => 'Periksa kondisi mesin',
                    'B' => 'Nyalakan langsung',
                    'C' => 'Biarkan mesin panas',
                    'D' => 'Abaikan petunjuk',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa fungsi kacamata pelindung?',
                'options' => [
                    'A' => 'Melindungi mata dari debu',
                    'B' => 'Meningkatkan penglihatan',
                    'C' => 'Sebagai aksesori',
                    'D' => 'Menjaga suhu mata',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Jika ada tanda “Dilarang Merokok”, apa artinya?',
                'options' => [
                    'A' => 'Jangan menyalakan api',
                    'B' => 'Boleh merokok',
                    'C' => 'Area untuk istirahat',
                    'D' => 'Tempat aman',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa yang harus dilakukan saat ada tumpahan air di lantai?',
                'options' => [
                    'A' => 'Lap dan tandai area',
                    'B' => 'Biarkan mengering',
                    'C' => 'Lewati tanpa tindakan',
                    'D' => 'Tutup dengan karpet',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa yang harus dilakukan jika alat rusak?',
                'options' => [
                    'A' => 'Laporkan ke supervisor',
                    'B' => 'Terus gunakan',
                    'C' => 'Sembunyikan alat',
                    'D' => 'Buang alat',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Mengapa kita harus tahu lokasi alat pemadam kebakaran?',
                'options' => [
                    'A' => 'Untuk digunakan saat darurat',
                    'B' => 'Untuk dekorasi',
                    'C' => 'Untuk dipindahkan',
                    'D' => 'Untuk dijual',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa yang harus dilakukan saat masuk area berbahaya?',
                'options' => [
                    'A' => 'Pakai APD lengkap',
                    'B' => 'Masuk tanpa izin',
                    'C' => 'Berlari cepat',
                    'D' => 'Abaikan tanda',
                ],
                'correct_answer' => 'A',
            ],
        ];

        // Simpan soal untuk Paket A
        foreach ($questionsPackageA as $question) {
            Question::create([
                'question_package_id' => $packageA->id,
                'text' => $question['text'],
                'options' => $question['options'],
                'correct_answer' => $question['correct_answer'],
            ]);
        }

        // Simpan soal untuk Paket B
        foreach ($questionsPackageB as $question) {
            Question::create([
                'question_package_id' => $packageB->id,
                'text' => $question['text'],
                'options' => $question['options'],
                'correct_answer' => $question['correct_answer'],
            ]);
        }

        // Simpan soal untuk Paket Mudah
        foreach ($questionsPackageEasy as $question) {
            Question::create([
                'question_package_id' => $packageEasy->id,
                'text' => $question['text'],
                'options' => $question['options'],
                'correct_answer' => $question['correct_answer'],
            ]);
        }
    }
}
