<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListReportCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:list-report-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all report categories in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $categories = \App\Models\ReportCategory::all();
        
        if ($categories->isEmpty()) {
            $this->info('No report categories found in the database.');
            return Command::SUCCESS;
        }
        
        $headers = ['ID', 'Name', 'Description', 'Severity Level'];
        $rows = [];
        
        foreach ($categories as $category) {
            $rows[] = [
                $category->id,
                $category->name,
                $category->description,
                $category->severity_level
            ];
        }
        
        $this->table($headers, $rows);
        
        return Command::SUCCESS;
    }
}
