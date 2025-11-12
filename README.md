# oihana-php-signals

![Oihana PHP Signals](https://raw.githubusercontent.com/BcommeBois/oihana-php-signals/main/assets/images/oihana-php-signals-logo-inline-512x160.png)

A fast and flexible signal/slot implementation for event-driven programming.

[![Latest Version](https://img.shields.io/packagist/v/oihana/php-signals.svg?style=flat-square)](https://packagist.org/packages/oihana/php-signals)  
[![Total Downloads](https://img.shields.io/packagist/dt/oihana/php-signals.svg?style=flat-square)](https://packagist.org/packages/oihana/php-signals)  
[![License](https://img.shields.io/packagist/l/oihana/php-signals.svg?style=flat-square)](LICENSE)

## 📚 Documentation

Full project documentation is available at:  
👉 https://bcommebois.github.io/oihana-php-signals

## 📦 Installation

> Requires [PHP 8.4+](https://php.net/releases/) 

Install via Composer:
```bash
composer require oihana/php-signals
```

## ✨ Features

Provides a robust observer pattern implementation with priority-based execution, auto-disconnect capability, and support for both callable functions and Receiver objects. It is ideal for event-driven architectures and decoupled communication between components.

- Priority-based receiver execution (higher priority executes first)
- Auto-disconnect for one-time listeners
- Type-safe receiver management
- Efficient sorting and execution order
- Supports both object receivers implementing `Receiver` and PHP callables

## 🚀 Quick start

Basic usage with callables and Receiver objects.

```php
use oihana\signals\Signal;
use oihana\signals\Receiver;

// Define a Receiver class
class NotificationHandler implements Receiver
{
    public function receive( mixed ...$values ) :void
    {
        echo 'Notification: ' . implode(', ', $values) . PHP_EOL;
    }
}

// Create receivers
$logger = function( mixed ...$values )
{
     echo 'Log: ' . implode(', ', $values) . PHP_EOL;
};

$handler = new NotificationHandler();

// Setup signal
$signal = new Signal();

// Connect with different priorities
$signal->connect( $logger  , priority: 10 ); // Executes first
$signal->connect( $handler , priority: 5 ); // Executes second

// Emit values to all connected receivers
$signal->emit( 'User logged in', 'user123' );

// One-time listener
$signal->connect
(
    fn() => echo 'First emit only!' . PHP_EOL,
    autoDisconnect: true
);
```

**Note :** WeakReferences are used for object receivers to allow proper garbage collection without preventing objects from being destroyed.

## 🧰 Usage

Advanced usage with priority and auto-disconnect

```php
$signal = new Signal();

// High priority handler (executes first)
$signal->connect
(
    fn($msg) => echo "URGENT: $msg" . PHP_EOL,
    priority: 100
);

// One-time handler (disconnects after first emit)
$signal->connect
(
    fn($msg) => echo "Initialization: $msg" . PHP_EOL,
    priority: 50,
    autoDisconnect: true
);

// Normal priority handler
$signal->connect
(
     fn($msg) => echo "Info: $msg" . PHP_EOL
);

// First emit - all three handlers execute
$signal->emit('System started');

// Second emit - only two handlers execute (auto-disconnect removed one)
$signal->emit('Processing data');
```

## Running Unit Tests

To run all tests:
```shell
$ composer test
```

To run a specific test file:
```shell
$ composer test tests/oihana/signals/SignalTest.php
```

## 🤝 Contributing

Contributions are welcome! Whether you're fixing a bug, improving an existing feature, or proposing a new one, your help is appreciated.

Please feel free to:
- **Report a bug:** If you find a bug, please open an issue and provide as much detail as possible.
- **Suggest an enhancement:** Have an idea to make this library better? Open an issue to discuss it.
- **Submit a pull request:** Fork the repository, make your changes, and open a pull request. Please ensure all tests are passing before submitting.

You can find the issues page here: [https://github.com/BcommeBois/oihana-php-core/issues](https://github.com/BcommeBois/oihana-php-core/issues)

## 🗒️ Changelog

See `CHANGELOG.md` for notable changes.

## License
This project is licensed under the [Mozilla Public License 2.0 (MPL-2.0)](https://www.mozilla.org/en-US/MPL/2.0/).

## 👤 About the author
- Author : Marc ALCARAZ (aka eKameleon)
- Mail : [marc@ooop.fr](mailto:marc@ooop.fr)
- Website : http://www.ooop.fr

## 🛠️ Generate the Documentation

We use [phpDocumentor](https://phpdoc.org/) to generate the documentation into the `./docs` folder.

## 🔗 Related packages
- 
- `oihana/php-core` – core helpers and utilities used by this library: `https://github.com/BcommeBois/oihana-php-core`
- `oihana/php-reflect` – reflection and hydration utilities: `https://github.com/BcommeBois/oihana-php-reflect`
