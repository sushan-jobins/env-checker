# ENV Manager

A lightweight PHP CLI tool for comparing `.env` and `.env.example` files.

`envm` helps developers identify environment variable differences, view their status in a table format, and optionally synchronize values from `.env.example` to `.env` after confirmation.

---

## Requirements

* Composer `2.x`
* PHP `8.1+`

---

## Install Directly From GitHub

```bash
composer global config repositories.env-checker vcs git@github.com:sushan-jobins/env-checker.git
```

Then install the package:

```bash
composer global require sushan-jobins/env-checker:dev-main
```

## Verify the installation

Check that Composer installed the package:

```bash
composer global show sushan-jobins/env-checker
```

---

## Verify Installation

Check that Composer installed the package:

```bash
composer show sushan-jobins/env-checker
```

---

## Run the script

```bash
envm
```

The script displays the environment variable status table.

If changes are available, it asks:

If changes are available, the command asks for confirmation:

```text
⚠ Update .env with changes from .env.example? [y/N]:
```

Enter `y` to apply the changes or `n` to skip them.


to apply the changes.

Any other value skips the update.

---

# Status Filters

The following statuses are supported:

```text
all
added
changed
not_changed_on_env
only_on_env
same
```

## Show all statuses

```bash
envm --status=all
```

This displays all environment variables, including `same`.

---

## Show added variables

```bash
envm --status=added
```

`added` means the environment variable did not exist in the previous `.env` but exists in the current/generated `.env`.

---

## Show changed variables

```bash
envm --status=changed
```

`changed` means the environment variable existed before, but its value has changed.

---

## Show variables not changed in `.env`

```bash
envm --status=not_changed_on_env
```

`not_changed_on_env` means:

* The `.env` value has not changed.
* The value differs from `.env.example`.

---

## Show only_on_env variables

```bash
envm --status=only_on_env
```

`only_on_env` means no change or update was detected for the environment variable.

---

## Show variables with the same value

```bash
envm --status=same
```

`same` means the current `.env` value is exactly the same as the `.env.example` value.

---

# Status Information

To see the meaning of each status:

```bash
envm --info-status
```

The command displays information for:

```text
ADDED
CHANGED
NOT_CHANGED_ON_ENV
SAME
ONLY_ON_ENV
```

---

# Dry Run

To check which environment variables are missing without modifying `.env`:

```bash
envm --dry
```

or,

```bash
envm dry
```

The dry run only displays variables that exist in `.env.example` but are missing from `.env`.

No changes are made to `.env`.

---

## Help

To display the available commands and options:

```bash
envm --help
```

or,

```bash
envm -h
```

The help command displays the available options, supported statuses, and usage examples.


# Available Commands

| Purpose               | Command                            |
| --------------------- | ---------------------------------- |
| Run script            | `envm`                             |
| Show all              | `envm --status=all`                |
| Added                 | `envm --status=added`              |
| Changed               | `envm --status=changed`            |
| Not changed on `.env` | `envm --status=not_changed_on_env` |
| Only  on `.env`       | `envm --status=only_on_env`        |
| Same                  | `envm --status=same`               |
| Status information    | `envm --info-status`               |
| Dry run               | `envm --dry` / `envm dry`          |
| Help                  | `envm --help` / `envm -h`          |

---