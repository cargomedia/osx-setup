#!/usr/bin/env ruby
require 'shellwords'
require 'readline'

def exec(command)
  `#{command}`
end

def exec!(command)
  result = exec(command)
  raise "Command `#{command}` failed" unless $? == 0
  result
end

def remote
  exec!('git remote | grep upstream').strip! || 'origin'
end

def git_changelog_pullrequests(from, to)
  format = '%s: %b'
  changelog = exec!("git log #{from}..#{to} --reverse --pretty=#{format.shellescape}")

  find = /^Merge pull request (#\d+) from (.+?)\/(.+?): (.*)$/
  replace = "- \\4 (\\1, @\\2)"
  changelog.split("\n").select { |l| l =~ find }.map { |l| l.chomp.sub(find, replace) }.join("\n")
end

def create_newtag
  exec! "git fetch --quiet #{remote}"
  exec! "git fetch --quiet --tags #{remote}"
  latest_commit = exec("git rev-parse #{remote}/master").chop
  latest_tagged_commit = exec('git rev-list --tags --max-count=1 2>/dev/null').chop
  latest_tag = exec("git describe --tags #{latest_tagged_commit} 2>/dev/null").chop

  changelog = git_changelog_pullrequests(latest_tag, latest_commit)
  content = "# You're about to create new tag with the following changes:\n"
  if changelog.empty?
    content += '# (No merged pull-requests)'
  else
    content += changelog
  end
  message = exec! "echo #{Shellwords.escape(content)} | vipe"
  puts message
  message = message.split("\n").reject { |line| /^\s*#/.match(line) }.join("\n")

  tag_hint = "(previous #{latest_tag})"
  tag_hint.clear if latest_tag.empty?
  newtag_name = Readline.readline("Please provide new tag name #{tag_hint}: ", false)

  exec!("git tag #{newtag_name.shellescape} #{latest_commit.shellescape} -m #{message.shellescape}")
  exec!("git push --quiet #{remote} --tags")
end

create_newtag
