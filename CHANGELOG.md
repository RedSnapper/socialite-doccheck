# Changelog

All notable changes to `doccheck` will be documented in this file.

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
