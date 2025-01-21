<?php

namespace Tests\Feature;

use App\Models\Crew;
use App\Models\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CustomSchemaTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_create_schema_for_existing_team()
    {
        $test_crew = Crew::factory()->create();
        $crew_id = $test_crew->id;
        $db_name = Schema::getSchemaName($crew_id);

        $this->artisan('make:schema ' . $crew_id)
            ->assertSuccessful()
            ->expectsOutput("Created database: " . $db_name);

        $this->assertDatabaseHas('schemas', [
            'crew_id' => $crew_id
        ]);

        $migrationFolderPath = Config::get('database.migration_path');
        $this->assertTrue(File::exists($migrationFolderPath . DIRECTORY_SEPARATOR . $crew_id));

        $dbConfig = Config::get('database.connections.mysql');
        $dbConfig['database'] = $db_name;
        Config::set('database.connections.' . $db_name, $dbConfig);

        $this->assertEquals($db_name, DB::connection($db_name)->getDatabaseName());

    }

    public function test_recreate_schema_for_existing_team()
    {
        $test_crew = Crew::factory()->create();
        $this->artisan('make:schema ' . $test_crew->id)
            ->assertSuccessful();

        $this->artisan('make:schema ' . $test_crew->id)
            ->assertSuccessful()
            ->expectsOutput('Database already exists for this team');
    }

    public function test_create_schema_for_non_existing_team()
    {
        $this->artisan('make:schema ' . 'test')
            ->assertFailed()
            ->expectsOutput("Team test doesn't exist yet.");
    }

    public function test_migrate_to_non_existing_team()
    {
        $this->artisan('migrate:custom test')
            ->assertFailed()
            ->expectsOutput("Team test doesn't exist yet.");
    }

    public function test_migrate_to_existing_team_without_schema()
    {
        $crew = Crew::factory()->create();

        $this->artisan('migrate:custom ' . $crew->id)
            ->assertFailed()
            ->expectsOutput("No database found for " . $crew->id);
    }

    public function test_migrate_to_existing_team_with_schema()
    {
        $crew = Crew::factory()->create();
        $db_name = Schema::getSchemaName($crew->id);

        $this->artisan('make:schema ' . $crew->id)
            ->assertSuccessful();

        $this->artisan('migrate:custom ' . $crew->id)
            ->assertSuccessful();

        $dbConfig = Config::get('database.connections.mysql');
        $dbConfig['database'] = $db_name;
        Config::set('database.connections.' . $db_name, $dbConfig);

        $schemaBuilder = \Illuminate\Support\Facades\Schema::connection($db_name);
        $this->assertTrue($schemaBuilder->hasTable("migrations"));
    }
}
