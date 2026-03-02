<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('name'); // person to address in proposals/invoices
            $table->string('rfc')->nullable()->after('email');         // Mexican tax ID
            $table->text('notes')->nullable()->after('website');       // internal notes
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'rfc', 'notes']);
        });
    }
};
