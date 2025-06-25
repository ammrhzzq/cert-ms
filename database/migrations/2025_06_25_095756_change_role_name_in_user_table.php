<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing role values
        DB::table('users')->where('role', 'hod')->update(['role' => 'scheme_head']);
        DB::table('users')->where('role', 'manager')->update(['role' => 'scheme_manager']);
        DB::table('users')->where('role', 'staff')->update(['role' => 'certificate_admin']);
        
        // Optional: Add a check to ensure all roles are valid
        $validRoles = ['certificate_admin', 'scheme_manager', 'scheme_head', 'admin'];
        $invalidRoles = DB::table('users')
            ->whereNotIn('role', $validRoles)
            ->pluck('role')
            ->unique();
            
        if ($invalidRoles->isNotEmpty()) {
            throw new Exception('Found invalid roles: ' . $invalidRoles->implode(', '));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the role name changes
        DB::table('users')->where('role', 'scheme_head')->update(['role' => 'hod']);
        DB::table('users')->where('role', 'scheme_manager')->update(['role' => 'manager']);
        DB::table('users')->where('role', 'certificate_admin')->update(['role' => 'staff']);
    }
};