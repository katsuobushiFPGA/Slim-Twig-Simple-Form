# Slim + Twig Simple Form - AI開発エージェント向け指示書

## プロジェクト概要
PHP 8.4、Slim Framework 4、Twigテンプレートを使用した3ステップお問い合わせフォーム（入力 → 確認 → 完了）アプリケーション。包括的なバリデーションとテストを実装。

## アーキテクチャ

### コアアプリケーションフロー
- **エントリーポイント**: `app/public/index.php` でSlimアプリとTwigミドルウェアを初期化
- **ルーティング**: `app/src/routes.php` 単一ファイルで全エンドポイントを定義（callable配列構文使用）
- **コントローラーパターン**: `FormController` でステップ毎のメソッドによるフォーム処理
- **バリデーション**: 日本語エラーメッセージと全角文字対応の専用 `ContactFormValidator` クラス
- **テンプレート**: `app/templates/` 内のTwigテンプレート、`base.html.twig` の継承パターン

### 重要なパターン
```php
// コントローラーアクションはTwigレスポンスを返す
return $view->render($response, 'template.html.twig', ['data' => $data]);

// バリデーションはエラー配列パターンに従う
$errors = $validator->validate($data); // エラー文字列の配列を返す

// ルートはcallable配列構文を使用
$app->post('/form/confirm', [FormController::class, 'confirm']);
```

## 開発ワークフロー

### DevContainer（推奨）
- **起動**: VS Code → "Dev Containers: Reopen in Container"
- **自動セットアップ**: Intelephense、Xdebug、PHPStan統合、推奨拡張機能
- **組み込みエイリアス**: ターミナルで `test`、`stan`、`up`、`down` 利用可能

### 手動Dockerコマンド
```bash
# 環境セットアップ（開発環境）
cp .env.example .env  # INSTALL_DEV_DEPS=true、APP_ENV=development設定

# 基本コマンド
make up          # 全サービス起動（nginx:8080、mysql:3306）
make test        # PHPUnitテストスイート実行（39テスト）
make phpstan     # 静的解析（レベル8）
make php-shell   # PHPコンテナに入る
```

### 環境切り替え
Docker Composeオーバーライドパターンを使用：
- **開発環境**: `compose.yml` + `compose.database.yml` + `compose.development.yml`
- **本番環境**: `compose.yml` + `compose.production.yml`
- **重要な違い**: `INSTALL_DEV_DEPS` で開発依存関係のインストールを制御

## コード構成

### フォームバリデーションパターン
```php
// FormController::confirm() 内で
$validator = new ContactFormValidator();
$errors = $validator->validate($data);
$data = $validator->getTrimmedData($data); // 常に入力値をトリム

if (!empty($errors)) {
    // エラーとデータを保持して入力画面に戻る
    return $view->render($response, 'form/input.html.twig', [
        'errors' => $errors, 'data' => $data
    ]);
}
```

### テスト構造
- **BaseTestCase**: 全テスト用のSlimアプリ + Twigミドルウェアセットアップ
- **パターン**: リクエスト作成 → アプリ実行 → レスポンス検証
- **カバレッジ**: コントローラーアクション、バリデーションロジック、ルーティング、統合フロー

### 日本語対応の考慮事項
- 全バリデーションメッセージが日本語
- 全角文字長検証に `mb_strlen()` 使用
- テンプレートコンテンツとエラーメッセージはUTF-8エンコード

## 重要ファイルと役割
- `app/src/Controllers/FormController.php`: バリデーション統合付き3ステップフォームフロー
- `app/src/Validators/ContactFormValidator.php`: 日本語メッセージ付き包括的バリデーション
- `app/templates/base.html.twig`: ナビゲーションとスタイル付き共通レイアウト
- `tests/BaseTestCase.php`: Twigセットアップ付きSlimアプリテスト基盤
- `Makefile`: 重要な開発コマンドのラッパー
- `.devcontainer/`: 完全なVS Code開発環境

## よくあるタスク
- **新しいフォームフィールド追加**: バリデーターの検証メソッド、テンプレート、テストを更新
- **新しいルート**: `routes.php` に追加、コントローラーメソッド実装、テンプレート作成
- **データベース**: `compose.database.yml` オーバーレイ使用、環境変数で接続
- **デバッグ**: DevContainerにXdebugがポート9003で事前設定済み
