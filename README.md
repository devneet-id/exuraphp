# E X U R A . P H P

**Exura.php** is an HTTP API micro-framework for PHP: a route, a function, a JSON response — in an *Express-like* style.
Part of the **Exura series** — the power of a modern API framework in a single, super-light file.

## ✨ Key Features

- ⚡ **Simple routing** — `Exura::module($method, $route, $callback)`; works with `index.php?/route` or a rewritten path, no server config required.
- 📑 **Request data** — `Exura::method('key', $default)` reads JSON body, form data and query string in one call.
- 📦 **Responses** — return an array, or use `Exura::return($data, $code)`, `Exura::state($code)`, `Exura::data($key, $value)` and `Exura::header([...])`.
- 🛡️ **Errors as JSON** — PHP errors and exceptions become a structured JSON response with status `500`.
- 🔍 **Trace** — every response carries an `E X U R A` block (url, route, method, request) for easier debugging.

## 📦 Install

Copy `dist/exura.php` (or `dist/exura.min.php`) into your project and require it:

```php
require __DIR__ . '/exura.php';
```

## 🔍 Example

```php
require __DIR__ . '/exura.php';

// GET index.php?/hello
Exura::module('GET', 'hello', function() {
  return ['data' => 'Hello World 👋'];
});

// POST index.php?/notes   body: {"title": "First note"}
Exura::module('POST', 'notes', function() {
  $title = Exura::method('title', 'untitled');

  Exura::header(['Content-Type: application/json']);
  Exura::return(['data' => ['title' => $title]], 201);
});

// GET index.php?/fail → 500 with the error as JSON
Exura::module('GET', 'fail', function() {
  throw new RuntimeException('Something went wrong');
});
```

## 🧩 The Exura Series

Exura mirrors a modern CSS / JS / PHP framework — at a fraction of the weight, without losing the abilities of a framework.

| Package | Focus | Repository |
|---|---|---|
| **exuracss** | modern semantic styling | [devneet-id/exuracss](https://github.com/devneet-id/exuracss) |
| **exurajs** | modern reactive JavaScript | [devneet-id/exurajs](https://github.com/devneet-id/exurajs) |
| **exuraphp** | modern HTTP API endpoints | [devneet-id/exuraphp](https://github.com/devneet-id/exuraphp) |

## 🛠️ Shaped with Rune

This project is shaped with **Rune** — a lore-driven, modular concept beneath PHP that responds to development rather than dictating it.  
Its runite, `cast`, holds the casts that turn `src/` into `dist/`.  
👉 <https://github.com/devneet-id/rune>

You can simply run the casts below; knowing how a runite works helps when you want to shape your own.

Requirements:
- **Composer** installed
- **PHP version 8.1+**

Then run this command:

```bash
composer install
```

The runite of this project is `cast`. To view its main screen (all casts with notes):
```shell
php cast
```

To build the project:
```shell
php cast build
php cast build --min=true
```

Version (stored in the runite itself as `const VERSION`, applied on build):
```shell
php cast version
php cast version --up            # 1.0.0 -> 1.0.1  (--up=minor | major | release)
php cast version --pre           # 1.0.1 -> 1.0.1-dev  (--pre=beta.1)
php cast version --set=1.0.0
```

## 🎯 Goal

Exura.php will:

- Cover the **whole HTTP layer** — route parameters (`/user/:id`), wildcards, a 404 fallback, several methods per route and consistent JSON headers.
- Add **middleware hooks** (before / after) for auth, CORS and validation — still in a single file.
- Separate **development and production** — the trace block only where it helps.
- Never slow down or interfere with the developer's workflow.

Exura.php is here to assist — not to intrude.
