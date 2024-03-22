# MPC Rebold

### CS Focused Education Software for Monterey Peninsula College

<!--suppress HtmlDeprecatedAttribute -->
<div align="center">
<p>Built WIth:</p>
<a href="https://www.php.net/"><img src=https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white alt="PHP"></a>
<a href="https://laravel.com/"><img src=https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white alt="Laravel"></a>
<a href="https://livewire.laravel.com/"><img src=https://img.shields.io/badge/Livewire-edf2f7?style=for-the-badge&logo=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEUAAABFCAMAAAArU9sbAAAAY1BMVEXt8vf/////7/bv9fnu7fX23uzi5/H4zOHM0OX0vdn4rc61t9j6mMHpmMr7hramq9L7ea/8cKn7bqjmc7DzY6iMj8DPZqHnU6jiSqVtdLaVXqBbYKpNVaZHTJZDQn0kKIgCBXX2ZCZ8AAAC90lEQVR42u2Y6Y6rIBiGQTZBahHHOm217f1f5WGHk5GuvyaZN62BiA/vt0iagj/9biGEAMBGdvQuAhMmhHQSjOA3QAgQLvtBew16UJIT8CqHCIsYnHS4KvESB2HeB0QprQ3neQyRfv8NTs+ehfC+ZPxvytjBz0GULgjKKGXGSWD0CkQPvWCkaQiTquBogV+B9LyBQUQo7RjafsSj6rAMkQQWYkWyNL8fE8lrpTHSdi1Kdnrnxl6+xvsFFzmcBtLz9XZd24gpbM47fM+KGoIUgXRd95PhJAx3abGor/lOTFim3QSE05WamK63c6Q0MpsZ62aYKuKBq30cGzM0xTTEzMx3zIjSCr7ax7sypCbl/ut7xI8LxCBE683l5XajMErEltHzzLbNID5EKdtu081rgUnMQRzle1cJSSYrvaXQq4Ncu0whjmEx1ZBwn9sWWrWrhexhlusE7RMzE3S3WXSgQNxNewr/p/jMaF2pEmJDlO7htvJGX7XE8ILSbCBidn1IhrJZIlvG3P+b4mmJrlBAQRn4NsVWMXzmWKS6Fy1raUmqUEBJUWyLIrKVR17q+WUq3XYRgVqNdDrq5Q8M6aMVI1tpUO2XzCmP3fLg1eGl/t5Ve7cMSgmS/DRMunvh4igc1d8j7UwHTi85Y4wL2WdGbLqZoUcNk0BG7lIyQokIqCQmnYiZk5XRPi2Pzjp9OMR9tR4PY8y5H/uA6gdv7KrT5XJyy/34cgg2RjM+xW4hNQpR4cF1WU7e1+FiJufRd2JnKItFzjGgupnRLF08RqvJTCKSt2ZikMlKPTO656ux4lcrPl1Wj9SDxK2bnMaQlTqGC0LPi6O0XDA0rYtTJzhGrZ+cdjv28CcqPQYKMtovgYIQQK3nH6mZPFKmAFBQQEEB4DdREJ3i2k+8oM4t3oO3KFnd8Xzc0x+UGOsEnhKi1DB+UoBzeWwReE0lxWGmaTKQDykIYPN9mdJlyrtyZUnN84n2ZwvpwGfC3XScOgw+FaYY/f0r8Cenf+cJc9Lv4fO5AAAAAElFTkSuQmCC&logoColor=fff alt="AlpineJS"></a>
<a href="https://tailwindcss.com/"><img src="https://img.shields.io/badge/tailwindcss-%2338B2AC.svg?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS"></a>
<a href="https://alpinejs.dev/"><img src=https://img.shields.io/badge/AlpineJS-FFFFFF?style=for-the-badge&logo=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEYAAABBCAMAAABb9SIOAAAALVBMVEVHcEywvsOKmZ9UV16jydKdxtBGSlGVxNE6PUePwc82PEWNwc+JwNKGwdQuNEBw7EKcAAAADHRSTlMABhouPml4osnY2vBA0YtbAAAA5UlEQVR42u3WQQ6DMAxE0dgFQsHk/setcKlGqkUMcqVu/HdsngavUrIsy/4XPag4MfvKtEyOMzwru0prbaK+skllVzGOUTZ1HMU6Vtm2yr4CxyqijO5xFDhW2Q0NjlV6Dv7oqLKvwLEKHF+BAwUI7myUjgPF7HEVOFAQnI4CB4pmHU+Bg+siex+em+eMoohN1qF8engOURX52iP6sY5UrjuFqmWg3HKAHKJAubkHYUvAwZaoo0rEkbdS0G1HZFdElZCje1SJONgSdLAl5EAJOESjKq6znDUfTrkS8XmUb6Usy7Jf9wL3ezLd1yv0xQAAAABJRU5ErkJggg==&logoColor=fff alt="AlpineJS"></a>
<a href="https://www.sqlite.org/"><img src="https://img.shields.io/badge/sqlite-%2307405e.svg?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite"></a>
</div>

---

## Getting Started

### Deployment (Production)

Follow these instructions for deployment on a shared hosting environment.

Requirements: `php == 8.2` `composer >= 2.7`

1. Clone branch `production` from this repository

```bash
git clone -b production https://github.com/MPC-Rebold/assessments-laravel.git
```

2. Copy `.env.example` to `.env` and set the environment variables

3. Run the following:

```bash
composer install
php artisan key:generate
php artisan migrate --force
php artisan db:seed
```

4. Copy the seed files to the `database/seed` directory

5. Copy the following into the root directory of the server (`public_html`)

    - `./public/build/**`
    - `./public/.htaccess`
    - `./public/index.php`
    - `./public/robots.txt`

6. In `public_html/index.php` change the following lines:

```php
require __DIR__.'/../vendor/autoload.php';
/* ... */
$app = require_once __DIR__.'/../bootstrap/app.php';
```

to

```php
require __DIR__.'/../assessments-laravel/vendor/autoload.php';
/* ... */
$app = require_once __DIR__.'/../assessments-laravel/bootstrap/app.php';
```

---

### Development

Follow these instructions for development on a local environment.

Requirements: `php == 8.2` `composer >= 2.7` `node >= 20` `npm >= 8`

1. Clone this repository
2. Copy `.env.example` to `.env` and set the environment variables
3. Run the following:

```bash
npm install
composer install
php artisan key:generate
php artisan migrate --force
php artisan db:seed
```

4. Copy the seed files to the `database/seed` directory

---
