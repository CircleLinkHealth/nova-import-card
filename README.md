# CPM Monorepo

### How to build the monorepo
The scripts that build the monorepo perform various operations like `git pull`, `git fetch`, etc. This means that the contents of the monorepo directory will change. To avoid any issues, we need to copy the build scripts a directory above the monorepo. To do this we can run below command from the root of the monorepo.
```bash
cp -rf bin/monorepo/build/* ../
```
Now that we've copied the necessary files, we can build the mono repo by running below from the monorepo root dir
```bash
sh ../build.sh
```