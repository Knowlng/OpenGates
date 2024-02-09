#!/bin/sh

# OpenNebula endpoint
CENDPOINT="EnterYourOpenNebulaURLEndpointHere"

# VM credentials
CUSER=$3
CPASS=$4

# Succesfull VM creation prefix
prefix="VM ID:"

# Enable connections Without VPN
NIC="VNET2:NETWORK_UNAME=oneadmin:SECURITY_GROUPS=0"

# VM resources
CUSTOM_MEMORY=$1 
CUSTOM_DISK_SIZE=$2

# Create VM and capture the output into a variable
CVMREZ=$(onetemplate instantiate "debian12" --user $CUSER --password $CPASS --endpoint $CENDPOINT --memory $CUSTOM_MEMORY --disk "oneadmin[3612]:size="$CUSTOM_DISK_SIZE --nic $NIC --name "Customer VM" --context "NETWORK=YES,ROOT_PASSWORD=$6,SWAP_SIZE=512,USER_NAME=$5,USER_PASSWORD=$6")

# Check if the VM was created successfully
case $CVMREZ in
    "$prefix"*)
	VM_ID=$(echo $CVMREZ | cut -d ' ' -f 3)
	echo $VM_ID
        ;;
    *)
    echo "Error creating VM, please contact support!"
    exit 0
        ;;
esac

exit 0
