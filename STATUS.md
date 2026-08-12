# STATUS — фактический срез проекта

Собрано по реальному состоянию `git log`, `git status` и файлам проекта на 2026-08-12.

## 1. Этапы

| № | Этап | Статус | Что осталось |
|---|------|--------|--------------|
| 1 | Scaffold | Готово | — |
| 2 | БД + модели | Готово | — |
| 3 | Сидеры | Готово | — |
| 4 | Локализация + роутинг | Готово | — |
| 5 | Публичные страницы | Частично | 7 маршрутов не закрыты: `/about-us`, `/about-us/{slug}`, `/contact`, `/faq`, `/news`, `/services`, `/solutions` |
| 6 | Форма заявки | Готово | POST-логика и backend уже есть; GET `/contact` пока не закончен |
| 7 | Админ-авторизация | Готово | — |
| 8 | Админ-панель | Готово | HEAD = `eaa0733` |
| 9 | SEO | Частично | Сервис, sitemap, robots, тест SEO уже есть в рабочем дереве, но не закоммичены |
| 10 | Финальная сборка / деплой | Частично | Нужно закрыть этап 5, координированный дефект-коммит, layout, public/build и PHP 8.3 |

## 2. Инвентаризация страниц

### Публичные (18)

- Готово и частично сверено: `/`, `/products`, `/products/pumps`, `/products/valves`, `/products/control`, `/products/water`, `/products/{slug}`, `/projects`, `/project/{slug}`, `/applications`, `/applications/water-wastewater`.
- Не закрыты: `/about-us`, `/about-us/{slug}`, `/contact`, `/faq`, `/news`, `/services`, `/solutions`.
- Итого: 11/18 закрыто, 7/18 остаются.

### Админские (5)

- `/admin/login` — реализация есть
- `/admin` — реализация есть
- `/admin/products` — реализация есть
- `/admin/requests` — реализация есть
- `/admin/requests/{id}` — реализация есть

### API (2)

- `GET /api/admin/attachments/{id}` — реализация есть
- `POST /api/admin/products/image` — реализация есть

## 3. Тесты — фактический вывод

Запуск в WSL:

- `php artisan test` → 76 passed, 0 failed, 1612 assertions, duration 242.32s
- `DB_CONNECTION=mysql ... php artisan test` → 76 passed, 0 failed, 1612 assertions, duration 189.81s
- `DB_CONNECTION=mysql ... php artisan test --filter=SeoMetadataTest` → 4 passed, 0 failed, 1394 assertions, duration 44.84s

Тесты падают/не падают по факту: зелёные, и на SQLite, и на MySQL.

## 4. Что не закоммичено

`git status --short --branch` на момент проверки показывает, что последний коммит — `eaa0733` (`Stage 8`), а рабочее дерево уже содержит незакоммиченные изменения по SEO и этапу 5.

Реально видны изменения в:

- `routes/web.php`
- `app/Services/SeoMetadataService.php`
- `app/Http/Controllers/SitemapController.php`
- `app/Http/Controllers/RobotsController.php`
- `tests/Feature/SeoMetadataTest.php`
- `public/build/*`
- части `resources/views/...` и контроллеров страниц

Это главный риск при трёх сессиях: код уже есть в рабочем дереве, но не закреплён в git-истории как один коммит.

## 5. Незакрытые хвосты и блокеры

- закрыть этап 5 до приёмки
- координированный коммит локализации + 24 переводов после приёмки этапа 5
- врезка hreflang в layout после стабилизации layout
- финальная пересборка `public/build/`
- подтверждение PHP 8.3 на тарифе PS.kz; по repo это не подтверждено
- сверка общих файлов между сессиями (`routes/web.php`, `config/*`, `composer.json`, `DEPLOY.md`, `STATUS.md`)

## 6. Оценка готовности

По факту:

- кодовая база по основным этапам уже очень близка к готовности,
- но к выкладке ещё нельзя: осталось закрыть 7 публичных маршрутов и ряд процедурных блокеров,
- общий уровень готовности — около 70–80% по коду и 50–60% по готовности к продакшен-выкату.

## 7. Итог

Проверка показывает: этапы 1–8 готовы и зафиксированы в Git; SEO уже работает в рабочем дереве и проходит тесты, но пока не доведён до единого коммита/финального слоя; этап 5 и финальная сборка всё ещё остаются основными блокерами.
