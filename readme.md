# CPM Locations Hierarchy

## Documentation

This project is an API for CircleLink's CPM.


## How to set everything up 

First clone the repository:
	- git clone https://git@bitbucket.org/medadherence/cpm-git.git

Then get the project's dependencies by running this on the command line, in the project's root directory:
	
        - composer update

Create a MySQL database to use with the project.

Create a .env file in the project's root directory, using the .env.example file as a template.

Run these on the command line:

	Generate an app key for encryption
	- php artisan key:generate

	Create DB tables
	- php artisan migrate
