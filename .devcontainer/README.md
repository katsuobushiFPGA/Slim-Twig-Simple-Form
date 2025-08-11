# DevContainer Setup

このプロジェクトはVS Code DevContainerをサポートしています。

## 🚀 クイックスタート

### 必要な準備
1. **VS Code** と **Dev Containers 拡張機能** をインストール
2. **Docker Desktop** を起動

### DevContainer起動手順
1. VS Codeでプロジェクトを開く
2. `Ctrl+Shift+P` (Cmd+Shift+P) でコマンドパレットを開く
3. "Dev Containers: Reopen in Container" を選択
4. 初回はコンテナのビルドに数分かかります

## 📦 含まれる拡張機能

### PHP開発
- **Intelephense** - PHP言語サーバー
- **PHP Debug** - Xdebugサポート

### Web開発
- **Twig** - Twigテンプレートサポート
- **Auto Rename Tag** - HTMLタグの自動リネーム
- **Path Intellisense** - パス補完

### 開発支援
- **GitLens** - Git機能拡張
- **TODO Tree** - TODOコメント管理
- **Error Lens** - エラー表示強化
- **Coverage Gutters** - テストカバレッジ表示

### DevOps
- **Docker** - Docker関連機能
- **YAML** - YAML設定ファイルサポート
- **Makefile Tools** - Makefile編集支援

## 🔧 事前設定内容

### PHP設定
- **Intelephense** によるコード補完・定義ジャンプ
- **Xdebug** デバッグ環境（ポート9003）
- **PHPStan** 静的解析統合

### 開発環境
- **composer** 依存関係管理
- **PHPUnit** テスト実行
- **make** コマンド自動化

### VS Code統合
- **フォーマット** 保存時自動実行
- **タスク** 一般的な開発作業の自動化
- **デバッグ** Xdebugによるブレークポイント設定

## 📋 利用可能なタスク

VS Codeのタスク機能（`Ctrl+Shift+P` → "Tasks: Run Task"）で以下を実行可能：

- **Start Development Environment** - 開発環境起動
- **Stop Development Environment** - 開発環境停止
- **Run PHPUnit Tests** - テスト実行
- **Run PHPStan Analysis** - 静的解析実行
- **Install Dependencies** - 依存関係インストール
- **View Logs** - ログ表示
- **Check Status** - サービス状況確認

## 🐛 デバッグ方法

1. ブレークポイントを設定（行番号左をクリック）
2. `F5` でデバッグ開始（"Listen for Xdebug"）
3. ブラウザでアプリケーションを操作
4. ブレークポイントで停止し、変数や実行フローを確認

## 🏃 ターミナルコマンド

DevContainer内で以下のエイリアスが利用可能：

```bash
# 基本操作
ll          # ls -alF
..          # cd ..
...         # cd ../..

# PHP/Composer
composer    # Composer実行
phpunit     # PHPUnit実行
phpstan     # PHPStan実行

# Git
gs          # git status
ga          # git add
gc          # git commit
gp          # git push
gl          # git log --oneline

# プロジェクト固有
test        # make test
stan        # make phpstan
up          # make up
down        # make down
logs        # make logs
```

## 🔗 ポートフォワーディング

- **8080** - Webサーバー（自動的にブラウザで開く）
- **3306** - MySQL（必要に応じて接続）
- **9003** - Xdebug（デバッグ用）

## 💡 開発のヒント

1. **コード補完**: `Ctrl+Space` でIntelephenseによる候補表示
2. **定義ジャンプ**: `F12` で関数/クラス定義へジャンプ
3. **問題パネル**: `Ctrl+Shift+M` でPHPStan/PHPUnitエラー表示
4. **統合ターミナル**: `Ctrl+`` でターミナル表示
5. **ファイル検索**: `Ctrl+P` で高速ファイル検索

## 🚨 トラブルシューティング

### コンテナが起動しない
```bash
# Docker環境のリセット
docker system prune -a
# VS Code reload
Ctrl+Shift+P → "Developer: Reload Window"
```

### デバッグが動作しない
1. Xdebug拡張機能がインストールされているか確認
2. launch.jsonの設定を確認
3. ポート9003が利用可能か確認

### Composerエラー
```bash
# 権限問題の場合
sudo chown -R www-data:www-data /var/www/html
# キャッシュクリア
composer clear-cache
```
