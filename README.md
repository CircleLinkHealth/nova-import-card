# CPM Monorepo

### Deployments
[![Provider App - Staging](https://github.com/CircleLinkHealth/cpm-monorepo/actions/workflows/provider.yml/badge.svg)](https://github.com/CircleLinkHealth/cpm-monorepo/actions/workflows/provider.yml)

### How does it work?
The monorepo is created by mirroring all CPM repositories into one, while maintaining references to the original repositories. Once we build the monorepo (see section "How to build the monorepo from scratch" below for how), we commit the outcome, and all further work happens on the monorepo. During development, we will commit all changes directly to the monorepo, and open a PR. When we're ready to make a release, we will run a command to write all our changes back to all the original repos. We also have the capability to merge changes from the original apps into the monorepo, but for an easier workflow let's make the monorepo the source of truth. The monorepo allows us to work with the entire CPM ecosystem in one state. This means that we should not expect any surprises in "Admin App" if say we deploy "Provider App" after having made changes to "Customer Module". Going forward, any module CLH maintains should live in the monorepo. This includes all languages. Each module's repository will be a read only instance of tags and versioned branches.

**Make sure you only have the monorepo in PHPStorm's VCS settings otherwise things can go seriously wrong.**

### Prerequisites
1. PHP version v7.4
2. MySQL
3. Composer
4. Laravel Valet

### Install Laravel Valet
1. Add the `~/.composer/vendor/bin` directory to your system path if is doesn't already exist;
   - `export PATH="$HOME/.composer/vendor/bin:$PATH"`
2. Run `composer global require laravel/valet`
3. Run `valet install`

### Local Database Setup
1. Login to mysql console with: mysql -u root -p
2. Then execute the following SQL commands:
   - `CREATE DATABASE cpm;`
   - `GRANT ALL PRIVILEGES ON cpm.* TO 'root'@'localhost';`
   - `FLUSH PRIVILEGES;`

### Local Project Setup
1. Clone the monorepo locally
2. Make sure all apps and modules in the monorepo have branch `master`
3. When running `git checkout master`. This will chekout branch `master` for **all** apps and modules in the monorepo. Branch `master` contains the head of development.
4. In `apps/provider-app` update the `DB_USERNAME` and `DB_PASSWORD` in the `.env.testing` file to that of your local MySQL user (root).
5. Run `sh bin/local-dev/install.sh`. This will also add .env files in each app based on .env.example files. **Please make sure to adjust these vars for your local environment while the script runs**.

### Starting work on a new feature
1. Create a new branch in the monorepo with a descriptive name, using command below.
```bash
sh bin/git/new-feature.sh abp_add_force_cs
```
2. Any work done should be committed to the monorepo **only**.
3. Create a PR as soon as possible to get the code reviewed.

### Adding an existing module
The instructions below assume we want to add module LaravelScheduleMonitor.
1. Manually copy/paste the module in `modules/LaravelScheduleMonitor`. One way is to download the master branch from github and add that.
2. Add the remote refs to .gitconfig
    ```bash
    [remote "LaravelScheduleMonitor"]
        url = git@github.com:CircleLinkHealth/laravel-schedule-monitor.git
        fetch = +refs/heads/*:refs/remotes/LaravelScheduleMonitor/*
    ```
3. Add update helpers to merge-from-individual-repos.txt
   ```bash
    git merge --strategy recursive --strategy-option subtree=modules/LaravelScheduleMonitor LaravelScheduleMonitor/master
    git merge --strategy recursive --strategy-option subtree=modules/LaravelScheduleMonitor LaravelScheduleMonitor/development
    ```
4. `sh bin/local-dev/setup-gitconfig.sh`
4. Fetch all the latest changes. `git fetch --all --no-tags`
5. The first time we need to merge using `--allow-unrelated-histories`. See command `git merge --strategy recursive --strategy-option subtree=modules/LaravelScheduleMonitor LaravelScheduleMonitor/master --allow-unrelated-histories`



## Deployments
We use a modified version of vapor cli that lives in the monorepo. Use the commands below to setup an alias for easy use.
```bash
# Run these commands in the monorepo root directory
echo 'alias monovapor="'$PWD'/modules/VaporCli/vapor"' >> ~/.zshrc 
source ~/.zshrc
```
Now to deploy an app, we need to run the deploy command from the app's directory
```bash
cd apps/provider-app
monovapor deploy staging staging
```

### Creating a Release
Creating a release creates branches and tags in all single repos. There's 2 steps into creating a release:
1. Run the split command to create our release branch on all repos.
2. Run the release command to create tags on all repos. 

Both should run on the same branch. The branch has to start with `release-`. For example `release-new-billing`, `release-v2.5-dev`

#### Split Command
Splitting the monorepo means writing to branches in the original single repos. 
1. Create a new branch in the monorepo and push it to git remote (or start with a branch already pushed to remote)
```bash
git checkout -b feature_abp_add_force_cs
git push --set-upstream origin feature_abp_add_force_cs
git push
```
2. Run split command. This will push changes to each individual repo on branch `feature_abp_add_force_cs`.
```bash
sh bin/split.sh feature_abp_add_force_cs
```
From here we have the option to deploy `feature_abp_add_force_cs` form any of the single repos using GitHub Actions, or a manual vapor command, etc. For example I could deploy only Provider App, and Admin App.

#### Release Command
1. Create a new branch in the monorepo that starts with `release-` and push it to git remote (or start with a branch already pushed to remote)
```bash
git checkout -b release-feature_abp_add_force_cs
git push --set-upstream origin release-feature_abp_add_force_cs
git push
sh bin/release.sh release-feature_abp_add_force_cs my_version_tag
```
2. Run the release command. 
```bash
sh bin/release.sh release-release_feature_billing-revamp_mono billing_monorepo_test_v4
```

### Available Scripts
#### Run a shell command in an app
```bash
sh bin/run.sh superadmin-app "php artisan module:make-migration TestMigration CpmMigrations"
```
### Merging from separate repositories into the monorepo
```bash
# Assuming I want to merge provider-app/master into the monorepo's master branch

git fetch --all --no-tags
# Make sure I'm on the master branch
git checkout master
# Make sure I have the latest version
git pull
# Make a new branch that I'd make a PR with
git checkout -b provider_master
# Merge provider-app/master in directory apps/provider-app
git merge --strategy recursive --strategy-option subtree=apps/provider-app provider-app/master 
```

### How to build the monorepo from scratch
This is like a hard "reset". What it does is it will erase everything, and re-build the monorepo from scratch by mirroring each repository. 

Building from scratch **has to** happen on the `master` branch.

Firstly, delete all apps and modules. 
```bash
rm -rf apps/* modules/*
```

After, uncomment all lines in `repos.txt` by removing the leading `#`, and **commit and push** all changes.

After, delete the repo from your local, and clone it again. This step is a mandatory CLH best practice to test the monorepo works from scratch, and to avoid any "git mess-ups" and errors in case we are renaming any remotes, or attempting to re-add existing remotes.

```bash
# go a dir up and delete the repo
cd .. && rm -rf cpm-monorepo

# clone monorepo
git clone git@github.com:CircleLinkHealth/cpm-monorepo.git 
```

The scripts that build the monorepo perform various operations like `git pull`, `git fetch`, `git checkout all-the-repos-we-give-it`etc. This means that the contents of the monorepo directory will change. To avoid any issues, we need to copy the build scripts a directory above the monorepo, by running below

```bash
cp -rf bin/monorepo/build/* ../
```
Now that we've copied the necessary files, we can build the mono repo by running below from the monorepo root dir
```bash
sh ../build.sh
```
 
### Credits
The monorepo is powered by
- https://github.com/hraban/tomono
- https://github.com/splitsh/lite
