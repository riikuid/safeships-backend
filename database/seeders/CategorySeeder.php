<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Level 1: Pembangunan Dan Pemeliharaan Komitmen
        $level1_1 = Category::create([
            'name' => 'Pembangunan Dan Pemeliharaan Komitmen',
            'code' => '1',
            'parent_id' => null,
        ]);

        // Level 2: Kebijakan K3
        $level2_1_1 = Category::create([
            'name' => 'Kebijakan K3',
            'code' => '1.1',
            'parent_id' => $level1_1->id,
        ]);

        // Level 3: Subkategori Kebijakan K3
        Category::create([
            'name' => 'Terdapat kebijakan K3 yang tertulis, bertanggal, ditandatangani oleh pengusaha atau pengurus, secara jelas menyatakan tujuan dan sasaran K3 serta komitmen terhadap peningkatan K3.',
            'code' => '1.1.1',
            'parent_id' => $level2_1_1->id,
        ]);
        Category::create([
            'name' => 'Kebijakan disusun oleh pengusaha dan/atau pengurus setelah melalui proses konsultasi dengan wakil tenaga kerja.',
            'code' => '1.1.2',
            'parent_id' => $level2_1_1->id,
        ]);
        Category::create([
            'name' => 'Perusahaan mengkomunikasikan kebijakan K3 kepada seluruh tenaga kerja, tamu, kontraktor, pelanggan, dan pemasok dengan tata cara yang tepat.',
            'code' => '1.1.3',
            'parent_id' => $level2_1_1->id,
        ]);
        Category::create([
            'name' => 'Kebijakan khusus dibuat untuk masalah K3 yang bersifat khusus.',
            'code' => '1.1.4',
            'parent_id' => $level2_1_1->id,
        ]);
        Category::create([
            'name' => 'Kebijakan K3 dan kebijakan khusus lainnya ditinjau ulang secara berkala untuk menjamin bahwa kebijakan tersebut sesuai dengan perubahan yang terjadi dalam perusahaan dan dalam peraturan perundangundangan.',
            'code' => '1.1.5',
            'parent_id' => $level2_1_1->id,
        ]);

        // Level 2: Tanggung Jawab dan Wewenang Untuk Bertindak
        $level2_1_2 = Category::create([
            'name' => 'Tanggung Jawab dan Wewenang Untuk Bertindak',
            'code' => '1.2',
            'parent_id' => $level1_1->id,
        ]);

        // Level 3: Subkategori Tanggung Jawab dan Wewenang
        Category::create([
            'name' => 'Tanggung jawab dan wewenang untuk mengambil tindakan dan melaporkan kepada semua pihak yang terkait dalam perusahaan di bidang K3 telah ditetapkan, diinformasikan dan didokumentasikan.',
            'code' => '1.2.1',
            'parent_id' => $level2_1_2->id,
        ]);
        Category::create([
            'name' => 'Penunjukan penanggung jawab K3 harus sesuai peraturan perundang-undangan.',
            'code' => '1.2.2',
            'parent_id' => $level2_1_2->id,
        ]);
        Category::create([
            'name' => 'Pimpinan unit kerja dalam suatu perusahaan bertanggung jawab atas kinerja K3 pada unit kerjanya.',
            'code' => '1.2.3',
            'parent_id' => $level2_1_2->id,
        ]);
        Category::create([
            'name' => 'Pengusaha atau pengurus bertanggung jawab secara penuh untuk menjamin pelaksanaan SMK3.',
            'code' => '1.2.4',
            'parent_id' => $level2_1_2->id,
        ]);
        Category::create([
            'name' => 'Petugas yang bertanggung jawab untuk penanganan keadaan darurat telah ditetapkan dan mendapatkan pelatihan.',
            'code' => '1.2.5',
            'parent_id' => $level2_1_2->id,
        ]);
        Category::create([
            'name' => 'Perusahaan mendapatkan saran-saran dari para ahli di bidang K3 yang berasal dari dalam dan/atau luar perusahaan.',
            'code' => '1.2.6',
            'parent_id' => $level2_1_2->id,
        ]);
        Category::create([
            'name' => 'Kinerja K3 termuat dalam laporan tahunan perusahaan atau laporan lain yang setingkat.',
            'code' => '1.2.7',
            'parent_id' => $level2_1_2->id,
        ]);

        // Level 2: Tinjauan dan Evaluasi
        $level2_1_3 = Category::create([
            'name' => 'Tinjauan dan Evaluasi',
            'code' => '1.3',
            'parent_id' => $level1_1->id,
        ]);

        // Level 3: Subkategori Tinjauan dan Evaluasi
        Category::create([
            'name' => 'Tinjauan terhadap penerapan SMK3 meliputi kebijakan, perencanaan, pelaksanaan, pemantauan dan evaluasi telah dilakukan, dicatat dan didokumentasikan.',
            'code' => '1.3.1',
            'parent_id' => $level2_1_3->id,
        ]);
        Category::create([
            'name' => 'Hasil tinjauan dimasukkan dalam perencanaan tindakan manajemen.',
            'code' => '1.3.2',
            'parent_id' => $level2_1_3->id,
        ]);
        Category::create([
            'name' => 'Pengurus harus meninjau ulang pelaksanaan SMK3 secara berkala untuk menilai kesesuaian dan efektivitas SMK3.',
            'code' => '1.3.3',
            'parent_id' => $level2_1_3->id,
        ]);

        // Level 2: Keterlibatan dan Konsultasi dengan Tenaga Kerja
        $level2_1_4 = Category::create([
            'name' => 'Keterlibatan dan Konsultasi dengan Tenaga Kerja',
            'code' => '1.4',
            'parent_id' => $level1_1->id,
        ]);

        // Level 3: Subkategori Keterlibatan dan Konsultasi
        Category::create([
            'name' => 'Keterlibatan dan penjadwalan konsultasi tenaga kerja dengan wakil perusahaan didokumentasikan dan disebarluaskan ke seluruh tenaga kerja.',
            'code' => '1.4.1',
            'parent_id' => $level2_1_4->id,
        ]);
        Category::create([
            'name' => 'Terdapat prosedur yang memudahkan konsultasi mengenai perubahan-perubahan yang mempunyai implikasi terhadap K3.',
            'code' => '1.4.2',
            'parent_id' => $level2_1_4->id,
        ]);
        Category::create([
            'name' => 'Perusahaan telah membentuk P2K3 Sesuai dengan peraturan perundang-undangan.',
            'code' => '1.4.3',
            'parent_id' => $level2_1_4->id,
        ]);
        Category::create([
            'name' => 'Ketua P2K3 adalah pimpinan puncak atau pengurus.',
            'code' => '1.4.4',
            'parent_id' => $level2_1_4->id,
        ]);
        Category::create([
            'name' => 'Sekretaris P2K3 adalah ahli K3 sesuai dengan peraturan perundang-undangan.',
            'code' => '1.4.5',
            'parent_id' => $level2_1_4->id,
        ]);
        Category::create([
            'name' => 'P2K3 menitikberatkan kegiatan pada pengembangan kebijakan dan prosedur mengendalikan risiko.',
            'code' => '1.4.6',
            'parent_id' => $level2_1_4->id,
        ]);
        Category::create([
            'name' => 'Susunan pengurus P2K3 didokumentasikan dan diinformasikan kepada tenaga kerja.',
            'code' => '1.4.7',
            'parent_id' => $level2_1_4->id,
        ]);
        Category::create([
            'name' => 'P2K3 mengadakan pertemuan secara teratur dan hasilnya disebarluaskan di tempat kerja.',
            'code' => '1.4.8',
            'parent_id' => $level2_1_4->id,
        ]);
        Category::create([
            'name' => 'P2K3 melaporkan kegiatannya secara teratur sesuai dengan peraturan perundangundangan.',
            'code' => '1.4.9',
            'parent_id' => $level2_1_4->id,
        ]);
        Category::create([
            'name' => 'Dibentuk kelompok-kelompok kerja dan dipilih dari wakil-wakil tenaga kerja yang ditunjuk sebagai penanggung jawab K3 di tempat kerjanya dan kepadanya diberikan pelatihan sesuai dengan peraturan perundang-undangan.',
            'code' => '1.4.10',
            'parent_id' => $level2_1_4->id,
        ]);
        Category::create([
            'name' => 'Susunan kelompok-kelompok kerja yang telah terbentuk didokumentasikan dan diinformasikan kepada tenaga kerja.',
            'code' => '1.4.11',
            'parent_id' => $level2_1_4->id,
        ]);

        // Level 1: Pembuatan dan Pendokumentasian Rencana K3
        $level1_2 = Category::create([
            'name' => 'Pembuatan dan Pendokumentasian Rencana K3',
            'code' => '2',
            'parent_id' => null,
        ]);

        // Level 2: Rencana strategi K3
        $level2_2_1 = Category::create([
            'name' => 'Rencana strategi K3',
            'code' => '2.1',
            'parent_id' => $level1_2->id,
        ]);

        // Level 3: Subkategori Rencana strategi K3
        Category::create([
            'name' => 'Terdapat prosedur terdokumentasi untuk identifikasi potensi bahaya, penilaian, dan pengendalian risiko K3.',
            'code' => '2.1.1',
            'parent_id' => $level2_2_1->id,
        ]);
        Category::create([
            'name' => 'Identifikasi potensi bahaya, penilaian, dan pengendalian risiko K3 sebagai rencana strategi K3 dilakukan oleh petugas yang berkompeten.',
            'code' => '2.1.2',
            'parent_id' => $level2_2_1->id,
        ]);
        Category::create([
            'name' => 'Rencana strategi K3 sekurang-kurangya berdasarkan tinjauan awal, identifikasi potensi bahaya, penilaian, pengendalian risiko, dan peraturan perundang-undangan serta informasi K3 lain baik dari dalam maupun luar perusahaan.',
            'code' => '2.1.3',
            'parent_id' => $level2_2_1->id,
        ]);
        Category::create([
            'name' => 'Rencana strategi K3 yang telah ditetapkan digunakan untuk mengendalikan risiko K3 dengan menetapkan tujuan dan sasaran yang dapat diukur dan menjadi prioritas serta menyediakan sumber daya.',
            'code' => '2.1.4',
            'parent_id' => $level2_2_1->id,
        ]);
        Category::create([
            'name' => 'Rencana kerja dan rencana khusus yang berkaitan dengan produk, proses, proyek atau tempat kerja tertentu telah dibuat dengan menetapkan tujuan dan sasaran yang dapat diukur, menetapkan waktu pencapaian dan menyediakan sumber daya.',
            'code' => '2.1.5',
            'parent_id' => $level2_2_1->id,
        ]);
        Category::create([
            'name' => 'Rencana K3 diselaraskan dengan rencana sistem manajemen perusahaan.',
            'code' => '2.1.6',
            'parent_id' => $level2_2_1->id,
        ]);

        // Level 2: Manual SMK3
        $level2_2_2 = Category::create([
            'name' => 'Manual SMK3',
            'code' => '2.2',
            'parent_id' => $level1_2->id,
        ]);

        // Level 3: Subkategori Manual SMK3
        Category::create([
            'name' => 'Manual SMK3 meliputi kebijakan, tujuan, rencana, prosedur K3, instruksi kerja, formulir, catatan dan tanggung jawab serta wewenang tanggung jawab K3 untuk semua tingkatan dalam perusahaan.',
            'code' => '2.2.1',
            'parent_id' => $level2_2_2->id,
        ]);
        Category::create([
            'name' => 'Terdapat manual khusus yang berkaitan dengan produk, proses, atau tempat kerja tertentu.',
            'code' => '2.2.2',
            'parent_id' => $level2_2_2->id,
        ]);
        Category::create([
            'name' => 'Manual SMK3 mudah didapat oleh semua personil dalam perusahaan sesuai kebutuhan.',
            'code' => '2.2.3',
            'parent_id' => $level2_2_2->id,
        ]);

        // Level 2: Peraturan perundangan dan persyaratan lain dibidang K3
        $level2_2_3 = Category::create([
            'name' => 'Peraturan perundangan dan persyaratan lain dibidang K3',
            'code' => '2.3',
            'parent_id' => $level1_2->id,
        ]);

        // Level 3: Subkategori Peraturan perundangan
        Category::create([
            'name' => 'Terdapat prosedur yang terdokumentasi untuk mengidentifikasi, memperoleh, memelihara dan memahami peraturan perundang-undangan, standar, pedoman teknis, dan persyaratan lain yang relevan dibidang K3 untuk seluruh tenaga kerja di perusahaan.',
            'code' => '2.3.1',
            'parent_id' => $level2_2_3->id,
        ]);
        Category::create([
            'name' => 'Penanggung jawab untuk memelihara dan mendistribusikan informasi terbaru mengenai peraturan perundangan, standar, pedoman teknis, dan persyaratan lain telah ditetapkan.',
            'code' => '2.3.2',
            'parent_id' => $level2_2_3->id,
        ]);
        Category::create([
            'name' => 'Persyaratan pada peraturan perundangundangan, standar, pedoman teknis, dan persyaratan lain yang relevan di bidang K3 dimasukkan pada prosedur-prosedur dan petunjuk-petunjuk kerja.',
            'code' => '2.3.3',
            'parent_id' => $level2_2_3->id,
        ]);
        Category::create([
            'name' => 'Perubahan pada peraturan perundangundangan, standar, pedoman teknis, dan persyaratan lain yang relevan di bidang K3 digunakan untuk peninjauan prosedurprosedur dan petunjuk-petunjuk kerja.',
            'code' => '2.3.4',
            'parent_id' => $level2_2_3->id,
        ]);

        // Level 2: Informasi K3
        $level2_2_4 = Category::create([
            'name' => 'Informasi K3',
            'code' => '2.4',
            'parent_id' => $level1_2->id,
        ]);

        // Level 3: Subkategori Informasi K3
        Category::create([
            'name' => 'Informasi yang dibutuhkan mengenai kegiatan K3 disebarluaskan secara sistematis kepada seluruh tenaga kerja, tamu, kontraktor, pelanggan, dan pemasok.',
            'code' => '2.4.1',
            'parent_id' => $level2_2_4->id,
        ]);

        // Level 1: Pengendalian Perancangan dan Peninjauan Kontrak
        $level1_3 = Category::create([
            'name' => 'Pengendalian Perancangan dan Peninjauan Kontrak',
            'code' => '3',
            'parent_id' => null,
        ]);

        // Level 2: Pengendalian Perancangan
        $level2_3_1 = Category::create([
            'name' => 'Pengendalian Perancangan',
            'code' => '3.1',
            'parent_id' => $level1_3->id,
        ]);

        // Level 3: Subkategori Pengendalian Perancangan
        Category::create([
            'name' => 'Prosedur yang terdokumentasi mempertimbangkan identifikasi potensi bahaya, penilaian, dan pengendalian risiko yang dilakukan pada tahap perancangan dan modifikasi.',
            'code' => '3.1.1',
            'parent_id' => $level2_3_1->id,
        ]);
        Category::create([
            'name' => 'Prosedur, instruksi kerja dalam penggunaan produk, pengoperasian mesin dan peralatan, instalasi, pesawat atau proses serta informasi lainnya yang berkaitan dengan K3 telah dikembangkan selama perancangan dan/atau modifikasi.',
            'code' => '3.1.2',
            'parent_id' => $level2_3_1->id,
        ]);
        Category::create([
            'name' => 'Petugas yang berkompeten melakukan verifikasi bahwa perancangan dan/atau modifikasi memenuhi persyaratan K3 yang ditetapkan sebelum penggunaan hasil rancangan.',
            'code' => '3.1.3',
            'parent_id' => $level2_3_1->id,
        ]);
        Category::create([
            'name' => 'Semua perubahan dan modifikasi perancangan yang mempunyai implikasi terhadap K3 diidentifikasikan, didokumentasikan, ditinjau ulang dan disetujui oleh petugas yang berwenang sebelum pelaksanaan.',
            'code' => '3.1.4',
            'parent_id' => $level2_3_1->id,
        ]);

        // Level 2: Peninjauan Kontrak
        $level2_3_2 = Category::create([
            'name' => 'Peninjauan Kontrak',
            'code' => '3.2',
            'parent_id' => $level1_3->id,
        ]);

        // Level 3: Subkategori Peninjauan Kontrak
        Category::create([
            'name' => 'Prosedur yang terdokumentasi harus mampu mengidentifikasi bahaya dan menilai risiko K3 bagi tenaga kerja, lingkungan, dan masyarakat, dimana prosedur tersebut digunakan pada saat memasok barang dan jasa dalam suatu kontrak.',
            'code' => '3.2.1',
            'parent_id' => $level2_3_2->id,
        ]);
        Category::create([
            'name' => 'Identifikasi bahaya dan penilaian risiko dilakukan pada tinjauan kontrak oleh petugas yang berkompeten.',
            'code' => '3.2.2',
            'parent_id' => $level2_3_2->id,
        ]);
        Category::create([
            'name' => 'Kontrak ditinjau ulang untuk menjamin bahwa pemasok dapat memenuhi persyaratan K3 bagi pelanggan.',
            'code' => '3.2.3',
            'parent_id' => $level2_3_2->id,
        ]);
        Category::create([
            'name' => 'Catatan tinjauan kontrak dipelihara dan didokumentasikan.',
            'code' => '3.2.4',
            'parent_id' => $level2_3_2->id,
        ]);

        // Level 1: Pengendalian Dokumen
        $level1_4 = Category::create([
            'name' => 'Pengendalian Dokumen',
            'code' => '4',
            'parent_id' => null,
        ]);

        // Level 2: Persetujuan, Pengeluaran dan Pengendalian Dokumen
        $level2_4_1 = Category::create([
            'name' => 'Persetujuan, Pengeluaran dan Pengendalian Dokumen',
            'code' => '4.1',
            'parent_id' => $level1_4->id,
        ]);

        // Level 3: Subkategori Persetujuan Dokumen
        Category::create([
            'name' => 'Dokumen K3 mempunyai identifikasi status, wewenang, tanggal pengeluaran dan tanggal modifikasi.',
            'code' => '4.1.1',
            'parent_id' => $level2_4_1->id,
        ]);
        Category::create([
            'name' => 'Penerima distribusi dokumen tercantum dalam dokumen tersebut.',
            'code' => '4.1.2',
            'parent_id' => $level2_4_1->id,
        ]);
        Category::create([
            'name' => 'Dokumen K3 edisi terbaru disimpan secara sistematis pada tempat yang ditentukan.',
            'code' => '4.1.3',
            'parent_id' => $level2_4_1->id,
        ]);
        Category::create([
            'name' => 'Dokumen usang segera disingkirkan dari penggunaannya sedangkan dokumen usang yang disimpan untuk keperluan tertentu diberi tanda khusus.',
            'code' => '4.1.4',
            'parent_id' => $level2_4_1->id,
        ]);

        // Level 2: Perubahan dan Modifikasi Dokumen
        $level2_4_2 = Category::create([
            'name' => 'Perubahan dan Modifikasi Dokumen',
            'code' => '4.2',
            'parent_id' => $level1_4->id,
        ]);

        // Level 3: Subkategori Perubahan Dokumen
        Category::create([
            'name' => 'Terdapat sistem untuk membuat, menyetujui perubahan terhadap dokumen K3.',
            'code' => '4.2.1',
            'parent_id' => $level2_4_2->id,
        ]);
        Category::create([
            'name' => 'Dalam hal terjadi perubahan diberikan alasan terjadinya perubahan dan tertera dalam dokumen atau lampirannya dan menginformasikan kepada pihak terkait.',
            'code' => '4.2.2',
            'parent_id' => $level2_4_2->id,
        ]);
        Category::create([
            'name' => 'Terdapat prosedur pengendalian dokumen atau daftar seluruh dokumen yang mencantumkan status dari setiap dokumen tersebut, dalam upaya mencegah penggunaan dokumen yang usang.',
            'code' => '4.2.3',
            'parent_id' => $level2_4_2->id,
        ]);

        // Level 1: Pembelian dan Pengendalian Produk
        $level1_5 = Category::create([
            'name' => 'Pembelian dan Pengendalian Produk',
            'code' => '5',
            'parent_id' => null,
        ]);

        // Level 2: Spesifikasi Pembelian Barang dan Jasa
        $level2_5_1 = Category::create([
            'name' => 'Spesifikasi Pembelian Barang dan Jasa',
            'code' => '5.1',
            'parent_id' => $level1_5->id,
        ]);

        // Level 3: Subkategori Spesifikasi Pembelian
        Category::create([
            'name' => 'Terdapat prosedur yang terdokumentasi yang dapat menjamin bahwa spesifikasi teknik dan informasi lain yang relevan dengan K3 telah diperiksa sebelum keputusan untuk membeli.',
            'code' => '5.1.1',
            'parent_id' => $level2_5_1->id,
        ]);
        Category::create([
            'name' => 'Spesifikasi pembelian untuk setiap sarana produksi, zat kimia atau jasa harus dilengkapi spesifikasi yang sesuai dengan persyaratan peraturan perundang-undangan dan standar K3.',
            'code' => '5.1.2',
            'parent_id' => $level2_5_1->id,
        ]);
        Category::create([
            'name' => 'Konsultasi dengan tenaga kerja yang kompeten pada saat keputusan pembelian, dilakukan untuk menetapkan persyaratan K3 yang dicantumkan dalam spesifikasi pembelian dan diinformasikan kepada tenaga kerja yang menggunakannya.',
            'code' => '5.1.3',
            'parent_id' => $level2_5_1->id,
        ]);
        Category::create([
            'name' => 'Kebutuhan pelatihan, pasokan alat pelindung diri dan perubahan terhadap prosedur kerja harus dipertimbangkan sebelum pembelian dan penggunaannya.',
            'code' => '5.1.4',
            'parent_id' => $level2_5_1->id,
        ]);
        Category::create([
            'name' => 'Persyaratan K3 dievaluasi dan menjadi pertimbangan dalam seleksi pembelian.',
            'code' => '5.1.5',
            'parent_id' => $level2_5_1->id,
        ]);

        // Level 2: Sistem Verifikasi Barang dan Jasa Yang Telah Dibeli
        $level2_5_2 = Category::create([
            'name' => 'Sistem Verifikasi Barang dan Jasa Yang Telah Dibeli',
            'code' => '5.2',
            'parent_id' => $level1_5->id,
        ]);

        // Level 3: Subkategori Verifikasi Barang
        Category::create([
            'name' => 'Barang dan jasa yang dibeli diperiksa kesesuaiannya dengan spesifikasi pembelian.',
            'code' => '5.2.1',
            'parent_id' => $level2_5_2->id,
        ]);

        // Level 2: Pengendalian Barang dan Jasa Yang Dipasok Pelanggan
        $level2_5_3 = Category::create([
            'name' => 'Pengendalian Barang dan Jasa Yang Dipasok Pelanggan',
            'code' => '5.3',
            'parent_id' => $level1_5->id,
        ]);

        // Level 3: Subkategori Pengendalian Barang Pelanggan
        Category::create([
            'name' => 'Barang dan jasa yang dipasok pelanggan, sebelum digunakan terlebih dahulu diidentifikasi potensi bahaya dan dinilai risikonya dan catatan tersebut dipelihara untuk memeriksa prosedur.',
            'code' => '5.3.1',
            'parent_id' => $level2_5_3->id,
        ]);

        // Level 2: Kemampuan Telusur Produk
        $level2_5_4 = Category::create([
            'name' => 'Kemampuan Telusur Produk',
            'code' => '5.4',
            'parent_id' => $level1_5->id,
        ]);

        // Level 3: Subkategori Kemampuan Telusur Produk
        Category::create([
            'name' => 'Semua produk yang digunakan dalam proses produksi dapat diidentifikasi di seluruh tahapan produksi dan instalasi, jika terdapat potensi masalah K3.',
            'code' => '5.4.1',
            'parent_id' => $level2_5_4->id,
        ]);
        Category::create([
            'name' => 'Terdapat prosedur yang terdokumentasi untuk penelusuran produk yang telah terjual, jika terdapat potensi masalah K3 di dalam penggunaannya.',
            'code' => '5.4.2',
            'parent_id' => $level2_5_4->id,
        ]);

        // Level 1: Keamanan Bekerja Berdasarkan SMK3
        $level1_6 = Category::create([
            'name' => 'Keamanan Bekerja Berdasarkan SMK3',
            'code' => '6',
            'parent_id' => null,
        ]);

        // Level 2: Sistem Kerja
        $level2_6_1 = Category::create([
            'name' => 'Sistem Kerja',
            'code' => '6.1',
            'parent_id' => $level1_6->id,
        ]);

        // Level 3: Subkategori Sistem Kerja
        Category::create([
            'name' => 'Petugas yang kompeten telah mengidentifikasi bahaya, menilai dan mengendalikan risiko yang timbul dari suatu proses kerja.',
            'code' => '6.1.1',
            'parent_id' => $level2_6_1->id,
        ]);
        Category::create([
            'name' => 'Apabila upaya pengendalian risiko diperlukan, maka upaya tersebut ditetapkan melalui tingkat pengendalian.',
            'code' => '6.1.2',
            'parent_id' => $level2_6_1->id,
        ]);
        Category::create([
            'name' => 'Terdapat prosedur atau petunjuk kerja yang terdokumentasi untuk mengendalikan risiko yang teridentifikasi dan dibuat atas dasar masukan dari personil yang kompeten serta tenaga kerja yang terkait dan disahkan oleh orang yang berwenang di perusahaan.',
            'code' => '6.1.3',
            'parent_id' => $level2_6_1->id,
        ]);
        Category::create([
            'name' => 'Kepatuhan terhadap peraturan perundangundangan, standar serta pedoman teknis yang relevan diperhatikan pada saat mengembangkan atau melakukan modifikasi atau petunjuk kerja.',
            'code' => '6.1.4',
            'parent_id' => $level2_6_1->id,
        ]);
        Category::create([
            'name' => 'Terdapat sistem izin kerja untuk tugas berisiko tinggi.',
            'code' => '6.1.5',
            'parent_id' => $level2_6_1->id,
        ]);
        Category::create([
            'name' => 'Alat pelindung diri disediakan sesuai kebutuhan dan digunakan secara benar serta selalu dipelihara dalam kondisi layak pakai.',
            'code' => '6.1.6',
            'parent_id' => $level2_6_1->id,
        ]);
        Category::create([
            'name' => 'Alat pelindung diri yang digunakan dipastikan telah dinyatakan layak pakai sesuai dengan standar dan/atau peraturan perundang-undangan yang berlaku.',
            'code' => '6.1.7',
            'parent_id' => $level2_6_1->id,
        ]);
        Category::create([
            'name' => 'Upaya pengendalian risiko dievaluasi secara berkala apabila terjadi ketidaksesuaian atau perubahan pada proses kerja.',
            'code' => '6.1.8',
            'parent_id' => $level2_6_1->id,
        ]);

        // Level 2: Pengawasan
        $level2_6_2 = Category::create([
            'name' => 'Pengawasan',
            'code' => '6.2',
            'parent_id' => $level1_6->id,
        ]);

        // Level 3: Subkategori Pengawasan
        Category::create([
            'name' => 'Dilakukan pengawasan untuk menjamin bahwa setiap pekerjaan dilaksanakan dengan aman dan mengikuti prosedur dan petunjuk kerja yang telah ditentukan.',
            'code' => '6.2.1',
            'parent_id' => $level2_6_2->id,
        ]);
        Category::create([
            'name' => 'Setiap orang diawasi sesuai dengan tingkat kemampuan dan tingkat risiko tugas.',
            'code' => '6.2.2',
            'parent_id' => $level2_6_2->id,
        ]);
        Category::create([
            'name' => 'Pengawas/penyelia ikut serta dalam identifikasi bahaya dan membuat upaya pengendalian.',
            'code' => '6.2.3',
            'parent_id' => $level2_6_2->id,
        ]);
        Category::create([
            'name' => 'Pengawas/penyelia ikut serta dalam melakukan penyelidikan dan pembuatan laporan terhadap terjadinya kecelakaan dan penyakit akibat kerja serta wajib menyerahkan laporan dan saran-saran kepada pengusaha atau pengurus.',
            'code' => '6.2.4',
            'parent_id' => $level2_6_2->id,
        ]);
        Category::create([
            'name' => 'Pengawas/penyelia ikut serta dalam proses konsultasi.',
            'code' => '6.2.5',
            'parent_id' => $level2_6_2->id,
        ]);

        // Level 2: Seleksi dan Penempatan Personil
        $level2_6_3 = Category::create([
            'name' => 'Seleksi dan Penempatan Personil',
            'code' => '6.3',
            'parent_id' => $level1_6->id,
        ]);

        // Level 3: Subkategori Seleksi Personil
        Category::create([
            'name' => 'Persyaratan tugas tertentu termasuk persyaratan kesehatan diidentifikasi dan dipakai untuk menyeleksi dan menempatkan tenaga kerja.',
            'code' => '6.3.1',
            'parent_id' => $level2_6_3->id,
        ]);
        Category::create([
            'name' => 'Penugasan pekerjaan harus berdasarkan kemampuan dan keterampilan serta kewenangan yang dimiliki.',
            'code' => '6.3.2',
            'parent_id' => $level2_6_3->id,
        ]);

        // Level 2: Area Terbatas
        $level2_6_4 = Category::create([
            'name' => 'Area Terbatas',
            'code' => '6.4',
            'parent_id' => $level1_6->id,
        ]);

        // Level 3: Subkategori Area Terbatas
        Category::create([
            'name' => 'Pengusaha atau pengurus melakukan penilaian risiko lingkungan kerja untuk mengetahui daerah-daerah yang memerlukan pembatasan izin masuk.',
            'code' => '6.4.1',
            'parent_id' => $level2_6_4->id,
        ]);
        Category::create([
            'name' => 'Terdapat pengendalian atas daerah/tempat dengan pembatasan izin masuk.',
            'code' => '6.4.2',
            'parent_id' => $level2_6_4->id,
        ]);
        Category::create([
            'name' => 'Tersedianya fasilitas dan layanan di tempat kerja sesuai dengan standar dan pedoman teknis.',
            'code' => '6.4.3',
            'parent_id' => $level2_6_4->id,
        ]);
        Category::create([
            'name' => 'Rambu-rambu K3 harus dipasang sesuai dengan standar dan pedoman teknis.',
            'code' => '6.4.4',
            'parent_id' => $level2_6_4->id,
        ]);

        // Level 2: Pemeliharaan, Perbaikan, dan Perubahan Sarana Produksi
        $level2_6_5 = Category::create([
            'name' => 'Pemeliharaan, Perbaikan, dan Perubahan Sarana Produksi',
            'code' => '6.5',
            'parent_id' => $level1_6->id,
        ]);

        // Level 3: Subkategori Pemeliharaan Sarana Produksi
        Category::create([
            'name' => 'Penjadualan pemeriksaan dan pemeliharaan sarana produksi serta peralatan mencakup verifikasi alat-alat pengaman serta persyaratan yang ditetapkan oleh peraturan perundang-undangan, standar dan pedoman teknis yang relevan.',
            'code' => '6.5.1',
            'parent_id' => $level2_6_5->id,
        ]);
        Category::create([
            'name' => 'Semua catatan yang memuat data secara rinci dari kegiatan pemeriksaan, pemeliharaan, perbaikan dan perubahan yang dilakukan atas sarana dan peralatan produksi harus disimpan dan dipelihara.',
            'code' => '6.5.2',
            'parent_id' => $level2_6_5->id,
        ]);
        Category::create([
            'name' => 'Sarana dan peralatan produksi memiliki sertifikat yang masih berlaku sesuai dengan persyaratan peraturan perundang-undangan dan standar.',
            'code' => '6.5.3',
            'parent_id' => $level2_6_5->id,
        ]);
        Category::create([
            'name' => 'Pemeriksaan, pemeliharaan, perawatan, perbaikan dan setiap perubahan harus dilakukan petugas yang kompeten dan berwenang.',
            'code' => '6.5.4',
            'parent_id' => $level2_6_5->id,
        ]);
        Category::create([
            'name' => 'Terdapat prosedur untuk menjamin bahwa Jika terjadi perubahan terhadap sarana dan peralatan produksi, perubahan tersebut harus sesuai dengan persyaratan peraturan perundang-undangan, standar dan pedoman teknis yang relevan.',
            'code' => '6.5.5',
            'parent_id' => $level2_6_5->id,
        ]);
        Category::create([
            'name' => 'Terdapat prosedur permintaan pemeliharaan sarana dan peralatan produksi dengan kondisi K3 yang tidak memenuhi persyaratan dan perlu segera diperbaiki.',
            'code' => '6.5.6',
            'parent_id' => $level2_6_5->id,
        ]);
        Category::create([
            'name' => 'Terdapat sistem untuk penandaan bagi peralatan yang sudah tidak aman lagi untuk digunakan atau sudah tidak digunakan.',
            'code' => '6.5.7',
            'parent_id' => $level2_6_5->id,
        ]);
        Category::create([
            'name' => 'Apabila diperlukan dilakukan penerapan sistem penguncian pengoperasian (lock out system) untuk mencegah agar sarana produksi tidak dihidupkan sebelum saatnya.',
            'code' => '6.5.8',
            'parent_id' => $level2_6_5->id,
        ]);
        Category::create([
            'name' => 'Terdapat prosedur yang dapat menjamin keselamatan dan kesehatan tenaga kerja atau orang lain yang berada didekat sarana dan peralatan produksi pada saat proses pemeriksaan, pemeliharaan, perbaikan dan perubahan.',
            'code' => '6.5.9',
            'parent_id' => $level2_6_5->id,
        ]);
        Category::create([
            'name' => 'Terdapat penanggung jawab untuk menyetujui bahwa sarana dan peralatan produksi telah aman digunakan setelah proses pemeliharaan, perawatan, perbaikan atau perubahan.',
            'code' => '6.5.10',
            'parent_id' => $level2_6_5->id,
        ]);

        // Level 2: Pelayanan
        $level2_6_6 = Category::create([
            'name' => 'Pelayanan',
            'code' => '6.6',
            'parent_id' => $level1_6->id,
        ]);

        // Level 3: Subkategori Pelayanan
        Category::create([
            'name' => 'Apabila perusahaan dikontrak untuk menyediakan pelayanan yang tunduk pada standar dan peraturan perundang-undangan mengenai K3, maka perlu disusun prosedur untuk menjamin bahwa pelayanan memenuhi persyaratan.',
            'code' => '6.6.1',
            'parent_id' => $level2_6_6->id,
        ]);
        Category::create([
            'name' => 'Apabila perusahaan diberi pelayanan melalui kontrak, dan pelayanan tunduk pada standar dan peraturan perundang-undangan K3, maka perlu disusun prosedur untuk menjamin bahwa pelayanan memenuhi persyaratan.',
            'code' => '6.6.2',
            'parent_id' => $level2_6_6->id,
        ]);

        // Level 2: Kesiapan Untuk Menangani Keadaan Darurat
        $level2_6_7 = Category::create([
            'name' => 'Kesiapan Untuk Menangani Keadaan Darurat',
            'code' => '6.7',
            'parent_id' => $level1_6->id,
        ]);

        // Level 3: Subkategori Kesiapan Keadaan Darurat
        Category::create([
            'name' => 'Keadaan darurat yang potensial di dalam dan/atau di luar tempat kerja telah diidentifikasi dan prosedur keadaan darurat telah didokumentasikan dan diinformasikan agar diketahui oleh seluruh orang yang ada di tempat kerja.',
            'code' => '6.7.1',
            'parent_id' => $level2_6_7->id,
        ]);
        Category::create([
            'name' => 'Penyediaan alat/sarana dan prosedur keadaan darurat berdasarkan hasil identifikasi dan diuji serta ditinjau secara rutin oleh petugas yang berkompeten dan berwenang.',
            'code' => '6.7.2',
            'parent_id' => $level2_6_7->id,
        ]);
        Category::create([
            'name' => 'Tenaga kerja mendapat instruksi dan pelatihan mengenai prosedur keadaan darurat yang sesuai dengan tingkat risiko.',
            'code' => '6.7.3',
            'parent_id' => $level2_6_7->id,
        ]);
        Category::create([
            'name' => 'Petugas penanganan keadaan darurat ditetapkan dan diberikan pelatihan khusus serta diinformasikan kepada seluruh orang yang ada di tempat kerja.',
            'code' => '6.7.4',
            'parent_id' => $level2_6_7->id,
        ]);
        Category::create([
            'name' => 'Instruksi/prosedur keadaan darurat dan hubungan keadaan darurat diperlihatkan secara jelas dan menyolok serta diketahui oleh seluruh tenaga kerja di perusahaan.',
            'code' => '6.7.5',
            'parent_id' => $level2_6_7->id,
        ]);
        Category::create([
            'name' => 'Peralatan, dan sistem tanda bahaya keadaan darurat disediakan, diperiksa, diuji dan dipelihara secara berkala sesuai dengan peraturan perundang-undangan, standar dan pedoman teknis yang relevan.',
            'code' => '6.7.6',
            'parent_id' => $level2_6_7->id,
        ]);
        Category::create([
            'name' => 'Jenis, jumlah, penempatan dan kemudahan untuk mendapatkan alat keadaan darurat telah sesuai dengan peraturan perundangundangan atau standar dan dinilai oleh petugas yang berkompeten dan berwenang.',
            'code' => '6.7.7',
            'parent_id' => $level2_6_7->id,
        ]);

        // Level 2: Pertolongan Pertama Pada Kecelakaan
        $level2_6_8 = Category::create([
            'name' => 'Pertolongan Pertama Pada Kecelakaan',
            'code' => '6.8',
            'parent_id' => $level1_6->id,
        ]);

        // Level 3: Subkategori P3K
        Category::create([
            'name' => 'Perusahaan telah mengevaluasi alat P3K dan menjamin bahwa sistem P3K yang ada memenuhi peraturan perundang-undangan, standar dan pedoman teknis.',
            'code' => '6.8.1',
            'parent_id' => $level2_6_8->id,
        ]);
        Category::create([
            'name' => 'Petugas P3K telah dilatih dan ditunjuk sesuai dengan peraturan perundangan-undangan.',
            'code' => '6.8.2',
            'parent_id' => $level2_6_8->id,
        ]);

        // Level 2: Rencana dan Pemulihan Keadaan Darurat
        $level2_6_9 = Category::create([
            'name' => 'Rencana dan Pemulihan Keadaan Darurat',
            'code' => '6.9',
            'parent_id' => $level1_6->id,
        ]);

        // Level 3: Subkategori Pemulihan Keadaan Darurat
        Category::create([
            'name' => 'Prosedur untuk pemulihan kondisi tenaga kerja maupun sarana dan peralatan produksi yang mengalami kerusakan telah ditetapkan dan dapat diterapkan sesegera mungkin setelah terjadinya kecelakaan dan penyakit akibat kerja.',
            'code' => '6.9.1',
            'parent_id' => $level2_6_9->id,
        ]);

        // Level 1: Standar Pemantauan
        $level1_7 = Category::create([
            'name' => 'Standar Pemantauan',
            'code' => '7',
            'parent_id' => null,
        ]);

        // Level 2: Pemeriksaan Bahaya
        $level2_7_1 = Category::create([
            'name' => 'Pemeriksaan Bahaya',
            'code' => '7.1',
            'parent_id' => $level1_7->id,
        ]);

        // Level 3: Subkategori Pemeriksaan Bahaya
        Category::create([
            'name' => 'Pemeriksaan/inspeksi terhadap tempat kerja dan cara kerja dilaksanakan secara teratur.',
            'code' => '7.1.1',
            'parent_id' => $level2_7_1->id,
        ]);
        Category::create([
            'name' => 'Pemeriksaan/inspeksi dilaksanakan oleh petugas yang berkompeten dan berwenang yang telah memperoleh pelatihan mengenai identifikasi bahaya.',
            'code' => '7.1.2',
            'parent_id' => $level2_7_1->id,
        ]);
        Category::create([
            'name' => 'Pemeriksaan/inspeksi mencari masukan dari tenaga kerja yang melakukan tugas di tempat yang diperiksa.',
            'code' => '7.1.3',
            'parent_id' => $level2_7_1->id,
        ]);
        Category::create([
            'name' => 'Daftar periksa (check list) tempat kerja telah disusun untuk digunakan pada saat pemeriksaan/inspeksi.',
            'code' => '7.1.4',
            'parent_id' => $level2_7_1->id,
        ]);
        Category::create([
            'name' => 'Laporan pemeriksaan/inspeksi berisi rekomendasi untuk tindakan perbaikan dan diajukan kepada pengurus dan P2K3 sesuai dengan kebutuhan.',
            'code' => '7.1.5',
            'parent_id' => $level2_7_1->id,
        ]);
        Category::create([
            'name' => 'Pengusaha atau pengurus telah menetapkan penanggung jawab untuk pelaksanaan tindakan perbaikan dari hasil laporan pemeriksaan/inspeksi.',
            'code' => '7.1.6',
            'parent_id' => $level2_7_1->id,
        ]);
        Category::create([
            'name' => 'Tindakan perbaikan dari hasil laporan pemeriksaan/inspeksi dipantau untuk menentukan efektifitasnya.',
            'code' => '7.1.7',
            'parent_id' => $level2_7_1->id,
        ]);

        // Level 2: Pemantauan/Pengukuran Lingkungan Kerja
        $level2_7_2 = Category::create([
            'name' => 'Pemantauan/Pengukuran Lingkungan Kerja',
            'code' => '7.2',
            'parent_id' => $level1_7->id,
        ]);

        // Level 3: Subkategori Pemantauan Lingkungan Kerja
        Category::create([
            'name' => 'Pemantauan/pengukuran lingkungan kerja dilaksanakan secara teratur dan hasilnya didokumentasikan, dipelihara dan digunakan untuk penilaian dan pengendalian risiko.',
            'code' => '7.2.1',
            'parent_id' => $level2_7_2->id,
        ]);
        Category::create([
            'name' => 'Pemantauan/pengukuran lingkungan kerja meliputi faktor fisik, kimia, biologi, ergonomi dan psikologi.',
            'code' => '7.2.2',
            'parent_id' => $level2_7_2->id,
        ]);
        Category::create([
            'name' => 'Pemantauan/pengukuran lingkungan kerja dilakukan oleh petugas atau pihak yang berkompeten dan berwenang dari dalam dan/atau luar perusahaan.',
            'code' => '7.2.3',
            'parent_id' => $level2_7_2->id,
        ]);

        // Level 2: Peralatan Pemeriksaan/Inspeksi, Pengukuran dan Pengujian
        $level2_7_3 = Category::create([
            'name' => 'Peralatan Pemeriksaan/Inspeksi, Pengukuran dan Pengujian',
            'code' => '7.3',
            'parent_id' => $level1_7->id,
        ]);

        // Level 3: Subkategori Peralatan Pemeriksaan
        Category::create([
            'name' => 'Terdapat prosedur yang terdokumentasi mengenai identifikasi, kalibrasi, pemeliharaan dan penyimpanan untuk alat pemeriksaan, ukur dan uji mengenai K3.',
            'code' => '7.3.1',
            'parent_id' => $level2_7_3->id,
        ]);
        Category::create([
            'name' => 'Alat dipelihara dan dikalibrasi oleh petugas atau pihak yang berkompeten dan berwenang dari dalam dan/atau luar perusahaan.',
            'code' => '7.3.2',
            'parent_id' => $level2_7_3->id,
        ]);

        // Level 2: Pemantauan Kesehatan Tenaga Kerja
        $level2_7_4 = Category::create([
            'name' => 'Pemantauan Kesehatan Tenaga Kerja',
            'code' => '7.4',
            'parent_id' => $level1_7->id,
        ]);

        // Level 3: Subkategori Pemantauan Kesehatan
        Category::create([
            'name' => 'Dilakukan pemantauan kesehatan tenaga kerja yang bekerja pada tempat kerja yang mengandung potensi bahaya tinggi sesuai dengan peraturan perundang-undangan.',
            'code' => '7.4.1',
            'parent_id' => $level2_7_4->id,
        ]);
        Category::create([
            'name' => 'Pengusaha atau pengurus telah melaksanakan identifikasi keadaan dimana pemeriksaan kesehatan tenaga kerja perlu dilakukan dan telah melaksanakan sistem untuk membantu pemeriksaan ini.',
            'code' => '7.4.2',
            'parent_id' => $level2_7_4->id,
        ]);
        Category::create([
            'name' => 'Pemeriksaan kesehatan tenaga kerja dilakukan oleh dokter pemeriksa yang ditunjuk sesuai peraturan perundangundangan.',
            'code' => '7.4.3',
            'parent_id' => $level2_7_4->id,
        ]);
        Category::create([
            'name' => 'Perusahaan menyediakan pelayanan kesehatan kerja sesuai peraturan perundangundangan.',
            'code' => '7.4.4',
            'parent_id' => $level2_7_4->id,
        ]);
        Category::create([
            'name' => 'Catatan mengenai pemantauan kesehatan tenaga kerja dibuat sesuai dengan peraturan perundang-undangan.',
            'code' => '7.4.5',
            'parent_id' => $level2_7_4->id,
        ]);

        // Level 1: Pelaporan dan Perbaikan Kekurangan
        $level1_8 = Category::create([
            'name' => 'Pelaporan dan Perbaikan Kekurangan',
            'code' => '8',
            'parent_id' => null,
        ]);

        // Level 2: Pelaporan Bahaya
        $level2_8_1 = Category::create([
            'name' => 'Pelaporan Bahaya',
            'code' => '8.1',
            'parent_id' => $level1_8->id,
        ]);

        // Level 3: Subkategori Pelaporan Bahaya
        Category::create([
            'name' => 'Terdapat prosedur pelaporan bahaya yang berhubungan dengan K3 dan prosedur ini diketahui oleh tenaga kerja.',
            'code' => '8.1.1',
            'parent_id' => $level2_8_1->id,
        ]);

        // Level 2: Pelaporan Kecelakaan
        $level2_8_2 = Category::create([
            'name' => 'Pelaporan Kecelakaan',
            'code' => '8.2',
            'parent_id' => $level1_8->id,
        ]);

        // Level 3: Subkategori Pelaporan Kecelakaan
        Category::create([
            'name' => 'Terdapat prosedur terdokumentasi yang menjamin bahwa semua kecelakaan kerja, penyakit akibat kerja, kebakaran atau peledakan serta kejadian berbahaya lainnya di tempat kerja dicatat dan dilaporkan sesuai dengan peraturan perundang-undangan.',
            'code' => '8.2.1',
            'parent_id' => $level2_8_2->id,
        ]);

        // Level 2: Pemeriksaan dan pengkajian Kecelakaan
        $level2_8_3 = Category::create([
            'name' => 'Pemeriksaan dan pengkajian Kecelakaan',
            'code' => '8.3',
            'parent_id' => $level1_8->id,
        ]);

        // Level 3: Subkategori Pemeriksaan Kecelakaan
        Category::create([
            'name' => 'Tempat kerja/perusahaan mempunyai prosedur pemeriksaan dan pengkajian kecelakaan kerja dan penyakit akibat kerja.',
            'code' => '8.3.1',
            'parent_id' => $level2_8_3->id,
        ]);
        Category::create([
            'name' => 'Pemeriksaan dan pengkajian kecelakaan kerja dilakukan oleh petugas atau Ahli K3 yang ditunjuk sesuai peraturan perundangundangan atau pihak lain yang berkompeten dan berwenang.',
            'code' => '8.3.2',
            'parent_id' => $level2_8_3->id,
        ]);
        Category::create([
            'name' => 'Laporan pemeriksaan dan pengkajian berisi tentang sebab dan akibat serta rekomendasi/saran dan jadwal waktu pelaksanaan usaha perbaikan.',
            'code' => '8.3.3',
            'parent_id' => $level2_8_3->id,
        ]);
        Category::create([
            'name' => 'Penanggung jawab untuk melaksanakan tindakan perbaikan atas laporan pemeriksaan dan pengkajian telah ditetapkan.',
            'code' => '8.3.4',
            'parent_id' => $level2_8_3->id,
        ]);
        Category::create([
            'name' => 'Tindakan perbaikan diinformasikan kepada tenaga kerja yang bekerja di tempat terjadinya kecelakaan.',
            'code' => '8.3.5',
            'parent_id' => $level2_8_3->id,
        ]);
        Category::create([
            'name' => 'Pelaksanaan tindakan perbaikan dipantau, didokumentasikan dan diinformasikan ke seluruh tenaga kerja.',
            'code' => '8.3.6',
            'parent_id' => $level2_8_3->id,
        ]);

        // Level 2: Penanganan Masalah
        $level2_8_4 = Category::create([
            'name' => 'Penanganan Masalah',
            'code' => '8.4',
            'parent_id' => $level1_8->id,
        ]);

        // Level 3: Subkategori Penanganan Masalah
        Category::create([
            'name' => 'Terdapat prosedur untuk menangani masalah keselamatan dan kesehatan yang timbul dan sesuai dengan peraturan perundangundangan yang berlaku.',
            'code' => '8.4.1',
            'parent_id' => $level2_8_4->id,
        ]);

        // Level 1: Pengelolaan Material dan Perpindahannya
        $level1_9 = Category::create([
            'name' => 'Pengelolaan Material dan Perpindahannya',
            'code' => '9',
            'parent_id' => null,
        ]);

        // Level 2: Penanganan Secara Manual dan Mekanis
        $level2_9_1 = Category::create([
            'name' => 'Penanganan Secara Manual dan Mekanis',
            'code' => '9.1',
            'parent_id' => $level1_9->id,
        ]);

        // Level 3: Subkategori Penanganan Manual dan Mekanis
        Category::create([
            'name' => 'Terdapat prosedur untuk mengidentifikasi potensi bahaya dan menilai risiko yang berhubungan dengan penanganan secara manual dan mekanis.',
            'code' => '9.1.1',
            'parent_id' => $level2_9_1->id,
        ]);
        Category::create([
            'name' => 'Identifikasi bahaya dan penilaian risiko dilaksanakan oleh petugas yang berkompeten dan berwenang.',
            'code' => '9.1.2',
            'parent_id' => $level2_9_1->id,
        ]);
        Category::create([
            'name' => 'Pengusaha atau pengurus menerapkan dan meninjau cara pengendalian risiko yang berhubungan dengan penanganan secara manual atau mekanis.',
            'code' => '9.1.3',
            'parent_id' => $level2_9_1->id,
        ]);
        Category::create([
            'name' => 'Terdapat prosedur untuk penanganan bahan meliputi metode pencegahan terhadap kerusakan, tumpahan dan/atau kebocoran.',
            'code' => '9.1.4',
            'parent_id' => $level2_9_1->id,
        ]);

        // Level 2: Sistem Pengangkutan, Penyimpanan dan Pembuangan
        $level2_9_2 = Category::create([
            'name' => 'Sistem Pengangkutan, Penyimpanan dan Pembuangan',
            'code' => '9.2',
            'parent_id' => $level1_9->id,
        ]);

        // Level 3: Subkategori Pengangkutan dan Penyimpanan
        Category::create([
            'name' => 'Terdapat prosedur yang menjamin bahwa bahan disimpan dan dipindahkan dengan cara yang aman sesuai dengan peraturan perundang-undangan.',
            'code' => '9.2.1',
            'parent_id' => $level2_9_2->id,
        ]);
        Category::create([
            'name' => 'Terdapat prosedur yang menjelaskan persyaratan pengendalian bahan yang dapat rusak atau kadaluarsa.',
            'code' => '9.2.2',
            'parent_id' => $level2_9_2->id,
        ]);
        Category::create([
            'name' => 'Terdapat prosedur yang menjamin bahwa bahan dibuang dengan cara yang aman sesuai dengan peraturan perundangundangan.',
            'code' => '9.2.3',
            'parent_id' => $level2_9_2->id,
        ]);

        // Level 2: Pengendalian Bahan Kimia Berbahaya (BKB)
        $level2_9_3 = Category::create([
            'name' => 'Pengendalian Bahan Kimia Berbahaya (BKB)',
            'code' => '9.3',
            'parent_id' => $level1_9->id,
        ]);

        // Level 3: Subkategori Pengendalian BKB
        Category::create([
            'name' => 'Perusahaan telah mendokumentasikan dan menerapkan prosedur mengenai penyimpanan, penanganan dan pemindahan BKB sesuai dengan persyaratan peraturan perundang-undangan, standar dan pedoman teknis yang relevan.',
            'code' => '9.3.1',
            'parent_id' => $level2_9_3->id,
        ]);
        Category::create([
            'name' => 'Terdapat Lembar Data Keselamatan BKB (Material Safety Data Sheets) meliputi keterangan mengenai keselamatan bahan sebagaimana diatur pada peraturan perundang-undangan dan dengan mudah dapat diperoleh.',
            'code' => '9.3.2',
            'parent_id' => $level2_9_3->id,
        ]);
        Category::create([
            'name' => 'Terdapat sistem untuk mengidentifikasi dan pemberian label secara jelas pada bahan kimia berbahaya.',
            'code' => '9.3.3',
            'parent_id' => $level2_9_3->id,
        ]);
        Category::create([
            'name' => 'Rambu peringatan bahaya terpasang sesuai dengan persyaratan peraturan perundangundangan dan/atau standar yang relevan.',
            'code' => '9.3.4',
            'parent_id' => $level2_9_3->id,
        ]);
        Category::create([
            'name' => 'Penanganan BKB dilakukan oleh petugas yang berkompeten dan berwenang.',
            'code' => '9.3.5',
            'parent_id' => $level2_9_3->id,
        ]);

        // Level 1: Pengumpulan Dan Penggunaan Data
        $level1_10 = Category::create([
            'name' => 'Pengumpulan Dan Penggunaan Data',
            'code' => '10',
            'parent_id' => null,
        ]);

        // Level 2: Catatan K3
        $level2_10_1 = Category::create([
            'name' => 'Catatan K3',
            'code' => '10.1',
            'parent_id' => $level1_10->id,
        ]);

        // Level 3: Subkategori Catatan K3
        Category::create([
            'name' => 'Pengusaha atau pengurus telah mendokumentasikan dan menerapkan prosedur pelaksanaan identifikasi, pengumpulan, pengarsipan, pemeliharaan, penyimpanan dan penggantian catatan K3.',
            'code' => '10.1.1',
            'parent_id' => $level2_10_1->id,
        ]);
        Category::create([
            'name' => 'Peraturan perundang-undangan, standar dan pedoman teknis K3 yang relevan dipelihara pada tempat yang mudah didapat.',
            'code' => '10.1.2',
            'parent_id' => $level2_10_1->id,
        ]);
        Category::create([
            'name' => 'Terdapat prosedur yang menentukan persyaratan untuk menjaga kerahasiaan catatan.',
            'code' => '10.1.3',
            'parent_id' => $level2_10_1->id,
        ]);
        Category::create([
            'name' => 'Catatan kompensasi kecelakaan dan rehabilitasi kesehatan tenaga kerja dipelihara.',
            'code' => '10.1.4',
            'parent_id' => $level2_10_1->id,
        ]);

        // Level 2: Data dan Laporan K3
        $level2_10_2 = Category::create([
            'name' => 'Data dan Laporan K3',
            'code' => '10.2',
            'parent_id' => $level1_10->id,
        ]);

        // Level 3: Subkategori Data dan Laporan K3
        Category::create([
            'name' => 'Data K3 yang terbaru dikumpulkan dan dianalisa.',
            'code' => '10.2.1',
            'parent_id' => $level2_10_2->id,
        ]);
        Category::create([
            'name' => 'Laporan rutin kinerja K3 dibuat dan disebarluaskan di dalam tempat kerja.',
            'code' => '10.2.2',
            'parent_id' => $level2_10_2->id,
        ]);

        // Level 1: Pemeriksaan SMK3
        $level1_11 = Category::create([
            'name' => 'Pemeriksaan SMK3',
            'code' => '11',
            'parent_id' => null,
        ]);

        // Level 2: Audit Internal SMK3
        $level2_11_1 = Category::create([
            'name' => 'Audit Internal SMK3',
            'code' => '11.1',
            'parent_id' => $level1_11->id,
        ]);

        // Level 3: Subkategori Audit Internal SMK3
        Category::create([
            'name' => 'Audit internal SMK3 yang terjadwal dilaksanakan untuk memeriksa kesesuaian kegiatan perencanaan dan untuk menentukan efektifitas kegiatan tersebut.',
            'code' => '11.1.1',
            'parent_id' => $level2_11_1->id,
        ]);
        Category::create([
            'name' => 'Audit internal SMK3 dilakukan oleh petugas yang independen, berkompeten dan berwenang.',
            'code' => '11.1.2',
            'parent_id' => $level2_11_1->id,
        ]);
        Category::create([
            'name' => 'Laporan audit didistribusikan kepada pengusaha atau pengurus dan petugas lain yang berkepentingan dan dipantau untuk menjamin dilakukannya tindakan perbaikan.',
            'code' => '11.1.3',
            'parent_id' => $level2_11_1->id,
        ]);

        // Level 1: Pengembangan Keterampilan dan Kemampuan
        $level1_12 = Category::create([
            'name' => 'Pengembangan Keterampilan dan Kemampuan',
            'code' => '12',
            'parent_id' => null,
        ]);

        // Level 2: Strategi Pelatihan
        $level2_12_1 = Category::create([
            'name' => 'Strategi Pelatihan',
            'code' => '12.1',
            'parent_id' => $level1_12->id,
        ]);

        // Level 3: Subkategori Strategi Pelatihan
        Category::create([
            'name' => 'Analisis kebutuhan pelatihan K3 sesuai persyaratan peraturan perundang-undangan telah dilakukan.',
            'code' => '12.1.1',
            'parent_id' => $level2_12_1->id,
        ]);
        Category::create([
            'name' => 'Rencana pelatihan K3 bagi semua tingkatan telah disusun.',
            'code' => '12.1.2',
            'parent_id' => $level2_12_1->id,
        ]);
        Category::create([
            'name' => 'Jenis pelatihan K3 yang dilakukan harus disesuaikan dengan kebutuhan untuk pengendalian potensi bahaya.',
            'code' => '12.1.3',
            'parent_id' => $level2_12_1->id,
        ]);
        Category::create([
            'name' => 'Pelatihan dilakukan oleh orang atau badan yang berkompeten dan berwenang sesuai peraturan perundang-undangan.',
            'code' => '12.1.4',
            'parent_id' => $level2_12_1->id,
        ]);
        Category::create([
            'name' => 'Terdapat fasilitas dan sumber daya memadai untuk pelaksanaan pelatihan yang efektif.',
            'code' => '12.1.5',
            'parent_id' => $level2_12_1->id,
        ]);
        Category::create([
            'name' => 'Pengusaha atau pengurus mendokumentasikan dan menyimpan catatan seluruh pelatihan.',
            'code' => '12.1.6',
            'parent_id' => $level2_12_1->id,
        ]);
        Category::create([
            'name' => 'Program pelatihan ditinjau secara teratur untuk menjamin agar tetap relevan dan efektif.',
            'code' => '12.1.7',
            'parent_id' => $level2_12_1->id,
        ]);

        // Level 2: Pelatihan Bagi Manajemen dan Penyelia
        $level2_12_2 = Category::create([
            'name' => 'Pelatihan Bagi Manajemen dan Penyelia',
            'code' => '12.2',
            'parent_id' => $level1_12->id,
        ]);

        // Level 3: Subkategori Pelatihan Manajemen dan Penyelia
        Category::create([
            'name' => 'Anggota manajemen eksekutif dan pengurus berperan serta dalam pelatihan yang mencakup penjelasan tentang kewajiban hukum dan prinsip-prinsip serta pelaksanaan K3.',
            'code' => '12.2.1',
            'parent_id' => $level2_12_2->id,
        ]);
        Category::create([
            'name' => 'Manajer dan pengawas/penyelia menerima pelatihan yang sesuai dengan peran dan tanggung jawab mereka.',
            'code' => '12.2.2',
            'parent_id' => $level2_12_2->id,
        ]);

        // Level 2: Pelatihan Bagi Tenaga Kerja
        $level2_12_3 = Category::create([
            'name' => 'Pelatihan Bagi Tenaga Kerja',
            'code' => '12.3',
            'parent_id' => $level1_12->id,
        ]);

        // Level 3: Subkategori Pelatihan Tenaga Kerja
        Category::create([
            'name' => 'Pelatihan diberikan kepada semua tenaga kerja termasuk tenaga kerja baru dan yang dipindahkan agar mereka dapat melaksanakan tugasnya secara aman.',
            'code' => '12.3.1',
            'parent_id' => $level2_12_3->id,
        ]);
        Category::create([
            'name' => 'Pelatihan diberikan kepada tenaga kerja apabila di tempat kerjanya terjadi perubahan sarana produksi atau proses.',
            'code' => '12.3.2',
            'parent_id' => $level2_12_3->id,
        ]);
        Category::create([
            'name' => 'Pengusaha atau pengurus memberikan pelatihan penyegaran kepada semua tenaga kerja.',
            'code' => '12.3.3',
            'parent_id' => $level2_12_3->id,
        ]);

        // Level 2: Pelatihan Pengenalan dan Pelatihan Untuk Pengunjung dan Kontraktor
        $level2_12_4 = Category::create([
            'name' => 'Pelatihan Pengenalan dan Pelatihan Untuk Pengunjung dan Kontraktor',
            'code' => '12.4',
            'parent_id' => $level1_12->id,
        ]);

        // Level 3: Subkategori Pelatihan Pengunjung dan Kontraktor
        Category::create([
            'name' => 'Terdapat prosedur yang menetapkan persyaratan untuk memberikan taklimat (briefing) kepada pengunjung dan mitra kerja guna menjamin K3.',
            'code' => '12.4.1',
            'parent_id' => $level2_12_4->id,
        ]);

        // Level 2: Pelatihan Keahlian Khusus
        $level2_12_5 = Category::create([
            'name' => 'Pelatihan Keahlian Khusus',
            'code' => '12.5',
            'parent_id' => $level1_12->id,
        ]);

        // Level 3: Subkategori Pelatihan Keahlian Khusus
        Category::create([
            'name' => 'Perusahaan mempunyai sistem yang menjamin kepatuhan terhadap persyaratan lisensi atau kualifikasi sesuai dengan peraturan perundangan untuk melaksanakan tugas khusus, melaksanakan pekerjaan atau mengoperasikan peralatan.',
            'code' => '12.5.1',
            'parent_id' => $level2_12_5->id,
        ]);
    }
}
