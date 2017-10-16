# CarePlan Manager

## Setting up 

1. First clone the repository: git clone https://git@bitbucket.org/medadherence/cpm-git.git
2. Make necessary local hosting configurations (Homestead/Valet)
3. Run `composer install` in the project's root directory
4. Generate App Key `php artisan key:generate`
5. Run database migrations `php artisan migrate` (ask a team member for a copy of staging DB)
6. Run database seeders with `php artisan db:seed`
7. Set environment variables as structured in the /.env.example file
8. Fire up the server and you'r egood to go!
