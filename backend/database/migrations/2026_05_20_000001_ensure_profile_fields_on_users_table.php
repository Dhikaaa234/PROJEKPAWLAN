<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'nim')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('nim')->nullable()->after('email');
            });
        }

        if (!Schema::hasIndex('users', 'users_nim_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('nim', 'users_nim_unique');
            });
        }

        if (!Schema::hasColumn('users', 'no_telepon')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('no_telepon')->nullable()->after('nim');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('users', 'users_nim_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_nim_unique');
            });
        }
    }
};
