<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Activity;
use App\Models\ActivityBill;
use App\Models\Classroom;
use App\Models\FeeRate;
use App\Models\Institution;
use App\Models\MonthlyBill;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentPlacement;
use Illuminate\Database\Seeder;

class SppSeeder extends Seeder
{
    public function run(): void
    {
        // Create Institutions
        $mi = Institution::create(['name' => 'MI', 'code' => 'mi']);
        $smp = Institution::create(['name' => 'SMP', 'code' => 'smp']);

        // Create Academic Years
        $ay2025 = AcademicYear::create(['name' => '2025/2026', 'is_active' => false]);
        $ay2026 = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);

        // Create Classrooms for 2026/2027
        $miClasses = [];
        foreach (['1A', '1B', '2A', '2B', '3A', '3B', '4A', '4B', '5A', '5B', '6A', '6B'] as $name) {
            $miClasses[$name] = Classroom::create([
                'academic_year_id' => $ay2026->id,
                'institution_id' => $mi->id,
                'name' => 'Kelas ' . $name,
            ]);
        }

        $smpClasses = [];
        foreach (['7A', '7B', '8A', '8B', '9A', '9B'] as $name) {
            $smpClasses[$name] = Classroom::create([
                'academic_year_id' => $ay2026->id,
                'institution_id' => $smp->id,
                'name' => 'Kelas ' . $name,
            ]);
        }

        // Also create classrooms for previous year
        foreach (['1A', '1B', '2A', '2B', '3A', '3B', '4A', '4B', '5A', '5B', '6A', '6B'] as $name) {
            Classroom::create([
                'academic_year_id' => $ay2025->id,
                'institution_id' => $mi->id,
                'name' => 'Kelas ' . $name,
            ]);
        }
        foreach (['7A', '7B', '8A', '8B', '9A', '9B'] as $name) {
            Classroom::create([
                'academic_year_id' => $ay2025->id,
                'institution_id' => $smp->id,
                'name' => 'Kelas ' . $name,
            ]);
        }

        // Create Fee Rates
        FeeRate::create([
            'academic_year_id' => $ay2026->id,
            'institution_id' => $mi->id,
            'name' => 'SPP Bulanan',
            'amount' => 250000,
        ]);

        FeeRate::create([
            'academic_year_id' => $ay2026->id,
            'institution_id' => $smp->id,
            'name' => 'SPP Bulanan',
            'amount' => 350000,
        ]);

        FeeRate::create([
            'academic_year_id' => $ay2025->id,
            'institution_id' => $mi->id,
            'name' => 'SPP Bulanan',
            'amount' => 225000,
        ]);

        FeeRate::create([
            'academic_year_id' => $ay2025->id,
            'institution_id' => $smp->id,
            'name' => 'SPP Bulanan',
            'amount' => 325000,
        ]);

        // Create MI Students
        $miStudents = [];
        $miNames = [
            'Ahmad Fauzi', 'Siti Aisyah', 'Muhammad Rizki', 'Fatimah Zahra', 'Abdullah Rahman',
            'Khadijah Nur', 'Umar Faruq', 'Zainab Putri', 'Ali Akbar', 'Maryam Salsabila',
            'Hasan Basri', 'Hafizah Aulia', 'Ibrahim Malik', 'Ruqayyah Dewi', 'Yusuf Hakim',
            'Aminah Safira', 'Bilal Ramadhan', 'Aisyah Rahmah', 'Khalid Zain', 'Nadia Husna',
            'Hamzah Putra', 'Laila Fitri', 'Salman Alfarisi', 'Zahra Amelia', 'Taufik Hidayat',
            'Nurul Aini', 'Daud Firmansyah', 'Halimah Sari', 'Ismail Harun', 'Safiyyah Nur',
        ];

        foreach ($miNames as $i => $name) {
            $miStudents[] = Student::create([
                'institution_id' => $mi->id,
                'nis' => 'MI' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'nisn' => '00' . str_pad($i + 1, 8, '0', STR_PAD_LEFT),
                'name' => $name,
                'domicile' => fake()->randomElement(['kota_tangerang', 'luar_kota_tangerang']),
                'is_active' => true,
            ]);
        }

        // Create SMP Students
        $smpStudents = [];
        $smpNames = [
            'Rafi Pratama', 'Anisa Putri', 'Dimas Arya', 'Nabila Azzahra', 'Farhan Maulana',
            'Syifa Ramadhani', 'Galang Saputra', 'Alya Nurhaliza', 'Bayu Setiawan', 'Cantika Dewi',
            'Eka Prasetyo', 'Fitri Handayani', 'Gilang Ramadhan', 'Hana Safitri', 'Irfan Hakim',
            'Jasmine Aulia', 'Kevin Wijaya', 'Lestari Ningrum', 'Muhamad Iqbal', 'Nisa Amalia',
            'Oscar Firmansyah', 'Putri Maharani', 'Qori Hidayat', 'Rina Wulandari', 'Surya Adi',
        ];

        foreach ($smpNames as $i => $name) {
            // Mix domicile: first 10 kota_tangerang (free), rest luar
            $domicile = $i < 10 ? 'kota_tangerang' : 'luar_kota_tangerang';
            $smpStudents[] = Student::create([
                'institution_id' => $smp->id,
                'nis' => 'SMP' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'nisn' => '01' . str_pad($i + 1, 8, '0', STR_PAD_LEFT),
                'name' => $name,
                'domicile' => $domicile,
                'is_active' => true,
            ]);
        }

        // Place MI students into classes
        $miClassKeys = array_keys($miClasses);
        foreach ($miStudents as $i => $student) {
            $classKey = $miClassKeys[$i % count($miClassKeys)];
            StudentPlacement::create([
                'student_id' => $student->id,
                'academic_year_id' => $ay2026->id,
                'classroom_id' => $miClasses[$classKey]->id,
            ]);
        }

        // Place SMP students into classes
        $smpClassKeys = array_keys($smpClasses);
        foreach ($smpStudents as $i => $student) {
            $classKey = $smpClassKeys[$i % count($smpClassKeys)];
            StudentPlacement::create([
                'student_id' => $student->id,
                'academic_year_id' => $ay2026->id,
                'classroom_id' => $smpClasses[$classKey]->id,
            ]);
        }

        // Generate Monthly Bills for current year (July - December 2026)
        $miFeeRate = FeeRate::where('academic_year_id', $ay2026->id)
            ->where('institution_id', $mi->id)
            ->first();
        $smpFeeRate = FeeRate::where('academic_year_id', $ay2026->id)
            ->where('institution_id', $smp->id)
            ->first();

        // MI: all students get monthly bills
        foreach ($miStudents as $student) {
            for ($month = 7; $month <= 12; $month++) {
                MonthlyBill::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $ay2026->id,
                    'fee_rate_id' => $miFeeRate->id,
                    'month' => $month,
                    'amount' => $miFeeRate->amount,
                    'paid_amount' => 0,
                    'status' => 'unpaid',
                ]);
            }
        }

        // SMP: only luar_kota_tangerang students get monthly bills
        foreach ($smpStudents as $student) {
            if ($student->isExemptFromMonthlyFee()) {
                continue;
            }
            for ($month = 7; $month <= 12; $month++) {
                MonthlyBill::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $ay2026->id,
                    'fee_rate_id' => $smpFeeRate->id,
                    'month' => $month,
                    'amount' => $smpFeeRate->amount,
                    'paid_amount' => 0,
                    'status' => 'unpaid',
                ]);
            }
        }

        // Create some payments (simulate some students have paid July-August)
        $paidMonths = [7, 8];
        $paymentCount = 0;
        foreach (array_slice($miStudents, 0, 15) as $student) {
            foreach ($paidMonths as $month) {
                $bill = MonthlyBill::where('student_id', $student->id)
                    ->where('month', $month)
                    ->where('academic_year_id', $ay2026->id)
                    ->first();

                if ($bill) {
                    $paymentCount++;
                    Payment::create([
                        'transaction_number' => 'TRX-20260801-' . str_pad($paymentCount, 4, '0', STR_PAD_LEFT),
                        'student_id' => $student->id,
                        'academic_year_id' => $ay2026->id,
                        'billable_type' => MonthlyBill::class,
                        'billable_id' => $bill->id,
                        'amount' => $bill->amount,
                        'payment_method' => fake()->randomElement(['cash', 'transfer']),
                        'payment_date' => fake()->dateTimeBetween('2026-07-01', '2026-08-31'),
                        'notes' => null,
                        'status' => 'valid',
                        'created_by' => 1,
                    ]);

                    $bill->update(['paid_amount' => $bill->amount, 'status' => 'paid']);
                }
            }
        }

        // Create some partial payments for SMP
        foreach (array_slice($smpStudents, 10, 5) as $student) {
            $bill = MonthlyBill::where('student_id', $student->id)
                ->where('month', 7)
                ->where('academic_year_id', $ay2026->id)
                ->first();

            if ($bill) {
                $paymentCount++;
                $partialAmount = (int) ($bill->amount * 0.5);
                Payment::create([
                    'transaction_number' => 'TRX-20260801-' . str_pad($paymentCount, 4, '0', STR_PAD_LEFT),
                    'student_id' => $student->id,
                    'academic_year_id' => $ay2026->id,
                    'billable_type' => MonthlyBill::class,
                    'billable_id' => $bill->id,
                    'amount' => $partialAmount,
                    'payment_method' => 'cash',
                    'payment_date' => '2026-07-15',
                    'notes' => 'Cicilan pertama',
                    'status' => 'valid',
                    'created_by' => 1,
                ]);

                $bill->update(['paid_amount' => $partialAmount, 'status' => 'partial']);
            }
        }

        // Create Activities
        $studyTour = Activity::create([
            'academic_year_id' => $ay2026->id,
            'institution_id' => $smp->id,
            'name' => 'Study Tour Bandung',
            'amount' => 500000,
            'activity_date' => '2026-10-15',
            'description' => 'Kunjungan edukasi ke Bandung',
        ]);

        $camping = Activity::create([
            'academic_year_id' => $ay2026->id,
            'institution_id' => $smp->id,
            'name' => 'Camping Pramuka',
            'amount' => 300000,
            'activity_date' => '2026-11-20',
            'description' => 'Perkemahan tahunan',
        ]);

        $ldk = Activity::create([
            'academic_year_id' => $ay2026->id,
            'institution_id' => $mi->id,
            'name' => 'Pesantren Kilat',
            'amount' => 200000,
            'activity_date' => '2026-09-01',
            'description' => 'Pesantren kilat Ramadhan',
        ]);

        // Create Activity Bills for SMP students (study tour for all SMP)
        foreach ($smpStudents as $student) {
            ActivityBill::create([
                'activity_id' => $studyTour->id,
                'student_id' => $student->id,
                'amount' => $studyTour->amount,
            ]);
        }

        // Camping only for kelas 8 and 9
        foreach ($smpStudents as $i => $student) {
            $classKey = $smpClassKeys[$i % count($smpClassKeys)];
            if (str_starts_with($classKey, '8') || str_starts_with($classKey, '9')) {
                ActivityBill::create([
                    'activity_id' => $camping->id,
                    'student_id' => $student->id,
                    'amount' => $camping->amount,
                ]);
            }
        }

        // Pesantren kilat for MI kelas 4-6
        foreach ($miStudents as $i => $student) {
            $classKey = $miClassKeys[$i % count($miClassKeys)];
            $classNum = (int) $classKey[0];
            if ($classNum >= 4) {
                ActivityBill::create([
                    'activity_id' => $ldk->id,
                    'student_id' => $student->id,
                    'amount' => $ldk->amount,
                ]);
            }
        }

        // Some activity payments
        $activityBills = ActivityBill::where('activity_id', $studyTour->id)->take(5)->get();
        foreach ($activityBills as $bill) {
            $paymentCount++;
            Payment::create([
                'transaction_number' => 'TRX-20260901-' . str_pad($paymentCount, 4, '0', STR_PAD_LEFT),
                'student_id' => $bill->student_id,
                'academic_year_id' => $ay2026->id,
                'billable_type' => ActivityBill::class,
                'billable_id' => $bill->id,
                'amount' => $bill->amount,
                'payment_method' => 'transfer',
                'payment_date' => '2026-09-10',
                'status' => 'valid',
                'created_by' => 1,
            ]);
            $bill->update(['paid_amount' => $bill->amount, 'status' => 'paid']);
        }
    }
}
