# Changelog

All notable changes to `doccheck` will be documented in this file.

## 2.1.0 - 2026-05-11

### Added

- `DocCheckAuthorizationException` thrown from `Provider::user()` when DocCheck redirects back to the callback URL with an OAuth 2.0 error response (`?error=…&error_description=…`, per [RFC 6749 §4.1.2.1](https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.2.1)). Exposes the raw `error` code and optional `errorDescription` so consumers can branch on the failure reason — e.g. DocCheck's undocumented `R0100_PROFESSION_NOT_ALLOWED` when the user's profession isn't on the login-client's whitelist.

Previously this case fell through to the token exchange and surfaced as an opaque Guzzle `BadResponseException`. Existing happy-path behaviour is unchanged.

### Changed

- `composer.json` PHP constraint bumped from `^8.0` to `^8.1`. The new exception uses readonly promoted properties (8.1+). This matches the effective floor that was already enforced transitively by `socialiteproviders/manager: ~4.0` (which requires PHP 8.1) — no consumer who could install v2.0 is excluded by this change.

## 2.0.0 - 2026-05-07

Migration to DocCheck's new OAuth 2.0 endpoints (`auth.doccheck.com`). v1 is preserved on the `v1.x` tags for legacy consumers.

### Breaking changes

- Authorize URL changed from `https://login.doccheck.com/code/` to `https://auth.doccheck.com/{lang}/authorize`. The `lang` path segment is required and is provided per-request via `->with(['lang' => '...'])`.
- Token URL changed to `https://auth.doccheck.com/token`.
- User data URL changed to `https://auth.doccheck.com/api/users/data`.
- Authorize request now uses the standard OAuth `client_id` parameter (was `dc_client_id` in v1) and a space-separated `scope` parameter.
- Renamed payload keys to match DocCheck's v2 schema:
  - `uniquekey` → `unique_id`
  - `address_name_first` → `first_name`
  - `address_name_last` → `last_name`
  - `occupation_discipline_id` → `discipline_id`
  - `occupation_profession_id` → `profession_id`
- Removed `DocCheckUser::getOccupationProfessionParentId()` — the `occupation_profession_parent_id` field is no longer returned by DocCheck.
- The legacy decline-consent shortcut (`dc_agreement=0` short-circuit) has been removed. Under v2, declined fields are simply omitted from the `/api/users/data` response.

### Added

- Default `unique_id` scope is always requested. Consumers extend with `->scopes(['name', 'email', 'occupation_detail', ...])`.
- Space (` `) scope separator (per OAuth 2.0 standard).

## 1.0.0 - 201X-XX-XX

- initial release
