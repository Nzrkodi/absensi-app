<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Services\HolidayDetectionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();

        // Filter by year
        $year = $request->get('year', Carbon::now()->year);
        $query->whereYear('date', $year);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $holidays = $query->orderBy('date', 'asc')->paginate(15);
        
        // Get available years
        $years = Holiday::selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        if ($years->isEmpty()) {
            $years = collect([Carbon::now()->year]);
        }

        return view('admin.holidays.index', compact('holidays', 'years', 'year'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:national,school,weekend'
        ]);

        try {
            Holiday::create([
                'name' => $request->name,
                'date' => $request->date,
                'description' => $request->description,
                'type' => $request->type,
                'is_active' => true
            ]);

            return redirect()->route('admin.holidays.index')
                ->with('success', 'Hari libur berhasil ditambahkan');

        } catch (\Exception $e) {
            return redirect()->route('admin.holidays.index')
                ->with('error', 'Gagal menambahkan hari libur: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:national,school,weekend'
        ]);

        try {
            $holiday->update([
                'name' => $request->name,
                'date' => $request->date,
                'description' => $request->description,
                'type' => $request->type
            ]);

            return redirect()->route('admin.holidays.index')
                ->with('success', 'Hari libur berhasil diupdate');

        } catch (\Exception $e) {
            return redirect()->route('admin.holidays.index')
                ->with('error', 'Gagal mengupdate hari libur: ' . $e->getMessage());
        }
    }

    public function destroy(Holiday $holiday)
    {
        try {
            $holiday->delete();

            return redirect()->route('admin.holidays.index')
                ->with('success', 'Hari libur berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()->route('admin.holidays.index')
                ->with('error', 'Gagal menghapus hari libur: ' . $e->getMessage());
        }
    }

    public function toggle(Holiday $holiday)
    {
        try {
            $holiday->update([
                'is_active' => !$holiday->is_active
            ]);

            $status = $holiday->is_active ? 'diaktifkan' : 'dinonaktifkan';
            
            return redirect()->route('admin.holidays.index')
                ->with('success', "Hari libur berhasil {$status}");

        } catch (\Exception $e) {
            return redirect()->route('admin.holidays.index')
                ->with('error', 'Gagal mengubah status hari libur: ' . $e->getMessage());
        }
    }

    public function createWeekends(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        try {
            $count = Holiday::createWeekendHolidays($request->year);

            return redirect()->route('admin.holidays.index', ['year' => $request->year])
                ->with('success', "Berhasil menambahkan {$count} hari libur weekend untuk tahun {$request->year}");

        } catch (\Exception $e) {
            return redirect()->route('admin.holidays.index')
                ->with('error', 'Gagal membuat hari libur weekend: ' . $e->getMessage());
        }
    }

    public function checkToday()
    {
        $isHoliday = Holiday::isTodayHoliday();
        $holiday = null;
        
        if ($isHoliday) {
            $holiday = Holiday::getHoliday(Carbon::today('Asia/Makassar'));
        }

        return response()->json([
            'is_holiday' => $isHoliday,
            'holiday' => $holiday ? [
                'name' => $holiday->name,
                'type' => $holiday->type,
                'description' => $holiday->description
            ] : null,
            'date' => Carbon::today('Asia/Makassar')->format('Y-m-d')
        ]);
    }

    public function autoSync(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'types' => 'required|array',
            'types.*' => 'in:weekend,national'
        ]);

        try {
            $service = app(HolidayDetectionService::class);
            $year = $request->year;
            $types = $request->types;
            $totalCreated = 0;
            $results = [];

            // Clear cache first
            $service->clearCache($year);

            // Sync weekends
            if (in_array('weekend', $types)) {
                $weekendCount = $service->createWeekendHolidays($year);
                $totalCreated += $weekendCount;
                $results[] = "Weekend: {$weekendCount} hari";
            }

            // Sync national holidays
            if (in_array('national', $types)) {
                $nationalCount = $service->createNationalHolidays($year);
                $totalCreated += $nationalCount;
                $results[] = "Nasional: {$nationalCount} hari";
            }

            $message = "Berhasil auto-sync {$totalCreated} hari libur untuk tahun {$year}";
            if (!empty($results)) {
                $message .= " (" . implode(', ', $results) . ")";
            }

            return redirect()->route('admin.holidays.index', ['year' => $year])
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('admin.holidays.index')
                ->with('error', 'Gagal auto-sync hari libur: ' . $e->getMessage());
        }
    }

    public function clearCache(Request $request)
    {
        try {
            $service = app(HolidayDetectionService::class);
            $year = $request->get('year');
            
            $service->clearCache($year);
            
            $message = $year 
                ? "Cache hari libur untuk tahun {$year} berhasil dibersihkan"
                : "Semua cache hari libur berhasil dibersihkan";

            return redirect()->route('admin.holidays.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('admin.holidays.index')
                ->with('error', 'Gagal membersihkan cache: ' . $e->getMessage());
        }
    }
}