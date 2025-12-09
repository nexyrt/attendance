# update-namespaces.ps1
# Run from project root: D:\Laravel\Attendance

# Dashboard
(Get-Content "app\Livewire\Dashboard\StaffDashboard.php") -replace 'namespace App\\Livewire\\Staff\\Dashboard;', 'namespace App\Livewire\Dashboard;' -replace 'class Index extends', 'class StaffDashboard extends' | Set-Content "app\Livewire\Dashboard\StaffDashboard.php"

# Attendance - MyAttendance
(Get-Content "app\Livewire\Attendance\MyAttendance.php") -replace 'namespace App\\Livewire\\Staff\\Attendance;', 'namespace App\Livewire\Attendance;' -replace 'class Index extends', 'class MyAttendance extends' | Set-Content "app\Livewire\Attendance\MyAttendance.php"

# Attendance - TeamAttendance (3 files)
Get-ChildItem "app\Livewire\Attendance\TeamAttendance\*.php" | ForEach-Object {
    (Get-Content $_.FullName) -replace 'namespace App\\Livewire\\Manager\\TeamAttendance;', 'namespace App\Livewire\Attendance\TeamAttendance;' | Set-Content $_.FullName
}

# Attendance - AllAttendance
(Get-Content "app\Livewire\Attendance\AllAttendance\Index.php") -replace 'namespace App\\Livewire\\Director\\Attendance;', 'namespace App\Livewire\Attendance\AllAttendance;' | Set-Content "app\Livewire\Attendance\AllAttendance\Index.php"

# LeaveRequests - MyLeaves (4 files)
Get-ChildItem "app\Livewire\LeaveRequests\MyLeaves\*.php" | ForEach-Object {
    (Get-Content $_.FullName) -replace 'namespace App\\Livewire\\Staff\\LeaveRequest;', 'namespace App\Livewire\LeaveRequests\MyLeaves;' | Set-Content $_.FullName
}

# LeaveRequests - Approvals (4 files)
Get-ChildItem "app\Livewire\LeaveRequests\Approvals\*.php" | ForEach-Object {
    (Get-Content $_.FullName) -replace 'namespace App\\Livewire\\Manager\\LeaveRequest;', 'namespace App\Livewire\LeaveRequests\Approvals;' | Set-Content $_.FullName
}

# OfficeLocations
Get-ChildItem "app\Livewire\OfficeLocations\*.php" | ForEach-Object {
    (Get-Content $_.FullName) -replace 'namespace App\\Livewire\\OfficeManagement;', 'namespace App\Livewire\OfficeLocations;' | Set-Content $_.FullName
}

Write-Host "✅ All namespaces updated successfully!" -ForegroundColor Green