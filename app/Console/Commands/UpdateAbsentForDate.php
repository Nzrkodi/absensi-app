<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Holiday;

class UpdateAbsentForDate extends Command
{
    protected $signature = 'attendance:update-absent-date {date : Tanggal dalam format YYYY-MM-DD}';
    
    protected $description = 'Update absent status untuk tanggal tertentu (manual)';

    public function handle()
    {
        $dateInput = $this->argument('date');
        
        try {
            $date = Carbon::parse($dateInput, 'Asia/Makassar');
        } catch (\Exception $e) {
            $this->error("Format tanggal salah! Gunakan format: YYYY-MM-DD (contoh: 2026-01-09)");
            return 1;
        }

        $this->info("=== Update Absent untuk tanggal: {$date->format('d F Y')} ===");

        // Check if target date is a holiday
        if (Holiday::isHoliday($date)) {
            $holiday = Holiday::getHoliday($date);
            $this->warn("Tanggal {$date->format('d F Y')} adalah hari libur: {$holiday->name}");
            $this->warn("Tidak perlu menandai absent pada hari libur.");
            return 0;
        }

        // Get all active students
        $activeStudents = Student::where('status', 'active')->get();
        $this->info("Total siswa aktif: " . $activeStudents->count());

        $absentCount = 0;
        $bolosCount = 0;
        $alreadyProcessed = 0;
        $sickPermissionCount = 0;

        $this->info("\nMemproses data absensi...");
        
        foreach ($activeStudents as $student) {
            // Check if student has attendance record for target date
            $attendance = Attendance::where('student_id', $student->id)
                ->where('date', $date)
                ->first();
            
            // If no attendance record exists, create one with absent status
            if (!$attendance) {
                Attendance::create([
                    'student_id' => $student->id,
                    'date' => $date,
                    'status' => 'absent',
                    'notes' => "Otomatis ditandai absent - tidak melakukan absensi pada {$date->format('d F Y')}"
                ]);
                $absentCount++;
                $this->line("✓ {$student->nisn} - {$student->name} → ABSENT (tidak ada record)");
            } 
            // If attendance exists but no clock_in and status is not sick/permission, mark as absent
            elseif (!$attendance->clock_in && !in_array($attendance->status, ['sick', 'permission'])) {
                $oldNotes = $attendance->notes;
                $newNotes = $oldNotes ? $oldNotes . " | Otomatis ditandai absent - tidak clock in pada {$date->format('d F Y')}" : "Otomatis ditandai absent - tidak clock in pada {$date->format('d F Y')}";
                
                $attendance->update([
                    'status' => 'absent',
                    'notes' => $newNotes
                ]);
                $absentCount++;
                $this->line("✓ {$student->nisn} - {$student->name} → ABSENT (tidak clock in)");
            }
            // If clock_in exists but no clock_out, mark as bolos
            elseif ($attendance->clock_in && !$attendance->clock_out && in_array($attendance->status, ['present', 'late'])) {
                $oldNotes = $attendance->notes;
                $newNotes = $oldNotes ? $oldNotes . " | Otomatis ditandai bolos - clock in tanpa clock out pada {$date->format('d F Y')}" : "Otomatis ditandai bolos - clock in tanpa clock out pada {$date->format('d F Y')}";
                
                $attendance->update([
                    'status' => 'bolos',
                    'notes' => $newNotes
                ]);
                $bolosCount++;
                $this->line("✓ {$student->nisn} - {$student->name} → BOLOS (clock in tanpa clock out)");
            } 
            // If status is sick or permission, skip
            elseif ($attendance && in_array($attendance->status, ['sick', 'permission'])) {
                $sickPermissionCount++;
            }
            // Already processed (present, late, or complete attendance)
            else {
                $alreadyProcessed++;
            }
        }

        $this->info("\n=== RINGKASAN ===");
        $this->info("Tanggal: {$date->format('d F Y')}");
        $this->info("Siswa ditandai ABSENT: {$absentCount}");
        $this->info("Siswa ditandai BOLOS: {$bolosCount}");
        $this->info("Siswa sakit/izin: {$sickPermissionCount}");
        $this->info("Sudah lengkap (hadir/terlambat): {$alreadyProcessed}");
        $this->info("Total siswa: " . $activeStudents->count());

        // Show final statistics
        $finalStats = Attendance::where('date', $date)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        $this->info("\n=== STATISTIK AKHIR ABSENSI {$date->format('d F Y')} ===");
        foreach ($finalStats as $stat) {
            $statusName = match($stat->status) {
                'present' => 'Hadir',
                'late' => 'Terlambat', 
                'absent' => 'Tidak Hadir',
                'sick' => 'Sakit',
                'permission' => 'Izin',
                'bolos' => 'Bolos',
                default => $stat->status
            };
            $this->info("{$statusName}: {$stat->count}");
        }

        $this->info("\n✅ Selesai! Data absensi untuk {$date->format('d F Y')} sudah diperbarui.");
        
        return 0;
    }
}