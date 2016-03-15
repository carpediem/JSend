# Changelog

All notable changes to `Jsend` will be documented in this file.

## 1.2.3 - 2016-09-15

### Added

- None

### Fixed

- Bug Fix SPL Exception usage.

### Deprecated

- None

### Removed

- None

## 1.2.2 - 2016-09-14

### Added

- None

### Fixed

- The JSend data property can also be a `JsonSerializable` object
- When using `JSend::withError` the JSend status of the return instance is set to `error`.

### Deprecated

- None

### Removed

- None

## 1.2.1 - 2016-09-11

### Added

- None

### Fixed

- Improve property validation
    - empty message error now throw exception
    - a message error can be an object implementing the `__toString` method

- Improve HTTP response generation
    - adding the `Content-Length` header
    - adding overiding possibility for all header

### Deprecated

- None

### Removed

- None

## 1.2.0 - 2016-09-04

### Added

- `JSend::__set_state`
- `JSend::__debugInfo`

### Fixed

- None

### Deprecated

- None

### Removed

- None

## 1.1.0 - 2016-03-04

### Added

- `JSend::isSuccess`
- `JSend::isFail`
- `JSend::isError`

### Fixed

- None

### Deprecated

- None

### Removed

- None

## 1.0.0 - 2016-03-03

First release