<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\AboutController;
use \App\Http\Controllers\ApplyController;
use \App\Http\Controllers\LoginController;
use \App\Http\Controllers\PasswordController;
use \App\Http\Controllers\UserIdentifierController;
use \App\Http\Controllers\TopicController;
use \App\Http\Controllers\HomeController;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\CommentController;
use \App\Http\Controllers\SupportController;
use \App\Http\Controllers\AnnouncementController;
use \App\Http\Controllers\Admin\user\ApproveController;
use \App\Http\Controllers\Admin\support\AdminSupportController;

/* ------------------------------------------------------------------------------------------------ */
/* ログイン状態に関わらずアクセス可能 */

// TOP(MOT2紹介ページ)の表示
Route::get('/', [AboutController::class, 'index'])->name('top');

/* ユーザー登録申請 */
Route::prefix('/apply')
    ->name('apply.')
    ->group(function () {
        // 新規登録申請画面の表示
        Route::get('', [ApplyController::class, 'showForm'])->name('form');
        // 入力データのバリデーション
        Route::post('/check', [ApplyController::class, 'check'])->name('check');
        // 確認画面の表示
        Route::get('/confirm', [ApplyController::class, 'showConfirm'])->name('show.confirm');
        // 登録処理
        Route::post('/store', [ApplyController::class, 'store'])->name('store');
        // 登録申請完了画面の表示
        Route::get('/complete', [ApplyController::class, 'showComplete'])->name('show.complete');
    });

/* パスワード関連 */
Route::prefix('/password')
    ->name('password.')
    ->group(function () {

        /* 新規登録関連 */
        Route::prefix('/new')
            ->name('new.')
            ->group(function () {
                // パスワード新規登録 - 入力画面の表示
                Route::get('/{token}', [PasswordController::class, 'showFormNew'])->name('show.form');
                // パスワード新規登録 - 登録実行
                Route::post('/store', [PasswordController::class, 'storeNew'])->name('store');
                // パスワード新規登録 - 完了画面の表示 ※なぜか「to_route('password.new.complete');」が動作しないので、一旦viewファイルを直接返却させる
                // Route::get('/complete', [PasswordController::class, 'showCompleteNew'])->name('show.complete');
            });

        /* リセット関連 */
        Route::prefix('/reset')
            ->name('reset.')
            ->group(function () {
                // パスワードリセット(非ログイン時) - 入力画面の表示
                Route::get('/mail-check', [PasswordController::class, 'showMailFormReset'])->name('show.form-mail');
                // パスワードリセット(非ログイン時) - メール送信実行
                Route::post('/mail-check', [PasswordController::class, 'resetSendMail'])->name('send');
                // パスワードリセット(非ログイン時) - メール送信完了画面の表示
                Route::get('/mail-check/send', [PasswordController::class, 'showSendMailReset'])->name('show.send');
                // パスワードリセット(非ログイン時) - パスワード入力画面の表示
                Route::get('/form', [PasswordController::class, 'showPasswordFormReset'])->name('show.form-password');
                // パスワードリセット(非ログイン時) - パスワード変更実行
                Route::post('/store', [PasswordController::class, 'storeReset'])->name('store');
                // パスワードリセット(非ログイン時) - パスワード変更完了画面の表示
                Route::get('/complete', [PasswordController::class, 'showCompleteReset'])->name('show.complete');
            });
    });

/* ------------------------------------------------------------------------------------------------ */


/* ------------------------------------------------------------------------------------------------ */
/**
 * 未ログイン時のみアクセス可能
 */
Route::middleware('guest')
    ->group(
        function () {

            /* ログイン */
            Route::prefix('/login')
                ->name('login')
                ->group(
                    function () {
                        // ログインフォームの表示
                        Route::get('/', [LoginController::class, 'showForm'])->name('.show.form');
                        // ログイン処理
                        Route::post('/', [LoginController::class, 'login'])->name('');
                    }
                );

            /* ユーザーIDの設定 */
            Route::prefix('/identifier')
                ->name('identifier.')
                ->group(
                    function () {
                        // 入力画面の表示
                        Route::get('/{token}', [UserIdentifierController::class, 'showForm'])->name('show.form');
                        // 登録実行
                        Route::post('/store', [UserIdentifierController::class, 'store'])->name('store');
                        // 登録完了/
                        Route::get('/complete', [UserIdentifierController::class, 'showComplete'])->name('show.complete');
                    }
                );
        }
    );

/* ------------------------------------------------------------------------------------------------ */

/* ------------------------------------------------------------------------------------------------ */
/**
 * ログイン時のみアクセス可能
 */
Route::middleware('auth')
    ->group(function () {

        // ホーム画面の表示
        Route::get('/home', [HomeController::class, 'index'])->name('home.index');

        // お知らせ詳細画面の表示
        Route::get('/announcement/detail/{id}', [AnnouncementController::class, 'showDetail'])->name('show.detail.announcement');

        // ログアウト処理
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        /* ユーザー情報 */
        Route::prefix('/user')
            ->name('user.')
            ->group(function () {
                // 一覧画面の表示
                Route::get('/{page?}', [UserController::class, 'showList'])->name('show.list');
                // 詳細画面の表示
                Route::get('/detail/{id}', [UserController::class, 'showDetail'])->name('show.detail');
                // 編集画面の表示
                Route::get('/edit/{id}', [UserController::class, 'showEdit'])->name('show.edit');
                // 更新実行
                Route::post('/store', [UserController::class, 'store'])->name('store');
            });

        /* トピック関連 */
        Route::prefix('/topic')
            ->name('topic.')
            ->group(function () {
                // トピック - 一覧画面の表示
                Route::get('/list/{page?}', [TopicController::class, 'showList'])->name('show.list');
                // トピック新規作成 - 入力画面の表示
                Route::get('/new', [TopicController::class, 'showCreate'])->name('show.create');
                // トピック - 詳細画面の表示
                Route::get('/detail/{id}', [TopicController::class, 'showDetail'])->name('show.detail');
                // トピック編集 - 編集画面の表示
                Route::get('/edit/{id}', [TopicController::class, 'showEdit'])->name('show.edit');
                // トピック - 作成・更新・削除実行
                Route::post('/store', [TopicController::class, 'store'])->name('store');
                // コメント - 入力画面の表示
                Route::get('/comment/{topic_id}', [CommentController::class, 'showForm'])->name('show.create.comment');
            });

        /* コメント関連 */
        Route::prefix('/comment')
            ->name('comment.')
            ->group(function () {
                // コメント新規作成・編集の実行
                Route::post('/store', [CommentController::class, 'store'])->name('store');
                // コメント編集画面の表示
                Route::get('/edit/{comment_id}', [CommentController::class, 'showEdit'])->name('show.edit');
            });

        /* サポート機能 */
        Route::prefix('/support')
            ->name('support.')
            ->group(function () {
                // メッセージの保存実行
                Route::post('/store', [SupportController::class, 'store'])->name('store');
                // メッセージ送信完了画面の表示
                Route::get('/complete', [SupportController::class, 'showComplete'])->name('show.complete');
            });


        /**
         * 管理者側
         * 管理者権限チェックはログイン認証時に行っている
         */
        Route::middleware('AdminMiddleware')
            ->group(function () {
                Route::prefix('/admin')
                    ->name('admin.')
                    ->group(function () {
                        /* 管理画面TOP */
                        Route::get('', function () {
                            return view('admin/index');
                        })->name('show.index');

                        /* 承認待ちユーザー関連 */
                        // 承認待ちユーザー 一覧画面の表示
                        Route::get('/user/unapproved', [ApproveController::class, 'showList'])->name('show.unapproved.list');
                        // 承認待ちユーザー 詳細画面の表示
                        Route::get('/user/unapproved/{id}', [ApproveController::class, 'showDetail'])->name('show.detail');
                        // 承認処理
                        Route::post('/user/approve', [ApproveController::class, 'approve'])->name('unapprovedUser.approve');

                        /* サポート */
                        // メッセージ 一覧画面表示
                        Route::get('/support', [AdminSupportController::class, 'showList'])->name('show.support.list');

                        /* お知らせ */
                        // お知らせ 一覧画面表示
                        Route::get('/announcement', [AnnouncementController::class, 'showList'])->name('show.announcement.list');
                        // お知らせ 新規作成画面表示
                        Route::get('/announcement/new', [AnnouncementController::class, 'showCreate'])->name('show.announcement.create');
                        // お知らせ 編集画面表示
                        Route::get('/announcement/edit/{id}', [AnnouncementController::class, 'showEdit'])->name('show.announcement.edit');
                        // お知らせ 保存実行
                        Route::post('/announcement/store', [AnnouncementController::class, 'store'])->name('announcement.store');
                    });
            });
    });


/* ------------------------------------------------------------------------------------------------ */

/* 404エラー */
Route::get('/error', function () {
    return view('errors/404');
})->name('404');
