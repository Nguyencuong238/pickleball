<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
     {
         // Fix match_date and match_time format in the database
         DB::statement('UPDATE matches SET match_date = DATE(match_date) WHERE match_date IS NOT NULL');
         DB::statement('UPDATE matches SET match_time = TIME(match_time) WHERE match_time IS NOT NULL');
     }

     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         // No need to reverse since we're just fixing data format
     }
};
