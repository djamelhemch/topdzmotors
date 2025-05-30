[![v1: PHP >= 5.4 < 8.4](https://img.shields.io/badge/v1-PHP%20%3E%3D%205.4%20%3C%208.4-8892bf.svg)](https://maikuolan.github.io/Compatibility-Charts/)
[![v2: PHP >= 7.2 < 8.4](https://img.shields.io/badge/v2-PHP%20%3E%3D%207.2%20%3C%208.4-8892bf.svg)](https://maikuolan.github.io/Compatibility-Charts/)
[![v3: PHP >= 7.2](https://img.shields.io/badge/v3-PHP%20%3E%3D%207.2-8892bf.svg)](https://maikuolan.github.io/Compatibility-Charts/)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
[![PRs Welcome](https://img.shields.io/badge/PRs-Welcome-brightgreen.svg)](http://makeapullrequest.com)

## **What is phpMussel?**

An ideal solution for shared hosting environments, where it's often not possible to utilise or install conventional anti-virus protection solutions, phpMussel is a PHP script designed to **detect trojans, viruses, malware and other threats** within files uploaded to your system wherever the script is hooked, based on the signatures of [ClamAV](https://www.clamav.net/) and others.

---


### What's this repository for?

This provides a bridge between phpMussel and PHPMailer, enabling phpMussel to utilise PHPMailer for two-factor authentication, email notification about blocked file uploads, etc.

```
composer require phpmussel/phpmailer
```

__*Example:*__
```PHP
<?php
// Path to vendor directory.
$Vendor = __DIR__ . DIRECTORY_SEPARATOR . 'vendor';

// Composer's autoloader.
require $Vendor . DIRECTORY_SEPARATOR . 'autoload.php';

$Loader = new \phpMussel\Core\Loader();
$Scanner = new \phpMussel\Core\Scanner($Loader);
$FrontEnd = new \phpMussel\FrontEnd\FrontEnd($Loader, $Scanner);
$Web = new \phpMussel\Web\Web($Loader, $Scanner);
$Loader->Events->addHandler('sendMail', new \phpMussel\PHPMailer\Linker($Loader));

$Web->scan();
$FrontEnd->view();

unset($Web, $FrontEnd, $Scanner, $Loader);
```

---


### Documentation:
- **[English](https://github.com/phpMussel/Docs/blob/master/readme.en.md)**
- **[العربية](https://github.com/phpMussel/Docs/blob/master/readme.ar.md)**
- **[Deutsch](https://github.com/phpMussel/Docs/blob/master/readme.de.md)**
- **[Español](https://github.com/phpMussel/Docs/blob/master/readme.es.md)**
- **[Français](https://github.com/phpMussel/Docs/blob/master/readme.fr.md)**
- **[Bahasa Indonesia](https://github.com/phpMussel/Docs/blob/master/readme.id.md)**
- **[Italiano](https://github.com/phpMussel/Docs/blob/master/readme.it.md)**
- **[日本語](https://github.com/phpMussel/Docs/blob/master/readme.ja.md)**
- **[한국어](https://github.com/phpMussel/Docs/blob/master/readme.ko.md)**
- **[Nederlandse](https://github.com/phpMussel/Docs/blob/master/readme.nl.md)**
- **[Português](https://github.com/phpMussel/Docs/blob/master/readme.pt.md)**
- **[Русский](https://github.com/phpMussel/Docs/blob/master/readme.ru.md)**
- **[اردو](https://github.com/phpMussel/Docs/blob/master/readme.ur.md)**
- **[Tiếng Việt](https://github.com/phpMussel/Docs/blob/master/readme.vi.md)**
- **[中文（简体）](https://github.com/phpMussel/Docs/blob/master/readme.zh-Hans.md)**
- **[中文（傳統）](https://github.com/phpMussel/Docs/blob/master/readme.zh-Hant.md)**

#### See also:
- [**phpMussel/phpMussel**](https://github.com/phpMussel/phpMussel) – The main phpMussel repository (you can get phpMussel versions prior to v3 from here).
- [**phpMussel/Core**](https://github.com/phpMussel/Core) – phpMussel core (dedicated Composer version).
- [**phpMussel/CLI**](https://github.com/phpMussel/CLI) – phpMussel CLI-mode (dedicated Composer version).
- [**phpMussel/FrontEnd**](https://github.com/phpMussel/FrontEnd) – phpMussel front-end (dedicated Composer version).
- [**phpMussel/Web**](https://github.com/phpMussel/Web) – phpMussel upload handler (dedicated Composer version).
- [**phpMussel/Examples**](https://github.com/phpMussel/Examples) – Prebuilt examples for phpMussel (useful for users which don't want to use Composer to install phpMussel).
- [**phpMussel/plugin-boilerplates**](https://github.com/phpMussel/plugin-boilerplates) – This repository contains boilerplate code which can be used to create new plugins for phpMussel.
- [**phpMussel/Plugin-PHPMailer**](https://github.com/phpMussel/Plugin-PHPMailer) – Provides 2FA and email notifications support for phpMussel v3+.
- [**CONTRIBUTING.md**](https://github.com/phpMussel/.github/blob/master/CONTRIBUTING.md) – Contribution guidelines.

---


Last Updated: 1 July 2024 (2024.07.01).
