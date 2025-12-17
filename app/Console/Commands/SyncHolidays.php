<?php

namespace App\Console\Commands;

use App\Services\HolidayDetectionService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SyncHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:sync 
                            {year? : The year to sync holidays for (default: current year)}
                            {--weekends : Include weekend holidays}
                            {--national : Include national holidays}
                            {--all : Include both weekends and national holidays}
                            {--clear-cache : Clear holiday cache before syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync holidays (weekends and national holidays) from external APIs';

    protected HolidayDetectionService $holidayService;

    public function __construct(HolidayDetectionService $holidayService)
    {
        parent::__construct();
        $this->holidayService = $holidayService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?: Carbon::now()->year;
        $includeWeekends = $this->option('weekends') || $this->option('all');
        $includeNational = $this->option('national') || $this->option('all');
        
        // If no specific option is provided, sync both
        if (!$includeWeekends && !$includeNational) {
            $includeWeekends = true;
            $includeNational = true;
        }

        $this->info("🗓️  Syncing holidays for year {$year}...");

        // Clear cache if requested
        if ($this->option('clear-cache')) {
            $this->info("🧹 Clearing holiday cache...");
            $this->holidayService->clearCache($year);
        }

        $totalCreated = 0;

        // Sync weekend holidays
        if ($includeWeekends) {
            $this->info("📅 Creating weekend holidays...");
            $weekendCount = $this->holidayService->createWeekendHolidays($year);
            $totalCreated += $weekendCount;
            $this->line("   ✅ Created {$weekendCount} weekend holidays");
        }

        // Sync national holidays
        if ($includeNational) {
            $this->info("🇮🇩 Fetching national holidays from API...");
            
            try {
                $nationalCount = $this->holidayService->createNationalHolidays($year);
                $totalCreated += $nationalCount;
                $this->line("   ✅ Created {$nationalCount} national holidays");
            } catch (\Exception $e) {
                $this->error("   ❌ Failed to fetch national holidays: " . $e->getMessage());
            }
        }

        // Show summary
        $this->newLine();
        $this->info("🎉 Holiday sync completed!");
        $this->table(
            ['Type', 'Count'],
            [
                ['Total Created', $totalCreated],
                ['Year', $year],
                ['Weekends Included', $includeWeekends ? 'Yes' : 'No'],
                ['National Included', $includeNational ? 'Yes' : 'No'],
            ]
        );

        // Show some examples
        $this->showExamples($year);

        return Command::SUCCESS;
    }

    private function showExamples(int $year): void
    {
        $this->info("📋 Recent holidays created:");
        
        $holidays = \App\Models\Holiday::where('date', '>=', Carbon::create($year, 1, 1))
            ->where('date', '<=', Carbon::create($year, 12, 31))
            ->orderBy('date', 'asc')
            ->limit(5)
            ->get();

        if ($holidays->count() > 0) {
            $data = [];
            foreach ($holidays as $holiday) {
                $data[] = [
                    $holiday->date->format('d M Y'),
                    $holiday->name,
                    ucfirst($holiday->type)
                ];
            }
            
            $this->table(['Date', 'Name', 'Type'], $data);
        } else {
            $this->line("   No holidays found for {$year}");
        }
    }
}
