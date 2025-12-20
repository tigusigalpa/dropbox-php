# Dropbox PHP SDK

![Dropbox PHP SDK](https://github.com/user-attachments/assets/361952c5-03d0-4ef6-b0b8-3106bb4ca3be)

[![Latest Version](https://img.shields.io/packagist/v/tigusigalpa/dropbox-php.svg)](https://packagist.org/packages/tigusigalpa/dropbox-php)
[![License](https://img.shields.io/packagist/l/tigusigalpa/dropbox-php.svg)](https://github.com/tigusigalpa/dropbox-php/blob/main/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/tigusigalpa/dropbox-php.svg)](https://packagist.org/packages/tigusigalpa/dropbox-php)

Современный PHP SDK для [Dropbox API v2](https://www.dropbox.com/developers/documentation/http/documentation) с полной
поддержкой Laravel (версии 8-12). Пакет предоставляет чистый и интуитивный интерфейс для взаимодействия с мощными
возможностями облачного хранилища и совместной работы Dropbox.

**🌐 Язык:** Русский | [English](README.md)

## Содержание

- [Особенности](#особенности)
- [Поддерживаемые эндпоинты](#поддерживаемые-эндпоинты)
- [Требования](#требования)
- [Установка](#установка)
- [Быстрый старт](#быстрый-старт)
- [Подробные примеры использования](#подробные-примеры-использования)
    - [Работа с файлами](#работа-с-файлами)
    - [Совместный доступ](#совместный-доступ)
    - [Пользователи и аккаунт](#пользователи-и-аккаунт)
    - [File Requests](#file-requests)
    - [Paper документы](#paper-документы)
    - [Пакетные операции](#пакетные-операции)
- [OAuth 2.0 авторизация](#oauth-20-авторизация)
- [Использование в Laravel](#использование-в-laravel)
- [Обработка ошибок](#обработка-ошибок)
- [Продвинутые примеры](#продвинутые-примеры)
- [Структура пакета](#структура-пакета)
- [Тестирование](#тестирование)
- [Contributing](#contributing)
- [Changelog](#changelog)
- [Лицензия](#лицензия)

## Особенности

- ✅ **Полное покрытие Dropbox API v2** - Реализация всех основных эндпоинтов
- 🚀 **Интеграция с Laravel** - Бесшовная работа с Laravel 8, 9, 10, 11 и 12
- 🎯 **Современный PHP** - Построен на PHP 8.1+ с лучшими практиками
- 📦 **Автономное использование** - Отлично работает без Laravel
- 🔐 **Поддержка OAuth 2.0** - Встроенные помощники для OAuth flow
- 📝 **Подробная документация** - Детальные примеры и справочники API
- 🧪 **Хорошо протестирован** - Включает PHPUnit тесты
- 🎨 **Чистый API** - Интуитивные, цепочечные методы
- ⚡ **Chunked Upload** - Поддержка загрузки больших файлов по частям
- 🔄 **Batch Operations** - Пакетные операции для эффективной работы

## Поддерживаемые эндпоинты

- **Files** - Загрузка, скачивание, перемещение, копирование, удаление, поиск и управление файлами/папками
- **Sharing** - Создание общих ссылок, управление доступом к папкам/файлам, совместная работа
- **Users** - Информация об аккаунте, использование места, функции пользователя
- **File Requests** - Создание и управление формами запроса файлов
- **Paper** - Создание, редактирование и управление документами Dropbox Paper
- **Check** - Проверка подключения к API

## Требования

- PHP 8.1 или выше
- Guzzle HTTP client 7.0+
- Laravel 8.0+ (опционально, для интеграции с Laravel)

## Установка

Установите пакет через Composer:

```bash
composer require tigusigalpa/dropbox-php
```

### Установка в Laravel

Пакет автоматически зарегистрирует свой service provider и facade.

Опубликуйте конфигурационный файл:

```bash
php artisan vendor:publish --tag=dropbox-config
```

Добавьте учетные данные Dropbox в ваш `.env` файл:

```env
DROPBOX_ACCESS_TOKEN=your_access_token_here
DROPBOX_APP_KEY=your_app_key
DROPBOX_APP_SECRET=your_app_secret
DROPBOX_REDIRECT_URI=https://your-app.com/callback
```

## Быстрый старт

### Получение Access Token

1. Создайте приложение Dropbox в [Dropbox App Console](https://www.dropbox.com/developers/apps)
2. Выберите тип приложения и разрешения
3. Сгенерируйте access token на странице настроек приложения

Для production приложений реализуйте OAuth 2.0 flow (см. раздел [OAuth 2.0 авторизация](#oauth-20-авторизация)).

### Базовое использование (Standalone PHP)

```php
use Tigusigalpa\Dropbox\DropboxClient;

$client = new DropboxClient('your_access_token');

// Получить информацию о текущем пользователе
$account = $client->users->getCurrentAccount();
echo "Привет, " . $account['name']['display_name'];

// Загрузить файл
$result = $client->files->upload(
    '/Documents/hello.txt',
    'Hello, Dropbox!',
    'add'
);

// Список содержимого папки
$contents = $client->files->listFolder('/Documents');
foreach ($contents['entries'] as $entry) {
    echo $entry['name'] . "\n";
}

// Скачать файл
$file = $client->files->download('/Documents/hello.txt');
file_put_contents('local-hello.txt', $file['content']);

// Создать общую ссылку
$link = $client->sharing->createSharedLinkWithSettings('/Documents/hello.txt');
echo "Поделиться ссылкой: " . $link['url'];
```

### Использование в Laravel

```php
use Tigusigalpa\Dropbox\Facades\Dropbox;

// Using Facade
$account = Dropbox::users()->getCurrentAccount();

// Using Dependency Injection
use Tigusigalpa\Dropbox\DropboxClient;

class FileController extends Controller
{
    public function upload(Request $request, DropboxClient $dropbox)
    {
        $content = file_get_contents($request->file('document')->path());
        
        $result = $dropbox->files->upload(
            '/uploads/' . $request->file('document')->getClientOriginalName(),
            $content
        );
        
        return response()->json($result);
    }
}
```

## Подробные примеры использования

### Работа с файлами

#### Загрузка файлов

```php
// Простая загрузка
$client->files->upload('/path/to/file.txt', 'Содержимое файла');

// Загрузка с опциями
$client->files->upload(
    '/path/to/file.txt',
    $content,
    'overwrite',  // режим: 'add', 'overwrite', или 'update'
    true,         // автопереименование при конфликте
    false,        // отключить уведомления
    false         // строгая проверка конфликтов
);

// Загрузка из локального файла
$content = file_get_contents('/local/path/file.pdf');
$client->files->upload('/Dropbox/file.pdf', $content);

// Загрузка с метаданными
$result = $client->files->upload(
    '/Documents/report.pdf',
    file_get_contents('local-report.pdf'),
    'add',
    false,
    false,
    false
);
echo "Загружен файл: {$result['name']}, размер: {$result['size']} байт";
```

#### Загрузка больших файлов (по частям)

```php
// Начать сессию загрузки
$session = $client->files->uploadSessionStart($firstChunk, false);
$sessionId = $session['session_id'];

// Добавить части файла
$offset = strlen($firstChunk);
$client->files->uploadSessionAppend($sessionId, $offset, $secondChunk, false);

// Завершить загрузку
$offset += strlen($secondChunk);
$result = $client->files->uploadSessionFinish(
    $sessionId,
    $offset,
    $lastChunk,
    ['path' => '/large-file.zip', 'mode' => 'add']
);

// Пример загрузки большого файла целиком
$filePath = '/path/to/large-video.mp4';
$chunkSize = 4 * 1024 * 1024; // 4MB chunks
$file = fopen($filePath, 'rb');

// Первый чанк
$firstChunk = fread($file, $chunkSize);
$session = $client->files->uploadSessionStart($firstChunk, false);
$offset = strlen($firstChunk);

// Остальные чанки
while (!feof($file)) {
    $chunk = fread($file, $chunkSize);
    $isLast = feof($file);
    
    if ($isLast) {
        // Последний чанк - завершаем сессию
        $client->files->uploadSessionFinish(
            $session['session_id'],
            $offset,
            $chunk,
            ['path' => '/Videos/large-video.mp4', 'mode' => 'add']
        );
    } else {
        // Промежуточный чанк
        $client->files->uploadSessionAppend(
            $session['session_id'],
            $offset,
            $chunk,
            false
        );
        $offset += strlen($chunk);
    }
}
fclose($file);
```

#### Скачивание файлов

```php
// Скачать файл
$file = $client->files->download('/Documents/report.pdf');
file_put_contents('local-report.pdf', $file['content']);
echo "Скачан файл размером: " . strlen($file['content']) . " байт";

// Скачать конкретную ревизию
$file = $client->files->download('/Documents/report.pdf', 'rev123abc');

// Скачать папку как ZIP
$zip = $client->files->downloadZip('/Documents/Project');
file_put_contents('project.zip', $zip['content']);

// Получить временную ссылку для скачивания (действует 4 часа)
$link = $client->files->getTemporaryLink('/Documents/report.pdf');
echo "Временная ссылка: " . $link['link'];

// Экспорт файла (например, Google Docs в PDF)
$exported = $client->files->export('/Documents/google-doc.gdoc', 'pdf');
file_put_contents('exported.pdf', $exported['content']);
```

#### Управление файлами и папками

```php
// Создать папку
$folder = $client->files->createFolder('/Projects/NewProject');
echo "Создана папка: " . $folder['metadata']['path_display'];

// Создать несколько папок одновременно
$result = $client->files->createFolderBatch([
    '/Projects/Project1',
    '/Projects/Project2',
    '/Projects/Project3'
], false, false);

// Переместить файл/папку
$moved = $client->files->move('/old/path.txt', '/new/path.txt');
echo "Перемещено в: " . $moved['metadata']['path_display'];

// Копировать файл/папку
$copied = $client->files->copy('/source.txt', '/destination.txt');

// Удалить файл/папку (в корзину)
$client->files->delete('/path/to/delete.txt');

// Удалить навсегда (минуя корзину)
$client->files->permanentlyDelete('/path/to/file.txt');

// Восстановить файл к предыдущей ревизии
$restored = $client->files->restore('/path/to/file.txt', 'rev123abc');

// Получить список ревизий файла
$revisions = $client->files->listRevisions('/Documents/important.docx', 'path', 100);
foreach ($revisions['entries'] as $rev) {
    echo "Ревизия: {$rev['rev']}, дата: {$rev['client_modified']}\n";
}
```

#### Поиск и листинг

```php
// Список содержимого папки
$result = $client->files->listFolder('/Documents');
foreach ($result['entries'] as $entry) {
    if ($entry['.tag'] === 'folder') {
        echo "Папка: " . $entry['name'] . "\n";
    } else {
        echo "Файл: " . $entry['name'] . " (" . $entry['size'] . " байт)\n";
        echo "  Изменен: " . $entry['client_modified'] . "\n";
    }
}

// Рекурсивный листинг всех файлов
$result = $client->files->listFolder('', true);

// Продолжить листинг с курсором (пагинация)
if ($result['has_more']) {
    $more = $client->files->listFolderContinue($result['cursor']);
}

// Полный листинг большой папки с пагинацией
$allEntries = [];
$result = $client->files->listFolder('/BigFolder');
$allEntries = array_merge($allEntries, $result['entries']);

while ($result['has_more']) {
    $result = $client->files->listFolderContinue($result['cursor']);
    $allEntries = array_merge($allEntries, $result['entries']);
}
echo "Всего элементов: " . count($allEntries);

// Поиск файлов
$results = $client->files->search(
    'счет',              // поисковый запрос
    '/Documents',        // путь для поиска
    100,                 // максимум результатов
    'relevance',         // сортировка по релевантности
    'active',            // статус файлов
    null,                // искать только в именах файлов
    ['pdf', 'docx'],     // расширения файлов
    ['documents']        // категории файлов
);

foreach ($results['matches'] as $match) {
    $file = $match['metadata']['metadata'];
    echo "Найден: {$file['name']} в {$file['path_display']}\n";
}

// Продолжить поиск с курсором
if ($results['has_more']) {
    $moreResults = $client->files->searchContinue($results['cursor']);
}

// Получить метаданные файла
$metadata = $client->files->getMetadata('/Documents/file.txt', true);
echo "Изменен: " . $metadata['client_modified'];
echo "Размер: " . $metadata['size'] . " байт";
echo "ID: " . $metadata['id'];

// Получить метаданные с информацией о медиа
$metadata = $client->files->getMetadata('/Photos/vacation.jpg', true);
if (isset($metadata['media_info'])) {
    echo "Размеры: {$metadata['media_info']['metadata']['dimensions']['width']}x";
    echo "{$metadata['media_info']['metadata']['dimensions']['height']}";
}
```

#### Превью и миниатюры

```php
// Получить миниатюру изображения
$thumb = $client->files->getThumbnail(
    '/Photos/vacation.jpg',
    'jpeg',      // формат: 'jpeg' или 'png'
    'w256h256',  // размер: w32h32, w64h64, w128h128, w256h256, w480h320, w640h480, w960h640, w1024h768, w2048h1536
    'strict'     // режим: 'strict', 'bestfit', 'fitone_bestfit'
);
file_put_contents('thumb.jpg', $thumb['content']);

// Получить превью документа
$preview = $client->files->getPreview('/Documents/presentation.pptx');
file_put_contents('preview.pdf', $preview['content']);

// Получить миниатюры пакетом
$thumbs = $client->files->getThumbnailBatch([
    [
        'path' => '/Photos/img1.jpg',
        'format' => 'jpeg',
        'size' => 'w128h128',
        'mode' => 'strict'
    ],
    [
        'path' => '/Photos/img2.jpg',
        'format' => 'jpeg',
        'size' => 'w128h128',
        'mode' => 'strict'
    ],
]);

foreach ($thumbs['entries'] as $entry) {
    if ($entry['.tag'] === 'success') {
        $thumbnail = $entry['thumbnail'];
        file_put_contents('thumb_' . basename($entry['metadata']['name']), $thumbnail);
    }
}
```

### Совместный доступ

#### Общие ссылки

```php
// Создать общую ссылку
$link = $client->sharing->createSharedLinkWithSettings('/Documents/report.pdf', [
    'requested_visibility' => ['.tag' => 'public'],
    'audience' => ['.tag' => 'public'],
    'access' => ['.tag' => 'viewer'],
]);
echo "URL для общего доступа: " . $link['url'];

// Создать ссылку с паролем и сроком действия
$link = $client->sharing->createSharedLinkWithSettings('/Documents/secret.pdf', [
    'link_password' => 'mypassword123',
    'expires' => '2024-12-31T23:59:59Z',
]);

// Список всех общих ссылок
$links = $client->sharing->listSharedLinks();

// Получить метаданные общей ссылки
$metadata = $client->sharing->getSharedLinkMetadata('https://www.dropbox.com/...');

// Изменить настройки общей ссылки
$updated = $client->sharing->modifySharedLinkSettings(
    'https://www.dropbox.com/...',
    ['requested_visibility' => ['.tag' => 'password']]
);

// Отозвать общую ссылку
$client->sharing->revokeSharedLink('https://www.dropbox.com/...');
```

#### Совместный доступ к папкам

```php
// Открыть общий доступ к папке
$shared = $client->sharing->shareFolder('/Projects/TeamProject', null, false);
$folderId = $shared['shared_folder_id'];

// Добавить участников в общую папку
$client->sharing->addFolderMember($folderId, [
    [
        'member' => ['.tag' => 'email', 'email' => 'colleague@example.com'],
        'access_level' => ['.tag' => 'editor'],
    ],
], false, 'Пожалуйста, просмотрите этот проект');

// Список участников папки
$members = $client->sharing->listFolderMembers($folderId);

// Обновить права участника
$client->sharing->updateFolderMember(
    $folderId,
    ['.tag' => 'email', 'email' => 'colleague@example.com'],
    'viewer'
);

// Удалить участника из папки
$client->sharing->removeFolderMember(
    $folderId,
    ['.tag' => 'email', 'email' => 'colleague@example.com'],
    true  // оставить копию
);

// Список общих папок
$folders = $client->sharing->listFolders(100);

// Подключить общую папку
$client->sharing->mountFolder($folderId);

// Отключить общую папку
$client->sharing->unmountFolder($folderId);

// Закрыть общий доступ к папке
$client->sharing->unshareFolder($folderId, false);
```

#### Совместный доступ к файлам

```php
// Добавить участников к файлу
$client->sharing->addFileMember(
    '/Documents/contract.pdf',
    [
        [
            'member' => ['.tag' => 'email', 'email' => 'client@example.com'],
            'access_level' => ['.tag' => 'viewer'],
        ],
    ],
    'Пожалуйста, просмотрите и подпишите',
    false,
    'viewer'
);

// Список участников файла
$members = $client->sharing->listFileMembers('/Documents/contract.pdf');

// Удалить участника из файла
$client->sharing->removeFileMember(
    '/Documents/contract.pdf',
    ['.tag' => 'email', 'email' => 'client@example.com']
);
```

### Пользователи и аккаунт

```php
// Получить информацию о текущем аккаунте
$account = $client->users->getCurrentAccount();
echo "ID аккаунта: " . $account['account_id'];
echo "Имя: " . $account['name']['display_name'];
echo "Email: " . $account['email'];
echo "Страна: " . $account['country'];

// Получить информацию об аккаунте другого пользователя
$user = $client->users->getAccount('dbid:AAH4f99T0taONIb-OurWxbNQ6ywGRopQngc');

// Получить информацию о нескольких пользователях
$users = $client->users->getAccountBatch([
    'dbid:AAH4f99T0taONIb-OurWxbNQ6ywGRopQngc',
    'dbid:AAH1234567890abcdefghijklmnopqrst',
]);

// Получить информацию об использовании места
$space = $client->users->getSpaceUsage();
echo "Использовано: " . $space['used'] . " байт\n";
echo "Выделено: " . $space['allocation']['allocated'] . " байт\n";
$percentage = ($space['used'] / $space['allocation']['allocated']) * 100;
echo "Использование: " . number_format($percentage, 2) . "%\n";

// Проверить доступность функций
$features = $client->users->getFeaturesValues([
    'paper_as_files',
    'file_locking',
]);
```

### File Requests

```php
// Создать file request
$request = $client->fileRequests->create(
    'Загрузите ваши документы',
    '/File Requests/Documents',
    '2024-12-31T23:59:59Z',  // срок
    true,                     // открыт
    'Пожалуйста, загрузите все необходимые документы для заявки'
);
echo "URL file request: " . $request['url'];

// Получить file request
$request = $client->fileRequests->get('oaCAVmEyrqYnkZX9955Y');

// Список всех file requests
$requests = $client->fileRequests->list(1000);

// Обновить file request
$updated = $client->fileRequests->update('oaCAVmEyrqYnkZX9955Y', [
    'title' => 'Обновленный заголовок',
    'open' => false,
]);

// Удалить file requests
$client->fileRequests->delete(['oaCAVmEyrqYnkZX9955Y']);

// Удалить все закрытые file requests
$client->fileRequests->deleteAllClosed();

// Подсчитать file requests
$count = $client->fileRequests->count();
echo "Всего file requests: " . $count['file_request_count'];
```

### Paper документы

```php
// Создать Paper документ
$doc = $client->paper->docsCreate(
    '<h1>Заметки встречи</h1><p>Обсуждаемые вопросы...</p>',
    'html'
);
$docId = $doc['doc_id'];

// Скачать Paper документ
$content = $client->paper->docsDownload($docId, 'markdown');
file_put_contents('notes.md', $content['content']);

// Обновить Paper документ
$client->paper->docsUpdate(
    $docId,
    '<h1>Обновленные заметки</h1><p>Новое содержимое...</p>',
    'html',
    'append',
    1
);

// Получить метаданные Paper документа
$metadata = $client->paper->docsGetMetadata($docId);

// Список Paper документов
$docs = $client->paper->docsList('docs_accessed', 'modified', 'descending', 100);

// Открыть доступ к Paper документу
$client->paper->docsUsersAdd($docId, [
    [
        'member' => ['.tag' => 'email', 'email' => 'team@example.com'],
        'permission_level' => ['.tag' => 'edit'],
    ],
]);

// Список пользователей с доступом
$users = $client->paper->docsUsersList($docId, 100);

// Удалить пользователей
$client->paper->docsUsersRemove($docId, [
    ['.tag' => 'email', 'email' => 'team@example.com'],
]);

// Удалить Paper документ
$client->paper->docsPermanentlyDelete($docId);
```

### Пакетные операции

```php
// Копировать несколько файлов
$job = $client->files->copyBatch([
    ['from_path' => '/file1.txt', 'to_path' => '/backup/file1.txt'],
    ['from_path' => '/file2.txt', 'to_path' => '/backup/file2.txt'],
]);

// Проверить статус пакетной операции
$status = $client->files->copyBatchCheck($job['async_job_id']);

// Переместить несколько файлов
$job = $client->files->moveBatch([
    ['from_path' => '/old/file1.txt', 'to_path' => '/new/file1.txt'],
    ['from_path' => '/old/file2.txt', 'to_path' => '/new/file2.txt'],
]);

// Удалить несколько файлов
$job = $client->files->deleteBatch(['/file1.txt', '/file2.txt', '/file3.txt']);
```

### Сохранение файлов из URL

```php
// Сохранить файл из URL
$job = $client->files->saveUrl('/Downloads/image.jpg', 'https://example.com/image.jpg');

// Проверить статус сохранения из URL
$status = $client->files->saveUrlCheckJobStatus($job['async_job_id']);

if ($status['.tag'] === 'complete') {
    echo "Файл успешно сохранен!";
}
```

## OAuth 2.0 авторизация

### URL авторизации

```php
use Tigusigalpa\Dropbox\DropboxClient;

// Сгенерировать URL авторизации
$authUrl = DropboxClient::getAuthorizationUrl(
    'your_app_key',
    'https://your-app.com/callback',
    'random_state_string',  // защита от CSRF
    ['files.content.write', 'files.content.read']  // опциональные scopes
);

// Перенаправить пользователя на URL авторизации
header('Location: ' . $authUrl);
```

### Обмен кода на токен

```php
// В вашем callback route
$code = $_GET['code'];
$state = $_GET['state'];

// Проверить state параметр (защита от CSRF)
if ($state !== $_SESSION['oauth_state']) {
    die('Неверный state параметр');
}

// Обменять код на access token
$tokenData = DropboxClient::getAccessToken(
    $code,
    'your_app_key',
    'your_app_secret',
    'https://your-app.com/callback'
);

// Сохранить токены безопасно
$accessToken = $tokenData['access_token'];
$refreshToken = $tokenData['refresh_token'] ?? null;

// Создать клиент с новым токеном
$client = new DropboxClient($accessToken);
```

### Пример OAuth в Laravel

```php
// routes/web.php
Route::get('/dropbox/auth', [DropboxController::class, 'redirectToDropbox']);
Route::get('/dropbox/callback', [DropboxController::class, 'handleCallback']);

// app/Http/Controllers/DropboxController.php
use Tigusigalpa\Dropbox\DropboxClient;

class DropboxController extends Controller
{
    public function redirectToDropbox()
    {
        $state = Str::random(40);
        session(['dropbox_state' => $state]);
        
        $url = DropboxClient::getAuthorizationUrl(
            config('dropbox.app_key'),
            config('dropbox.redirect_uri'),
            $state
        );
        
        return redirect($url);
    }
    
    public function handleCallback(Request $request)
    {
        if ($request->state !== session('dropbox_state')) {
            abort(403, 'Неверный state');
        }
        
        $tokenData = DropboxClient::getAccessToken(
            $request->code,
            config('dropbox.app_key'),
            config('dropbox.app_secret'),
            config('dropbox.redirect_uri')
        );
        
        // Сохранить токены для пользователя
        auth()->user()->update([
            'dropbox_access_token' => encrypt($tokenData['access_token']),
            'dropbox_refresh_token' => encrypt($tokenData['refresh_token'] ?? null),
        ]);
        
        return redirect('/dashboard')->with('success', 'Dropbox подключен!');
    }
}
```

### Обновление токена

```php
// Обновить access token когда истек срок
$newTokenData = DropboxClient::refreshAccessToken(
    $refreshToken,
    'your_app_key',
    'your_app_secret'
);

$newAccessToken = $newTokenData['access_token'];

// Обновить токен клиента
$client->setAccessToken($newAccessToken);
```

## Обработка ошибок

```php
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

try {
    $result = $client->files->upload('/test.txt', 'content');
} catch (DropboxException $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo "Код статуса: " . $e->getCode() . "\n";
    
    // Получить детальную информацию об ошибке
    $response = $e->getResponse();
    if ($response) {
        echo "Описание ошибки: " . $e->getErrorSummary() . "\n";
        echo "Тег ошибки: " . $e->getErrorTag() . "\n";
        print_r($response);
    }
}
```

## Продвинутые примеры

### Настройка HTTP клиента

```php
use GuzzleHttp\Client as GuzzleClient;
use Tigusigalpa\Dropbox\DropboxClient;

// Создать кастомный Guzzle клиент
$guzzle = new GuzzleClient([
    'timeout' => 60,
    'verify' => true,
    'proxy' => 'http://proxy.example.com:8080',
]);

// Примечание: В настоящее время пакет создает свой собственный экземпляр Guzzle
// Для кастомной конфигурации может потребоваться расширение класса DropboxClient
```

### Работа с курсорами (пагинация)

```php
// Получить все файлы в большой папке
$cursor = null;
$allFiles = [];

do {
    if ($cursor === null) {
        $result = $client->files->listFolder('/LargeFolder');
    } else {
        $result = $client->files->listFolderContinue($cursor);
    }
    
    $allFiles = array_merge($allFiles, $result['entries']);
    $cursor = $result['cursor'];
} while ($result['has_more']);

echo "Всего файлов: " . count($allFiles);
```

### Мониторинг изменений в папке

```php
// Получить начальный курсор
$cursor = $client->files->listFolderGetLatestCursor('/MonitoredFolder', true);
$cursorValue = $cursor['cursor'];

// Позже, проверить изменения
$changes = $client->files->listFolderLongpoll($cursorValue, 30);

if ($changes['changes']) {
    // Получить фактические изменения
    $result = $client->files->listFolderContinue($cursorValue);
    
    foreach ($result['entries'] as $entry) {
        echo "Изменено: " . $entry['name'] . "\n";
    }
}
```

## Тестирование

Запустить тестовый набор:

```bash
composer test
```

Запустить тесты с покрытием:

```bash
composer test:coverage
```

## Справочник API

Для полной документации API посетите:

- [Документация Dropbox HTTP API](https://www.dropbox.com/developers/documentation/http/documentation)
- [Dropbox API Explorer](https://dropbox.github.io/dropbox-api-v2-explorer/)

## Типичные сценарии использования

### Система резервного копирования

```php
// Резервное копирование локальных файлов в Dropbox
$backupFolder = '/Backups/' . date('Y-m-d');
$client->files->createFolder($backupFolder);

$files = glob('/var/www/app/storage/backups/*.sql');
foreach ($files as $file) {
    $content = file_get_contents($file);
    $client->files->upload(
        $backupFolder . '/' . basename($file),
        $content
    );
}
```

### Синхронизация файлов

```php
// Синхронизировать локальную папку с Dropbox
$localPath = '/local/documents';
$remotePath = '/Documents';

$localFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($localPath)
);

foreach ($localFiles as $file) {
    if ($file->isFile()) {
        $relativePath = str_replace($localPath, '', $file->getPathname());
        $content = file_get_contents($file->getPathname());
        
        $client->files->upload(
            $remotePath . $relativePath,
            $content,
            'overwrite'
        );
    }
}
```

### Галерея изображений

```php
// Создать галерею изображений с миниатюрами
$photos = $client->files->listFolder('/Photos');

foreach ($photos['entries'] as $photo) {
    if ($photo['.tag'] === 'file') {
        // Получить миниатюру
        $thumb = $client->files->getThumbnail(
            $photo['path_display'],
            'jpeg',
            'w256h256'
        );
        
        // Сохранить миниатюру
        file_put_contents(
            'thumbs/' . $photo['name'],
            $thumb['content']
        );
        
        // Создать общую ссылку для полного изображения
        $link = $client->sharing->createSharedLinkWithSettings(
            $photo['path_display']
        );
        
        echo '<img src="thumbs/' . $photo['name'] . '" data-full="' . $link['url'] . '">';
    }
}
```

## Структура пакета

### Основные компоненты

```
dropbox-php/
├── config/
│   └── dropbox.php             # Конфигурация Laravel
├── examples/
│   ├── basic-usage.php         # Примеры standalone использования
│   ├── laravel-usage.php       # Примеры интеграции с Laravel
│   └── oauth-flow.php          # Реализация OAuth 2.0 flow
├── src/
│   ├── Endpoints/              # Реализация API эндпоинтов
│   │   ├── Check.php           # Проверка API
│   │   ├── FileRequests.php    # Операции с file requests
│   │   ├── Files.php           # Операции с файлами/папками (40+ методов)
│   │   ├── Paper.php           # Операции с Dropbox Paper
│   │   ├── Sharing.php         # Совместный доступ (30+ методов)
│   │   └── Users.php           # Операции с пользователями
│   ├── Exceptions/
│   │   └── DropboxException.php # Кастомное исключение
│   ├── Facades/
│   │   └── Dropbox.php         # Laravel facade
│   ├── DropboxClient.php       # Главный клиент
│   └── DropboxServiceProvider.php # Laravel service provider
└── tests/                      # PHPUnit тесты
```

### Паттерны использования

**Standalone PHP:**

```php
$client = new DropboxClient($accessToken);
$result = $client->files->upload('/path/file.txt', $content);
```

**Laravel Facade:**

```php
use Tigusigalpa\Dropbox\Facades\Dropbox;
$result = Dropbox::files()->upload('/path/file.txt', $content);
```

**Laravel Dependency Injection:**

```php
public function upload(DropboxClient $dropbox) {
    $result = $dropbox->files->upload('/path/file.txt', $content);
}
```

## Тестирование

Пакет включает:

- Unit тесты для основной функциональности
- Примеры интеграционных тестов
- GitHub Actions workflow для CI/CD
- PHPUnit конфигурацию

Запуск тестов:

```bash
composer test
```

Запуск тестов с покрытием:

```bash
composer test:coverage
```

### Настройка для тестирования

1. Форкните репозиторий
2. Клонируйте ваш форк:
   ```bash
   git clone https://github.com/YOUR_USERNAME/dropbox-php.git
   cd dropbox-php
   ```

3. Установите зависимости:
   ```bash
   composer install
   ```

4. Создайте `.env` файл с вашими учетными данными Dropbox для тестирования:
   ```env
   DROPBOX_ACCESS_TOKEN=your_test_token
   ```

## Contributing

Мы приветствуем ваш вклад в развитие проекта! Пожалуйста, следуйте этим рекомендациям:

### Стандарты кодирования

- Следуйте стандартам PSR-12
- Пишите понятные, описательные commit сообщения
- Добавляйте PHPDoc блоки для всех публичных методов
- Используйте type hints для параметров и возвращаемых значений
- Держите методы сфокусированными и однозадачными

### Процесс Pull Request

1. Создайте новую ветку для вашей функции:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Внесите изменения и закоммитьте:
   ```bash
   git commit -m "Add feature: description"
   ```

3. Отправьте в ваш форк:
   ```bash
   git push origin feature/your-feature-name
   ```

4. Создайте Pull Request на GitHub

5. Убедитесь, что все тесты проходят и код соответствует стандартам

### Добавление новых функций

При добавлении новых эндпоинтов Dropbox API:

1. Создайте или обновите соответствующий класс эндпоинта в `src/Endpoints/`
2. Добавьте подробные PHPDoc комментарии
3. Включите ссылки на официальную документацию Dropbox API
4. Добавьте примеры использования в README.md
5. Напишите тесты для новой функциональности

### Сообщение об ошибках

- Используйте GitHub issue tracker
- Укажите версию PHP, версию Laravel (если применимо)
- Предоставьте примеры кода, воспроизводящие проблему
- Включите сообщения об ошибках и stack traces

## Changelog

### Версия 1.0.0 - 2024-12-20

**Добавлено:**

- Первый релиз
- Полная поддержка Dropbox API v2
- Эндпоинт Files с полным набором операций с файлами/папками
- Эндпоинт Sharing для функций совместной работы
- Эндпоинт Users для управления аккаунтом
- Эндпоинт File Requests
- Эндпоинт Paper для документов Dropbox Paper
- Эндпоинт Check для проверки API
- Интеграция с Laravel 8-12 через service provider и facade
- Помощники для OAuth 2.0 flow
- Подробная документация и примеры
- PHPUnit тестовый набор
- GitHub Actions CI/CD workflow

**Функции:**

- Загрузка/скачивание файлов с поддержкой chunked upload
- Управление файлами и папками (копирование, перемещение, удаление, поиск)
- Общие ссылки и совместный доступ к папкам
- Поддержка пакетных операций
- Генерация миниатюр
- Превью и экспорт файлов
- Отслеживание использования места
- Обработка ошибок с подробными исключениями

## Безопасность

Если вы обнаружили проблемы безопасности, пожалуйста, напишите на sovletig@gmail.com вместо использования issue tracker.

## Авторы

- [Igor Sazonov](https://github.com/tigusigalpa)
- [Все участники](https://github.com/tigusigalpa/dropbox-php/contributors)

## Лицензия

MIT License (MIT). Подробности в файле [LICENSE](LICENSE).

## Ссылки

- [GitHub Repository](https://github.com/tigusigalpa/dropbox-php)
- [Packagist](https://packagist.org/packages/tigusigalpa/dropbox-php)
- [Dropbox API Documentation](https://www.dropbox.com/developers/documentation/http/documentation)
- [Dropbox Developer Portal](https://www.dropbox.com/developers)
- [Dropbox App Console](https://www.dropbox.com/developers/apps)
- [Dropbox API Explorer](https://dropbox.github.io/dropbox-api-v2-explorer/)

---

Создано с ❤️ [Igor Sazonov](https://github.com/tigusigalpa)
