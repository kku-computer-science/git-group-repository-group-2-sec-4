@extends('dashboards.users.layouts.user-dash-layout')
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.0/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.bootstrap4.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
@section('content')
<style>
    .table-responsive {
        margin: 30px 0;
    }

    .table-wrapper {
        min-width: 1000px;
        background: #fff;
        padding: 20px 25px;
        border-radius: 3px;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
    }


    .search-box {
        position: relative;
        float: right;
    }

    .search-box .input-group {
        min-width: 300px;
        position: absolute;
        right: 0;
    }

    .search-box .input-group-addon,
    .search-box input {
        border-color: #ddd;
        border-radius: 0;
    }

    .search-box input {
        height: 34px;
        padding-right: 35px;
        background: #0e393e;
        color: #ffffff;
        border: none;
        border-radius: 15px !important;
    }

    .search-box input:focus {
        background: #0e393e;
        color: #ffffff;
    }

    .search-box input::placeholder {
        font-style: italic;
    }

    .search-box .input-group-addon {
        min-width: 35px;
        border: none;
        background: transparent;
        position: absolute;
        right: 0;
        z-index: 9;
        padding: 6px 0;
    }

    .search-box i {
        color: #a0a5b1;
        font-size: 19px;
        position: relative;
        top: 2px;
    }
</style>
<div class="container">
    <div class="card" style="padding: 16px;">
        <div class="card-body">
            <h4 class="card-title">System Logs</h4>
            <!-- Advanced Search Form -->
            <form method="GET" action="{{ route('admin.logs') }}">
                <input type="text" name="user_id" placeholder="User ID" value="{{ request('user_id') }}">
                <input type="text" name="user_name" placeholder="User Name" value="{{ request('user_name') }}">
                <input type="text" name="user_email" placeholder="User Email" value="{{ request('user_email') }}">
                <input type="text" name="action" placeholder="Action" value="{{ request('action') }}">

                <select name="log_level">
                    <option value="">-- Select Log Level --</option>
                    <option value="INFO" {{ request('log_level')=='INFO' ? 'selected' : '' }}>INFO</option>
                    <option value="WARNING" {{ request('log_level')=='WARNING' ? 'selected' : '' }}>WARNING</option>
                    <option value="ERROR" {{ request('log_level')=='ERROR' ? 'selected' : '' }}>ERROR</option>
                </select>

                <input type="text" name="ip_address" placeholder="IP Address" value="{{ request('ip_address') }}">

                <label for="start_date">Start Date:</label>
                <input type="text" name="start_date" id="start_date" placeholder="DD/MM/YYYY"
                    value="{{ request('start_date') }}">

                <label for="end_date">End Date:</label>
                <input type="text" name="end_date" id="end_date" placeholder="DD/MM/YYYY"
                    value="{{ request('end_date') }}">

                <button type="submit">Search</button>
                <a href="{{ route('admin.logs') }}">Reset</a>
            </form>

            <div class="table-responsive">
                <table id="example1" class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>User Name</th>
                            <th>User Email</th>
                            <th>Action</th>
                            <th>Log Level</th>
                            <th>Message</th>
                            <th>Related Table</th>
                            <th>Related ID</th>
                            <th>IP Address</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                        <tr>
                            <td>{{ $log->log_id }}</td>
                            <td>{{ $log->user_id ?? 'Guest' }}</td>
                            <td>{{ $log->user ? $log->user->fname_en . ' ' . $log->user->lname_en : 'Unknown' }}</td>
                            <td>{{ $log->user ? $log->user->email : 'Unknown' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->log_level }}</td>
                            <td>{{ $log->message }}</td>
                            <td>{{ $log->related_table }}</td>
                            <td>{{ $log->related_id }}</td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ $log->created_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="http://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdn.datatables.net/1.12.0/js/dataTables.bootstrap4.min.js" defer></script>
<script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js" defer></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        flatpickr("#start_date", {
            dateFormat: "d/m/Y"
        });

        flatpickr("#end_date", {
            dateFormat: "d/m/Y"
        });
    });
</script>

{{ $logs->links() }}
@endsection