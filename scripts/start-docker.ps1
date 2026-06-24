param(
    [int] $StartPort = 8080,
    [int] $MaxPort = 8099
)

$ErrorActionPreference = "Stop"

function Test-PortAvailable {
    param([int] $Port)

    $tcp = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue
    return $null -eq $tcp
}

$selectedPort = $null
foreach ($port in $StartPort..$MaxPort) {
    if (Test-PortAvailable -Port $port) {
        $selectedPort = $port
        break
    }
}

if ($null -eq $selectedPort) {
    throw "No free port found between $StartPort and $MaxPort."
}

"APP_PORT=$selectedPort" | Set-Content -Path ".env" -Encoding ascii

docker compose up --build

