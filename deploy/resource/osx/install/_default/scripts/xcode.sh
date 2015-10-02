function is_xcode_installed {
    if (xcode-select -p &>/dev/null); then
     return 0
    else
     return 1
    fi
}

if ( ! is_xcode_installed ) ; then
    xcode-select --install
fi

if ( is_xcode_installed ) ; then

    XCODE_ACCEPT_COMMAND=$(cat <<EOS
    set timeout 5
    spawn sudo xcodebuild -license

    expect {
        "By typing 'agree' you are agreeing" {
            send "agree\r\n"
        }
        "Software License Agreements Press 'space' for more, or 'q' to quit" {
            send " ";
            exp_continue;
        }
        timeout {
            send_user "\nTimeout 2\n";
            exit 1
        }
    }

    expect {
        timeout {
            send_user "\nFailed\n";
            exit 1
        }
    }
EOS)

    expect -c "${XCODE_ACCEPT_COMMAND}"

fi
