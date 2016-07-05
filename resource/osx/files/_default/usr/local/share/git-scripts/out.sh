#!/bin/sh -e

branch=$(git rev-parse --abbrev-ref @)
ref=$(git rev-parse --abbrev-ref @{push} 2>/dev/null)
if [[ $? -ne 0 ]]; then
  ref=origin/$branch
fi
git log --pretty=oneline --abbrev-commit --graph $ref..$branch
