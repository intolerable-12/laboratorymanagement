<?php

namespace App\Console\Commands;

use App\Services\ReportGenerationService;
use Illuminate\Console\Command;

class GenerateQuarterlyInventoryReportsCommand extends Command
{
    protected $signature = 'reports:generate-quarterly';

    protected $description = 'Generate and archive the quarterly equipment and chemical inventory reports';

    public function handle(ReportGenerationService $reportGenerationService): int
    {
        $generated = $reportGenerationService->generateQuarterlyReports();

        $this->info("Generated {$generated} quarterly inventory report(s).");

        return self::SUCCESS;
    }
}
