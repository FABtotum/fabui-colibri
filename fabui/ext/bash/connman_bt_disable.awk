#!/usr/bin/env awk
# filename: ~/connman_bt_disable.awk

BEGIN {
    in_bt_section=0;
    bt_disabled=0;
}

in_bt_section == 1 {
    if ($0 ~ /^Enable[[:space:]]*=/) {
        print "Enable=false";
        in_bt_section=0;
        next
    }
}

/^\[Bluetooth\]/ {
    in_bt_section=1;
}

{ print; }
