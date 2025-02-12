<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->checkAndCreateDatabase();
        $this->checkAndCreateLogsTable();
    }

    /**
     * Check if database exists, if not, create it and run migrations
     */
    private function checkAndCreateDatabase()
    {
        $databaseName = env('DB_DATABASE', 'default_db');
        $databaseExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
        
        if (!$databaseExists) {
            try {
                DB::statement("CREATE DATABASE `$databaseName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                Log::info("Database '$databaseName' created successfully.");
                
                // Run migrations automatically
                Artisan::call('migrate --force');
                
                // Run seeders automatically
                Artisan::call('db:seed --force');
                
                Log::info("Database migration and seeding completed.");
            } catch (\Exception $e) {
                Log::error("Database creation failed: " . $e->getMessage());
            }
        } else {
            Log::info("Database '$databaseName' already exists. Running migrations...");
            Artisan::call('migrate --force');
        }
    }

    /**
     * Check if logs table exists, if not, create it automatically
     */
    private function checkAndCreateLogsTable()
    {
        if (!Schema::hasTable('logs')) {
            Schema::create('logs', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('action');
                $table->string('log_level');
                $table->text('message');
                $table->string('ip_address');
                $table->string('related_table')->nullable();
                $table->unsignedBigInteger('related_id')->nullable();
                $table->timestamps();
            });
            Log::info("Logs table created successfully.");
        }
    }
}