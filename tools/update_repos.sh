#!/usr/bin/env bash

LOCAL_REPO="/mnt/development"
REMOTE_REPOS="Colibri-Embedded/colibri-buildroot\
 Colibri-Embedded/colibri-earlyboot
 Colibri-Embedded/earlyboot-utils.git\
 Colibri-Embedded/FABEmu.git\
 FABtotum/colibri-fabtotum.git\
 FABtotum/colibri-fabui.git"

cd $LOCAL_REPO

for repo in $REMOTE_REPOS; do
   repodir=$(basename $repo .git)
   if [ -d $repodir ]; then
      if [ -d $repodir/.git ]; then
         echo "Updating $LOCAL_REPO/$repodir..."
         cd $repodir
         git pull --ff-only --no-progress
         cd $LOCAL_REPO
      else
         echo "Skipping $LOCAL_REPO/$repodir: not a Git repository >,<"
      fi
   else
      echo "Cloning $repo..."
      git clone -q https://github.com/$repo
   fi
done

# Just to be sure...
sudo chown -R developer:developer .
