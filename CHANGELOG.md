# Locale Redirector Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [1.0.8] - 2018-09-10

### Changed
- Used Craft's i18n service instead of the php-intl extension

## [1.0.7] - 2018-07-28

### Changed
- Initialize the language match only after the app is fully initialized

### Fixed
- Fixed a bug that would occur if the currently-visited entry wasn’t enable in all Sites

## [1.0.6] - 2018-07-12

### Fixed
- Called the exit() function after setting the location header

## [1.0.5] - 2018-06-10

### Fixed
- Fixed a bug that would occur when mixing language-based and country-based locales in the configuration file

### Changed
- Now using PRS-2 and Symfony code syntax rules

## [1.0.4] - 2018-06-08

### Fixed
- Fixed a bug that would occur when a redirected URL already contains URL parameters (thanks to @vieko)

## [1.0.3] - 2018-06-08

### Fixed
- Fixed return value bug when craft.languageSwitcher.getUrls wouldn't return any language

## [1.0.2] - 2018-04-22

### Changed
- Prevent redirect if the target Element doesn't exist
- Hide elements for which no entry exist in the language switcher

### Removed
- The "enabled" setting

## [1.0.1] - 2018-04-08
### Added
- Documented the plugin

### Changed
- Merged files and classes

## [1.0.0] - 2018-04-08
### Added
- Initial release
