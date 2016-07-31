#!/bin/sh

if [ ! -n "$WERCKER_GH_PAGES_TOKEN" ]; then
  fail "Missing 'token' for pushing to Github"
fi

if [ -n "$WERCKER_GH_PAGES_REPO" ]; then
  repo="$WERCKER_GH_PAGES_REPO"
elif [ 'github.com' == "$WERCKER_GIT_DOMAIN" ]; then
  repo="$WERCKER_GIT_OWNER/$WERCKER_GIT_REPOSITORY"
else
  fail "Missing 'repo' for distinguishing target"
fi

info "Using '$repo'"

branch="gh-pages"
if [[ "$repo" =~ $WERCKER_GIT_OWNER\/$WERCKER_GIT_OWNER\.github\.(io|com)$ ]]; then
  branch="master"
fi
info "Using branch '$branch'"

if [ -d "$WERCKER_GH_PAGES_PATH" ]; then
  info "Using '$WERCKER_GH_PAGES_PATH' as root dir instead of git root"
  cd $WERCKER_GH_PAGES_PATH
fi

if [ -n $WERCKER_GH_PAGES_DOMAIN ]; then
  info "Generating CNAME for '$WERCKER_GH_PAGES_DOMAIN'"
  echo $WERCKER_GH_PAGES_DOMAIN > CNAME
fi

remote="https://$WERCKER_GH_PAGES_TOKEN@github.com/$repo.git"

if ! type "git" > /dev/null; then
  apt-get update
  apt-get install -y git-core
fi

rm -rf .git
git init
git config user.email "pleasemailus@wercker.com"
git config user.name "werckerbot"
git remote add origin "$remote"
git fetch origin
git branch --track master origin/master
git add -A
git commit -m "Deploy from $WERCKER_STARTED_BY"
result="$(git push $remote master:$branch)"

if [[ $? -ne 0 ]]; then
  warning "$result"
  fail "Failed deploying to Github Pages"
else
  success "Deployed to Github Pages"
fi
