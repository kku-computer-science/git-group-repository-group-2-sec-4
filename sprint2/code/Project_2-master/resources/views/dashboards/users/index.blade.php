@extends('dashboards.users.layouts.user-dash-layout')
@section('title', 'Dashboard')

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

        .log-level.info {
            color: green;
            font-weight: bold;
        }

        .log-level.warning {
            color: orange;
            font-weight: bold;
        }

        .log-level.error {
            color: red;
            font-weight: bold;
        }

        /* กำหนดขนาด Scroll Bar ด้านบน */
        .top-scroll {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 10px;
        }

        /* กำหนดขนาด Scroll Bar ด้านล่าง */
        .bottom-scroll {
            width: 100%;
            overflow-x: auto;
            margin-top: 10px;
        }

        /* ทำให้ Scroll Bar บนและล่างมีขนาดเท่ากัน */
        .scroll-div {
            width: 100%;
            height: 1px;
        }

        /* กำหนดให้ Scroll Bar ของ Table อยู่ข้างใน */
        .scroll-container {
            overflow-x: auto;
            white-space: nowrap;
        }

        .advanced {
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            color: rgb(85, 85, 85) !important;
        }

        .btn-secondary,
        .btn-secondary:focus,
        .btn-secondary:active {
            color: #fff;
            background-color: rgb(85, 85, 85) !important;
            border-color: rgb(85, 85, 85) !important;
            box-shadow: none;
        }

        .form-control:focus {
            box-shadow: none;
            border: 1px solid rgb(85, 85, 85);
        }
    </style>
    <script>
        $(document).ready(function () {
            // Activate tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Filter table rows based on searched term
            $("#search").on("keyup", function () {
                var term = $(this).val().toLowerCase();
                $("table tbody tr").each(function () {
                    $row = $(this);
                    var name = $row.find("td:nth-child(2)").text().toLowerCase();
                    console.log(name);
                    if (name.search(term) < 0) {
                        $row.hide();
                    } else {
                        $row.show();
                    }
                });
            });
        });
    </script>

    <h3 style="padding-top: 10px;">ยินดีต้อนรับเข้าสู่ระบบจัดการข้อมูลวิจัยของสาขาวิชาวิทยาการคอมพิวเตอร์</h3>
    <br>
    <h4>สวัสดี {{ Auth::user()->position_th }} {{ Auth::user()->fname_th }} {{ Auth::user()->lname_th }}</h4>

    @if($isAdmin)
        <div class="row mt-4">
            <!-- Total Logs -->
            <div class="col-md-3">
                <div class="card shadow-sm border-dark log-filter" data-type="totalLogs">
                    <div class="card-body">
                        <h5 class="card-title text-dark"><i class="fas fa-clipboard-list"></i> Total Logs</h5>
                        <p class="card-text"><strong>{{ $logsCount }}</strong></p>
                    </div>
                </div>
            </div>

            <!-- Error Logs -->
            <div class="col-md-3">
                <div class="card shadow-sm border-danger log-filter" data-type="errors">
                    <div class="card-body">
                        <h5 class="card-title text-danger"><i class="fas fa-exclamation-circle"></i> Total Errors</h5>
                        <p class="card-text"><strong>{{ $errorLogsCount }}</strong></p>
                    </div>
                </div>
            </div>

            <!-- Warning Logs -->
            <div class="col-md-3">
                <div class="card shadow-sm border-warning log-filter" data-type="warnings">
                    <div class="card-body">
                        <h5 class="card-title text-warning"><i class="fas fa-exclamation-triangle"></i> Total Warnings</h5>
                        <p class="card-text"><strong>{{ $warningLogsCount }}</strong></p>
                    </div>
                </div>
            </div>

            <!-- Info Logs -->
            <div class="col-md-3">
                <div class="card shadow-sm border-info log-filter" data-type="info">
                    <div class="card-body">
                        <h5 class="card-title text-info"><i class="fas fa-info-circle"></i> Total Info</h5>
                        <p class="card-text"><strong>{{ $infoLogsCount }}</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Area Chart -->
        <div class="card shadow mb-4" style="margin-top: 30px; ">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Area Chart</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="myAreaChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart.js -->
        <script src="vendor/chart.js/Chart.min.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                let ctx = document.getElementById("myAreaChart").getContext("2d");

                // ดึงข้อมูลเวลาและจำนวน log จาก Laravel Blade
                let labels = {!! json_encode($logTimestamps) !!}; // ข้อมูลเวลา (X-Axis)
                let chartData = {
                    totalLogs: {!! json_encode($logCounts['totalLogs']) !!},
                    errors: {!! json_encode($logCounts['errors']) !!},
                    warnings: {!! json_encode($logCounts['warnings']) !!},
                    info: {!! json_encode($logCounts['info']) !!}
                };

                let myAreaChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels, // **ใช้ค่าเวลาเป็นแกน X**
                        datasets: [{
                            label: "Log Count",
                            backgroundColor: "rgba(78, 115, 223, 0.1)",
                            borderColor: "#4e73df",
                            pointBackgroundColor: "#4e73df",
                            pointBorderColor: "#fff",
                            pointHoverBackgroundColor: "#fff",
                            pointHoverBorderColor: "#4e73df",
                            data: chartData.totalLogs // Default: แสดง Total Logs
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { beginAtZero: false },
                            y: { beginAtZero: true }
                        }
                    }
                });

                document.querySelectorAll(".log-filter").forEach(card => {
                    card.addEventListener("click", function () {
                        let type = this.getAttribute("data-type");
                        myAreaChart.data.datasets[0].data = chartData[type]; // เปลี่ยนข้อมูลตามประเภทที่เลือก
                        myAreaChart.update();
                    });
                });
            });
        </script>

        <!-- Top 5 Most Frequent Logs Table -->
        <div class="card row" style="padding: 16px; margin-top: 30px;" >
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

        <div class="card" style="padding: 16px; margin-top: 30px;" >
            <div class="card-body">
                <h4 class="card-title">System Logs</h4>
                <!-- Advanced Search Form -->
                <!-- ฟอร์มค้นหาหลัก -->
                <!-- ✅ FORM ค้นหาหลัก -->
                <form method="GET" action="{{ route('admin.logs') }}" id="searchForm">
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <input type="text" name="user_name" class="form-control" placeholder="User Name"
                                value="{{ request('user_name') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="user_email" class="form-control" placeholder="User Email"
                                value="{{ request('user_email') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="log_level" class="form-control">
                                <option value="">-- Log Level --</option>
                                <option value="INFO" {{ request('log_level') == 'INFO' ? 'selected' : '' }}>INFO</option>
                                <option value="WARNING" {{ request('log_level') == 'WARNING' ? 'selected' : '' }}>WARNING
                                </option>
                                <option value="ERROR" {{ request('log_level') == 'ERROR' ? 'selected' : '' }}>ERROR</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex">
                            <button type="submit" class="btn btn-primary">Search Logs</button>
                            <!-- <button type="button" class="btn btn-danger ml-2" id="resetBtn">Reset</button> -->
                            <a href="{{ route('admin.logs.exportCsv') }}" class="btn btn-warning">Export CSV</a>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a data-toggle="collapse" href="#advancedSearch" role="button" aria-expanded="false" class="advanced">
                            Advanced Filters <i class="fa fa-angle-down"></i>
                        </a>

                        <div class="collapse" id="advancedSearch">
                            <div class="card card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="text" name="action" class="form-control" placeholder="Action"
                                            value="{{ request('action') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="related_table" class="form-control" placeholder="Related Table"
                                            value="{{ request('related_table') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="related_id" class="form-control" placeholder="Related ID"
                                            value="{{ request('related_id') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="ip_address" class="form-control" placeholder="IP Address"
                                            value="{{ request('ip_address') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="start_date" id="start_date" class="form-control"
                                            placeholder="Start Date (DD/MM/YYYY)" value="{{ request('start_date') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="end_date" id="end_date" class="form-control"
                                            placeholder="End Date (DD/MM/YYYY)" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <!-- Scroll Bar ด้านบน -->
                    <div class="top-scroll">
                        <div class="scroll-div"></div>
                    </div>

                    <!-- ตาราง -->
                    <div class="scroll-container">
                        <table id="example1" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User ID</th>
                                    <th>User Name</th>
                                    <th>User Email</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                    <th>Log Level</th>
                                    <th>Message</th>
                                    <!-- <th>Related Table</th>
                                                                                                                    <th>Related ID</th> -->
                                    <th>IP Address</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>{{ $log->log_id }}</td>
                                        <td>{{ $log->user_id ?? 'Guest' }}</td>
                                        <td>{{ $log->user ? $log->user->fname_en . ' ' . $log->user->lname_en : 'Unknown' }}
                                        </td>
                                        <td>{{ $log->user ? $log->user->email : 'Unknown' }}</td>
                                        <td>
                                            @if($log->user && $log->user->roles->count())
                                                {{ $log->user->roles->first()->name }}
                                            @else
                                                Unknown
                                            @endif
                                        </td>
                                        <td>{{ $log->action }}</td>
                                        <td class="log-level {{ strtolower($log->log_level) }}">
                                            {{ $log->log_level }}
                                        </td>
                                        <td style="width: 350px; word-wrap: break-word; white-space: normal;">
                                            {{ $log->message }}
                                        </td>
                                        <!-- <td>{{ $log->related_table }}</td>
                                                                                                                                                    <td>{{ $log->related_id }}</td> -->
                                        <td>{{ $log->ip_address }}</td>
                                        <td>{{ $log->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Scroll Bar ด้านล่าง -->
                    <div class="bottom-scroll">
                        <div class="scroll-div"></div>
                    </div>
                </div>
                @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $logs->links() }} <!-- ✅ แสดง pagination เฉพาะกรณีที่ใช้ paginate() -->
                @endif
            </div>

        </div>
        <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script src="http://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js" defer></script>
        <script src="https://cdn.datatables.net/1.12.0/js/dataTables.bootstrap4.min.js" defer></script>
        <script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                flatpickr("#start_date", { dateFormat: "d/m/Y" });
                flatpickr("#end_date", { dateFormat: "d/m/Y" });

                $("#resetBtn").click(function () {
                    window.location.href = "{{ route('admin.logs') }}";
                });
            });

        </script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                let topScroll = document.querySelector(".top-scroll");
                let bottomScroll = document.querySelector(".bottom-scroll");
                let tableScroll = document.querySelector(".scroll-container");

                // ทำให้ Scroll บน-ล่าง Sync กัน
                topScroll.addEventListener("scroll", function () {
                    tableScroll.scrollLeft = topScroll.scrollLeft;
                    bottomScroll.scrollLeft = topScroll.scrollLeft;
                });

                bottomScroll.addEventListener("scroll", function () {
                    tableScroll.scrollLeft = bottomScroll.scrollLeft;
                    topScroll.scrollLeft = bottomScroll.scrollLeft;
                });

                tableScroll.addEventListener("scroll", function () {
                    topScroll.scrollLeft = tableScroll.scrollLeft;
                    bottomScroll.scrollLeft = tableScroll.scrollLeft;
                });

                // ปรับขนาด Scroll ด้านบนให้เท่ากับ Table
                document.querySelector(".scroll-div").style.width = tableScroll.scrollWidth + "px";
            });
        </script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                flatpickr("#start_date", { dateFormat: "d/m/Y" });
                flatpickr("#end_date", { dateFormat: "d/m/Y" });
            });
        </script>
    @endif

@endsection
