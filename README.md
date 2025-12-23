# 勤怠管理アプリ

## 環境構築
### Dockerビルド
1.git clone git@github.com:hirori374/attendance-management-app.git
2.cd attendance-management-app
3.DockerDesktopアプリを立ち上げる
4.docker-compose up -d --build

### Laravel環境構築
1.docker-compose exec php bash
2.composer install
3.cp .env.example .env
4..env に以下の環境変数を追加
```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
5..envファイルにMailHog設定の追記
```
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=任意のメールアドレスを入力してください
```
6.アプリケーションキーの作成
```
php artisan key:generate
```
7.マイグレーションの実行
```
php artisan migrate
```
8.シーディングの実行
```
php artisan db:seed
```

## テーブル仕様
### usersテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| name | varchar(255) |  |  | ◯ |  |
| email | varchar(255) |  | ◯ | ◯ |  |
| email_verified_at | timestamp |  |  |  |  |
| password | varchar(255) |  |  | ◯ |  |
| remember_token | varchar(100) |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### attendancesテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| user_id | bigint |  |  | ◯ | users(id) |
| date | date |  |  | ◯ |  |
| attendance_start_time | varchar(255) |  |  |  |  |
| attendance_end_time | varchar(255) |  |  |  |  |
| remarks | varchar(255) |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### restsテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| attendance_id | bigint |  |  | ◯ | attendances(id) |
| rest_start_time | varchar(255) |  |  |  |  |
| rest_end_time | varchar(255) |  |  |  |  |
| remarks | varchar(255) |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### attendance_correctionsテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint |  |  | ◯ |  |
| request_batch_id | uuid | ◯ |  |  |  |
| request_date | date |  |  | ◯ |  |
| user_id | bigint |  |  | ◯ | users(id) |
| date | date |  |  | ◯ |  |
| attendance_id | bigint |  |  | ◯ | attendances(id) |
| attendance_request_start_time | varchar(255) |  |  |  |  |
| attendance_request_end_time | varchar(255) |  |  |  |  |
| remarks | varchar(255) |  |  | ◯ |  |
| status | varchar(255) |  |  | ◯ |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### rest_correctionsテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint |  |  | ◯ |  |
| request_batch_id | uuid | ◯ |  |  |  |
| request_date | date |  |  | ◯ |  |
| user_id | bigint |  |  | ◯ | users(id) |
| date | date |  |  | ◯ |  |
| rest_id | bigint |  |  | ◯ | rests(id) |
| rest_request_start_time | varchar(255) |  |  |  |  |
| rest_request_end_time | varchar(255) |  |  |  |  |
| remarks | varchar(255) |  |  | ◯ |  |
| status | varchar(255) |  |  | ◯ |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

## テストアカウント
    name:管理者
    email:manager@example.com
    password:manager0000
    -------------------------
    name:一般ユーザー1
    email:general1@example.com
    password:general1111
    -------------------------
    name:一般ユーザー2
    email:general2@example.com
    password:general2222

## PHPUnitを利用したテストに関して
以下のコマンド:
```
//テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
//パスワードはrootと入力
create database laravel_testing;

docker-compose exec php bash
php artisan migrate:fresh --env=testing
./vendor/bin/phpunit
```

## 使用技術
・PHP 8.2.29
・Laravel Framework 8.83.29
・Mysql 8.0.26
・PHPUnit 9.6.29

## ER図
![Alt text](/src/ER.png)

## URL
・一般ユーザー登録：http://localhost/register
・管理者ログイン：http://localhost/admin/login
・phpMyAdmin：http://localhost:8080/
・mailhog：http://localhost:8025/