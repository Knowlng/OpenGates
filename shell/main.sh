#!/bin/bash

# Check if --new-machine is passed as an argument
if [[ " $* " == *" --new-machine "* ]]; then
    echo "Setting up a new machine..." 

    echo "Installing OpenNebula-tools, please wait..."
    sudo apt-get update > /dev/null
    sudo apt-get install gnupg -y > /dev/null
    wget -q -O- https://downloads.opennebula.org/repo/repo.key | sudo apt-key add - > /dev/null
    echo "deb https://downloads.opennebula.org/repo/5.6/Debian/9 stable opennebula" | sudo tee /etc/apt/sources.list.d/opennebula.list > /dev/null
    sudo apt-get update > /dev/null
    sudo apt-get install opennebula-tools -y > /dev/null

    echo "Installing Ansible, please wait..."
    sudo apt-get install ansible -y > /dev/null

    #For password login
    sudo apt-get install sshpass -y > /dev/null

    echo "Finished setting up new machine"
fi

OpenNebulaCredentialsPassLocation="../misc/OpenNebulaCredentials_pass.txt"
VMVaultPassLocation="../misc/vault_pass.txt"
# Ask user to enter password and save the password in txt file
echo "Accessing OpenNebulaCredentials vault..."
read -sp 'Please enter vault pass: ' passvar
echo "" # new line

# save password to txt file
echo $passvar > $OpenNebulaCredentialsPassLocation

#delete password from storage
passvar=""

OpenNebulaCredentials=$(ansible-vault view ../misc/OpenNebulaCredentials.yml --vault-password-file $OpenNebulaCredentialsPassLocation)

# Check if the vault was opened successfully
if [ $? -ne 0 ]; then
    echo "Failed opening OpenNebulaCredentials vault: $OpenNebulaCredentials"
    rm $OpenNebulaCredentialsPassLocation
    exit 1
fi

#delete txt file
rm $OpenNebulaCredentialsPassLocation

echo "Accessing VM vault..."
read -sp 'Please enter vault pass: ' passvar
echo "" # new line

# save password to txt file
echo $passvar > $VMVaultPassLocation
#delete password from storage
passvar=""

# Check if VM vault password is correct
ansible-vault view ../misc/vault.yml --vault-password-file $VMVaultPassLocation > /dev/null
if [ $? -ne 0 ]; then
    echo "Failed opening: VM vault"
    rm $OpenNebulaCredentialsPassLocation
    rm $VMVaultPassLocation
    exit 1
fi


#FILL VM credentials from vault

# User 1
CUSER=$(echo "$OpenNebulaCredentials" | awk -F': *' '/user:/ {print $2}' | tr -d '"')
CPASS=$(echo "$OpenNebulaCredentials" | awk -F': *' '/password:/ {print $2}' | tr -d '"')

# User 2
CUSER2=$(echo "$OpenNebulaCredentials" | awk -F': *' '/user2:/ {print $2}' | tr -d '"')
CPASS2=$(echo "$OpenNebulaCredentials" | awk -F': *' '/password2:/ {print $2}' | tr -d '"')

# User 3
CUSER3=$(echo "$OpenNebulaCredentials" | awk -F': *' '/user3:/ {print $2}' | tr -d '"')
CPASS3=$(echo "$OpenNebulaCredentials" | awk -F': *' '/password3:/ {print $2}' | tr -d '"')

CENDPOINT="EnterYourOpenNebulaURLEndpointHere"

# Succesfull VM creation prefix
prefix="VM ID:"

# Enable connections Without VPN
NIC1="VNET1:NETWORK_UNAME=oneadmin"
NIC2="VNET1:NETWORK_UNAME=oneadmin:SECURITY_GROUPS=0"
NIC3="VNET2:NETWORK_UNAME=oneadmin:SECURITY_GROUPS=0"
# START Create [Database VM]

# VM resources
CUSTOM_MEMORY="512" # 512MB
CUSTOM_DISK_SIZE="6000" # 8GB

# Create VM and capture the output into a variable
CVMREZ=$(onetemplate instantiate "debian12" --user $CUSER --password $CPASS --endpoint $CENDPOINT --memory $CUSTOM_MEMORY --disk "oneadmin[3612]:size="$CUSTOM_DISK_SIZE --name "Database VM" --nic $NIC1)

# Check if the VM was created successfully
case $CVMREZ in
    "$prefix"*)
	DATABASE_VM_ID=$(echo $CVMREZ | cut -d ' ' -f 3)
	echo "[Database VM]: created successfully with ID: $DATABASE_VM_ID"
        ;;
    *)
    echo "[Database VM]: error creating VM: $CVMREZ"
    exit 1
        ;;
esac

# End of [Database VM] creation


# Create [WebServer VM]

# VM resources
CUSTOM_MEMORY="512" # 512MB
CUSTOM_DISK_SIZE="8192" # 8GB

# Create VM and capture the output into a variable
CVMREZ=$(onetemplate instantiate "debian12" --user $CUSER2 --password $CPASS2 --endpoint $CENDPOINT --memory $CUSTOM_MEMORY --name "WebServer VM" --disk "oneadmin[3612]:size="$CUSTOM_DISK_SIZE --nic $NIC2 --raw "\"TCP_PORT_FORWARDING=\"22 80\"\"")

# Check if the VM was created successfully
case $CVMREZ in
    "$prefix"*)
	WEBSERVER_VM_ID=$(echo $CVMREZ | cut -d ' ' -f 3)
	echo "[WebServer VM]: created successfully with ID: $WEBSERVER_VM_ID"
        ;;
    *)
    echo "[WebServer VM]: error creating VM: $CVMREZ"
    exit 1
        ;;
esac

# End of [WebServer VM] creation

# Create [Client VM]

# VM resources
CUSTOM_MEMORY="4000" 
CUSTOM_DISK_SIZE="12000" 

#Create VM and capture the output into a variable
CVMREZ=$(onetemplate instantiate "debian12-lxde" --user $CUSER3 --password $CPASS3 --endpoint $CENDPOINT --memory $CUSTOM_MEMORY --disk "oneadmin[3613]:size="$CUSTOM_DISK_SIZE --name "Client VM" --nic $NIC3)

#Check if the VM was created successfully
case $CVMREZ in
    "$prefix"*)
	CLIENT_VM_ID=$(echo $CVMREZ | cut -d ' ' -f 3)
	echo "[Client VM]: created successfully with ID: $CLIENT_VM_ID"
        ;;
    *)
    echo "[Client VM]: error creating VM"
    exit 1
        ;;
esac

# End of [Client VM] creation


# wait for VMs to be read
echo "Waiting for VMs to be ready..."
sleep 50

# GET details of [Database VM]

# Capture the output of the 'onevm show' command into a variable
vm_info=$(onevm show $DATABASE_VM_ID --user $CUSER --password $CPASS --endpoint $CENDPOINT)

# Get the private ip of the VM
DATABASE_VM_PRIVATE_IP=$(echo "$vm_info" | grep PRIVATE\_IP | cut -d '=' -f 2 | tr -d '"')

DATABASE_VM_PUBLIC_IP=$(echo "$vm_info" | grep PUBLIC\_IP | cut -d '=' -f 2 | tr -d '"')

# Get the TCP ports that are forwarded to the VM
DATABASE_VM_TCP_PORTS=$(echo "$vm_info" | grep TCP\_PORT\_FORWARDING | cut -d '=' -f 2 | tr -d '"')

# Find to which port is port 22 forwarded
DATABASE_VM_TCP_PORT_22=$(echo "$DATABASE_VM_TCP_PORTS" | grep -oE '[0-9]+:22' | awk -F: '{print $1}')

echo "[Database VM]: Private IP: $DATABASE_VM_PRIVATE_IP"

# End of GET details of [Database VM]

# GET details of [WebServer VM]

# Capture the output of the 'onevm show' command into a variable
vm_info=$(onevm show $WEBSERVER_VM_ID --user $CUSER2 --password $CPASS2 --endpoint $CENDPOINT)

# Get the public IP of the VM
WEBSERVER_VM_PUBLIC_IP=$(echo "$vm_info" | grep PUBLIC\_IP | cut -d '=' -f 2 | tr -d '"')

# Get the private IP of the VM
WEBSERVER_VM_PRIVATE_IP=$(echo "$vm_info" | grep PRIVATE\_IP | cut -d '=' -f 2 | tr -d '"')

# Get the TCP ports that are forwarded to the VM
WEBSERVER_VM_TCP_PORTS=$(echo "$vm_info" | grep TCP\_PORT\_FORWARDING | cut -d '=' -f 2 | tr -d '"')

# Find to which port is port 80 forwarded
WEBSERVER_VM_TCP_PORT_80=$(echo "$WEBSERVER_VM_TCP_PORTS" | grep -oE '[0-9]+:80' | awk -F: '{print $1}')

# Find to which port is port 22 forwarded
WEBSERVER_VM_TCP_PORT_22=$(echo "$WEBSERVER_VM_TCP_PORTS" | grep -oE '[0-9]+:22' | awk -F: '{print $1}')

echo "[WebServer VM]: Public IP: $WEBSERVER_VM_PUBLIC_IP:$WEBSERVER_VM_TCP_PORT_80"
echo "[WebServer VM]: Private IP: $WEBSERVER_VM_PRIVATE_IP"
# End of GET details of [WebServer VM]

# GET details of [Client VM]
vm_info=$(onevm show $CLIENT_VM_ID --user $CUSER3 --password $CPASS3 --endpoint $CENDPOINT)

# Get the private IP of the VM
CLIENT_VM_PRIVATE_IP=$(echo "$vm_info" | grep PRIVATE\_IP | cut -d '=' -f 2 | tr -d '"')

# Get the public IP of the VM
CLIENT_VM_PUBLIC_IP=$(echo "$vm_info" | grep PUBLIC\_IP | cut -d '=' -f 2 | tr -d '"')

# Get the TCP ports that are forwarded to the VM
CLIENT_VM_TCP_PORTS=$(echo "$vm_info" | grep TCP\_PORT\_FORWARDING | cut -d '=' -f 2 | tr -d '"')

# Find to which port is port 80 forwarded
CLIENT_VM_TCP_PORT_3389=$(echo "$CLIENT_VM_TCP_PORTS" | grep -oE '[0-9]+:3389' | awk -F: '{print $1}')


echo "[Client VM]: Private IP: $CLIENT_VM_PRIVATE_IP"
echo "[Client VM]: Public IP: $CLIENT_VM_PUBLIC_IP:$CLIENT_VM_TCP_PORT_3389"
# End of GET details of [Client VM]

echo "Creating ansible inventory file..."

AnsibleInventoryLocation="../misc/inventory"

echo "[databases]" > $AnsibleInventoryLocation
echo "$DATABASE_VM_PUBLIC_IP" >> $AnsibleInventoryLocation
echo "" >> $AnsibleInventoryLocation
echo "[databases:vars]" >> $AnsibleInventoryLocation
echo "remote_address=$WEBSERVER_VM_PRIVATE_IP" >> $AnsibleInventoryLocation
echo "" >> $AnsibleInventoryLocation
echo "[webservers]" >> $AnsibleInventoryLocation
echo "$WEBSERVER_VM_PUBLIC_IP" >> $AnsibleInventoryLocation
echo "" >> $AnsibleInventoryLocation
echo "[webservers:vars]" >> $AnsibleInventoryLocation
echo "db_host=$DATABASE_VM_PRIVATE_IP" >> $AnsibleInventoryLocation
echo "" >> $AnsibleInventoryLocation
echo "[clients]" >> $AnsibleInventoryLocation
echo "$CLIENT_VM_PRIVATE_IP" >> $AnsibleInventoryLocation
echo "" >> $AnsibleInventoryLocation
echo "[clients:vars]" >> $AnsibleInventoryLocation
echo "webserver_host=$WEBSERVER_VM_PUBLIC_IP" >> $AnsibleInventoryLocation
echo "webserver_user=$CUSER2" >> $AnsibleInventoryLocation
echo "webserver_ssh_port=$WEBSERVER_VM_TCP_PORT_22" >> $AnsibleInventoryLocation

# Run ansible scripts
echo "Running ansible scripts..."

# Remove old host keys to insure that ansible will not fail
ssh-keygen -f ~/.ssh/known_hosts -R "$DATABASE_VM_PRIVATE_IP" > /dev/null
ssh-keygen -f ~/.ssh/known_hosts -R "$WEBSERVER_VM_PRIVATE_IP" > /dev/null
ssh-keygen -f ~/.ssh/known_hosts -R "$CLIENT_VM_PRIVATE_IP" > /dev/null

#ssh-keyscan -p $DATABASE_VM_TCP_PORT_22 $DATABASE_VM_PUBLIC_IP >> ~/.ssh/known_hosts
#ssh-keyscan -p $WEBSERVER_VM_TCP_PORT_22 $WEBSERVER_VM_PUBLIC_IP>> ~/.ssh/known_hosts

#Check if ansible playbook finished without errors
ANSIBLE_HOST_KEY_CHECKING=False ansible-playbook -i $AnsibleInventoryLocation -u $CUSER -e "ansible_ssh_port=$DATABASE_VM_TCP_PORT_22" ../ansible/database.yml --vault-password-file $VMVaultPassLocation
if [ $? -ne 0 ]; then
    echo "[Database VM]: error running ansible playbook"
    rm $VMVaultPassLocation
    exit 1
fi

ANSIBLE_HOST_KEY_CHECKING=False ansible-playbook -i $AnsibleInventoryLocation -u $CUSER2 -e "ansible_ssh_port=$WEBSERVER_VM_TCP_PORT_22" ../ansible/webserver.yml --vault-password-file $VMVaultPassLocation
if [ $? -ne 0 ]; then
    echo "[Webserver VM]: error running ansible playbook"
    rm $VMVaultPassLocation
    exit 1
fi

ANSIBLE_HOST_KEY_CHECKING=False ansible-playbook -i $AnsibleInventoryLocation -u $CUSER3 ../ansible/client.yml --vault-password-file $VMVaultPassLocation
if [ $? -ne 0 ]; then
    echo "[Client VM]: error running ansible playbook"
    rm $VMVaultPassLocation
    exit 1
fi

rm $VMVaultPassLocation

echo "Everything is ready!"
echo "You can access the website at: http://$WEBSERVER_VM_PUBLIC_IP:$WEBSERVER_VM_TCP_PORT_80"
echo "You can connect to your remote desktop at: $CLIENT_VM_PUBLIC_IP:$CLIENT_VM_TCP_PORT_3389"
echo "Remote desktop user: $CUSER3"

exit 0
