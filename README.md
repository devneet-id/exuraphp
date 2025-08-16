# E X U R A . P H P

**Exura.php** is a minimalist PHP micro-framework designed to simplify modern backend development.  
Inspired by *Express-like* design, Exura focuses on clarity, modularity, and control — without the bloat of large frameworks.

## ✨ Key Features

- ⚡ **Simple Routing**  
  Define routes with `Exura::module($method, $route, $callback)` — expressive and easy to read.

- 🛡️ **Error & Exception Handling**  
  All errors and exceptions are automatically converted into structured JSON.

- 📦 **Response Management**  
  Use `Exura::return()`, `Exura::state()`, or `Exura::header()` to control responses clearly.

- 📑 **Method Helper**  
  Easily retrieve request data using `Exura::method()`.

- 🔍 **Trace & Debug Info**  
  Attach traces or additional method info into responses for easier debugging.

## 🔍 Example

```php
Exura::module('GET', 'demo', function() {
  Exura::state(200);
  return [
    'data' => 'Hello World 👋'
  ];
});
Exura::module('POST', 'demo', function() {
  $data1 = Exura::method('data1', false);

  Exura::return(
    data: ['data1'=> $data1],
    code: 200
  );
});
Exura::module('DELETE', 'demo', function() {
  // Simulated error
  $x = $y + 2; 

  return [
    'data'=> 'DELETE:hello world',
    'method'=> Exura::method()
  ];
});
```

Simple, clean, and powerful — that’s the Exura.php way ✨
Perfect for developers who love concise code, full control, and zero bloat.

## 🛠️ BUILD WITH RUNE

**Exura** uses the **Rune** build engine.  
Before getting started, we recommend reading the official documentation here:  
👉 <https://github.com/devneet-id/rune>

Although in most cases you can simply run the available `build` commands, understanding the **Rune structure** will help you better track and manage post-processing tasks more efficiently.

Requirements, Make sure you have:
- **Composer** installed
- **PHP version 8+**

Then run this command:

```bash
composer install
```

To view the main rune script:
```shell
php rune
```

To build the project:
```shell
php rune build
php rune build --min=true
```

Development mode (auto watch & build on changes):
```shell
php rune watch
```


## 🎯 G O A L
Exura will:
- Be fully integrated with **Rune** as its built-in build engine.

- Feature a custom **preprocessing system**, inspired by JSX — but built natively into Rune’s environment.

- Embrace **all aspects of modern JavaScript**, including API design, reactive patterns, modular structure, and future-facing concepts.

- Stay committed to **never slowing down or interfering** with the developer's workflow.

Exura.js is here to assist — not to intrude.