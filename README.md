# MOT2

## 概要

NPO法人IIMS（学生時代から携わっている団体）が主催するイベントの参加者限定で使える、会員制の簡易SNS「MOT2」です。イベントを通じて得たつながりを継続できる場を作ることを目的とし、参加者はトピック投稿とコメントで交流します。利用にはユーザー登録申請と管理者の承認が必要です。

2024年に有志メンバーで開発（デザイン・コーディングは別メンバー、私はバックエンドを担当）したアプリケーションを、ポートフォリオとしてリファクタリングしました。

## 主な機能

### 一般ユーザー向け
- 会員登録申請〜承認フロー：申請フォーム送信 → 管理者承認 → メール内トークンによるパスワード設定、という多段階の登録導線
- ログイン / ログアウト、パスワードリセット（メール認証によるトークン方式）
- トピック機能：一覧・詳細表示、新規投稿、編集、削除（論理削除）
- コメント機能：トピックへのコメント投稿・編集、投稿者へのメール通知
- お知らせ機能：公開期間内のお知らせの閲覧、閲覧時の既読管理
- プロフィール機能：ニックネーム、自己紹介、国籍、IIMS活動参加歴、アイコン／カバー画像、SNSリンクの登録・編集
- サポート（お問い合わせ）機能：運営への問い合わせ送信、管理者へのメール通知

### 管理者向け
- 承認待ちユーザーの一覧・詳細確認、承認処理
- お知らせの作成・編集・削除
- サポート（問い合わせ）メッセージの確認

## 技術スタック

| 分類 | 採用技術 |
| --- | --- |
| Backend | PHP 8.4, Laravel 13 |
| Database | MySQL 8.4 |
| Authentication | セッション認証（Guard: `web`）。Laravel Sanctum は将来のAPI化に備えて導入済みだが現状未使用 |
| Frontend | Blade（サーバーサイドレンダリング） |
| Email | SMTP（開発時は Mailpit） |
| Environment | Laravel Sail（Docker） |
| Testing | PHPUnit, Mockery |
| Code Formatting | Laravel Pint（デフォルト設定のまま、手動実行のみ。CI等での自動チェックは未導入） |

## 開発方針

本リファクタリングは、可読性・保守性を重視した設計にすることを目的としています。
Claude Code を活用していますが、AI が提示した改善提案・実装内容はすべて開発者本人が内容を確認したうえで、採用の可否を判断しています。

## アーキテクチャと設計方針

責務を明確にするため、`Controller → Service → Model` の層構造を意識した設計にしています。

- **Controller**：リクエストの受け渡しに特化し、バリデーションは `FormRequest`（`app/Http/Requests`）に委譲
- **Service**（`app/Services`）：複数モデルにまたがる更新処理や、削除に伴う関連レコードの整合性維持など、業務ロジックを担当
  - `TopicService`：トピック削除時に関連コメントを含めて論理削除
  - `UserService`：プロフィール更新（表示用ID、メールアドレス変更通知、SNSリンク、画像アップロード）、ユーザー承認処理
  - `AnnouncementService`：お知らせ削除、既読レコードの同期・既読状態の算出
- **Model**（`app/Models`）：Eloquent モデル。クエリビルダをコントローラーから直接触らず、一覧・詳細取得などのクエリロジックをメソッドとしてモデルに集約
- **論理削除**：会員・コンテンツ系テーブル（`users` / `topics` / `comments` / `announcements` / `supports`）で SoftDeletes を採用し、データ追跡性を確保（既読管理用の中間テーブル `announcement_reads` 等は対象外）
- **文言の一元管理**（`lang/ja`）：バリデーションメッセージや画面文言を言語ファイルに集約。フレームワーク標準メッセージの日本語化には `laravel-lang/lang` を利用（日本語専用で、多言語切り替えは未実装）

### データモデル

| テーブル | 概要 |
| --- | --- |
| `users` | 会員情報。承認フラグ (`is_approved`)、管理者フラグ (`is_admin`) を保持 |
| `topics` | 投稿。ユーザーに紐づく |
| `comments` | トピックへのコメント |
| `announcements` | お知らせ。公開期間・公開ステータスを保持 |
| `announcement_reads` | ユーザーごとの既読状態を管理する中間テーブル（`user_id` + `announcement_id` の複合キー） |
| `supports` | 運営への問い合わせメッセージ |

## テスト

Feature テストを中心に、HTTPリクエストからDB操作までを検証（計17ファイル）。

```
tests/Feature/Controllers/  … 10ファイル（各コントローラーのHTTPレベルの振る舞いを検証）
tests/Feature/Services/     …  3ファイル（業務ロジックを検証）
tests/Feature/Models/       …  4ファイル（クエリ・リレーション・アクセサを検証）
```

実行:

```bash
./vendor/bin/sail test
```

## セットアップ（Laravel Sail）

```bash
# 依存パッケージのインストール
composer install

# 環境変数ファイルの作成
cp .env.example .env

# アプリケーションキーの生成
./vendor/bin/sail artisan key:generate

# コンテナ起動（app / mysql / mailpit / phpmyadmin）
./vendor/bin/sail up -d

# マイグレーション実行
./vendor/bin/sail artisan migrate

# 管理者ユーザーの作成（新規会員登録の承認に必要。email: admin_user@example.com / password: password123）
./vendor/bin/sail artisan db:seed
```

- アプリ: http://localhost
- Mailpit（メール確認用）: http://localhost:8025
- phpMyAdmin: http://localhost:8888

## 改善余地

- フロントエンドの切り分け：現状は Blade テンプレートによるサーバーサイドレンダリングでフロント/バックエンドが分離できていないため、Next.js や Nuxt.js 等を用いた API 連携構成への移行
- CI環境の整備（テスト自動実行、Larastan等による静的解析、Laravel Pintの自動チェック等）
