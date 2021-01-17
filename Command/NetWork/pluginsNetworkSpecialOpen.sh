#!/bin/bash
#network for special type
ConnectType=$1
#network for special server address
ServerAddress=$2
#network for special username
UserName=$3
#network for special pwd
PassWord=$4

#add network interface
uci set network.special='interface'
uci set network.special.proto="$ConnectType"
uci set network.special.server="$ServerAddress"
uci set network.special.username="$UserName"
uci set network.special.password="$PassWord"
uci set network.special.ipv6='auto'

#uci set network.special.ifname="tun0"
#uci set network.special.mtu="1410"
#uci set network.special.keepalive="60"
#uci set network.special.demand="30"
#uci set network.special.pppd_options="remotename $UserName"

uci commit network
sleep 2

#add firewall zone for special
uci add firewall zone
uci set firewall.@zone[-1].name='special'
uci set firewall.@zone[-1].input='ACCEPT'
uci set firewall.@zone[-1].forward='ACCEPT'
uci set firewall.@zone[-1].output='ACCEPT'
uci set firewall.@zone[-1].network='special'

uci commit firewall
sleep 2

/etc/init.d/network restart
#check network special connect
function check_connect()
{
    ping -c 1 $1 >/dev/null
    if [ $? -eq 0 ];then
        echo success
    else
        echo failed
    fi
}
sleep 20
check_connect google.hk
#
##restart network and firewall
##/etc/init.d/network reload
##/etc/init.d/firewall reload
##add forwarding for special
##ifup special