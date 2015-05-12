# CPM Locations Hierarchy

## Documentation

This project is an API to handle Locations for CircleLink's CPM.


## How to set everything up 

First clone the repository:
	- git clone https://michalisantoniou@bitbucket.org/michalisantoniou/locations-laravel.git

Then get the project's dependencies by running this on the command line, in the project's root directory:
	
        - composer update

Create a MySQL database to use with the project.

Create a .env file in the project's root directory, using the .env.example file as a template.

Run these on the command line:

	Generate an app key for encryption
	- php artisan key:generate

	Create DB tables
	- php artisan migrate


## Handling encryption/decryption
After running artisan key:generate, an APP_KEY is generated and stored in the .env file. This APP_KEY will be used to encrypt messages sent from the API.
To send an encrypted message from the API, use `Crypt::encrypt('Your message here')`. 
Remember to import Crypt into your file using `use Illuminate\Support\Facades\Crypt;` at the beginning of your file.

### Setting up WordPress to decrypt messages
I've pulled in `"illuminate/encryption"` (the same package laravel uses) through composer on the root directory in our CPM repository.
The dependencies are included in the repository, so there's no need to do `composer update` upon setting up the project.

So, here are the variables that need to be set in global-config.php:
	'locations_app_key' => '',     // App specific api key. At the moment use `php artisan api-key:generate` to generate a new key for a new app
	'locations_api_path' => '',    // Api endpoint url
	'api_encryption_key' => '',    // Paste the APP_KEY from Laravel's .env file here




