## About Project

This project tries to recreate the backend side TSM functionality by generating the necessary information to popularize the AppData.lua file that acts as an information collection from the Tauri Server auction house.

It is a project based on Laravel, since it is planned to integrate more complex functionalities. Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Quick Start

```
git clone https://github.com/Tauri-WoW-Community-Devs/TauriTSMAppServer
cd TauriTSMAppServer
composer install
cp .env.example .env
```
You will need to configure the `api_key` and `api_secret` keys at the end of this file.
Finally, you can use the next command:
```
php artisan serve
```
To generate the necessary file you will have to execute the following command
```
php artisan get:auctions-data
```
You can now find this file at `storage/app/public/AppData.lua`

## Contributing

If you would like to contribute to this project it would be great if you would follow certain guidelines and standards that will help maintain a clean code. You can get an example of them [here](https://guidelines.spatie.be/code-style/laravel-php#general-php-rules)
There is also a `.php-cs`  file in the root of the project that can help format the code automatically with tools like php-cs.
