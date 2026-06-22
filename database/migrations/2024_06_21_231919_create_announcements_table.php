<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id()->comment('ID');
            $table->string('title')->comment('タイトル');
            $table->text('content')->comment('本文');
            $table->foreignId('user_id')->comment('作成者(users.id)')->constrained()->onDelete('restrict');
            $table->date('pub_start_at')->nullable()->index()->comment('公開開始日');
            $table->date('pub_end_at')->nullable()->comment('公開終了日');
            $table->tinyInteger('publish_status')->default(0)->comment('公開ステータス 0:保留 1:公開');
            $table->timestamp('created_at')->useCurrent()->comment('作成日時');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新日時');
            $table->softDeletes()->comment('論理削除日時');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
