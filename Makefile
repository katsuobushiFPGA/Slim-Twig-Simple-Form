# Makefileでテスト実行を簡単に
.PHONY: help up down down-volume install test php-shell logs status

# デフォルトターゲット
help:
	@echo "利用可能なコマンド:"
	@echo "  make up         - アプリケーションを起動"
	@echo "  make down       - アプリケーションを停止"
	@echo "  make down-volume - アプリケーションを停止（ボリューム削除）"
	@echo "  make install    - Composer依存関係をインストール"
	@echo "  make test       - PHPUnitテストを実行"
	@echo "  make php-shell  - PHPコンテナに入る"
	@echo "  make logs       - ログを表示"
	@echo "  make status     - 現在の状態を確認"

# アプリケーション起動
up:
	docker compose up -d

# アプリケーション停止
down:
	docker compose down

# down-volume
down-volume:
	docker compose down -v	

# Composer依存関係のインストール
install:
	docker compose exec php composer install

# テスト実行
test:
	docker compose exec php vendor/bin/phpunit

# PHPコンテナに入る
php-shell:
	docker compose exec php bash

# ログ表示
logs:
	docker compose logs -f php

# 状態確認
status:
	docker compose ps
	@echo ""
	@echo "=== 環境変数 ==="
	@grep -E "(APP_ENV|INSTALL_DEV_DEPS|COMPOSE_FILE)" .env || echo "環境変数が設定されていません"
