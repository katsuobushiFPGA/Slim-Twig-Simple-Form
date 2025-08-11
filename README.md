# Slim + Twig Simple Form

PHP 8.4、Slim Framework 4、Twigテンプレートエンジンを使用したシンプルなフォームアプリケーションです。

## 技術スタック

- PHP 8.4
- Slim Framework 4
- Twig Template Engine
- Nginx
- MySQL 8.0
- Docker & Docker Compose

## セットアップ

### 必要条件

- Docker
- Docker Compose

### Docker Compose設定ファイル

プロジェクトは以下の分離されたCompose設定ファイルを使用します：

- `compose.base.yml` - PHPとNginxの基本設定
- `compose.database.yml` - MySQL設定
- `compose.development.yml` - 開発環境用オーバーライド
- `compose.production.yml` - 本番環境用オーバーライド

### 環境設定

#### 開発環境

```bash
# .envファイルを開発用設定にコピー
cp .env.example .env

# または手動で.envファイルを作成
cat > .env << EOF
USER_ID=1002
GROUP_ID=1002
APP_ENV=development
COMPOSE_FILE=compose.base.yml:compose.database.yml:compose.development.yml
INSTALL_DEV_DEPS=true
RUN_TESTS=true
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_DATABASE=slim_app
MYSQL_USER=slim_user
MYSQL_PASSWORD=slim_password
EOF
```

#### 本番環境

```bash
# .envファイルを本番用設定にコピー  
cp .env.production .env

# または手動で.envファイルを作成
cat > .env << EOF
USER_ID=1002
GROUP_ID=1002
APP_ENV=production
COMPOSE_FILE=compose.base.yml:compose.production.yml
INSTALL_DEV_DEPS=false
RUN_TESTS=false
EOF
```

### インストールと起動

1. プロジェクトディレクトリに移動
```bash
cd /home/kbushi/workspace/slim-twig-simple-form
```

2. 外部ネットワークを作成（初回のみ）
```bash
docker network create slim-network
```

3. Dockerコンテナをビルドして起動
```bash
docker compose up --build -d
```

4. ブラウザで以下のURLにアクセス
```
http://localhost:8080
```

### 環境の切り替え

#### 開発環境に切り替え
```bash
# 現在のコンテナを停止
docker compose down

# 開発環境設定をコピー
cp .env.example .env  # または開発用設定を作成

# vendor削除（依存関係を正しく再インストールするため）
rm -rf app/vendor

# 開発環境で起動
docker compose up --build -d
```

#### 本番環境に切り替え
```bash
# 現在のコンテナを停止
docker compose down

# 本番環境設定をコピー
cp .env.production .env

# vendor削除（開発依存関係を除外するため）
rm -rf app/vendor

# 本番環境で起動
docker compose up --build -d
```

### 利用可能なサービス

- **Webアプリケーション**: http://localhost:8080
- **MySQL**: localhost:3306

### MySQL接続情報

- Host: localhost
- Port: 3306
- Database: slim_app
- Username: slim_user
- Password: slim_password
- Root Password: rootpassword

## プロジェクト構造

```
├── docker/
│   └── nginx/
│       └── default.conf      # Nginx設定ファイル
├── public/
│   └── index.php            # アプリケーションエントリーポイント
├── templates/               # Twigテンプレート
│   ├── base.html.twig      # ベーステンプレート
│   ├── index.html.twig     # ホームページ
│   ├── form.html.twig      # フォームページ
│   └── result.html.twig    # 結果ページ
├── composer.json           # Composer設定
├── Dockerfile             # PHP-FPMイメージ
└── compose.yml           # Docker Compose設定
```

## 機能

- **ホームページ** (`/`): アプリケーションの概要
- **お問い合わせフォーム** (3ステップ):
  - **入力画面** (`/form/input`): お問い合わせ内容の入力
  - **確認画面** (`/form/confirm`): 入力内容の確認
  - **完了画面** (`/form/complete`): 送信完了

## テスト

このプロジェクトではPHPUnitを使用したテストが含まれています。

### テスト実行方法

#### 1. Makefileを使用（推奨）

```bash
# ヘルプ表示
make help

# アプリケーション起動
make up

# 依存関係インストール
make install

# テスト実行
make test

# 詳細出力でテスト実行
make test-verbose

# 特定のテストクラスを実行
make test-class

# テストカバレッジ
make test-coverage
```

#### 2. Docker Composeを直接使用

```bash
# 通常のテスト実行
docker compose run --rm test

# 詳細出力
docker compose run --rm test vendor/bin/phpunit --verbose

# 特定のテストファイルを実行
docker compose run --rm test vendor/bin/phpunit tests/Controllers/FormControllerTest.php

# テストカバレッジ
docker compose run --rm test vendor/bin/phpunit --coverage-text
```

#### 3. PHPコンテナ内でテスト実行

```bash
# PHPコンテナに入る
make shell
# または
docker compose exec php bash

# コンテナ内でテスト実行
vendor/bin/phpunit
```

### テスト構成

```
tests/
├── BaseTestCase.php                    # テストベースクラス
├── Controllers/
│   └── FormControllerTest.php         # コントローラーテスト
├── IntegrationTest.php                # 統合テスト
├── RoutesTest.php                     # ルートテスト
└── ValidationTest.php                 # バリデーションテスト
```

### テストの種類

- **コントローラーテスト**: 各エンドポイントの動作確認
- **バリデーションテスト**: フォームバリデーションの確認
- **統合テスト**: フォーム送信フロー全体のテスト
- **ルートテスト**: すべてのルートの存在確認

## 開発

### Composerパッケージのインストール

```bash
docker compose exec php composer install
```

### Composerパッケージの追加

```bash
docker compose exec php composer require パッケージ名
```

### ログの確認

```bash
# PHP-FPMログ
docker compose logs php

# Nginxログ
docker compose logs web

# MySQLログ
docker compose logs mysql
```

### コンテナに入る

```bash
# PHPコンテナ
docker compose exec php bash

# MySQLコンテナ
docker compose exec mysql mysql -u slim_user -p slim_app
```

## 停止

```bash
docker compose down
```

## 完全削除（ボリュームも含む）

```bash
docker compose down -v
```
