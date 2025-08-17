# Makefileでテスト実行を簡単に（Docker/DevContainer 両対応）
.PHONY: help up down down-volume install test phpstan php-shell logs status qa format format-check migrate migrate-rollback migrate-status migrate-create ci ci-test ci-migrate

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
	@echo "  make format     - PHP-CS-Fixerでコード整形 (自動でDocker/ローカル切替)"
	@echo "  make format-check - PHP-CS-Fixerでコード整形チェック (自動でDocker/ローカル切替)"
	@echo "  make migrate    - Phinxマイグレーション実行 (自動でDocker/ローカル切替)"
	@echo "  make migrate-rollback - Phinxマイグレーションロールバック (自動でDocker/ローカル切替)"
	@echo "  make migrate-status - Phinxマイグレーション状況確認 (自動でDocker/ローカル切替)"
	@echo "  make migrate-create - Phinxマイグレーション作成 (自動でDocker/ローカル切替)"
	@echo "  make php-shell  - PHPコンテナに入る (Docker)"
	@echo "  make logs       - ログを表示 (Docker)"
	@echo "  make status     - 現在の状態を確認 (Docker)"
	@echo "  make qa         - phpstan + phpunit をまとめて実行"
	@echo "  make ci         - CI環境用：マイグレーション + テスト + 静的解析"
	@echo "  make ci-test    - CI環境用：テスト実行（カバレッジ付き）"
	@echo "  make ci-migrate - CI環境用：マイグレーション実行"

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

# コード整形
format:
ifeq ($(USE_DOCKER),1)
	docker compose exec $(PHP_SERVICE) ./vendor/bin/php-cs-fixer fix
else
	cd $(APP_DIR) && ./vendor/bin/php-cs-fixer fix
endif

# コード整形チェック (dry-run)
format-check:
ifeq ($(USE_DOCKER),1)
	docker compose exec $(PHP_SERVICE) ./vendor/bin/php-cs-fixer fix --dry-run --diff
else
	cd $(APP_DIR) && ./vendor/bin/php-cs-fixer fix --dry-run --diff
endif

# マイグレーション実行
migrate:
ifeq ($(USE_DOCKER),1)
	docker compose exec $(PHP_SERVICE) ./vendor/bin/phinx migrate
else
	cd $(APP_DIR) && ./vendor/bin/phinx migrate
endif

# マイグレーションロールバック
migrate-rollback:
ifeq ($(USE_DOCKER),1)
	docker compose exec $(PHP_SERVICE) ./vendor/bin/phinx rollback
else
	cd $(APP_DIR) && ./vendor/bin/phinx rollback
endif

# マイグレーション状況確認
migrate-status:
ifeq ($(USE_DOCKER),1)
	docker compose exec $(PHP_SERVICE) ./vendor/bin/phinx status
else
	cd $(APP_DIR) && ./vendor/bin/phinx status
endif

# マイグレーション作成
migrate-create:
ifeq ($(USE_DOCKER),1)
	@read -p "Migration name: " name; \
	docker compose exec $(PHP_SERVICE) ./vendor/bin/phinx create $$name
else
	@read -p "Migration name: " name; \
	cd $(APP_DIR) && ./vendor/bin/phinx create $$name
endif

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

# CI環境用：フル実行 (migrate + test + phpstan)
ci: ci-migrate ci-test phpstan

# CI環境用：マイグレーション実行
ci-migrate:
	cd $(APP_DIR) && ./vendor/bin/phinx migrate -e testing

# CI環境用：テスト実行（カバレッジ付き）
ci-test:
	cd $(APP_DIR) && ./vendor/bin/phpunit --coverage-text --coverage-clover=coverage.xml
