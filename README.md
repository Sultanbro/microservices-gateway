# Microservices Gateway

API-шлюз на **PHP / Laravel** для взаимодействия с сервисами конструктора документов и котировок. Приложение принимает запросы, выбирает сервис по маршруту, передаёт параметры через HTTP и возвращает ответ клиенту.

## Мой вклад

Самостоятельно разработал gateway: маршруты интеграций, общий механизм отправки HTTP-запросов, обработку ответов и настройку доступа между сервисами.

## Основные возможности

- **Document Designer:** маршруты справочников, конструктора документов, редактора строк и колонок, кнопок, нумераторов, атрибутов и бизнес-процессов.
- **Mycent Quotation:** передача запроса `full-editor` в сервис котировок.
- **Общая логика интеграций:** повторное использование HTTP-клиента и обработки CRUD-запросов через traits.
- **Конфигурация сервисов:** отдельный базовый URL и заголовок авторизации для каждой интеграции.
- **Два контура доступа:** пользовательские маршруты `/api/*` с Bearer middleware и внутренние `/service/*` с проверкой сервисного секрета.

## Стек

| Часть | Технологии |
| --- | --- |
| Backend | PHP, Laravel 8.83.10 по `composer.lock` |
| HTTP-интеграции | Guzzle 7.4.2 |
| Данные | Eloquent ORM, миграции, репозитории пользователей |
| Инструменты | Composer, PHPUnit 9, Laravel Telescope |
| Развёртывание | Docker, Nginx, PHP-FPM, Supervisor |

Основная логика шлюза написана на PHP. JavaScript и Laravel Mix присутствуют в составе ресурсов приложения.

## Как проходит запрос

```text
Клиент → /api/handbook
       → BearerAuthenticate
       → DocumentDesignerController
       → RouteData + RequestService → HTTP → Document Designer
       ← ApiResponse ← ответ сервиса
```

Контроллер интеграции задаёт `base_uri` и `secret` из Laravel config. [RouteData](app/Traits/Request/RouteData.php) получает метод и путь из маршрута и убирает префикс `/api`. [RequestService](app/Traits/Request/RequestService.php) отправляет запрос через Guzzle с параметрами `form_params` и заголовком `Authorization`, содержащим секрет выбранного сервиса.

Для создания и обновления предусмотрено добавление полей `created_by` / `updated_by` из текущего пользователя, если эти поля не переданы в запросе. [ApiResponse](app/Traits/Request/ApiResponse.php) формирует ответ с JSON content type; успешный ответ по умолчанию получает статус `200`, ошибки обрабатываются отдельно. Это прикладной шлюз с заданными маршрутами, а не универсальный прозрачный HTTP-прокси.

## Маршруты интеграций

| Путь | Назначение | Сервис |
| --- | --- | --- |
| `/api/handbook` | Справочники | Document Designer |
| `/api/document-constructor` | Конструктор документов | Document Designer |
| `/api/row-column-editor` | Редактор строк и колонок | Document Designer |
| `/api/button-editor` | Редактор кнопок | Document Designer |
| `/api/numerator` | Нумераторы | Document Designer |
| `/api/attribute` | Атрибуты | Document Designer |
| `/api/document-row` | Строки документа | Document Designer |
| `/api/business_process` | Бизнес-процессы | Document Designer |
| `POST /api/full-editor` | Запрос редактора | Mycent Quotation |

Для ресурсных маршрутов общий trait реализует список, просмотр, создание, обновление и удаление. Дополнительные POST-маршруты `handbook/joint`, `handbook/dicti`, `handbook/dictiSaveInKias` и `numerator/get/on` объявлены в [routes/api.php](routes/api.php). Формат прикладных данных определяется принимающим сервисом.

Внутренний маршрут `POST /service/user/query` объявлен отдельно в [routes/service.php](routes/service.php). Его middleware сравнивает значение заголовка `Authorization` со списком `ALLOWED_SECRETS` через `hash_equals`; префикс `Bearer` для этого сравнения не добавляется. Пустой список не разрешает доступ.

## Где смотреть код

| Файл | Назначение |
| --- | --- |
| [DocumentDesignerController](app/Http/Controllers/Api/Services/DocumentDesigner/DocumentDesignerController.php) | Подключение сервиса документов |
| [MycentQuotationController](app/Http/Controllers/Api/Services/MycentQuotation/MycentQuotationController.php) | Подключение сервиса котировок |
| [RequestService](app/Traits/Request/RequestService.php) | HTTP-клиент и общие обработчики запросов |
| [RouteData](app/Traits/Request/RouteData.php) | Преобразование маршрута шлюза в путь сервиса |
| [AuthenticateAccess](app/Http/Middleware/AuthenticateAccess.php) | Проверка доступа внутренних сервисов |
| [BearerAuthenticate](app/Http/Middleware/BearerAuthenticate.php) | Пользовательская авторизация |
| [RouteServiceProvider](app/Providers/RouteServiceProvider.php) | Подключение групп `/api` и `/service` |
| [config/services.php](config/services.php) | URL, секреты сервисов и список разрешённых ключей |

## Настройка окружения

Для интеграционного запуска нужны PHP, Composer, база данных, сервисы Document Designer и Mycent Quotation, а также модуль пользовательской авторизации. В `composer.json` указан PHP `^7.3|^8.0`; существующий Dockerfile использует PHP 8.0. Требования конкретных зависимостей зафиксированы в `composer.lock`.

Начальная подготовка исходников и зависимостей:

```sh
git clone https://github.com/Sultanbro/microservices-gateway.git
cd microservices-gateway
cp .env.example .env
composer install --no-scripts
composer check-platform-reqs
```

`--no-scripts` позволяет установить зависимости без автоматического запуска Laravel. Для загрузки приложения сначала нужно восстановить отсутствующие зависимости: в `config/app.php` зарегистрированы, например, `EventServiceProvider` и `KeyCloakServiceProvider`, файлов которых нет в текущем репозитории. `BearerAuthenticate` также ссылается на отсутствующий `KeyCloakServiceInterface`. Изменения только `.env` для полноценного запуска недостаточно.

| Переменные `.env` | Назначение |
| --- | --- |
| `APP_URL` | Локальный адрес шлюза, например `http://127.0.0.1:8000` |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Подключение к отдельной локальной базе |
| `DOCUMENT_DESIGNER_SERVICE_BASE_URI` | Адрес сервиса документов |
| `DOCUMENT_DESIGNER_SERVICE_SECRET` | Значение исходящего заголовка `Authorization` для сервиса документов |
| `MYCENT_QUOTATION_SERVICE_BASE_URI` | Адрес сервиса котировок |
| `MYCENT_QUOTATION_SERVICE_SECRET` | Значение исходящего заголовка `Authorization` для сервиса котировок |
| `ALLOWED_SECRETS` | Разрешённые ключи входящих запросов `/service/*`, разделённые запятыми |

Адреса `*.example.invalid` в `.env.example` — примеры для замены на ваши локальные сервисы. `RouteData` формирует путь с начальным `/`, поэтому учитывайте это при настройке базового URL принимающего сервиса.

После восстановления модулей приложения и настройки окружения:

```sh
composer dump-autoload
php artisan key:generate
php artisan config:clear
php artisan migrate
php artisan route:list
php artisan serve --host=127.0.0.1 --port=8000
```

`DatabaseSeeder` не создаёт демонстрационные данные. Существующий Dockerfile использует частный реестр образов и внешнюю конфигурацию; для самостоятельной локальной среды его потребуется адаптировать. Сквозной запуск шлюза с принимающими сервисами пока не проверен.

## Проверки

В `tests/` находятся базовые примеры PHPUnit. После восстановления загрузки приложения можно выполнять `php artisan test`. Feature-пример ожидает `200` на `/`, хотя `routes/web.php` не задаёт такой маршрут, поэтому он требует обновления.

Следующий шаг для проверки интеграций — тесты преобразования путей, передачи параметров, проверки сервисных ключей и обработки ошибок принимающих сервисов.

## Зависимости и лицензии

Laravel framework распространяется по [лицензии MIT](https://opensource.org/licenses/MIT). Условия остальных зависимостей указаны в их пакетах и `composer.lock`.
