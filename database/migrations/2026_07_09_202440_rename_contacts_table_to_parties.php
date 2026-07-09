<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('contacts', 'parties');

        // Renaming the permission string in place (rather than deleting the
        // old ones and letting the seeder create fresh "parties.*" rows)
        // keeps every role's existing grants — role_has_permissions points
        // at the permission id, not its name.
        $this->renamePermissions('contacts.', 'parties.');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('parties', 'contacts');

        $this->renamePermissions('parties.', 'contacts.');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function renamePermissions(string $from, string $to): void
    {
        DB::table('permissions')->where('name', 'like', "{$from}%")->get()
            ->each(fn ($permission) => DB::table('permissions')->where('id', $permission->id)
                ->update(['name' => Str::replaceFirst($from, $to, $permission->name)]));
    }
};
