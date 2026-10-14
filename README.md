# Laravel Tips

A small Laravel + Filament app for recording daily tips. `main` is the clean demo. Each tip has two branches off `main`: the old behaviour, then the new one. Switch branches, reset the database, and record.

## Bootstrap

Requirements: PHP 8.4+, Composer, Node.js, SQLite.

```bash
bin/bootstrap
php artisan serve
```

`bin/bootstrap` installs PHP and npm dependencies, creates the SQLite database, migrates, and seeds.

Sign in at [http://localhost:8000/admin](http://localhost:8000/admin).

| | |
| --- | --- |
| Email | `admin@example.com` |
| Password | `password` |

Customers live at [http://localhost:8000/admin/customers](http://localhost:8000/admin/customers).

## Switching tips

```bash
bin/tip <slug> before
bin/tip <slug> after
```

The script checks out `tip/<date>-<slug>/before` or `tip/<date>-<slug>/after`, then runs `migrate:fresh --seed` so the recording starts from the same data. The slug is the part after the date, so `import-testing` and `2026-10-11-import-testing` both work.

Leave `php artisan serve` running. It follows the checked-out branch.

## Tips

| Date | Tip | Before | After |
| --- | --- | --- | --- |
| 2026-10-11 | [Import testing helpers](#2026-10-11-import-testing) | `tip/2026-10-11-import-testing/before` | `tip/2026-10-11-import-testing/after` |
| 2026-10-14 | [getTabComponents()](#2026-10-14-get-tab-components) | `tip/2026-10-14-get-tab-components/before` | `tip/2026-10-14-get-tab-components/after` |

## 2026-10-11 import testing

Filament v5.9.0 added helpers for testing importers without a CSV upload or a queue worker. `Importer::test()` returns a `TestableImport`. `ImportAction::fake()` records action submissions without processing rows.

Source: [filamentphp/filament#20560](https://github.com/filamentphp/filament/pull/20560). Docs: [Testing imports](https://filamentphp.com/docs/5.x/testing/testing-imports).

This app is on Filament v5.10 (the current v5), which includes those helpers. The installed API matches the docs: `import()`, `assertImported()`, `assertSkipped()`, `assertHasErrors()`, `assertHasRowFailure()`, `getRecord()`, `ImportAction::fake()`, `assertDispatched()`, and `assertNothingDispatched()`.

Both branches add the same `CustomerImporter` and an Import action on the Customers list. Only the Pest test changes.

### Before

```bash
bin/tip import-testing before
php artisan serve
```

1. Open [http://localhost:8000/admin/customers](http://localhost:8000/admin/customers) and show the Import action. The app queue is `sync`, so a CSV imported from that modal is processed before the modal closes.
2. Open `tests/Feature/CustomerImportTest.php`.
3. Run:

```bash
php artisan test --compact tests/Feature/CustomerImportTest.php
```

The test fakes the local disk, stores the CSV, parses the rows by hand, creates an `Import`, and runs the queued `ImportCsv` job with `Bus::dispatchSync()` before asserting the `customers` table.

### After

```bash
bin/tip import-testing after
```

1. Open the same test file. The importer and the Import button are unchanged.
2. Run the same command:

```bash
php artisan test --compact tests/Feature/CustomerImportTest.php
```

Row behaviour uses `CustomerImporter::test()->import([...])` with `assertImported()`, `assertSkipped()`, `assertHasErrors()`, `assertHasRowFailure()`, and `getRecord()`. A second test calls `ImportAction::fake()` and checks `assertDispatched()` / `assertNothingDispatched()`.

## 2026-10-14 getTabComponents()

Filament v5.10.2 lets an Edit or View page mix its own tabs with relation manager tabs, in any order, by overriding `getTabComponents()`. This is not the list-page `getTabs()` API. List filters still use `getTabs()` on `ListRecords`.

Source: [filamentphp/filament#20734](https://github.com/filamentphp/filament/pull/20734), method in [`HasRelationManagers`](https://github.com/filamentphp/filament/blob/v5.10.2/packages/filament/src/Resources/Pages/Concerns/HasRelationManagers.php). Docs: [Managing relationships](https://filamentphp.com/docs/5.x/resources/managing-relationships#mixing-content-and-relation-manager-tabs).

Both branches add a customer status (active, trial, churned) and a Notes relation manager with a count badge. The edit page turns on `hasCombinedRelationManagerTabsWithContent()`.

### Before

```bash
bin/tip get-tab-components before
php artisan serve
```

1. Open [http://localhost:8000/admin/customers](http://localhost:8000/admin/customers). The status column is a badge.
2. Edit Ada Lovelace: [http://localhost:8000/admin/customers/1/edit](http://localhost:8000/admin/customers/1/edit).
3. The tabs are **Edit**, then **Notes** with a count badge. The form is always the first tab, or the last one if you return `ContentTabPosition::After`. There is no way to insert another tab between them.
4. Open `tests/Feature/CustomerTabsTest.php` and run:

```bash
php artisan test --compact tests/Feature/CustomerTabsTest.php
```

### After

```bash
bin/tip get-tab-components after
```

1. Edit Ada Lovelace again: [http://localhost:8000/admin/customers/1/edit](http://localhost:8000/admin/customers/1/edit).
2. The page opens on **Status**. Its badge is green **Active**, and the tab shows account standing, company, and email. **Notes** (still with its count badge) sits between Status and **Edit**.
3. Edit Katherine Johnson ([http://localhost:8000/admin/customers/3/edit](http://localhost:8000/admin/customers/3/edit)). The same tab is red **Churned**.
4. Run the same test:

```bash
php artisan test --compact tests/Feature/CustomerTabsTest.php
```

`getTabComponents()` returns the Status tab, `getRelationManagerTabComponent('notes')`, and the form tab under the empty key `''`. The array order is the tab order.
