<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->foreignId('user_id')->comment('users.id')->constrained()->onDelete('cascade');
            $table->foreignId('announcement_id')->comment('announcements.id')->constrained()->onDelete('cascade');
            $table->primary(['user_id', 'announcement_id']);
            $table->timestamp('created_at')->useCurrent()->comment('既読日時');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');
    }
};
