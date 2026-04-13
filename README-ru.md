# Dropbox PHP SDK

![Dropbox PHP SDK](https://github.com/user-attachments/assets/361952c5-03d0-4ef6-b0b8-3106bb4ca3be)

[![Latest Version](https://img.shields.io/packagist/v/tigusigalpa/dropbox-php.svg)](https://packagist.org/packages/tigusigalpa/dropbox-php)
[![License](https://img.shields.io/packagist/l/tigusigalpa/dropbox-php.svg)](https://github.com/tigusigalpa/dropbox-php/blob/main/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/tigusigalpa/dropbox-php.svg)](https://packagist.org/packages/tigusigalpa/dropbox-php)

PHP SDK для [Dropbox API v2](https://www.dropbox.com/developers/documentation/http/documentation) с поддержкой Laravel
8-13.
Типобезопасный интерфейс для работы с хранилищем Dropbox, шарингом файлов и совместной работой из PHP 8.1+ приложений.
Можно использовать автономно или как Laravel-пакет с автоматической регистрацией service provider и facade.

> 📖 **[Полная документация доступна на Wiki](https://github.com/tigusigalpa/dropbox-php/wiki)**

## Что внутри

Пакет покрывает все основные эндпоинты Dropbox API v2: файлы, шаринг, пользователи, file requests и Paper. Есть
поддержка OAuth 2.0, загрузка больших файлов по частям и пакетные операции. Обработка ошибок построена на отдельном
классе исключений с доступом к деталям ошибок Dropbox.

В Laravel — service provider, facade и публикация конфига из коробки. Без Laravel — просто используйте `DropboxClient`
напрямую.

**🌐 Язык:** Русский | [English](README.md)

## Содержание

- [Особенности](#особенности)
- [Поддерживаемые эндпоинты](#поддерживаемые-эндпоинты)
- [Что внутри](#что-внутри)
- [Требования](#требования)
- [Установка](#установка)
- [Быстрый старт](#быстрый-старт)
- [Подробные примеры использования](#подробные-примеры-использования)
    - [Работа с файлами](#работа-с-файлами)
    - [Совместный доступ](#совместный-доступ)
    - [Хостинг изображений с прямыми ссылками](#хостинг-изображений-с-прямыми-ссылками)
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

- ✅ **Dropbox API v2** — покрыты все основные эндпоинты
- 🚀 **Laravel 8–13** — service provider, facade, публикация конфига
- 🎯 **PHP 8.1+** — типизированные свойства, enumы, именованные аргументы
- 📦 **Автономно** — работает без фреймворка
- 🔐 **OAuth 2.0** — URL авторизации, обмен токенов, обновление
- 📝 **Документация** — примеры использования и справочник API
- 🧪 **Тесты** — PHPUnit тесты в комплекте
- 🎨 **Простой API** — вызовы в стиле `$client->files->upload(...)`
- ⚡ **Chunked Upload** — большие файлы загружаются по частям с настраиваемым размером
- 🔄 **Batch Operations** — копирование, перемещение, удаление до 1000 файлов за вызов

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

#### Хостинг изображений с прямыми ссылками

Преобразование обычных Dropbox ссылок в прямые ссылки для встраивания изображений в HTML, Markdown или другой контент.
Идеально подходит для хостинга изображений для блогов, портфолио или документации.

```php
// Преобразовать существующую общую ссылку в прямую ссылку
$sharedLink = 'https://www.dropbox.com/s/abcd1234/image.jpg?dl=0';

// Метод 1: Использование userusercontent (рекомендуется - более чистые URL)
$directLink = $client->sharing->convertToDirectLink($sharedLink);
// Вернет: https://dl.dropboxusercontent.com/s/abcd1234/image.jpg

// Метод 2: Использование параметра raw
$directLink = $client->sharing->convertToDirectLink($sharedLink, 'raw');
// Вернет: https://www.dropbox.com/s/abcd1234/image.jpg?raw=1

// Создать новую общую ссылку и сразу преобразовать её
$result = $client->sharing->createDirectLink('/Photos/vacation.jpg');
echo "Прямая ссылка: " . $result['direct_url'];

// Использование в HTML
echo '<img src="' . $result['direct_url'] . '" alt="Отпуск">';

// Использование в Markdown
echo '![Отпуск](' . $result['direct_url'] . ')';

// Создать защищенную паролем прямую ссылку с истечением срока
$result = $client->sharing->createDirectLink(
    '/Photos/private.jpg',
    [
        'link_password' => 'mypassword123',
        'expires' => '2024-12-31T23:59:59Z',
    ]
);

// Пакетное преобразование нескольких ссылок
$links = [
    'https://www.dropbox.com/s/abc123/img1.jpg?dl=0',
    'https://www.dropbox.com/s/def456/img2.png?dl=0',
];

foreach ($links as $link) {
    $directLink = $client->sharing->convertToDirectLink($link);
    echo $directLink . "\n";
}

// Создать галерею изображений
$photos = $client->files->listFolder('/Photos');
$gallery = [];

foreach ($photos['entries'] as $photo) {
    if ($photo['.tag'] === 'file' && preg_match('/\.(jpg|jpeg|png|gif)$/i', $photo['name'])) {
        $linkData = $client->sharing->createDirectLink($photo['path_display']);
        $gallery[] = [
            'name' => $photo['name'],
            'url' => $linkData['direct_url'],
        ];
    }
}

// Отобразить галерею
foreach ($gallery as $image) {
    echo '<img src="' . $image['url'] . '" alt="' . $image['name'] . '">' . "\n";
}
```

**Пример для Laravel:**

```php
use Tigusigalpa\Dropbox\Facades\Dropbox;

// Преобразовать ссылку
$directLink = Dropbox::sharing()->convertToDirectLink($sharedLink);

// Загрузить и получить прямую ссылку
$file = $request->file('image');
$content = file_get_contents($file->getRealPath());
Dropbox::files()->upload('/Images/' . $file->getClientOriginalName(), $content);

$result = Dropbox::sharing()->createDirectLink('/Images/' . $file->getClientOriginalName());
return response()->json(['url' => $result['direct_url']]);
```

**Преимущества:**

- ✅ Не нужны внешние сервисы для хостинга изображений
- ✅ Прямое встраивание в HTML, Markdown, форумы и т.д.
- ✅ Надежная доставка через CDN инфраструктуру Dropbox
- ✅ Поддержка защиты паролем и сроков истечения
- ✅ Два метода преобразования для разных случаев использования

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

## Производительность и оптимизация

### Эффективные операции с файлами

- **Chunked-загрузка** — большие файлы разбиваются на части (по умолчанию 4МБ, настраивается), чтобы не упереться в
  память и таймауты
- **Пакетные эндпоинты** — копирование, перемещение, удаление до 1000 файлов за один API-вызов
- **Persistent-соединения** — Guzzle держит соединения открытыми между запросами
- **Потоковое скачивание** — файлы не загружаются целиком в память

### Стратегии кэширования

```php
// Кэшировать листинг папок для уменьшения API-вызовов
$cacheKey = 'dropbox_folder_' . md5($path);
$contents = Cache::remember($cacheKey, 3600, function() use ($client, $path) {
    return $client->files->listFolder($path);
});

// Кэшировать общие ссылки
$linkCache = Cache::remember('dropbox_link_' . $fileId, 86400, function() use ($client, $path) {
    return $client->sharing->createSharedLinkWithSettings($path);
});
```

### Rate limiting

Dropbox API имеет лимиты на частоту запросов. При превышении SDK выбрасывает исключение с кодом 429:

```php
try {
    $result = $client->files->upload($path, $content);
} catch (DropboxException $e) {
    if ($e->getCode() === 429) {
        // Ограничение скорости - ждем и повторяем
        $retryAfter = $e->getResponse()['retry_after'] ?? 60;
        sleep($retryAfter);
        $result = $client->files->upload($path, $content);
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

В комплекте:

- Unit-тесты основной функциональности
- Примеры интеграционных тестов
- GitHub Actions CI/CD workflow
- PHPUnit конфигурация

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

Принимаем контрибьюции. Пожалуйста, следуйте этим рекомендациям:

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

## Руководство по устранению неполадок

### Распространенные проблемы и решения

#### Ошибки аутентификации

**Проблема:** Ошибка "Invalid access token"

**Решения:**

- Проверьте, что ваш access token корректен и не истек
- Для OAuth токенов реализуйте логику обновления токена
- Убедитесь, что ваше приложение имеет необходимые разрешения/scopes
- Проверьте, что токен не был отозван в Dropbox App Console

```php
// Проверить валидность токена
try {
    $account = $client->users->getCurrentAccount();
    echo "Токен валиден для: " . $account['email'];
} catch (DropboxException $e) {
    if ($e->getCode() === 401) {
        // Токен невалиден - нужно обновить или переаутентифицироваться
        $newToken = DropboxClient::refreshAccessToken($refreshToken, $appKey, $appSecret);
    }
}
```

#### Ошибки загрузки файлов

**Проблема:** Загрузка не удается для больших файлов или происходит таймаут

**Решения:**

- Используйте chunked-загрузку для файлов больше 150МБ
- Увеличьте PHP `max_execution_time` и `memory_limit`
- Реализуйте логику повторных попыток для сетевых сбоев
- Проверьте формат пути файла (должен начинаться с /)

```php
// Надежная загрузка с повторными попытками
$maxRetries = 3;
$attempt = 0;

while ($attempt < $maxRetries) {
    try {
        $result = $client->files->upload($path, $content);
        break;
    } catch (DropboxException $e) {
        $attempt++;
        if ($attempt >= $maxRetries) {throw $e;}
        sleep(2 ** $attempt); // Экспоненциальная задержка
    }
}
```

#### Ошибки путей

**Проблема:** Ошибки "Path not found" или "Malformed path"

**Решения:**

- Убедитесь, что пути начинаются с `/` (например, `/Documents/file.txt`)
- Используйте правильное кодирование для специальных символов
- Проверьте, что родительские папки существуют перед созданием файлов
- Проверьте чувствительность к регистру (пути Dropbox нечувствительны к регистру, но сохраняют его)

```php
// Убедиться, что родительская папка существует
$filePath = '/Documents/Reports/2024/report.pdf';
$parentPath = dirname($filePath);

try {
    $client->files->getMetadata($parentPath);
} catch (DropboxException $e) {
    // Родительская папка не существует - создаем её
    $client->files->createFolder($parentPath);
}

$client->files->upload($filePath, $content);
```

#### Проблемы интеграции с Laravel

**Проблема:** Service provider не загружается или facade не работает

**Решения:**

- Очистить кэш Laravel: `php artisan cache:clear`
- Очистить кэш конфигурации: `php artisan config:clear`
- Переопубликовать конфигурацию: `php artisan vendor:publish --tag=dropbox-config --force`
- Проверить, что переменные `.env` установлены корректно
- Убедиться, что пакет находится в секции require файла `composer.json`

```bash
# Полный сброс Laravel
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

### Режим отладки

Включить детальное логирование ошибок:

```php
try {
    $result = $client->files->upload($path, $content);
} catch (DropboxException $e) {
    // Логировать детальную информацию об ошибке
    Log::error('Ошибка Dropbox API', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'error_summary' => $e->getErrorSummary(),
        'error_tag' => $e->getErrorTag(),
        'response' => $e->getResponse(),
        'trace' => $e->getTraceAsString()
    ]);
}
```

## Часто задаваемые вопросы (FAQ)

### Общие вопросы

**В: Готова ли эта библиотека к продакшену?**

О: Да. Есть обработка ошибок, тесты, соответствие PSR-12. Используется в продакшене несколькими проектами.

**В: В чем разница между этим SDK и официальным Dropbox SDK?**

О: Этот пакет рассчитан на PHP 8.1+, идёт с поддержкой Laravel (service provider, facade, конфиг) и даёт более простой
API на основе методов. Официальный SDK более низкоуровневый.

**В: Могу ли я использовать это без Laravel?**

О: Да, работает как автономная PHP-библиотека. Поддержка Laravel опциональна.

**В: Поддерживает ли SDK Dropbox Business/Team аккаунты?**

О: Да, поддерживаются и личные, и бизнес-аккаунты. Для командных операций используйте team-scoped токены.

### Технические вопросы

**В: Какой максимальный размер файла я могу загрузить?**

О: До 350ГБ через chunked-загрузку. Файлы до 150МБ можно загружать простым `upload()`. Для больших — сессии загрузки.

**В: Как обрабатывать ограничение скорости?**

О: SDK выбрасывает `DropboxException` с кодом 429. Реализуйте exponential backoff, как показано в разделе устранения
неполадок.

**В: Могу ли я загружать файлы из URL напрямую в Dropbox?**

О: Да, `saveUrl()` говорит Dropbox скачать файл по URL на своей стороне.

**В: Как получить постоянную ссылку на файл?**

О: `createSharedLinkWithSettings()` даёт постоянную ссылку. Для короткоживущих (4 часа) — `getTemporaryLink()`.

**В: Поддерживает ли SDK Dropbox Paper?**

О: Да, эндпоинт Paper покрывает создание, редактирование, шаринг и удаление Paper-документов.

**В: Как обрабатывать конфликты файлов?**

О: Передайте параметр `mode`: `'add'` (ошибка, если существует), `'overwrite'` (заменить) или `'update'` (обновить
конкретную ревизию).

### Вопросы безопасности

**В: Как следует хранить токены доступа?**

О: Не храните токены в открытом виде. В Laravel — `encrypt()`, иначе — переменные окружения или хранилище секретов. В
продакшене используйте OAuth с refresh-токенами.

**В: Безопасно ли использовать в мультитенантных приложениях?**

О: Да. Создавайте отдельный `DropboxClient` на каждого пользователя с его токеном. Не шарьте токены между
пользователями.

**В: Как отозвать доступ?**

О: Через Dropbox App Console или сделайте отзыв токенов в настройках вашего приложения.

## Лучшие практики

### Практики безопасности

1. **Никогда не хардкодьте токены доступа** - Используйте переменные окружения или безопасное управление конфигурацией
2. **Реализуйте OAuth 2.0 flow** - Для продакшен-приложений используйте правильный OAuth вместо сгенерированных токенов
3. **Используйте refresh токены** - Реализуйте логику обновления токенов для поддержания долгосрочного доступа
4. **Валидируйте пользовательский ввод** - Санитизируйте пути и имена файлов перед передачей в API
5. **Реализуйте ограничение скорости** - Добавьте ограничение скорости на уровне приложения для предотвращения
   злоупотребления API
6. **Логируйте события безопасности** - Отслеживайте неудачные попытки аутентификации и подозрительную активность
7. **Используйте только HTTPS** - Убедитесь, что все callbacks и webhooks используют HTTPS
8. **Правильно ограничивайте разрешения** - Запрашивайте только те OAuth scopes, которые нужны вашему приложению

### Практики разработки

1. **Используйте type hints** - Используйте систему типов PHP 8.1+ для лучшего качества кода
2. **Правильно обрабатывайте исключения** - Всегда оборачивайте API-вызовы в блоки try-catch
3. **Реализуйте логику повторных попыток** - Обрабатывайте временные сбои с экспоненциальной задержкой
4. **Кэшируйте ответы API** - Сокращайте API-вызовы, кэшируя листинги папок и метаданные
5. **Используйте пакетные операции** - Эффективно обрабатывайте несколько файлов с помощью пакетных эндпоинтов
6. **Тестируйте с реальными данными** - Создайте тестовый аккаунт Dropbox для разработки
7. **Мониторьте использование API** - Отслеживайте объемы API-вызовов, чтобы оставаться в пределах лимитов
8. **Версионируйте ваш код** - Используйте семантическое версионирование и ведите changelog

### Практики производительности

1. **Используйте chunked-загрузку** - Для файлов более 150МБ всегда используйте сессии загрузки
2. **Реализуйте пагинацию** - Обрабатывайте большие листинги папок с пагинацией на основе курсоров
3. **Стримьте большие загрузки** - Не загружайте целые файлы в память
4. **Оптимизируйте поисковые запросы** - Используйте конкретные пути и фильтры для уменьшения наборов результатов
5. **Используйте курсоры** - Используйте `listFolderContinue()` для эффективной пагинации
6. **Пакетные запросы миниатюр** - Получайте несколько миниатюр за один API-вызов
7. **Используйте временные ссылки** - Для публичного доступа к файлам временные ссылки быстрее, чем загрузки
8. **Реализуйте queue workers** - Обрабатывайте большие файловые операции асинхронно в Laravel

### Практики для Laravel

```php
// Используйте очереди для больших операций
class UploadToDropboxJob implements ShouldQueue
{
    public function handle(DropboxClient $dropbox)
    {
        $dropbox->files->upload($this->path, $this->content);
    }
}

// Используйте события для файловых операций
event(new FileUploadedToDropbox($filePath, $metadata));

// Реализуйте middleware для Dropbox webhooks
Route::post('/dropbox/webhook', [DropboxWebhookController::class, 'handle'])
    ->middleware('verify.dropbox.signature');
```

## Сравнение с официальным Dropbox SDK

| Функция            | Этот SDK                      | Официальный Dropbox SDK |
|--------------------|-------------------------------|-------------------------|
| Версия PHP         | 8.1+                          | 7.4+                    |
| Интеграция Laravel | Встроенная (provider, facade) | Ручная                  |
| Покрытие API v2    | Все основные эндпоинты        | Все эндпоинты           |
| Документация       | Примеры + справочник          | Справочник API          |
| Типобезопасность   | Полные type hints             | Частичная               |
| Обработка ошибок   | Отдельный класс исключений    | Базовые исключения      |
| Chunked-загрузка   | Встроенные хелперы            | Ручная реализация       |
| Пакетные операции  | Поддерживаются                | Поддерживаются          |
| OAuth 2.0 хелперы  | Включены                      | Ручная                  |

### Миграция с других библиотек

Имена методов соответствуют документации Dropbox HTTP API:

```php
// Старая библиотека (пример)
$dropbox->uploadFile('/path', $content);

// Этот SDK
$client->files->upload('/path', $content);

// Старая библиотека
$dropbox->getMetadata('/path');

// Этот SDK
$client->files->getMetadata('/path');
```

Имена методов повторяют названия из Dropbox API,
поэтому [официальная HTTP-документация](https://www.dropbox.com/developers/documentation/http/documentation) служит
справочником.

## Реальные сценарии использования

### E-Commerce платформа

Хранение изображений товаров, счетов и документов клиентов:

```php
// Загрузка изображений товаров с организованной структурой
$productId = 12345;
$imagePath = "/products/{$productId}/images/main.jpg";
$client->files->upload($imagePath, $imageContent);

// Генерация общей ссылки для изображения товара
$link = $client->sharing->createSharedLinkWithSettings($imagePath);
$product->image_url = $link['url'];
```

### Система управления документами

Управление корпоративными документами с контролем версий:

```php
// Загрузка документа с метаданными
$result = $client->files->upload(
    "/documents/contracts/{$contractId}.pdf",
    $pdfContent,
    'add'
);

// Отслеживание ревизий
$revisions = $client->files->listRevisions($result['path_display']);

// Совместный доступ с конкретными пользователями
$client->sharing->addFileMember($result['path_display'], [
    ['member' => ['.tag' => 'email', 'email' => 'legal@company.com']]
]);
```

### Автоматизированная система резервного копирования

Создание запланированных резервных копий критических данных:

```php
// Запланированная задача Laravel
protected function schedule(Schedule $schedule)
{
    $schedule->call(function (DropboxClient $dropbox) {
        $backupPath = '/backups/' . date('Y-m-d-H-i-s');
        $dropbox->files->createFolder($backupPath);
        
        // Резервное копирование базы данных
        $dbBackup = Storage::get('backups/database.sql');
        $dropbox->files->upload("{$backupPath}/database.sql", $dbBackup);
        
        // Резервное копирование файлов
        $filesBackup = Storage::get('backups/files.tar.gz');
        $dropbox->files->upload("{$backupPath}/files.tar.gz", $filesBackup);
    })->daily();
}
```

### Приложение медиа-галереи

Создание фотогалереи с миниатюрами:

```php
// Загрузка фотографий и генерация миниатюр
foreach ($photos as $photo) {
    $path = "/gallery/{$albumId}/{$photo->name}";
    $client->files->upload($path, $photo->content);
    
    // Получение миниатюры
    $thumb = $client->files->getThumbnail($path, 'jpeg', 'w256h256');
    Storage::put("thumbnails/{$photo->id}.jpg", $thumb['content']);
    
    // Создание публичной ссылки
    $link = $client->sharing->createSharedLinkWithSettings($path);
    $photo->public_url = $link['url'];
}
```

### Совместное рабочее пространство

Включение командной совместной работы с общими папками:

```php
// Создание рабочего пространства проекта
$projectFolder = "/projects/{$projectName}";
$client->files->createFolder($projectFolder);

// Совместный доступ с командой
$shared = $client->sharing->shareFolder($projectFolder);

// Добавление членов команды
foreach ($teamMembers as $member) {
    $client->sharing->addFolderMember($shared['shared_folder_id'], [[
        'member' => ['.tag' => 'email', 'email' => $member->email],
        'access_level' => ['.tag' => $member->role === 'admin' ? 'editor' : 'viewer']
    ]]);
}
```

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
