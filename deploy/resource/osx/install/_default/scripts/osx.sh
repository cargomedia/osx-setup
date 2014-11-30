if ! (test -e ~/.cargomedia-desktop); then

	# Remove Intros
	rm -rf ~/Documents/About\ Stacks.lpdf
	rm -rf ~/Downloads/About\ Downloads.lpdf

	# Dock size
	defaults write com.apple.dock 'tilesize' -int 42

	# Desktop Background
	osascript -e 'tell application "System Events" to set picture of every desktop to "/Library/Desktop Pictures/Solid Colors/Solid Aqua Graphite.png"'

	# Dock content
	dockutil --no-restart --remove all
	dockutil --no-restart --add '/Applications/iTunes.app'
	dockutil --no-restart --add '/Applications/Calendar.app'
	dockutil --no-restart --add '/Applications/Contacts.app'
	dockutil --no-restart --add '/Applications/System Preferences.app'
	dockutil --no-restart --add '/Applications/Google Chrome.app'
	dockutil --no-restart --add '/Applications/VirtualBox.app'
	dockutil --no-restart --add '/Applications/1Password 5.app'
	dockutil --no-restart --add '/Applications/TextEdit.app'
	dockutil --no-restart --add '/Applications/Utilities/Terminal.app'
	dockutil --no-restart --add '/Applications/PhpStorm.app'
	dockutil --no-restart --add '~/Downloads' --view grid --display stack
	# There's no restart action in dockutil (https://github.com/kcrawford/dockutil/pull/12)
	/usr/bin/killall -HUP cfprefsd
	/usr/bin/killall -HUP Dock

	# Expand save and print panels by default
	defaults write NSGlobalDomain NSNavPanelExpandedStateForSaveMode -bool true
	defaults write NSGlobalDomain PMPrintingExpandedStateForPrint -bool true

	# Disable shadow in screenshots
	defaults write com.apple.screencapture disable-shadow -bool true

	# Finder
	defaults write com.apple.finder ShowStatusBar -bool true
	defaults write com.apple.finder NewWindowTarget -string 'PfHm'
	defaults write com.apple.finder WarnOnEmptyTrash -bool false
	defaults write com.apple.finder FXEnableExtensionChangeWarning -bool false
	defaults write NSGlobalDomain AppleShowAllExtensions -bool true

	# Finder: Use current directory as default search scope
	defaults write com.apple.finder FXDefaultSearchScope -string "SCcf"

	# Trackpad: Tap to click
	defaults write com.apple.driver.AppleBluetoothMultitouch.trackpad Clicking -bool true
	defaults -currentHost write NSGlobalDomain com.apple.mouse.tapBehavior -int 1
	defaults write NSGlobalDomain com.apple.mouse.tapBehavior -int 1

	# Trackpad: swipe between pages with three fingers
	defaults write NSGlobalDomain AppleEnableSwipeNavigateWithScrolls -bool false
	defaults -currentHost write NSGlobalDomain com.apple.trackpad.threeFingerHorizSwipeGesture -int 1
	defaults write com.apple.driver.AppleBluetoothMultitouch.trackpad TrackpadThreeFingerHorizSwipeGesture -int 1

	# Keyboard: Function keys
	defaults write NSGlobalDomain com.apple.keyboard.fnState -bool true

	# Keyboard: Key repeat
	defaults write NSGlobalDomain ApplePressAndHoldEnabled -bool false
	defaults write NSGlobalDomain KeyRepeat -int 0

	# Keyboard: Tab for all controls
	defaults write NSGlobalDomain AppleKeyboardUIMode -int 3

	# Safari
	defaults write com.apple.Safari IncludeDebugMenu -bool true

	# SSH
	sudo perl -pi -e 's/^(\s*SendEnv LANG LC_\*)$/#$1/g' /etc/ssh_config

	touch ~/.cargomedia-desktop
	for app in Finder Dock SystemUIServer; do killall "$app"; done
fi

if [ ! -e ~/Library/QuickLook/QLColorCode.qlgenerator ]; then
	mkdir -p ~/Library/QuickLook
	curl -sL http://qlcolorcode.googlecode.com/files/QLColorCode-2.0.2.tgz | tar xzf -
	mv QLColorCode-2.0.2/QLColorCode.qlgenerator ~/Library/QuickLook/QLColorCode.qlgenerator
fi
