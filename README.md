# CPM Monorepo

### How does it work?
The monorepo was created by mirroring all CPM repositories into one, while maintaining references to the original repositories. During development, we will commit all changes directly to the monorepo. When we're ready to make a release, we will run a command to write all our changes back to all the original repos. We also have the capability to merge changes from the original apps into the monorepo, but for an easier workflow let's make the monorepo the source of truth.

### Development workflow
1. Clone the monorepo locally
2. Make sure all apps and modules in the monorepo have branches `development`, and `master`
3. Run `git checkout development`. This will chekout branch `development` for **all** apps and modules in the monorepo. You'd wanna do this to start developing new features. For hotfixes run `git checkout master`

### How to build the monorepo
The scripts that build the monorepo perform various operations like `git pull`, `git fetch`, etc. This means that the contents of the monorepo directory will change. To avoid any issues, we need to copy the build scripts a directory above the monorepo. To do this we can run below command from the root of the monorepo.
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
