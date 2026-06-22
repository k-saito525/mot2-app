<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment('ユーザーID');
            $table->string('name')->comment('ニックネーム');
            $table->string('nationality')->nullable()->comment('国籍');
            $table->text('introduction_text')->nullable()->comment('自己紹介');
            $table->json('past_join')->nullable()->comment('IIMS活動参加歴');
            $table->string('user_identifier', 50)->nullable()->unique()->comment('表示用ユーザーID');
            $table->string('user_icon')->nullable()->comment('アイコン画像');
            $table->string('user_cover_image')->nullable()->comment('カバー画像');
            $table->json('sns_links')->nullable()->comment('SNSアカウントURL {"x": "...", "facebook": "...", "instagram": "..."}');
            $table->string('email')->unique()->comment('メールアドレス');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable()->comment('パスワード');
            $table->rememberToken();
            $table->string('verify_token')->nullable()->unique()->comment('認証用トークン(会員登録時に使用)');
            $table->string('reset_password_access_key')->nullable()->unique()->comment('パスワード再設定キー');
            $table->timestamp('reset_password_expire_at')->nullable()->comment('パスワード再設定キー有効期限');
            $table->tinyInteger('is_approved')->default(0)->index()->comment('承認フラグ 1:承認済');
            $table->tinyInteger('is_admin')->default(0)->comment('管理者フラグ 1:管理者アカウント');
            $table->timestamp('created_at')->useCurrent()->comment('作成日時');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新日時');
            $table->softDeletes()->comment('論理削除日時');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
