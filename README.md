# Aerys Reverse

![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)

`amphp/aerys-reverse` is a reverse HTTP proxy handler for use with the [`Aerys`](https://github.com/amphp/aerys)
webserver.

---

**EXPERIMENTAL, USE AT YOUR OWN RISK!**

---

**Required PHP Version**

- PHP 7.0+

**Installation**

```bash
composer require amphp/aerys-reverse
```

## Usage

```PHP
<?php

return (new Aerys\Host)
    ->use(new Aerys\ReverseProxy("https://amphp.org/", ["Host" => ["amphp.org"]]);
```

Now all requests to the webserver are reverse proxied to https://amphp.org/, with all the headers preserved and the `Host` header set to `amphp.org`.

Alternatively one also can pass a callable as second parameter, which then gets all the headers in and should return the headers to send.

As optional third argument an `Amp\Artax\Client` instance can be passed (should use `NullCookieJar`) to setup certain options.
