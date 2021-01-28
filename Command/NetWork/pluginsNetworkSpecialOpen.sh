#!/bin/bash
#network for special type
ConnectType=$1
#network for special server address
ServerAddress=$2
#network for special username
UserName=$3
#network for special pwd
PassWord=$4
#remote address
RemoteAddress=$5
#vpn name
VpnName=$6
#wan eth?
WanPhysical=$7
#local wan ip
WanIp=$8
#lan gateway
LanGateway=$9
#nat type
NatType=${10}

uci delete network.special
uci commit network
sleep 1

#delete firewall last zone
uci delete firewall.@zone[-1]
uci commit firewall
sleep 1

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

iptables -t nat -A POSTROUTING -s $LanGateway/24 -o $WanPhysical -j SNAT --to $WanIp
iptables -t nat -A POSTROUTING -s $LanGateway/24 -o $NatType -j SNAT --to $RemoteAddress
sleep 1

#add firewall zone for special
uci add firewall zone
uci set firewall.@zone[-1].name='special'
uci set firewall.@zone[-1].input='ACCEPT'
uci set firewall.@zone[-1].forward='ACCEPT'
uci set firewall.@zone[-1].output='ACCEPT'
uci set firewall.@zone[-1].network='special'

uci commit firewall
sleep 2

/etc/init.d/network reload
sleep 2

ifup special
sleep 5

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
check_connect www.google.com
#
##restart network and firewall
##/etc/init.d/network reload
##/etc/init.d/firewall reload
##add forwarding for special
