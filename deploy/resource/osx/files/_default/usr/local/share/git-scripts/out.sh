#!/bin/sh -e

branch=$(git rev-parse --abbrev-ref HEAD)
ref=origin/$branch
if (git branch -r --list $ref | wc -w | grep -q 0); then
	ref='master'
fi
git log --pretty=oneline --abbrev-commit --graph $ref..$branch
