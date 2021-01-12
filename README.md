# CPM Monorepo

### How does it work?
The monorepo was created by mirroring all CPM repositories into one, while maintaining references to the original repositories. During development, we will commit all changes directly to the monorepo. When we're ready to make a release, we will run a command to write all our changes back to all the original repos. We also have the capability to merge changes from the original apps into the monorepo, but for an easier workflow let's make the monorepo the source of truth.

### Getting Started
1. Clone the monorepo locally
2. Make sure all apps and modules in the monorepo have branch `master`
3. When running `git checkout master`. This will chekout branch `master` for **all** apps and modules in the monorepo. Branch `master` contains the head of development.

### Starting work on a new feature
1. Create a new branch in the monorepo with a descriptive name. It has to start with `feature_`. Example `feature_abp_add_force_cs`.
2. Any work done should be committed to the monorepo **only**.
3. Create a PR as soon as possible to get the code reviewed.

### Getting a feature on staging
Assuming have `feature_abp_add_force_cs` branch checked out in the monorepo, we'd need to run `sh bin/split.sh feature_abp_add_force_cs`. This will push changes to each individual repo on branch `feature_abp_add_force_cs`. Then we can go on and deploy `feature_abp_add_force_cs` on any repos we want. For example I could deploy only Provdider App, and Admin App.

### How to build the monorepo
**This is only necessary when we want to create the monorepo from scratch. If the monorepo is already built, we just need to clone it and we can start working.** 

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
