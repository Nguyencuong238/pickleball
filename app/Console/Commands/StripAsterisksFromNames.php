<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StripAsterisksFromNames extends Command
{
    protected $signature = 'data:strip-asterisks';

    protected $description = "Replace '*' with '' in name columns across tournament_athletes, users, matches";

    public function handle(): int
    {
        $targets = [
            ['tournament_athletes', 'athlete_name'],
            ['users', 'name'],
            ['matches', 'athlete1_name'],
            ['matches', 'athlete2_name'],
        ];

        $this->info('Stripping "*" from name columns...');

        DB::transaction(function () use ($targets) {
            foreach ($targets as [$table, $column]) {
                $affected = DB::table($table)
                    ->where($column, 'like', '%*%')
                    ->update([
                        $column => DB::raw("REPLACE(`{$column}`, '*', '')"),
                    ]);

                $this->info("{$table}.{$column}: updated {$affected} rows");
            }
        });

        $this->info('Done.');

        return Command::SUCCESS;
    }
}
