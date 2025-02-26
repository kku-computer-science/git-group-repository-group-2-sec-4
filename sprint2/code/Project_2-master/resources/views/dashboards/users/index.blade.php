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

        .log-level {
            font-weight: bold;
            text-transform: uppercase;
        }

        .log-level.error {
            color: red;
        }

        .log-level.warning {
            color: orange;
        }

        .log-level.info {
            color: green;
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
        <!--Select Time Range มาด้านบนสุด -->
        <div class="row align-items-center d-flex justify-content-between">
            <!-- ✅ คอลัมน์ซ้ายสุดสำหรับ Last Updated -->
            <div class="col-md-6">
                <p id="lastUpdated" class="text-muted m-0">
                    Last updated: {{ \Carbon\Carbon::now()->format('M d, Y, h:i A') }}
                </p>
            </div>

            <!-- ✅ คอลัมน์ขวาสุดสำหรับ Select Time Range -->
            <div class="col-md-6 d-flex justify-content-end">
                <form method="GET" action="{{ route('dashboard') }}" id="timeRangeForm">
                    <label for="time_range" class="font-weight-bold">Select Time Range:</label>
                    <select name="time_range" id="time_range" class="form-control d-inline-block w-auto"
                        onchange="updateLastUpdated(); this.form.submit();">
                        <option value="now" {{ $timeRange == 'now' ? 'selected' : '' }}>Now</option>
                        <option value="1h" {{ $timeRange == '1h' ? 'selected' : '' }}>Last 1 Hour</option>
                        <option value="2h" {{ $timeRange == '2h' ? 'selected' : '' }}>Last 2 Hours</option>
                        <option value="6h" {{ $timeRange == '6h' ? 'selected' : '' }}>Last 6 Hours</option>
                        <option value="12h" {{ $timeRange == '12h' ? 'selected' : '' }}>Last 12 Hours</option>
                        <option value="24h" {{ $timeRange == '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                        <option value="3d" {{ $timeRange == '3d' ? 'selected' : '' }}>Last 3 Days</option>
                        <option value="7d" {{ $timeRange == '7d' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="14d" {{ $timeRange == '14d' ? 'selected' : '' }}>Last 14 Days</option>
                        <option value="30d" {{ $timeRange == '30d' ? 'selected' : '' }}>Last 30 Days</option>
                    </select>
                </form>
            </div>
        </div>
        <script>
            function updateLastUpdated() {
                let now = new Date();
                let formattedDate = now.toLocaleString('en-US', {
                    month: 'short', day: '2-digit', year: 'numeric',
                    hour: '2-digit', minute: '2-digit', hour12: true
                });
                document.getElementById("lastUpdated").innerText = "Last updated: " + formattedDate;
            }
        </script>
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
        <!-- <script src="vendor/chart.js/Chart.min.js"></script> -->

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                let ctx = document.getElementById("myAreaChart").getContext("2d");

                // 📌 ดึงข้อมูลจาก Laravel Blade
                let labels = {!! json_encode($logTimestamps) !!};
                let chartData = {
                    totalLogs: {!! json_encode($logCounts['totalLogs']) !!},
                    errors: {!! json_encode($logCounts['errors']) !!},
                    warnings: {!! json_encode($logCounts['warnings']) !!},
                    info: {!! json_encode($logCounts['info']) !!}
                };

                // 🛠 กำหนดค่า Title ของแกน X ให้เปลี่ยนอัตโนมัติ
                let xAxisLabel = "{{ in_array($timeRange, ['1h', '2h', '6h', '12h']) ? 'Time (HH:mm)' : (in_array($timeRange, ['24h', '3d']) ? 'Date & Hour (YYYY-MM-DD HH:00)' : 'Date (YYYY-MM-DD)') }}";

                // 🎯 สร้างกราฟเริ่มต้น (Total Logs)
                let myAreaChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels, // **ใช้ค่าเวลาที่ปรับแล้ว**
                        datasets: [{
                            label: "Total Logs",
                            backgroundColor: "rgba(78, 115, 223, 0.1)",
                            borderColor: "#4e73df",
                            pointBackgroundColor: "#4e73df",
                            pointBorderColor: "#fff",
                            pointHoverBackgroundColor: "#fff",
                            pointHoverBorderColor: "#4e73df",
                            data: chartData.totalLogs
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                beginAtZero: false,
                                title: { display: true, text: xAxisLabel }
                            },
                            y: { beginAtZero: true }
                        }
                    }
                });

                // ✅ เมื่อกดการ์ดให้เปลี่ยน dataset ใน Chart
                document.querySelectorAll(".log-filter").forEach(card => {
                    card.addEventListener("click", function () {
                        let type = this.getAttribute("data-type");
                        let labelText = {
                            totalLogs: "Total Logs",
                            errors: "Total Errors",
                            warnings: "Total Warnings",
                            info: "Total Info"
                        };

                        // 🎯 อัปเดต dataset ใน Chart
                        myAreaChart.data.datasets[0].data = chartData[type];
                        myAreaChart.data.datasets[0].label = labelText[type];
                        myAreaChart.update();
                    });
                });
            });
        </script>

        <!-- Top 5 Most Frequent Logs Table -->
        <div class="card shadow mb-4" style="margin-top: 30px;">
            <div class="card-body">
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Top 5 Most Frequent Logs</h5>
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
                                    <td class="log-level {{ strtolower($log->log_level) }}">
                                        {{ strtoupper($log->log_level) }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($log->last_occurrence)->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


            </div>
        </div>

        <div class="card" style="padding: 16px; margin-top: 30px;">
            <div class="card-body">
                <h4 class="card-title">System Logs</h4>
                <!-- Advanced Search Form -->
                <!-- ฟอร์มค้นหาหลัก -->
                <!-- ✅ FORM ค้นหาหลัก -->
                <!-- ✅ FORM ค้นหา Logs -->
                <form method="GET" id="searchForm">
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <input type="text" name="user_name" class="form-control" placeholder="User Name">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="user_email" class="form-control" placeholder="User Email">
                        </div>
                        <div class="col-md-3">
                            <select name="log_level" class="form-control">
                                <option value="">-- Log Level --</option>
                                <option value="INFO">INFO</option>
                                <option value="WARNING">WARNING</option>
                                <option value="ERROR">ERROR</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex">
                            <button type="submit" class="btn btn-primary">Search Logs</button>
                            <a href="{{ route('admin.logs.exportCsv') }}" class="btn btn-warning" style="margin-left: 2px;">Export CSV</a>
                        </div>
                    </div>

                    <!-- ✅ Advanced Filters -->
                    <div class="mt-3">
                        <a data-toggle="collapse" href="#advancedSearch" role="button" aria-expanded="false" class="advanced">
                            Advanced Filters <i class="fa fa-angle-down"></i>
                        </a>

                        <div class="collapse" id="advancedSearch">
                            <div class="card card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="text" name="action" class="form-control" placeholder="Action">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="ip_address" class="form-control" placeholder="IP Address">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="start_date" id="start_date" class="form-control datepicker"
                                            placeholder="Start Date (DD/MM/YYYY)" value="{{ request('start_date') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="end_date" id="end_date" class="form-control datepicker"
                                            placeholder="End Date (DD/MM/YYYY)" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- ✅ Table แสดง Logs -->
                <div class="table-responsive">
                    <table class="table table-striped">
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
                                <th>IP Address</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                            @include('dashboards.users.logs_table') <!-- ✅ โหลด Logs -->
                        </tbody>
                    </table>
                </div>

                <!-- ✅ Pagination -->
                <div class="pagination-links">
                    {{ $logs->links() }}
                </div>
            </div>
            <!-- ✅ JavaScript -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script>
                $(document).ready(function () {
                    // 🔍 ค้นหา Logs
                    $("#searchForm").submit(function (e) {
                        e.preventDefault();
                        let formData = $(this).serialize();

                        $.ajax({
                            url: "{{ route('admin.searchLogs') }}",
                            type: "GET",
                            data: formData,
                            success: function (response) {
                                $("#logsTableBody").html(response.tableData);
                                $(".pagination-links").html(response.pagination);
                            },
                            error: function (xhr) {
                                console.log(xhr.responseText);
                            }
                        });
                    });
                    // 📌 ใช้ Datepicker กับ input field วันที่
                    $(".datepicker").datepicker({
                        format: "dd/mm/yyyy",
                        autoclose: true,
                        todayHighlight: true,
                    });
                    // 🔍 ค้นหา Logs แบบ AJAX
                    $("#searchForm").submit(function (e) {
                        e.preventDefault();
                        let formData = $(this).serialize();

                        $.ajax({
                            url: "{{ route('admin.searchLogs') }}",
                            type: "GET",
                            data: formData,
                            success: function (response) {
                                $("#logsTableBody").html(response.tableData);
                                $(".pagination-links").html(response.pagination);
                            },
                            error: function (xhr) {
                                console.log(xhr.responseText);
                            }
                        });
                    });

                    // ✅ Pagination AJAX
                    $(document).on("click", ".pagination a", function (e) {
                        e.preventDefault();
                        let url = $(this).attr("href");

                        $.ajax({
                            url: url,
                            type: "GET",
                            success: function (response) {
                                $("#logsTableBody").html(response.tableData);
                                $(".pagination-links").html(response.pagination);
                            },
                            error: function (xhr) {
                                console.log(xhr.responseText);
                            }
                        });
                    });
                });
            </script>

            <!-- Scroll Bar ด้านล่าง -->
            <div class="bottom-scroll">
                <div class="scroll-div"></div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script src="http://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js" defer></script>
        <script src="https://cdn.datatables.net/1.12.0/js/dataTables.bootstrap4.min.js" defer></script>
        <script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js" defer></script>
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