# Changelog

All notable changes to `laravel-password-expiry` will be documented in this file.

## 1.3.0 - 2026-06-22

### Features

- Add support for Laravel 13
- Add support for PHP 8.5

### What's Changed

- ci: fix the `prefer-lowest` matrix for Laravel 11 by raising the `orchestra/testbench` floor to `^9.5`
- ci: run the full matrix with `fail-fast: false`

**Full Changelog**: https://github.com/beliven-it/laravel-password-expiry/compare/1.2.1...1.3.0

## 1.2.1 - 2025-08-18

### What's Changed

* chore(deps): bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot[bot] in https://github.com/beliven-it/laravel-password-expiry/pull/3
* chore(deps): bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/beliven-it/laravel-password-expiry/pull/5
* fix: handle password entries with null models

## 1.2.0 - 2025-03-07

### What's Changed

* chore(deps): bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/beliven-it/laravel-password-expiry/pull/1
* chore(deps): bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/beliven-it/laravel-password-expiry/pull/2

### New Contributors

* @dependabot made their first contribution in https://github.com/beliven-it/laravel-password-expiry/pull/1

**Full Changelog**: https://github.com/beliven-it/laravel-password-expiry/compare/1.1.0...1.2.0

## 1.1.0 - 2024-12-21

### Features

- Add trait method `tryClearPassword` for clear password expired from model

## 1.0.0 - 2024-12-21

First version of the package
