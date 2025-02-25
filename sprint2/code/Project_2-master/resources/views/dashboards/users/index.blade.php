@extends('dashboards.users.layouts.user-dash-layout')
@section('title', 'Dashboard')

@section('content')

    <h3 style="padding-top: 10px;">ยินดีต้อนรับเข้าสู่ระบบจัดการข้อมูลวิจัยของสาขาวิชาวิทยาการคอมพิวเตอร์</h3>
    <br>
    <h4>สวัสดี {{ Auth::user()->position_th }} {{ Auth::user()->fname_th }} {{ Auth::user()->lname_th }}</h4>

    @if($isAdmin)
        <div class="row mt-4">
            <!-- Total Logs -->
            <div class="col-md-3">
                <div class="card shadow-sm border-dark">
                    <div class="card-body">
                        <h5 class="card-title text-dark"><i class="fas fa-clipboard-list"></i> Total Logs</h5>
                        <p class="card-text"><strong>{{ $logsCount }}</strong></p>
                    </div>
                </div>
            </div>

            <!-- Error Logs -->
            <div class="col-md-3">
                <div class="card shadow-sm border-danger">
                    <div class="card-body">
                        <h5 class="card-title text-danger"><i class="fas fa-exclamation-circle"></i> Errors</h5>
                        <p class="card-text"><strong>{{ $errorLogsCount }}</strong></p>
                    </div>
                </div>
            </div>

            <!-- Warning Logs -->
            <div class="col-md-3">
                <div class="card shadow-sm border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning"><i class="fas fa-exclamation-triangle"></i> Warnings</h5>
                        <p class="card-text"><strong>{{ $warningLogsCount }}</strong></p>
                    </div>
                </div>
            </div>

            <!-- Info Logs -->
            <div class="col-md-3">
                <div class="card shadow-sm border-info">
                    <div class="card-body">
                        <h5 class="card-title text-info"><i class="fas fa-info-circle"></i> Info</h5>
                        <p class="card-text"><strong>{{ $infoLogsCount }}</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔥 Top 5 Most Frequent Logs Table -->
        <div class="card row mt-4" style="padding: 16px; ">
            <div class="card-body">
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Top 5 Most Frequent Logs</h5>
                    </div>
                    <div class="col-md-6 text-right">
                        <form method="GET" action="{{ route('dashboard') }}">
                            <label for="time_range">Select Time Range:</label>
                            <select name="time_range" id="time_range" class="form-control" onchange="this.form.submit()">
                                <option value="now" {{ $timeRange == 'now' ? 'selected' : '' }}>Now</option>
                                <option value="2h" {{ $timeRange == '2h' ? 'selected' : '' }}>Last 2 Hours</option>
                                <option value="24h" {{ $timeRange == '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                                <option value="7d" {{ $timeRange == '7d' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="30d" {{ $timeRange == '30d' ? 'selected' : '' }}>Last 30 Days</option>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Count</th>
                                <th>Log Level</th>
                                <th>Last Occurrence</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topLogs as $log)
                                <tr>
                                    <td>{{ $log->action }}</td>
                                    <td><strong>{{ $log->count }}</strong></td>
                                    <td>
                                        <span
                                            class="badge badge-{{ $log->log_level == 'ERROR' ? 'danger' : ($log->log_level == 'WARNING' ? 'warning' : 'info') }}">
                                            {{ $log->log_level }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($log->last_occurrence)->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

@endsection