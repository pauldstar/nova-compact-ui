# Compact UI

UI to minimise scrolling and clicking, with reactive (ajax) fields.

## Installation
1. Navigate to your Laravel Nova project root, and `cd nova-components`. Create the directory if non-existent.

2. In the `./nova-components` directory, add this repo as a git submodule:
    ```$xslt
    git submodule add https://pauldstar@bitbucket.org/pauldstar/nova-compact-ui.git
    ```

3. Register the tool in `./app/Providers/NovaServiceProvider`:
    ```$xslt
    public function tools()
    {
        return [
            \Pauldstar\NovaCompactUi\NovaCompactUi::make(),
        ];
    }
    ```

4. Add package to composer.json in project root:
    ```$xslt
    {
        "require": {
            "pauldstar/nova-compact-ui": "9999999-dev",
        },
        "repositories": [
            {
                "type": "path",
                "url": "./nova-components/nova-compact-ui"
            }
        ]
    }
    ```

   Then run `composer update`

   You have now successfully overridden Nova's Vue components. Now to override some of Nova's Blade templates.

5. Skip to step 6 if the `./resources/views/vendor/nova` directory exists WITH customisations. Else, from project root, run:
    * `php artisan nova:publish` to publish Nova's views into `./resources/views/vendor/nova`.
    * `cp -r nova-components/nova-compact-ui/resources/views/vendor/nova resources/views/vendor`

6. If you already have a `./resources/views/vendor/nova` directory WITH some customisations, to prevent any loss, you should consider manually cherry picking code from view files in `./nova-components/nova-compact-ui/resources/views/vendor/nova` into their respective directories in `./resources/views/vendor/nova`. Ensure you have the same structure in both 'nova' view directories since Nova (at this moment of writing) only overrides Blade templates in view directories organised like this:
    ```$xslt
    ├───nova
    │   ├───auth
    │   ├───dashboard
    │   ├───partials
    │   ├───resources
    │   │
    │   └───layout.blade.php
    ```

7. That's it! Your Nova website should now look compact. If you'd like to contribute to this project, you would need to:
    * Ensure view files are up-to-date, and saved in correct directories.
    * Compile js and scss assets by running one of the following commands from project root (with package.json):
    * Development: `npm run dev`
    * For Ongoing Development: `npm run watch`
    * Production: `npm run prod`
