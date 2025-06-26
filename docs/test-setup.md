# テスト環境のセットアップ手順

このプロジェクトでテストを実行するためのセットアップ手順です。以下の手順に従って、ローカル環境でテストを実行できるように設定してください。

## 1. 環境変数ファイルの作成

プロジェクトをクローンした後、`test` 環境用の `.env.testing` ファイルを作成する必要があります。このファイルにはテスト用のデータベース設定などが含まれます。

1. プロジェクトディレクトリに移動します。
2. `.env` ファイルを `.env.testing` としてコピーします。

```bash
cp .env .env.testing
```
3. .env.testing をテキストエディタで開き、必要な設定を行います。以下の設定を確認・変更してください。

.env.testing の例:

dotenv
```bash
DB_CONNECTION=mysql_test
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=demo_test
DB_USERNAME=root
DB_PASSWORD=root

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=（あなたのメールアドレス）
MAIL_FROM_NAME="Time Attendance Management"

```
APP_KEY は空のままにせず、後で生成します。

データベース設定（DB_DATABASE や DB_USERNAME など）を確認してください。

4. アプリケーションキーを生成
APP_KEY がまだ設定されていない場合、テスト環境用のアプリケーションキーを生成する必要があります。以下のコマンドを実行して、APP_KEY を生成します。

```bash
php artisan key:generate --env=testing
```
5. キャッシュのクリア
設定を反映させるために、キャッシュをクリアします。

```bash
php artisan config:clear
```


6. データベースマイグレーションの実行
7. 必要な場合、シーダーの実行
テスト用のデータベースを作成し、マイグレーションを実行します。これにより、テスト用データベースが最新のスキーマに更新されます。

```bash
php artisan migrate --env=testing
php artisan db:seed --env=testing
```
8. PHPUnit 設定ファイル (phpunit.xml) の確認
#    必要に応じて DB_CONNECTION や DB_DATABASE の設定を確認
phpunit.xml ファイルがクローン時にリポジトリからコピーされてきたことを確認してください。特に以下の設定が正しいか確認します。
DB_CONNECTION が mysql に設定されているか。
DB_DATABASE が demo_test に設定されているか。

phpunit.xml 内の設定例:
xml
```bash
<php>
    <server name="APP_ENV" value="testing"/>
    <server name="BCRYPT_ROUNDS" value="4"/>
    <server name="CACHE_DRIVER" value="array"/>
    <server name="DB_CONNECTION" value="mysql_test"/>
    <server name="DB_DATABASE" value="demo_test"/>
    <server name="MAIL_MAILER" value="array"/>
    <server name="QUEUE_CONNECTION" value="sync"/>
    <server name="SESSION_DRIVER" value="array"/>
    <server name="TELESCOPE_ENABLED" value="false"/>
</php>
```
9. テストの実行
テスト環境がセットアップできたら、以下のコマンドを使ってテストを実行できます。

```bash
php artisan test
```
# または直接 PHPUnit を実行

```bash
vendor/bin/phpunit
```
テストは tests/Feature と tests/Unit フォルダ内に配置されています。

10. メールの確認
テスト実行時にメールが送信される場合、メール送信には Mailtrap が使用されます。Mailtrap のアカウントを作成し、MAIL_USERNAME と MAIL_PASSWORD を .env.testing に設定することで、ダミーメールを確認できます。

注意事項
テスト用のデータベース（demo_test）はテストの前に作成しておく必要があります。

メールの送信は Mailtrap を使用して行います。アカウントを作成し、.env.testing に必要な設定を追加してください。

