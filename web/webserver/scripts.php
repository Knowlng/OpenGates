<?php

function createVM($resources)
{
    $output = [];

    // Create VM with the specified resources
    exec('../script.sh ' . $resources . ' 2>&1', $output);

    return $output;
}

function showVMdetails($VMID)
{
    $output = [];

    exec('../showVMdetails.sh ' . $VMID . ' 2>&1', $output);

    return $output;
}
