<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## SpringCare Storefront

The public website uses Laravel Blade, CSS, and vanilla JavaScript. No frontend
build is required for these pages. With the existing Docker services running,
open `http://localhost:8001/` for Home or `http://localhost:8001/shop` for Shop.

- Home displays the four newest published, in-stock products and active categories.
- Shop supports search, category, brand, price, condition, storage, sorting, and pagination.
- Product pages use `/shop/{product-slug}`. Select an individual unit to view its
	photos, condition, battery health, accessories, and selling price.
- Products require an active brand and category to appear publicly. Only
	available units can be added to the session-based shopping bag. A bag does
	not reserve inventory; checkout and payments are outside this phase.
- Prices display in naira with thousands separators (for example, `₦50,000`).
	The admin selling-price field and shop price filters accept comma-grouped
	amounts and normalize them before use.
- Publish products and add available units with photos in Backpack. Empty
	inventories and missing images display honest placeholder states.
- Price amounts remain numeric in storage; comma grouping is display formatting,
  not currency conversion.
Focused regression checks (run inside `PERSISTENCE/SpringCare_Technology`):

```sh
php artisan test --filter='PriceFormattingTest|ExampleTest'
```

From the repository root with Docker:

```sh
docker compose exec -T app php artisan test --filter='PriceFormattingTest|ExampleTest'
```

### Academy Advert Controls

In Backpack, open **Academy Advert** (`/admin/academy-advertisement`). Toggle
**Enable advert** and save to show or hide the homepage advert. Optional
**Show from** and **Hide at** dates schedule its visibility in `Africa/Lagos`
time (WAT). The start is inclusive and the end is exclusive. Leaving both
dates blank enables immediate manual control; disabling overrides any schedule.

The advert remains visible by default until settings are saved. Hidden adverts
are omitted from the page and the featured devices regain the full section
width. Schedule checks happen on each homepage request; no cron job is needed.
Settings are stored on Laravel's private `local` storage disk at
`storefront/academy-advertisement.json`; preserve this disk across deployments
and share it between app instances if running more than one.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
