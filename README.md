# Asocial Media

A social media website that probably won't make you any friends.

## Backend

The backend is written in Laravel. The server works as an API backend to the JavaScript frontend and doesn't render any HTML pages itself. It does however serve the files that are created by the Angular frontend.

### Laravel Installation

See [Installation - Laravel - The PHP Framework For Web Artisans](https://laravel.com/docs/8.x/installation) for installation instructions.

#### Using Sail

[Sail](https://laravel.com/docs/8.x/sail) is a "command-line interface for interacting with Laravel's default Docker development environment", this tool makes it easy to start developing with Laravel provided you have docker installed.

For Windows this means that you must have Windows Subsystem for Linux (wsl) activated (note that this is version 2 of wsl not the simpler version 1) and Docker Desktop installed.

To get started you should open a wsl command line prompt in the `backend` folder, VS Code makes this quite easy but you can also just type `wsl` in a normal terminal to open a wsl command line in the same folder. To install Laravel dependencies just run the `install-sail.sh` bash script. After that you can run `./vendor/bin/sail up` to start the backend inside docker.

#### Installing on Windows

You will need PHP and [Composer](https://getcomposer.org/) (Dependency Manager for PHP).

If you have the [Chocolatey](https://chocolatey.org/) package manager installed then you can run `choco install composer` to install both PHP and Composer.

After you have PHP and Composer installed you can run `composer install` in the `backend` folder to install all the needed dependencies. Note that if you get an error like `the requested PHP extension fileinfo is missing from your system` when trying to run that command then you might need to manually edit the `C:\tools\php81\php.ini` file to add the line `extension=php_fileinfo` into it.

## Frontend

The frontend is written in Angular and is a SPA (Single Page Application).

### Angular Installation

See [Angular - Setting up the local environment and workspace](https://angular.io/guide/setup-local) for installation instructions.

In short you should install `npm`, see [Downloading and installing Node.js and npm | npm Docs](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm). It is probably a good idea to use a node version manager such as [coreybutler/nvm-windows: A node.js version management utility for Windows. Ironically written in Go.](https://github.com/coreybutler/nvm-windows) which could be installed using `scoop install nvm` if you are using the `scoop` package manager. With `nvm` you can then run `nvm install lts` (lts = long-term support), then `nvm use lts` and finally `nvm on`.

Once you have `npm` you can install the Angular CLI (`ng`) using `npm install -g @angular/cli` which you can then use in the `frontend` folder to build and serve the project.
