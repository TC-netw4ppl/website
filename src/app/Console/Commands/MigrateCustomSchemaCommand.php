<?php

namespace App\Console\Commands;

use App\Models\Crew;
use App\Models\Schema;
use Illuminate\Console\Command;

class MigrateCustomSchemaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'migrate:custom {team_id} {--path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes the migration for the database linked to the team id';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $crew_id = $this->argument('team_id');
        $crew = Crew::find($crew_id);

        if(!$crew) {
            $this->warn("Team " . $this->argument('team_id') . " doesn't exist yet.");
            $this->info("Create the team before migrating to its schema");
            return Command::FAILURE;
        }

        $dbName = Schema::getSchemaName($crew_id);
        $schema = Schema::where("name", $dbName)->first();
        if(!$schema) {
            $this->warn("No database found for " . $this->argument('team_id'));
            $this->info("You can initiate a new schema with artisan make:schema {team_id}");
            return Command::FAILURE;
        }

        $schema->openDatabaseConnection();
        $path = $this->option('path') ?? config('database.migration_path');

        $this->call('migrate', ['--database' => $dbName, '--path' => $path]);
        return Command::SUCCESS;
    }
}
