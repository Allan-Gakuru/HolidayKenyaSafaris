[CmdletBinding()]
param(
    [string[]] $Path = @(
        'wp-content\plugins\hks-core',
        'wp-content\themes\hks-wayfinder'
    )
)

$ErrorActionPreference = 'Stop'
$php = (Get-Command php -ErrorAction Stop).Source
$workspace = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '..')).Path
$files = [System.Collections.Generic.List[System.IO.FileInfo]]::new()

foreach ($relativePath in $Path) {
    $candidate = Join-Path $workspace $relativePath
    if (-not (Test-Path -LiteralPath $candidate -PathType Container)) {
        throw "PHP source directory does not exist: $relativePath"
    }

    Get-ChildItem -LiteralPath $candidate -Recurse -File -Filter '*.php' |
        ForEach-Object { $files.Add($_) }
}

$orderedFiles = $files | Sort-Object -Property FullName -Unique
if ($orderedFiles.Count -eq 0) {
    throw 'No PHP files were found in the requested source directories.'
}

foreach ($file in $orderedFiles) {
    & $php -l $file.FullName
    if ($LASTEXITCODE -ne 0) {
        throw "PHP syntax validation failed: $($file.FullName)"
    }
}

Write-Host "PHP syntax validation passed for $($orderedFiles.Count) file(s)."
