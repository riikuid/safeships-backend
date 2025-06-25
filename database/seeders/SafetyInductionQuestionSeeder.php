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

        // Soal untuk Paket A (Kelompok 1: Standard)
        $questionsPackageA = [
            [
                'text' => 'Apa langkah pertama saat terjadi gempa bumi?',
                'options' => [
                    'A' => 'Lari ke luar ruangan',
                    'B' => 'Bersembunyi di bawah meja',
                    'C' => 'Merunduk dan merapat ke dinding',
                    'D' => 'Menutup semua pintu',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Apa yang sebaiknya dilakukan sebelum memulai aktivitas di ruangan seperti Grha Dewaruci?',
                'options' => [
                    'A' => 'Menyalakan alarm kebakaran',
                    'B' => 'Menghafal semua nama ruangan',
                    'C' => 'Mengikuti Safety Induction',
                    'D' => 'Menyapa semua orang di dalam ruangan',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Mengapa penting memeriksa pintu keluar terdekat?',
                'options' => [
                    'A' => 'Untuk keamanan barang bawaan',
                    'B' => 'Untuk efisiensi waktu',
                    'C' => 'Untuk evakuasi cepat saat darurat',
                    'D' => 'Untuk memenuhi aturan kampus',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Berikut merupakan jenis APAR yang ada di ruangan ini',
                'options' => [
                    'A' => 'CO2',
                    'B' => 'Foam',
                    'C' => 'Dry chemical powder and stored pressure system',
                    'D' => 'Cair',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Apa yang dimaksud dengan “assembly point”?',
                'options' => [
                    'A' => 'Ruang pemadam',
                    'B' => 'Ruang instruksi',
                    'C' => 'Titik kumpul darurat',
                    'D' => 'Pos kesehatan',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Di mana letak assembly point dari Grha Dewaruci?',
                'options' => [
                    'A' => 'Sebelah kiri gedung',
                    'B' => 'Di luar gerbang utama',
                    'C' => 'Di sebelah kanan Grha Dewaruci',
                    'D' => 'Di rooftop gedung',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Siapa yang membantu evakuasi saat darurat?',
                'options' => [
                    'A' => 'Petugas kebersihan',
                    'B' => 'Mahasiswa senior',
                    'C' => 'Petugas keselamatan',
                    'D' => 'Guru tamu',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Mengapa harus menghindari kaca saat gempa?',
                'options' => [
                    'A' => 'Karena bisa menimbulkan suara bising',
                    'B' => 'Karena kaca bisa pecah dan membahayakan',
                    'C' => 'Karena kaca membuat pusing',
                    'D' => 'Karena tidak nyaman',
                ],
                'correct_answer' => 'B',
            ],
            [
                'text' => 'Tindakan utama saat gempa dan kebakaran berbeda karena:',
                'options' => [
                    'A' => 'Resikonya samaa',
                    'B' => 'Prosedur evakuasi berbeda',
                    'C' => 'Lokasi evakuasi tidak perlu',
                    'D' => 'Sifat bencana yang sama',
                ],
                'correct_answer' => 'B',
            ],
            [
                'text' => 'Mengapa ditegaskan bahwa tidak ada latihan hari ini?',
                'options' => [
                    'A' => 'Agar orang tidak datang terlambat',
                    'B' => 'Agar orang tidak panik saat alarm berbunyi',
                    'C' => 'Agar orang tetap serius menghadapi situasi',
                    'D' => 'Agar orang tidak salah paham bila ada kejadian',
                ],
                'correct_answer' => 'D',
            ],
        ];

        // Soal untuk Paket B (Kelompok 2: Standard)
        $questionsPackageB = [
            [
                'text' => 'Jika terjadi kebakaran dan anda berada di tengah ruangan, tindakan paling aman adalah:',
                'options' => [
                    'A' => 'Lari ke jendela',
                    'B' => 'Menunggu instruksi',
                    'C' => 'Cari pintu terdekat dan evakuasi dengan tenang',
                    'D' => 'Sembunyi di bawah kursi',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Risiko jika prosedur gempa tidak diikuti:',
                'options' => [
                    'A' => 'Kehilangan barang pribadi',
                    'B' => 'Cedera akibat reruntuhan atau kaca',
                    'C' => 'Kesulitan keluar gedung',
                    'D' => 'Terlambat masuk kelas',
                ],
                'correct_answer' => 'B',
            ],
            [
                'text' => 'Tujuan dari safety induction ini adalah:',
                'options' => [
                    'A' => 'Memberikan nilai tambah',
                    'B' => 'Mempersulit pengunjung',
                    'C' => 'Menghindari tuntutan hukum',
                    'D' => 'Mencegah kecelakaan dan bahaya',
                ],
                'correct_answer' => 'D',
            ],
            [
                'text' => 'Mengapa tidak boleh panik saat kebakaran?',
                'options' => [
                    'A' => 'Agar tidak terlihat lemah',
                    'B' => 'Agar api tidak makin besar',
                    'C' => 'Agar evakuasi lebih tertib dan aman',
                    'D' => 'Agar api padam',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Jika seseorang tidak tahu pintu keluar terdekat:',
                'options' => [
                    'A' => 'Ia akan cepat keluar',
                    'B' => 'Ia bisa menghalangi orang lain',
                    'C' => 'Ia tetap bisa selamat',
                    'D' => 'Ia akan bingung saat evakuasi',
                ],
                'correct_answer' => 'D',
            ],
            [
                'text' => 'Konsep K3 dalam teks ini mencakup:',
                'options' => [
                    'A' => 'Informasi keamanan dan evakuasi',
                    'B' => 'Laporan kerusakan bangunan',
                    'C' => 'Pemilihan warna ruangan',
                    'D' => 'Jadwal kuliah',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Berikut merupakan jenis APAR yang ada di ruangan ini',
                'options' => [
                    'A' => 'CO2',
                    'B' => 'Foam',
                    'C' => 'Dry chemical powder and stored pressure system',
                    'D' => 'Cair',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Apa akibatnya jika seseorang tidak mengikuti instruksi evakuasi saat darurat?',
                'options' => [
                    'A' => 'Tidak bisa kembali ke ruangan',
                    'B' => 'Bisa membahayakan diri sendiri dan orang lain',
                    'C' => 'Akan diberi tugas tambahan',
                    'D' => 'Tidak dapat sertifikat pelatihan',
                ],
                'correct_answer' => 'B',
            ],
            [
                'text' => 'Jika APAR rusak, tindakan yang tepat:',
                'options' => [
                    'A' => 'Memperbaiki sendiri',
                    'B' => 'Melaporkan ke petugas keamanan atau K3',
                    'C' => 'Membuangnya ke luar ruangan',
                    'D' => 'Diamkan saja',
                ],
                'correct_answer' => 'B',
            ],
            [
                'text' => 'Mengapa penting untuk melindungi kepala saat gempa bumi?',
                'options' => [
                    'A' => 'Karena instruksi tertulis begitu',
                    'B' => 'Agar tetap bisa melihat',
                    'C' => 'Karena benda jatuh bisa menyebabkan cedera serius',
                    'D' => 'Supaya rambut tidak kotor',
                ],
                'correct_answer' => 'C',
            ],
        ];

        // Soal untuk Paket Mudah (Kelompok 3: Easy)
        $questionsPackageEasy = [
            [
                'text' => 'Di mana lokasi kita saat ini menurut teks induksi?',
                'options' => [
                    'A' => 'Gedung A',
                    'B' => 'Grha Dewaruci',
                    'C' => 'Assembly point',
                    'D' => 'Laboratorium',
                ],
                'correct_answer' => 'B',
            ],
            [
                'text' => 'Grha Dewaruci berada di lantai berapa?',
                'options' => [
                    'A' => 'Lantai 1',
                    'B' => 'Lantai 3',
                    'C' => 'Lantai 2',
                    'D' => 'Lantai dasar',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Gedung apa yang menaungi Grha Dewaruci?',
                'options' => [
                    'A' => 'Gedung Teknik',
                    'B' => 'Gedung Mesin',
                    'C' => 'Gedung Direktorat',
                    'D' => 'Gedung Kuliah Umum',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Berapa jumlah pintu keluar di ruangan ini?',
                'options' => [
                    'A' => 'Dua',
                    'B' => 'Empat',
                    'C' => 'Tiga',
                    'D' => 'Satu',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Di mana saja letak pintu keluar?',
                'options' => [
                    'A' => 'Depan, kanan, belakang',
                    'B' => 'Depan, kiri, belakang',
                    'C' => 'Kiri, kanan, tengah',
                    'D' => 'Atas, bawah, samping',
                ],
                'correct_answer' => 'B',
            ],
            [
                'text' => 'Berapa jumlah tangga yang tersedia?',
                'options' => [
                    'A' => 'Dua',
                    'B' => 'Empat',
                    'C' => 'Tiga',
                    'D' => 'Lima',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Di mana letak tangga di Grha Dewaruci?',
                'options' => [
                    'A' => 'Depan, kiri, belakang',
                    'B' => 'Depan, kanan, tengah',
                    'C' => 'Tengah, samping, luar',
                    'D' => 'Kanan, belakang, atas',
                ],
                'correct_answer' => 'A',
            ],
            [
                'text' => 'Apa yang harus dilakukan saat kebakaran?',
                'options' => [
                    'A' => 'Melarikan diri secepatnya',
                    'B' => 'Berteriak dan mencari pertolongan',
                    'C' => 'Tetap tenang dan gunakan APAR',
                    'D' => 'Menunggu hingga api padam',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Jenis alat pemadam api yang tersedia adalah?',
                'options' => [
                    'A' => 'Foam',
                    'B' => 'CO₂',
                    'C' => 'Dry chemical powder',
                    'D' => 'Air pressure',
                ],
                'correct_answer' => 'C',
            ],
            [
                'text' => 'Di mana letak APAR di ruangan ini?',
                'options' => [
                    'A' => 'Di belakang dan depan',
                    'B' => 'Di atas dan bawah',
                    'C' => 'Di kanan dan kiri',
                    'D' => 'Di tangga dan toilet',
                ],
                'correct_answer' => 'C',
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
