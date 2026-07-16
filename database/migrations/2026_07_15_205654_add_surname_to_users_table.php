<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('surname')->default('')->after('name');
        });

        DB::table('users')->orderBy('id')->each(function (object $user): void {
            $parts = preg_split('/\s+/u', trim((string) $user->name), 2) ?: [''];

            DB::table('users')->where('id', $user->id)->update([
                'name' => $parts[0] !== '' ? $parts[0] : 'User',
                'surname' => $parts[1] ?? '',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->orderBy('id')->each(function (object $user): void {
            DB::table('users')->where('id', $user->id)->update([
                'name' => trim($user->name.' '.$user->surname),
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('surname');
        });
    }
};
