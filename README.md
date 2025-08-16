# Slim + Twig Simple Form

PHP 8.4、Slim Framework 4、Twigテンプレートエンジンを使用したモダンな3ステップお問い合わせフォームアプリケーション。CSRF保護、包括的バリデーション、構造化ログ、コードスタイル統一などエンタープライズ品質の機能を実装。

## 🚀 開発方法

### VS Code DevContainer（推奨）

最も簡単な開発方法です：

1. **必要な準備**
   - VS Code + Dev Containers拡張機能
   - Docker Desktop

2. **起動手順**
   ```bash
   # VS Codeでプロジェクトを開く
   code .
   
   # コマンドパレット: Ctrl+Shift+P (Cmd+Shift+P)
   # "Dev Containers: Reopen in Container" を選択
   ```

3. **DevContainer機能**
   - PHP開発環境が自動設定
   - Xdebugデバッグ対応
   - PHPStan静的解析統合
   - 推奨VS Code拡張機能の自動インストール
   - ワンクリックでのテスト・解析実行

📖 **詳細**: [DevContainer README](.devcontainer/README.md)

### 手動Docker起動

DevContainerを使わない場合：

```bash
# 外部ネットワーク作成（初回のみ）
docker network create slim-network

# 開発環境起動
make up

# ブラウザでアクセス
open http://localhost:8080
```

## 技術スタック

### コア
- **PHP 8.4** - 最新のPHP機能（declare strict_types, union types等）
- **Slim Framework 4** - 軽量Webフレームワーク + ミドルウェアスタック
- **Twig 3** - 安全なテンプレートエンジン（XSS保護）
- **PHP-DI 7** - 依存性注入コンテナ

### セキュリティ & 品質
- **slim/csrf** - CSRF保護（Guard + セッション）
- **Monolog 3** - 構造化ログ（app/security/mail チャンネル、request_id 追跡）
- **PHPUnit 11.5** - テストフレームワーク（42テスト、統合・単体・コントローラー）
- **PHPStan 2.0** - 静的解析（レベル8、型安全性）
- **PHP-CS-Fixer 3.86** - コード品質（PSR-2/12 + 拡張ルール）

### インフラ & 開発環境
- **Nginx** - Webサーバー（PHP-FPM連携）
- **MySQL 8.4** - データベース（オプション）
- **Docker & Docker Compose** - コンテナ化開発環境
- **VS Code DevContainer** - 統合開発環境（Xdebug、Intelephense、推奨拡張機能）

## セットアップ

### 必要条件

- Docker
- Docker Compose

### Docker Compose設定ファイル

プロジェクトは以下のCompose設定ファイルを使用します：

- `compose.yml` - PHPとNginxの基本設定
- `compose.database.yml` - MySQL設定
- `compose.mailpit.yml` - メール送信テスト用Mailpit設定

### 環境設定

#### 基本的な起動

```bash
# 外部ネットワークを作成（初回のみ）
docker network create slim-network

# 基本サービス（PHP + Nginx）のみ起動
docker compose up --build -d

# データベースも使用する場合
docker compose -f compose.yml -f compose.database.yml up --build -d

# メールテスト機能も使用する場合
docker compose -f compose.yml -f compose.database.yml -f compose.mailpit.yml up --build -d
```

#### 環境変数（オプション）

以下の環境変数で動作をカスタマイズできます：

```bash
# .envファイル例
USER_ID=1000
GROUP_ID=1000
APP_ENV=development
INSTALL_DEV_DEPS=true
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_DATABASE=slim_app
MYSQL_USER=slim_user
MYSQL_PASSWORD=slim_password
```

### インストールと起動

1. プロジェクトディレクトリに移動
```bash
cd /var/www/project
```

2. 外部ネットワークを作成（初回のみ）
```bash
docker network create slim-network
```

3. Dockerコンテナをビルドして起動
```bash
# 基本サービスのみ
docker compose up --build -d

# データベースも含める場合
docker compose -f compose.yml -f compose.database.yml up --build -d
```

4. ブラウザで以下のURLにアクセス
```
http://localhost:8080
```

### 環境の切り替え

#### 開発環境（開発用依存関係含む）
```bash
# 現在のコンテナを停止
docker compose down

# APP_ENV=development で起動（開発用依存関係をインストール）
APP_ENV=development INSTALL_DEV_DEPS=true docker compose up --build -d
```

#### 本番環境（最小構成）
```bash
# 現在のコンテナを停止
docker compose down

# vendor削除（本番用に最適化）
rm -rf app/vendor

# APP_ENV=production で起動
APP_ENV=production INSTALL_DEV_DEPS=false docker compose up --build -d
```

### 利用可能なサービス

- **Webアプリケーション**: http://localhost:8080
- **MySQL**: localhost:3306 （compose.database.ymlを使用時）
- **Mailpit（メールテスト）**: http://localhost:8025 （compose.mailpit.ymlを使用時）

### MySQL接続情報

- Host: localhost
- Port: 3306
- Database: slim_app
- Username: slim_user
- Password: slim_password
- Root Password: rootpassword

## アーキテクチャ

### ディレクトリ構造
```
├── .devcontainer/              # VS Code DevContainer設定
├── .vscode/                   # VS Code設定（デバッグ、タスク、推奨拡張）
├── app/                       # アプリケーションソース
│   ├── config/
│   │   └── config.php         # 環境別設定（ログパス、デバッグフラグ等）
│   ├── src/
│   │   ├── bootstrap.php      # アプリ初期化（DI、ミドルウェア、エラーハンドラ）
│   │   ├── routes.php         # ルート定義（callable配列構文）
│   │   ├── Controllers/
│   │   │   └── FormController.php     # 3ステップフォーム制御（入力→確認→完了）
│   │   ├── Validators/
│   │   │   └── ContactFormValidator.php  # 日本語バリデーション（全角対応）
│   │   ├── Middleware/
│   │   │   ├── RequestContextMiddleware.php  # request_id 付与
│   │   │   └── CsrfMiddleware.php       # CSRF失敗ハンドラ
│   │   ├── Logging/
│   │   │   └── LoggerProvider.php      # Monolog 設定（3チャンネル）
│   │   └── Error/
│   │       ├── HttpErrorRenderer.php   # HTTP例外レンダラ（HTML/JSON）
│   │       └── GeneralErrorRenderer.php # 汎用エラーレンダラ
│   ├── templates/             # Twig テンプレート
│   │   ├── base.html.twig     # ベーステンプレート（Bootstrap CDN）
│   │   ├── error/             # エラーページ（HTML/JSON）
│   │   └── form/              # フォーム関連テンプレート
│   ├── tests/                 # テストスイート（42テスト）
│   │   ├── BaseTestCase.php   # 共通テスト基盤（CSRF自動付与）
│   │   ├── Controllers/       # コントローラーテスト
│   │   ├── Validators/        # バリデーションテスト
│   │   ├── Middleware/        # ミドルウェアテスト
│   │   ├── IntegrationTest.php # エンドツーエンド統合テスト
│   │   └── RoutesTest.php     # ルート存在確認
│   ├── logs/                  # ログファイル（app.log, security.log, mail.log）
│   ├── public/
│   │   └── index.php          # 最小エントリーポイント（bootstrap呼び出しのみ）
│   └── .php-cs-fixer.php      # コードスタイル設定
└── Makefile                   # 開発コマンド（Docker/DevContainer自動切替）
```

### 設計パターン

**ブートストラップ分離**: `public/index.php` は最小化し、`src/bootstrap.php` で初期化処理を集約  
**Provider パターン**: `LoggerProvider` でMonolog設定を外部化  
**設定配列**: `config/config.php` で環境依存値を一元管理  
**ミドルウェアスタック**: RequestContext → CSRF → Twig → Routes → Error の順序  
**エラー統一**: HTTP例外とGeneralエラーで HTML/JSON レスポンスを content negotiation

## 機能

### フォームワークフロー（3ステップ）
- **ホームページ** (`/`): プロジェクト紹介
- **入力画面** (`/form/input`): お問い合わせ内容の入力
- **確認画面** (`/form/confirm`): 入力内容の確認（バリデーション後）
- **完了画面** (`/form/complete`): 送信完了通知

### セキュリティ機能
- **CSRF保護**: slim/csrf による二重送信攻撃防止（セッション + hiddenフィールド）
- **XSS対策**: Twig自動エスケープ + 入力値サニタイズ
- **バリデーション**: 日本語対応の包括的入力検証（空値・形式・文字数制限）
- **エラーハンドリング**: 統一エラーレスポンス（HTML/JSON content negotiation）

### 観測性・運用
- **構造化ログ**: Monolog 3チャンネル（app/security/mail）
- **リクエスト追跡**: request_id による分散トレース対応  
- **静的解析**: PHPStan レベル8 で型安全性確保
- **コード品質**: PHP-CS-Fixer による PSR-2/12 準拠 + 拡張ルール
- **包括的テスト**: 42テスト（単体・統合・エンドツーエンド）

## テスト

このプロジェクトではPHPUnitを使用したテストが含まれています。

## 開発ワークフロー

### Makeコマンド（推奨）

Docker/DevContainer を自動判別し、最適な実行環境を選択：

```bash
# 開発コマンド（Docker環境・DevContainer両対応）
make help         # コマンド一覧表示
make up           # Docker環境起動
make down         # Docker環境停止
make install      # Composer依存関係インストール
make test         # PHPUnit実行（42テスト）
make phpstan      # 静的解析実行（レベル8）
make format       # コード整形（PHP-CS-Fixer）
make format-check # 整形差分確認（dry-run）
make qa           # 品質チェック（phpstan + test）
make php-shell    # PHPコンテナシェル接続
make logs         # アプリケーションログ表示
make status       # コンテナ状態・環境変数確認
```

### Composer スクリプト

```bash
# アプリ内（app/ ディレクトリ）でも実行可能
cd app
composer test           # PHPUnit
composer phpstan        # 静的解析
composer format         # コード整形
composer format-check   # 整形チェック
composer start          # 組み込みサーバー起動（開発用）
```

### 実行方法詳細

#### Docker Composeを直接使用

```bash
# テスト実行
docker compose exec php ./vendor/bin/phpunit

# 静的解析
docker compose exec php ./vendor/bin/phpstan analyse --no-progress

# コード整形
docker compose exec php ./vendor/bin/php-cs-fixer fix

# 特定テストファイル実行
docker compose exec php ./vendor/bin/phpunit tests/Controllers/FormControllerTest.php

# 詳細出力 + カバレッジ
docker compose exec php ./vendor/bin/phpunit --verbose --coverage-text
```

#### DevContainer/ローカル実行

```bash
cd app

# 直接実行（DevContainer内）
./vendor/bin/phpunit
./vendor/bin/phpstan analyse --no-progress
./vendor/bin/php-cs-fixer fix

# または Composer経由
composer test
composer phpstan
composer format
```

## テスト & 品質保証

### テストスイート構成（42テスト）

```
tests/
├── BaseTestCase.php                    # 共通テスト基盤（Slim App + CSRF自動付与）
├── Controllers/
│   └── FormControllerTest.php         # コントローラー単体テスト（各アクション）
├── Validators/
│   └── ContactFormValidatorTest.php   # バリデーション単体テスト（境界値・エラーケース）
├── Middleware/
│   └── CsrfMiddlewareTest.php         # CSRF保護テスト（正常・失敗）
├── IntegrationTest.php                # エンドツーエンド統合テスト（全フロー）
└── RoutesTest.php                     # ルート存在確認・パラメータテスト
```

### 品質ゲート

**静的解析**: PHPStan レベル8（型安全性・未定義変数・デッドコード検出）  
**コードスタイル**: PHP-CS-Fixer（PSR-2/12 + カスタムルール）  
**テスト**: PHPUnit 11.5（100% パス必須）  
**CSRF保護**: 全POST/PUT/PATCHルートでトークン検証

## 開発

### Composerパッケージのインストール

```bash
docker compose exec php composer install
```

### Composerパッケージの追加

```bash
docker compose exec php composer require パッケージ名
```

## ログ・デバッグ

### ログ出力

Monolog による3チャンネル構造化ログ（`app/logs/` ディレクトリ）：

- **app.log**: 一般アプリケーションログ（DEBUG レベル）
- **security.log**: セキュリティ関連ログ（CSRF失敗、認証エラー等）
- **mail.log**: メール送信ログ（将来の機能拡張用）

各ログエントリには `request_id` が自動付与され、リクエスト単位でのトレースが可能。

### デバッグ

**VS Code DevContainer**: Xdebug 設定済み（ポート9003）  
**ログレベル制御**: 設定ファイル (`config/config.php`) の `debug` フラグで制御  
**エラー詳細**: development環境では詳細エラー表示、productionでは隠蔽

### ログの確認

```bash
# アプリケーションログ（推奨）
make logs

# または Docker Compose直接
docker compose logs -f php

# ファイル直接確認
tail -f app/logs/app.log
tail -f app/logs/security.log
tail -f app/logs/mail.log

# 他のサービスログ
docker compose logs web      # Nginx
docker compose logs mysql    # MySQL（database設定時）
```

### コンテナシェル接続

```bash
# PHPコンテナ（推奨）
make php-shell

# 手動接続
docker compose exec php bash

# MySQLコンテナ（database設定時）
docker compose exec mysql mysql -u slim_user -p slim_app
```

## 停止

```bash
# 基本サービスのみ停止
docker compose down

# 複数ファイルで起動した場合も同様に停止
docker compose -f compose.yml -f compose.database.yml down
```

## 今後の拡張

詳細な改善ロードマップは [TASK.md](TASK.md) を参照してください。

### 優先度P0（セキュリティ・安定性）
- X-Request-Id レスポンスヘッダ付与
- セキュリティヘッダ追加（X-Frame-Options等）  
- バリデーション失敗ログ
- Rate Limiting（簡易）

### 優先度P1（アーキテクチャ・品質）
- Provider分割（ViewProvider、ErrorHandlerProvider等）
- Config クラス化
- GitHub Actions CI
- Coverage レポート

### 優先度P2（機能拡張）
- .env サポート（vlucas/phpdotenv）
- メール送信機能（Mailpit連携）
- 多言語化基盤
- Health Check エンドポイント

---

## ライセンス

MIT License
