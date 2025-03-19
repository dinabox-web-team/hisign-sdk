# HiSign SDK

**⚠️ IMPORTANT: This project is currently under active development and is not yet ready for production use. ⚠️**

## Overview

HiSign SDK is a PHP wrapper for integrating digital signature solutions into your applications. This SDK aims to provide a set of tools and libraries to enable secure and compliant digital signing processes.

## Features (Planned)

- Document management
- User authentication
- Signature creation and verification
- Signatory management

## Requirements

- PHP 7.4 or higher
- Composer

## Installation

As this project is still in development, installation instructions will be provided once a stable version is released.

### 1.1 - Composer

Just run composer require in your project:

```bash
composer require cliandinabox/hisign-sdk
```

### 1.2 - Manual

- To use this library you need to add this lib in a subdir in your project. This is the first step.
- Add this lib in **PSR-4 Autoload** in composer.json file from your project:

```json
    "autoload": {
        "psr-4": {
            "ClianDinabox\\HisignSdk\\": "lib/hisign-sdk/src"
        }
    },
```

> **Obs:** This is a part of composer json, be careful just not copy and paste, pay attention.
> to not corrupt yout file.

### 2 - Instancing class

To start working with HiSingSDK you need to use the correcty namespace **`ClianDinabox\HisignSdk`** 

```php
use ClianDinabox\HisignSdk\HiSign:
$hisignsdk = new HiSign(
        $apikey
        $email
        $password
        $mode
        $partnerCode
);
```

## Usage

Usage examples and documentation will be added as the project progresses.

## Contributing

We welcome contributions to the HiSign SDK! However, as the project is in its early stages, please contact the maintainers before submitting pull requests.

## License

This project is licensed under the MIT License.

## Contact

For any questions or concerns, please contact:
Cristiano Mozena - cristiano@dinabox.email

---

Remember, this SDK is a work in progress. Features, API, and documentation are subject to change as development continues.