<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration restructures year levels:
     * OLD: 1=Employee, 2-7=Students, 8=Guest
     * NEW: 1-6=Students, 7=Faculty, 8=Staff, 9=Guest
     */
    public function up(): void
    {
        // First, update year_levels table structure
        DB::table('year_levels')->truncate();

        // Insert new year level structure
        DB::table('year_levels')->insert([
            ['id' => 1, 'year_level_name' => '1st Year', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'year_level_name' => '2nd Year', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'year_level_name' => '3rd Year', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'year_level_name' => '4th Year', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'year_level_name' => '5th Year', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'year_level_name' => '6th Year', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'year_level_name' => 'Faculty', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'year_level_name' => 'Staff', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'year_level_name' => 'Guest', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Note: If you have existing users with year_level_id references, 
        // you may need to update them manually based on your data.
        // Example:
        // - Old ID 1 (Employee) -> Check position_type: if 'faculty' set to 7, if 'staff' set to 8
        // - Old IDs 2-7 (Students) -> Shift down by 1 (2->1, 3->2, etc.)
        // - Old ID 8 (Guest) -> Change to 9
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore old year level structure
        DB::table('year_levels')->truncate();

        DB::table('year_levels')->insert([
            ['id' => 1, 'year_level_name' => 'Employee', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'year_level_name' => '1st Year', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'year_level_name' => '2nd Year', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'year_level_name' => '3rd Year', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'year_level_name' => '4th Year', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'year_level_name' => '5th Year', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'year_level_name' => '6th Year and more', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'year_level_name' => 'Guest', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
