# 改善タスク一覧 (ROADMAP)

本プロジェクトの今後の改善タスク。優先度: P0 (最優先) > P1 > P2。完了時は Status を `DONE` に更新。

## 目次
- [P0 セキュリティ / 安定性](#p0-セキュリティ--安定性)
- [P0 オブザーバビリティ](#p0-オブザーバビリティ)
- [P1 アーキテクチャ / リファクタ](#p1-アーキテクチャ--リファクタ)
- [P1 DX / 品質向上](#p1-dx--品質向上)
- [P2 機能拡張 / 将来案](#p2-機能拡張--将来案)
- [テスト計画補足](#テスト計画補足)

---
## P0 セキュリティ / 安定性
| ID | Task | 概要 | 期待結果 | Status |
|----|------|------|----------|--------|
| SEC-001 | CSRF Failure Handler 抽出 | 無名クロージャ → 専用クラス (CsrfFailureHandler) | 責務分離/テスト容易化 | TODO |
| SEC-002 | セキュリティヘッダ追加 | X-Frame-Options, X-Content-Type-Options, Referrer-Policy 等ミドルウェア | レスポンスに主要ヘッダ付与 | TODO |
| SEC-003 | Rate Limiting (簡易) | IP + 時間窓でPOST回数制御 (in-memory) | 連続攻撃緩和 | TODO |
| SEC-004 | Validation失敗ログ | バリデーションエラーを security.log (WARN) に記録 (件数/フィールド名のみ) | 監査痕跡確保 | TODO |

## P0 オブザーバビリティ
| ID | Task | 概要 | 期待結果 | Status |
|----|------|------|----------|--------|
| OBS-001 | X-Request-Id レスポンスヘッダ | RequestContextMiddleware で生成済 request_id を Response へ付与 | クライアント/ログ間トレース可能 | TODO |
| OBS-002 | エラーレスポンスに request_id | HTML/JSON エラー出力へ request_id を表示/埋め込み | 障害調査容易化 | TODO |
| OBS-003 | ログレベル動的制御 | config(debug) に応じ Monolog ハンドラレベル変更 (production=INFO) | ノイズ低減 | TODO |
| OBS-004 | ログローテーション | StreamHandler → RotatingFileHandler | ファイル肥大防止 | TODO |
| OBS-005 | ログテスト追加 | request_id 付与/CSRF失敗ログ/validationログのアサーション | 回帰検出 | TODO |

## P1 アーキテクチャ / リファクタ
| ID | Task | 概要 | 期待結果 | Status |
|----|------|------|----------|--------|
| ARC-001 | Provider 分割 | LoggerProvider 以外に ViewProvider/ErrorHandlerProvider/CsrfProvider | bootstrap 短縮/関心分離 | TODO |
| ARC-002 | Config クラス化 | 配列 → Immutable Config オブジェクト + 型補完 | メンテ性 / IDE 支援向上 | TODO |
| ARC-003 | ミドルウェア登録順整理 | 一覧 (request id -> security headers -> csrf -> routing -> error) 文書化 | 一貫性 | TODO |
| ARC-004 | Twig キャッシュ条件分岐 | production のみキャッシュ有効 | パフォーマンス | TODO |
| ARC-005 | bootstrap 関数 → Factory | AppBuilder (build(): App) パターン | テストモック容易化 | TODO |

## P1 データベース / 永続化
| ID | Task | 概要 | 期待結果 | Status |
|----|------|------|----------|--------|
| DB-001 | Phinx マイグレーション導入 | robmorgan/phinx 追加 + 設定ファイル (phinx.php) | DBスキーマ版数管理 | DONE |
| DB-002 | 問い合わせテーブル設計 | contacts テーブル (id, name, email, message, status, request_id, created_at, updated_at) | データ永続化基盤 | DONE |
| DB-003 | マイグレーションファイル作成 | create_contacts_table.php (初期スキーマ) | テーブル作成SQL | DONE |
| DB-004 | DB接続設定統合 | config.php に database 設定追加 (PDO DSN, credentials) | 一元化設定管理 | DONE |
| DB-005 | Repository パターン導入 | ContactRepository クラス (save/find/list メソッド) | データアクセス抽象化 | DONE |
| DB-006 | フォーム送信時DB保存 | FormController::complete で問い合わせ内容を DB 保存 | 実用機能実装 | DONE |
| DB-007 | Migration コマンド統合 | Makefile + Composer script で phinx migrate/rollback | 開発効率化 | TODO |
| DB-008 | DB接続テスト追加 | Repository・Migration の単体/統合テスト | 品質担保 | TODO |

## P1 DX / 品質向上
| ID | Task | 概要 | 期待結果 | Status |
|----|------|------|----------|--------|
| DX-001 | GitHub Actions CI | phpunit + phpstan を PR で自動実行 | 早期検知 | TODO |
| DX-002 | Code Style 整備 | php-cs-fixer or ECS 導入 / make format | コード一貫性 | DONE |
| DX-003 | phpstan baseline/strict | 不要なら baseline 管理 + 追加ルール (bleedingEdge) | 静的解析強化 | TODO |
| DX-004 | Coverage レポート | Xdebug + coverage.xml → CI artifact | テスト品質把握 | TODO |
| DX-005 | Make タスク拡張 | make qa (stan+test+style) | ワンコマンド QA | TODO |
| DX-006 | README 更新 | 新しい bootstrap / config 設計記載 | ドキュメント最新化 | DONE |

## P2 機能拡張 / 将来案
| ID | Task | 概要 | 期待結果 | Status |
|----|------|------|----------|--------|
| FUT-001 | .env サポート | vlucas/phpdotenv 導入 | 環境変数管理 | TODO |
| FUT-002 | Mail 送信処理 雛形 | 完了時メール送信 (Mailpit 連携) | 実用性向上 | TODO |
| FUT-003 | 多言語化 基盤 | Validator メッセージ i18n 抽象化 | 拡張性 | TODO |
| FUT-004 | 簡易キャッシュ層 | PSR-16 実装 (filesystem) | 応答高速化 | TODO |
| FUT-005 | Health Check ルート | /health (依存チェック) | 運用監視 | TODO |
| FUT-006 | Rate Limiter 永続化 | Redis など外部ストア利用 | スケール | TODO |

## テスト計画補足
- CSRF: 正常/失敗/新トークン再発行
- ログ: request_id 出力 / レベル制御 / ローテーション日付生成
- エラー: HTML & JSON 両方で request_id 埋め込み
- セキュリティヘッダ: 主要ヘッダ存在 (X-Frame-Options 他)
- Config: production フラグで Twig キャッシュとログレベル切替
- Rate Limit: 窓内閾値超過時 429 応答 & 再試行後復帰
- **DB/Migration**: Phinx migrate/rollback テスト、Repository CRUD テスト、DB接続失敗処理

## DB設計補足 (contacts テーブル)
```sql
CREATE TABLE contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL COMMENT 'お名前',
    email VARCHAR(255) NOT NULL COMMENT 'メールアドレス',
    message TEXT NOT NULL COMMENT 'お問い合わせ内容',
    status ENUM('pending', 'processed', 'completed') DEFAULT 'pending' COMMENT '処理状況',
    request_id VARCHAR(16) NULL COMMENT 'トレース用リクエストID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_request_id (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='お問い合わせテーブル';
```

## 実装順 推奨
1. **DB-001 → DB-004**: Phinx 環境構築 + 設定統合
2. **DB-002 → DB-003**: スキーマ設計 + マイグレーション作成  
3. **DB-005 → DB-006**: Repository パターン + フォーム保存実装
4. **DB-007 → DB-008**: Make統合 + テスト追加
5. OBS-001 / OBS-002 (トレース性向上)
6. SEC-001 / SEC-004 (責務分離 + 監査)
7. DX-001 (CI 基盤)
8. ARC-001 / ARC-002 (構造整理)
9. 以降 P1 残 & P2 拡張

---
更新日: 2025-08-16
