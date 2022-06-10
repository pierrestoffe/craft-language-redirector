# Locale Redirector Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [2.0.1] - 2022-06-10

### Fixed
- Fixed language matching issue that would occur when the locales in the config file are not lowercase

## [2.0.0] - 2022-05-15

### Added
- Added support for Craft CMS 4.x

## [1.5.3] - 2020-12-03

### Fixed
- Fixed issue where visitors where automatically redirected to the defaultEntryId when it is defined. The defaultEntryId should instead only be used when calling the language switcher variable.

## [1.5.2] - 2020-11-18

### Fixed
- Fixed edge case of $defaultEntryId, when an entry exists in a Site but is disabled

## [1.5.1] - 2020-11-06

### Fixed
- Fixed release numbering format

## [1.5.0] - 2020-11-06

### Added
- Added `defaultEntryId` setting to default to a specific Entry when the current Entry is disabled in other Sites

## [1.4.0] - 2020-11-05

### Added
- Added `group` parameter to getUrls method so that the list of URLs in a specific language group can be retrieved

## [1.3.4] - 2020-03-23

### Changed
- Complied with psr-4 and eliminate composer deprecation warning

## [1.3.3] - 2020-03-05

### Changed
- Removed `__construct` method and moved the methods that were inside `__construct` after `canRedirectVisitor` method is called

## [1.3.2] - 2020-03-05

### Changed
- Moved the initial check to a public function within the main service

## [1.3.1] - 2019-11-26

### Fixed
- Replaced request.app.isLivePreview with request.app.livePreview, which was deprecated in Craft CMS since v3.2.1

## [1.3.0] - 2019-11-18

### Added
- Added support for multi-group Sites (multiple Sites inside multiple Site groups)

### Changed
- Crawlers are now subject to a redirection that only removes the lang URL query parameter

## [1.2.1] - 2019-09-26

### Fixed
- Fix for initializing plugin in console environment (thanks to @boboldehampsink)

## [1.2.0] - 2019-06-02

### Added
- Added method that defaults to fetching the Sites and languages defined in the Sites table, should the list of Sites be undefined in the plugin's settings (thanks to @jcherniak)

## [1.1.3] - 2019-04-09

### Added
- Added setting to enable/disable the redirection all together, and only keep the language switching feature

## [1.1.2] - 2018-12-09

### Changed
- Disable redirection for URLs that contain the ignore-lang parameter

## [1.1.1] - 2018-12-08

### Added
- Added option to overrides URLs per language in the language switcher

## [1.1.0] - 2018-10-19

### Added
- Added setting to enable/disable the redirection for users with CP access

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
