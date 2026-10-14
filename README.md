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
