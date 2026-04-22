@extends('Template')

@section('isi')
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; margin: 20px; border-radius: 10px;">
    <h1>🎉 TEST DASHBOARD - INTEGRATION WORKING!</h1>
    <h3>Data dari DashboardController:</h3>
    <ul>
        <li>Total Kitab: {{ $total_kitab ?? 'NULL' }}</li>
        <li>Total Users: {{ $total_user ?? 'NULL' }}</li>
        <li>Total Views: {{ $total_views ?? 'NULL' }}</li>
        <li>Total Downloads: {{ $total_download ?? 'NULL' }}</li>
        <li>Reading Time: {{ $readingStats['total_reading_time'] ?? 'NULL' }} minutes</li>
    </ul>
</div>

<div style="background: white; padding: 20px; margin: 20px; border: 2px solid #4facfe; border-radius: 10px;">
    <h2>📊 Chart Data Available:</h2>
    <p>User Registration Data: {{ count($grafik_user_reg ?? []) }} items</p>
    <p>Views Data: {{ count($grafik_views ?? []) }} items</p>
    <p>Reading Stats: {{ isset($readingStats) ? 'Available' : 'Not Available' }}</p>
</div>

<div style="background: #f8f9fa; padding: 20px; margin: 20px; border-radius: 10px;">
    <h3>🔍 Debug Info:</h3>
    <p>Current Time: {{ now()->format('Y-m-d H:i:s') }}</p>
    <p>User: {{ auth()->user()->username ?? 'Not Authenticated' }}</p>
    <p>Role: {{ auth()->user()->role ?? 'Not Authenticated' }}</p>
</div>
@endsection
