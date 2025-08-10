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

### インストールと起動

1. プロジェクトディレクトリに移動
```bash
cd /home/kbushi/workspace/slim-twig-simple-form
```

2. Dockerコンテナをビルドして起動
```bash
docker compose up --build
```

3. ブラウザで以下のURLにアクセス
```
http://localhost:8080
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
- **フォームページ** (`/form`): シンプルな入力フォーム
- **結果ページ** (`/form` POST): フォーム送信結果の表示

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
