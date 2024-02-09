<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getUserBalance($conn, $userId)
{
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['balance'] : null;
}

function updateUserBalance($conn, $userId, $newBalance)
{
    $stmt = $conn->prepare("UPDATE users SET balance = :balance WHERE id = :id");
    $stmt->execute(['balance' => $newBalance, 'id' => $userId]);
    return $stmt->rowCount() > 0;
}

function recordServicePurchase($conn, $userId, $VMID, $service, $sshString)
{
    $stmt = $conn->prepare("INSERT INTO services (user_id, service_name, cost, vmid, ssh_string) VALUES (:user_id, :service_name, :cost, :vmid, :ssh_string)");
    $stmt->execute(['user_id' => $userId, 'service_name' => $service['name'], 'cost' => $service['price'], 'vmid' => $VMID, 'ssh_string' => $sshString]);
    echo 'Purchased service: ' . $service['name'] . ' for ' . $service['price'] . ' €.<br>';
}

function getUserServices($conn, $userId)
{
    $stmt = $conn->prepare("SELECT * FROM services WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
