$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

# Get login page to get tokens
$loginPage = Invoke-WebRequest -Uri "https://testsafe-production.up.railway.app/login" -WebSession $session
$regex = '<input type="hidden" name="_token" value="([^"]*)">'
$token = ""
if ($loginPage.Content -match $regex) {
    $token = $matches[1]
}

# Post login
$loginBody = @{
    "_token" = $token
    "email" = "admin@email.com"
    "password" = "123456"
}
$loginResponse = Invoke-WebRequest -Uri "https://testsafe-production.up.railway.app/login" -Method Post -Body $loginBody -WebSession $session -ErrorAction SilentlyContinue

# Fetch products ajax
$ajaxHeaders = @{
    "Accept" = "application/json"
    "X-Requested-With" = "XMLHttpRequest"
}
try {
    $ajaxResponse = Invoke-WebRequest -Uri "https://testsafe-production.up.railway.app/admin/products/filter/products?page=1" -Method Get -Headers $ajaxHeaders -WebSession $session
    Write-Output "HTTP Status: $($ajaxResponse.StatusCode)"
    Write-Output $ajaxResponse.Content
} catch {
    Write-Output "HTTP Status: $($_.Exception.Response.StatusCode.value__)"
    $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
    $content = $reader.ReadToEnd()
    
    if ($content -match '<title([^>]*)>([^<]*)<\/title>') {
        Write-Output "Title: $($matches[2])"
    } else {
        Write-Output $content.Substring(0, [math]::Min(1000, $content.Length))
    }
}
