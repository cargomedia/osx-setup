#!/usr/bin/expect

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
