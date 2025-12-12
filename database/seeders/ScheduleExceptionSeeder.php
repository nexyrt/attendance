<?php

namespace Database\Seeders;

use App\Models\ScheduleException;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ScheduleExceptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua ID departemen untuk digunakan pada pengecualian umum
        $allDepartmentIds = Department::pluck('id');

        // Ambil ID departemen yang spesifik
        $digitalMarketingDepartmentId = Department::where('name', 'Digital Marketing')->value('id');

        // Pengecualian jadwal yang berlaku untuk SEMUA departemen (Libur Nasional & Acara Umum)
        $generalExceptions = [
            // National Holidays 2025
            [
                'title' => 'Tahun Baru Masehi',
                'date' => '2025-01-01',
                'status' => 'holiday',
                'note' => 'Libur nasional Tahun Baru Masehi',
            ],
            [
                'title' => 'Hari Raya Idul Fitri',
                'date' => '2025-03-30',
                'status' => 'holiday',
                'note' => 'Libur nasional Hari Raya Idul Fitri (estimasi)',
            ],
            [
                'title' => 'Hari Raya Idul Fitri',
                'date' => '2025-03-31',
                'status' => 'holiday',
                'note' => 'Libur nasional Hari Raya Idul Fitri (estimasi)',
            ],
            [
                'title' => 'Hari Buruh',
                'date' => '2025-05-01',
                'status' => 'holiday',
                'note' => 'Libur nasional Hari Buruh',
            ],
            [
                'title' => 'Hari Kemerdekaan RI',
                'date' => '2025-08-17',
                'status' => 'holiday',
                'note' => 'Libur nasional Hari Kemerdekaan Indonesia',
            ],
            [
                'title' => 'Hari Natal',
                'date' => '2025-12-25', // Termasuk di bulan Desember 2025
                'status' => 'holiday',
                'note' => 'Libur nasional Hari Natal',
            ],
            // **TAMBAHAN UNTUK DESEMBER 2025 (Cuti Bersama Natal/Tahun Baru)**
            [
                'title' => 'Cuti Bersama Natal',
                'date' => '2025-12-24',
                'status' => 'holiday',
                'note' => 'Cuti Bersama Hari Raya Natal',
            ],
            [
                'title' => 'Cuti Bersama Tahun Baru',
                'date' => '2025-12-26',
                'status' => 'holiday',
                'note' => 'Cuti Bersama Hari Raya Natal (Pengganti Cuti Bersama Tahun Baru)',
            ],
            // Company Events (General)
            [
                'title' => 'Rapat Tahunan Perusahaan',
                'date' => '2025-06-15',
                'status' => 'event',
                'start_time' => '09:00:00',
                'end_time' => '15:00:00',
                'late_tolerance' => 15,
                'note' => 'Rapat tahunan seluruh karyawan, jam kerja disesuaikan',
            ],
            [
                'title' => 'Team Building Day',
                'date' => '2025-09-10',
                'status' => 'event',
                'start_time' => '08:30:00',
                'end_time' => '16:30:00',
                'late_tolerance' => 30,
                'note' => 'Acara team building perusahaan',
            ],
            [
                'title' => 'Training IT Security',
                'date' => '2025-04-20',
                'status' => 'event',
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'late_tolerance' => 10,
                'note' => 'Pelatihan keamanan IT untuk semua departemen',
            ],
        ];

        // Tambahkan kolom timestamps dan nilai default null untuk kolom opsional
        $timestamp = Carbon::now();
        $defaults = [
            'start_time' => null,
            'end_time' => null,
            'late_tolerance' => null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];

        $insertData = collect($generalExceptions)->map(function ($item) use ($defaults) {
            return array_merge($defaults, $item);
        })->toArray();

        // Mass Insert untuk pengecualian umum (lebih cepat)
        ScheduleException::insert($insertData);

        // --- Pengecualian Departemen Spesifik (Perlu Loop dan Attach) ---
        $specificExceptions = [
            [
                'title' => 'Workshop Digital Marketing',
                'date' => '2025-07-22',
                'status' => 'event',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'late_tolerance' => 20,
                'note' => 'Workshop khusus untuk tim Digital Marketing',
                'department_ids' => [$digitalMarketingDepartmentId], // Departemen spesifik
            ],
        ];

        // Looping untuk pengecualian spesifik (jika ada) dan melakukan attach
        foreach ($specificExceptions as $exceptionData) {
            // Ambil ID departemen, lalu hapus dari array sebelum membuat record utama
            $deptIds = $exceptionData['department_ids'];
            unset($exceptionData['department_ids']);

            // Buat record pengecualian
            $scheduleException = ScheduleException::create($exceptionData);

            // Attach departemen terkait
            $scheduleException->departments()->attach($deptIds);
        }

        // Karena kita menggunakan mass insert untuk general exceptions, 
        // kita perlu melakukan attach untuk semua general exceptions yang baru saja dimasukkan.
        // Ini perlu jika ada relasi Many-to-Many yang harus diisi.

        // Jika Anda menggunakan relasi many-to-many, kita harus mengambil ID yang baru saja dibuat
        // (Mass insert tidak mengembalikan ID) dan melakukan attach.
        // Cara yang paling efisien adalah kembali menggunakan `create` jika jumlahnya tidak terlalu besar, 
        // atau melakukan attach setelah mass insert.

        // **Alternatif Efisien untuk Attach (Lebih disarankan jika ada relasi M:M):**
        // Saya akan kembali ke metode create() & attach() di dalam loop, namun dengan perbaikan 
        // logika attach agar lebih efisien dan mudah dibaca.

        // --- Versi Lebih Efisien dengan Relasi M:M yang Benar (Menggantikan Kode di Atas) ---
        $allExceptions = array_merge($generalExceptions, $specificExceptions);

        foreach ($allExceptions as $exceptionData) {
            // Tentukan ID departemen yang akan di-attach
            if (isset($exceptionData['department_ids'])) {
                $deptIds = $exceptionData['department_ids'];
                unset($exceptionData['department_ids']); // Hapus key department_ids
            } else {
                // Jika tidak ada department_ids, berarti untuk semua departemen
                $deptIds = $allDepartmentIds;
            }

            // Tambahkan nilai default untuk kolom opsional yang hilang (jika mass insertion tidak digunakan)
            if (!isset($exceptionData['start_time']))
                $exceptionData['start_time'] = null;
            if (!isset($exceptionData['end_time']))
                $exceptionData['end_time'] = null;
            if (!isset($exceptionData['late_tolerance']))
                $exceptionData['late_tolerance'] = null;

            // Buat record pengecualian
            $scheduleException = ScheduleException::create($exceptionData);

            // Attach departemen terkait
            $scheduleException->departments()->attach($deptIds);
        }

        // Catatan: Saya mengembalikan loop `create` karena relasi Many-to-Many 
        // (`departments()->attach()`) tidak bisa dilakukan dengan `insert()`. 
        // Saya hanya membuat logikanya lebih ringkas.

        // Jika model ScheduleException Anda tidak memiliki relasi Many-to-Many ke Department, 
        // maka blok kode pertama (dengan mass insertion) lebih efisien.

    }
}