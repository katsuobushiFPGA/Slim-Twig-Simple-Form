# Makefileでテスト実行を簡単に（Docker/DevContainer 両対応）
.PHONY: help up down down-volume install test phpstan php-shell logs status qa

# 設定
APP_DIR := app
# phpサービス名（compose.yml に合わせる）
PHP_SERVICE := php
# docker compose が使えるか + php サービスが起動しているか
USE_DOCKER := $(shell docker compose ps $(PHP_SERVICE) >/dev/null 2>&1 && echo 1 || echo 0)

# デフォルトターゲット
help:
	@echo "利用可能なコマンド:"
	@echo "  make up         - アプリケーションを起動 (Docker)"
	@echo "  make down       - アプリケーションを停止 (Docker)"
	@echo "  make down-volume - アプリケーションを停止（ボリューム削除）(Docker)"
	@echo "  make install    - Composer依存関係をインストール (Dockerが無い場合はローカル)"
	@echo "  make test       - PHPUnitテストを実行 (自動でDocker/ローカル切替)"
	@echo "  make phpstan    - PHPStan静的解析を実行 (自動でDocker/ローカル切替)"
	@echo "  make php-shell  - PHPコンテナに入る (Docker)"
	@echo "  make logs       - ログを表示 (Docker)"
	@echo "  make status     - 現在の状態を確認 (Docker)"
	@echo "  make qa         - phpstan + phpunit をまとめて実行"

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
ifeq ($(USE_DOCKER),1)
	docker compose exec $(PHP_SERVICE) composer install
else
	cd $(APP_DIR) && composer install
endif

# テスト実行
test:
ifeq ($(USE_DOCKER),1)
	docker compose exec $(PHP_SERVICE) vendor/bin/phpunit
else
	cd $(APP_DIR) && ./vendor/bin/phpunit --colors=never
endif

# PHPStan静的解析実行
phpstan:
ifeq ($(USE_DOCKER),1)
	docker compose exec $(PHP_SERVICE) vendor/bin/phpstan analyse --no-progress
else
	cd $(APP_DIR) && ./vendor/bin/phpstan analyse --no-progress
endif

# まとめて品質チェック
qa: phpstan test

# PHPコンテナに入る
php-shell:
ifeq ($(USE_DOCKER),1)
	docker compose exec $(PHP_SERVICE) bash
else
	@echo "[info] Docker 環境ではありません。コンテナシェルは使用できません。"
endif

# ログ表示
logs:
ifeq ($(USE_DOCKER),1)
	docker compose logs -f $(PHP_SERVICE)
else
	@echo "[info] Docker 環境ではありません。Docker ログは使用できません。"
endif

# 状態確認
status:
ifeq ($(USE_DOCKER),1)
	docker compose ps
else
	@echo "[info] Docker 環境ではありません。"
endif
	@echo ""
	@echo "=== 環境変数 ==="
	@grep -E "(APP_ENV|INSTALL_DEV_DEPS|COMPOSE_FILE)" .env || echo "環境変数が設定されていません"
