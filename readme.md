###### Production [![Deployment status from DeployBot](https://circlelink-health.deploybot.com/badge/02267418031917/97613.svg)](http://deploybot.com) Worker [![Deployment status from DeployBot](https://circlelink-health.deploybot.com/badge/34534836063834/97615.svg)](http://deploybot.com) Staging [![Deployment status from DeployBot](https://circlelink-health.deploybot.com/badge/02267418031917/97599.svg)](http://deploybot.com)

## Setting up 

1. First clone the repository: git clone https://git@bitbucket.org/medadherence/cpm-git.git
2. Make necessary local hosting configurations (Homestead/Valet)
3. Run `composer install` in the project's root directory
4. Generate App Key `php artisan key:generate`
5. Run database migrations `php artisan migrate` (ask a team member for a copy of staging DB)
6. Run database seeders with `php artisan db:seed`
7. Set environment variables as structured in the /.env.example file
8. Run `npm install` to install npm dependencies.
9. Run `npm run dev` to build js/css assets.
   
   Fire up the server and you're good to go!
