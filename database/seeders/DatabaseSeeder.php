<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * 新規会員登録には管理者による承認が必要なため、ローカル動作確認用に
     * あらかじめログイン可能な管理者ユーザーを1件作成する（email: admin_user@example.com / password: password123）。
     * is_admin / is_approved は $fillable に含まれないため、生成後に個別に設定する。
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name'     => 'Admin',
            'email'    => 'admin_user@example.com',
            // 'hashed' キャストにより自動でハッシュ化される
            'password' => 'password123',
        ]);
        $admin->user_identifier = 'admin';
        $admin->is_admin = 1;
        $admin->is_approved = 1;
        $admin->save();
    }
}
