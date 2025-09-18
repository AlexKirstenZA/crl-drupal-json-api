# CRL Drupal JSON API technical exercise

An example headless Drupal backend, that imports data from the
[Free Dictionary API](https://dictionaryapi.dev/), and then exposes the content via JSON:API.

## Prerequisites

- [DDEV](https://ddev.readthedocs.io/en/stable/users/install/) (local containerised development environment)
- [Make](https://www.gnu.org/software/make/) (for using Makefile commands)

## Quick Start

The easiest way to get started is using the provided Makefile:

```bash
make install
```

This will:
- Start DDEV containers
- Install Composer dependencies
- Install Drupal with minimal profile
- Create local settings file
- Generate admin login link

You can then follow the admin link output to the console, to login to Drupal:

```bash
https://crl-drupal-json-api.ddev.site/user/reset/1/1758188176/Sp6Qeek0cC6EBYOEFuLpO2e80jbGnp6nAWeC7CQPWEs/login
```

## Useful Commands

### DDEV Management
```bash
make start          # Start DDEV
make stop           # Stop DDEV
make remove         # Delete DDEV project
```

### Drupal Commands
```bash
ddev drush cr       # Clear cache
make login          # Generate admin login link
```

## Project Structure

- `web/` - Drupal docroot
- `web/modules/custom/` - Custom modules
  - `dictionary_import/` - Custom dictionary import functionality
- `config/sync/` - Configuration management files
- `.ddev/` - DDEV configuration
- `vendor/` - Composer dependencies

## Dictionary Import Module

Located in `web/modules/custom/dictionary_import/`, this module provides a Drush command for importing dictionary data.

See example command usage to import the word "happy":

```bash
ddev drush dictionary_entry:import happy

# OR using the command alias:

ddev drush dei happy
```

You can now view a JSON:API response containing the new node data, by filtering using the word "happy":

```bash
https://crl-drupal-json-api.ddev.site/jsonapi/node/dictionary_entry?filter[field_word]=happy
```

## TODO:

Outstanding features that haven't been included:

- Further test cases
- PHPStan configuration
- NextJS cache revalidation logic (i.e calling a revalidation endpoint when content is updated)
- Config split / config ignore per environment
- Tighten up CORS config (not currently production ready)

## Troubleshooting

### Clear All Caches
```bash
ddev drush cr
```

### Reset Local Environment
```bash
make remove
make install
```

### Check DDEV Status
```bash
ddev describe
```

### View Logs
```bash
ddev logs
```

## Development Workflow

### Configuration Management

Export configuration changes:
```bash
make config-export
```

Import configuration:
```bash
make config-import
```

### Code Quality

Check coding standards:
```bash
make coding-standards
```

Fix coding standards automatically:
```bash
make coding-standards-fix
```

### Testing

Run unit tests for custom modules:
```bash
make unit-tests
```
