# Entity Definition Update Manager


Provides developers with a class to perform automatic entity updates.

Use only in development environment. Not to use on production websites.



### Support for automatic entity updates has been removed

[https://www.drupal.org/node/3034742](https://www.drupal.org/node/3034742)

User deprecated function: `EntityDefinitionUpdateManagerInterface::applyUpdates()` was deprecated in **Drupal** **8.7.0** and was removed before **Drupal 9.0.0**.


## How to Use Entity Definition Update Manager Class

Customized class for the entity definition update manager.

### Require the Package in your root composer.json file

```
  "vardot/entity-definition-update-manager": "~1.0"
```

### Or require the Package in your Project with a command

```
$ composer require vardot/entity-definition-update-manager:~1.0
```

### 1. Add Needed Namespace

Add the following name space at in custom modules or custom installation profiles.

```
use Vardot\Entity\EntityDefinitionUpdateManager;
```

### 2. Do Any Type of Configuration Import or Updates

Import or update configs in hook install or hook update, or any post install or post update.

### 3. Run the Class Resolver for the Instance From the Definition Class

```
  // Entity updates to clear up any mismatched entity and/or field definitions
  // And Fix changes were detected in the entity type and field definitions.
  \Drupal::classResolver()
    ->getInstanceFromDefinition(EntityDefinitionUpdateManager::class)
    ->applyUpdates();
```

## Example Use On Installs

**Varbase Core** is important number of managed configurations on install. It needed to update entity definitions after that.

Have a look at the **`varbase_core_install`** hook function

[https://git.drupalcode.org/project/varbase\_core/-/blob/9.0.x/varbase\_core.install\#L77](https://git.drupalcode.org/project/varbase_core/-/blob/9.0.x/varbase_core.install#L77)


## Example Use On Updates

**Varbase API** in some point needed to update configurations in a hook update. It needed to update entity definitions after that. Which did not work without entity definition update.


Have a look at the **`varbase_api_update_8702`** hook function

[https://git.drupalcode.org/project/varbase\_api/-/blob/9.0.x/varbase\_api.install\#L159](https://git.drupalcode.org/project/varbase_api/-/blob/9.0.x/varbase_api.install#L159)


## When to Use and When Not to Use?


**Do not use** when the import/update of configs works in the normal way.

If all configs are in the **`config/install`** and no issues on install.



**Use** when custom managed configs are been imported or updated in a custom order, And custom actions or changes in between imports are being involved.

If the module or profile has number of optional or managed configs. Which located in **`config/optional`** or **`config/managed`** or any other physical locations. Then they are imported or updated with custom Drupal Config Factory or Drupal Install Factory.



**Must run at least ones** at the end of each installation steps for installation profiles like  [**Varbase**](https://www.drupal.org/project/varbase), [**Vardoc**](https://www.drupal.org/project/vardoc), [**Uber Publisher**](https://www.drupal.org/project/uber_publisher).





