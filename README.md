# ADMIX CRM — clean base (single layout)

Что есть:
- единый layout для всех внутренних страниц
- /login (если нет сессии — редиректит туда)
- /me (личный кабинет) — переключение темы dark/light/auto
- тема хранится в users.theme и применяется ко всей CRM

## Установка
1) Скопируй `.env.example` -> `.env` (или оставь свой существующий .env)
2) `composer install`
3) `php database/migrations/migrate.php`
4) DocumentRoot домена должен смотреть на `/public`

## Роуты
/login, /logout, /me
/, /orders, /clients, /schedule, /billing, /expenses, /messages, /materials, /admin


## Архитектура
См. файл `ARCHITECTURE.md`.
