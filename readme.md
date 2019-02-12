###### Production [![Deployment status from DeployBot](https://circlelink-health.deploybot.com/badge/02267418031917/97613.svg)](https://circlelink-health.deploybot.com/) Worker [![Deployment status from DeployBot](https://circlelink-health.deploybot.com/badge/34534836063834/97615.svg)](https://circlelink-health.deploybot.com/) Staging [![Deployment status from DeployBot](https://circlelink-health.deploybot.com/badge/02267418031917/97599.svg)](https://circlelink-health.deploybot.com/)

## Setting up 

1. Clone the repository by issuing command `git clone [repo url]`
2. If it's a local environment, make necessary local hosting configurations. Using Homestead or Valet is recommended.
3. Run `composer install` in the project's root directory to install PHP dependencies.
4. Generate App Key `php artisan key:generate`
5. Run database migrations `php artisan migrate` (alternatively, you may ask a team member for a copy of staging DB)
6. Run database seeders with `php artisan db:seed`
7. Duplicate the environment variables exampled file using command `cp .env.example .env`, and fill in using your environment's variables.
8. Run `npm install` to install npm dependencies.
9. Run `npm run dev` to build js/css assets.
