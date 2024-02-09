#!/bin/sh

# OpenNebula endpoint
CENDPOINT="EnterYourOpenNebulaURLEndpointHere"

# VM credentials
CUSER=$2
CPASS=$3

vm_info=$(onevm show $1 --user $CUSER --password $CPASS --endpoint $CENDPOINT)

# Get the public IP of the VM
VM_PUBLIC_IP=$(echo "$vm_info" | grep PUBLIC\_IP | cut -d '=' -f 2 | tr -d '"')

# Get the TCP ports that are forwarded to the VM
VM_TCP_PORTS=$(echo "$vm_info" | grep TCP\_PORT\_FORWARDING | cut -d '=' -f 2 | tr -d '"')

# Get the private IP of the VM
VM_PRIVATE_IP=$(echo "$vm_info" | grep PRIVATE\_IP | cut -d '=' -f 2 | tr -d '"')

# Get connection info
VM_SSH_CONNECTION=$(echo "$vm_info" | grep CONNECT\_INFO1| cut -d '=' -f 2 | tr -d '"')

USER_PASSWORD=$(echo "$vm_info" | grep USER\_PASSWORD| cut -d '"' -f 2)

echo "[VM INFO]:"
echo "Public IP: $VM_PUBLIC_IP"
echo "TCP ports: $VM_TCP_PORTS"
echo "Private IP: $VM_PRIVATE_IP"
echo "SSH connection: $VM_SSH_CONNECTION"
echo "Default password: $USER_PASSWORD"
exit 0
