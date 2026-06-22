<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id()->comment('コメントID');
            $table->text('comment')->comment('コメント');
            $table->foreignId('topic_id')->comment('コメント先のトピック(topics.id)')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->comment('コメント主(users.id)')->constrained()->onDelete('restrict');
            $table->timestamp('created_at')->useCurrent()->comment('作成日時');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新日時');
            $table->softDeletes()->comment('論理削除日時');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
