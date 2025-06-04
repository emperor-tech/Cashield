<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListCampusZones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:list-campus-zones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all campus zones in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $zones = \App\Models\CampusZone::all();
        
        if ($zones->isEmpty()) {
            $this->info('No campus zones found in the database.');
            return Command::SUCCESS;
        }
        
        $headers = ['ID', 'Name', 'Code', 'Description', 'Active'];
        $rows = [];
        
        foreach ($zones as $zone) {
            $rows[] = [
                $zone->id,
                $zone->name,
                $zone->code,
                $zone->description,
                $zone->active ? 'Yes' : 'No'
            ];
        }
        
        $this->table($headers, $rows);
        
        return Command::SUCCESS;
    }
}
