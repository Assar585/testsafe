$inputPath = "c:\Projects\MVP 1\SafeCon\_public_html\database.sql.gz"
$outputPath = "c:\Projects\MVP 1\SafeCon\_public_html\database_restored.sql"

try {
    $input = [System.IO.File]::OpenRead($inputPath)
    $output = [System.IO.File]::Create($outputPath)
    $gzip = New-Object System.IO.Compression.GZipStream($input, [System.IO.Compression.CompressionMode]::Decompress)
    $gzip.CopyTo($output)
    $gzip.Close()
    $output.Close()
    $input.Close()
    Write-Host "Decompression successful. Output at $outputPath"
    $size = (Get-Item $outputPath).Length
    Write-Host "Uncompressed size: $size bytes"
} catch {
    Write-Error $_.Exception.Message
}
