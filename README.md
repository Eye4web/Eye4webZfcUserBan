Eye4webZfcUserBan
==========
This module will allow you to ban a user by setting a flag in your database.

Installation
------------
#### With composer

1. Add this project composer.json:

    ```json
    "require": {
        "eye4web/eye4web-zfc-user-ban": "dev-master"
    }
    ```

2. Now tell composer to download the module by running the command:

    ```bash
    $ php composer.phar update
    ```

3. Enable it in your `application.config.php` file.

    ```php
    <?php
    return array(
        'modules' => array(
            // ...
            'Eye4web\ZfcUser\Ban'
        ),
        // ...
    );
    ```

4. Make your user entity implement `Eye4web\ZfcUser\Ban\UserBannableInterface`

Change 'banned' landing page
------------
If you want to use your own view, for the user banned landing page, all you have to is create a new view file in this
path: ´view/eye4web/zfc-user/ban/index.phtml`.
Alternatively you can overwrite the route `eye4web_zfcuser_ban` and point it to your own controller.
