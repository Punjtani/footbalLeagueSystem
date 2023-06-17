# SportsMania-admin
SportsMania-admin is a fully configurable admin panel for sports statistics to manage every team sport.

## Vision
To make every sports available in people's hand worldwide.

## Technologies
1. [PHP 7.4](https://www.php.net/manual/en/preface.php)
2. [Laravel 7.x](https://laravel.com/docs/6.x)
3. [Postgres 12.3](https://www.postgresql.org/download/windows/)
4. [Yajra Datatable 10](https://yajrabox.com/docs/laravel-datatables/master/installation)
5. [AWS Laravel](https://github.com/aws/aws-sdk-php-laravel)
6. [Intervention image Library](https://github.com/Intervention/image)
7. [Audit Logs](https://github.com/owen-it/laravel-auditing)
8. [Lint](https://github.com/tightenco/tlint)

## Git Structure
* Master is the most stable branch.
* Master branch will be locked, merge request is to be generated for adding new features or resolving bugs.
* New branches are to be created from master.

### Branch Name Convention
* Branches for Features will be prefixed with feature e.g (feature/login).
* Branches for Bugs will be prefixed with bugs e.g (bugs/login).
* Branches for a UI will be prefixed with UI e.g (UI/login-page).

## Notes for developers
* Always check lint before creating merge request as if there is a lint error, merge request will not be entertained.

## Installations
You need PHP 7.4 or above for this installation so make sure you have it.

Install PHP Composer on your system.

Then install Laravel with following command. 

`composer global require laravel/installer`

Simply run `composer install` and you are ready to go.

Sometimes [AWS Laravel](https://github.com/aws/aws-sdk-php-laravel) doesn't upload picture and throws exception for SSL certification on localhost, follow this [Link](https://stackoverflow.com/questions/29822686/curl-error-60-ssl-certificate-unable-to-get-local-issuer-certificate), it might come in handy.
