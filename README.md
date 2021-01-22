# CPM Monorepo

### How does it work?
The monorepo is created by mirroring all CPM repositories into one, while maintaining references to the original repositories. Once we build the monorepo (see section "How to build the monorepo from scratch" below for how), we commit the outcome, and all further work happens on the monorepo. During development, we will commit all changes directly to the monorepo, and open a PR. When we're ready to make a release, we will run a command to write all our changes back to all the original repos. We also have the capability to merge changes from the original apps into the monorepo, but for an easier workflow let's make the monorepo the source of truth. The monorepo allows us to work with the entire CPM ecosystem in one state. This means that we should not expect any surprises in "Admin App" if say we deploy "Provider App" after having made changes to "Customer Module". Going forward, any module CLH maintains should live in the monorepo. This includes all languages. Each module's repository will be a read only instance of tags and versioned branches.

### New workflow for pulling CLH Modules
Instead of pulling modules using composer, we'll just copy them from the mono-repo as a step in the release command. To do that create a `monorepo-modules.txt` file in the root of your app, and include one module directory name per line as it appears in `cpm-monorepo/modules`. When we run the split command, the monorepo will copy the modules in directory `CircleLinkHealth` in the root of your project.

```
// monorepo-modules.txt

CcdaParser
CcmBilling
CerberusGatekeeper
ConditionCodeLookup
Core
CpmAdmin
CpmMigrations
Customer
Eligibility
NurseInvoices
PatientApi
PdfService
Raygun
Revisionable
SamlSp
SelfEnrollment
SharedModels
SharedVueComponents
SqlViews
Synonyms
Revisionable
TimeTracking
TwilioIntegration
TwoFA
```

### Getting Started
1. Clone the monorepo locally
2. Make sure all apps and modules in the monorepo have branch `master`
3. When running `git checkout master`. This will chekout branch `master` for **all** apps and modules in the monorepo. Branch `master` contains the head of development.
4. Add .env files to all apps in `apps/`
5. Run `sh bin/local-dev/install.sh`

### Starting work on a new feature
1. Create a new branch in the monorepo with a descriptive name. It has to start with `feature_`. Example `feature_abp_add_force_cs`.
2. Any work done should be committed to the monorepo **only**.
3. Create a PR as soon as possible to get the code reviewed.

### Getting a feature on staging
Assuming have `feature_abp_add_force_cs` branch checked out in the monorepo, we'd need to run `sh bin/split.sh feature_abp_add_force_cs`. This will push changes to each individual repo on branch `feature_abp_add_force_cs`. Then we can go on and deploy `feature_abp_add_force_cs` on any repos we want. For example I could deploy only Provdider App, and Admin App.

### Available Scripts
#### Run a shell command in an app
```bash
sh bin/run.sh admin-app "php artisan module:make-migration TestMigration CpmMigrations"
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
rm apps/* modules/*
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
