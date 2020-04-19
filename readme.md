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

### Debug Twilio calls

1. Clone `app-cpm-caller`.
2. Run `composer install` and make sure that app is accessible from a browser.
3. In `app-cpm-caller`, You will need these env variables at least: 
    - APP_KEY (needs to be same as app-cpm-web)
    - APP_URL
    - DB config (same as `app-cpm-web`)
    - TWILIO_ENABLED=true
    - TWILIO_SID (get from twilio console, CPM Test sub-account)
    - TWILIO_FROM (CPM-Test sub-account, phone numbers)
    - TWIML_APP_SID (CPM-Test sub-account, the phone number should be connected to Local Development APP)
4. In `app-cpm-caller`, run `valet share`.
Copy the `https` url and set in Twilio Console, Local Development under Voice.
Make sure you click on `show optional settings` and set it on both Request URL and Status Callback URL.
5. In `app-cpm-web`, set `CPM_CALLER_URL` to the same `https` url you set above.
6. In `app-cpm-web`, run `valet share`.
Browse CPM through the `https` url (chrome and twilio need be running from https in order to allow WebRTC).
    

