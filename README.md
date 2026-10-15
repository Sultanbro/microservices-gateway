# Microservices Gateway

[![Gateway checks](https://github.com/Sultanbro/microservices-gateway/actions/workflows/tests.yml/badge.svg)](https://github.com/Sultanbro/microservices-gateway/actions/workflows/tests.yml)

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

Контроллер интеграции задаёт `base_uri` и `secret` из Laravel config. [RouteData](app/Traits/Request/RouteData.php) получает метод и путь из маршрута и убирает префикс `/api`. [RequestService](app/Traits/Request/RequestService.php) отправляет запрос через Laravel HTTP Client: GET-параметры передаются в query string, данные записи — как form params. Заголовок `Authorization` содержит секрет выбранного сервиса.

При создании и изменении поля автора задаются сервером: переданное клиентом значение не заменяет текущего пользователя. HTTP-метод сохраняется, включая PATCH; успешный статус принимающего сервиса передаётся клиенту, в том числе `201` и `204`. При сетевой ошибке возвращается `502`, при отсутствии настройки — `503`. Детали внутренних адресов и исключений в ответ не включаются. Перенаправления не выполняются.

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

## Проверки одной командой

```sh
git clone https://github.com/Sultanbro/microservices-gateway.git
cd microservices-gateway
docker compose -f compose.test.yml run --build --rm tests
```

Нужен Docker с Compose v2. Контейнер устанавливает зависимости из `composer.lock` и запускает **14 тестов**. PHP 8.1 используется для воспроизведения этого lock-файла; обновление устаревшего стека перед производственным развёртыванием остаётся отдельной задачей.

[GatewayTest](tests/Feature/GatewayTest.php) проверяет реальные маршруты Laravel и Bearer middleware: параметры GET, создание, PATCH, удаление, выбор сервиса, передачу статусов, запрет подмены автора, ошибки сети и отклонение токенов. [ServiceAccessTest](tests/Feature/ServiceAccessTest.php) проверяет сервисные ключи и запрет доступа при пустой конфигурации.

HTTP-ответы внешних сервисов подменены через `Http::fake`, для локального пользователя и токена используется временная SQLite-база в памяти. Тесты не обращаются к рабочим сервисам и не запускают миграции прикладной базы. Это проверка шлюза в изоляции; сквозная работа с реальными Document Designer и Mycent Quotation требует их окружения. Тот же набор выполняет GitHub Actions.

## Настройка интеграционного окружения

```sh
cp .env.example .env
composer install
php artisan key:generate
php artisan config:clear
php artisan route:list
```

| Переменная | Назначение |
| --- | --- |
| `DB_*` | Отдельная локальная база пользователей и токенов |
| `KEYCLOAK_USERINFO_URL` | Полный URL userinfo доверенного realm Keycloak |
| `DOCUMENT_DESIGNER_SERVICE_BASE_URI`, `DOCUMENT_DESIGNER_SERVICE_SECRET` | Базовый URL и сервисный ключ Document Designer |
| `MYCENT_QUOTATION_SERVICE_BASE_URI`, `MYCENT_QUOTATION_SERVICE_SECRET` | Базовый URL и сервисный ключ Mycent Quotation |
| `ALLOWED_SECRETS` | Ключи для `/service/*`, разделённые запятыми |

Bearer-токен должен существовать в локальной таблице `user_tokens`, быть связан с пользователем и успешно пройти userinfo. Ошибка, истечение токена или недоступность Keycloak не разрешают запрос. Получение токенов и создание пользователей выполняются внешним сервисом авторизации; неработавшие маршруты login/register/reset, для которых в репозитории не было реализации, удалены.

Убраны регистрации отсутствовавших service providers; модуль проверки userinfo реализован в репозитории. Внутренний `/service/user/query` зависит от прикладных данных пользователей и требует отдельной проверки в соответствующем окружении.

Адреса `*.example.invalid` — примеры. Перед запуском настройте сервисы и базу; основной Dockerfile и `.deploy/` относятся к прежней внешней инфраструктуре. Локальный [Dockerfile для проверок](docker/local/Dockerfile) не использует частный реестр.

## Зависимости и лицензии

Laravel framework распространяется по [лицензии MIT](https://opensource.org/licenses/MIT). Условия остальных зависимостей указаны в их пакетах и `composer.lock`.
