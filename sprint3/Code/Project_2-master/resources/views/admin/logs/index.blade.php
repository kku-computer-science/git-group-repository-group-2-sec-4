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
    <div class="container">
        <div class="card" style="padding: 16px;">
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
                        <a data-toggle="collapse" href="#advancedSearch" role="button" aria-expanded="false"
                            class="advanced">
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
                                        <input type="text" name="related_table" class="form-control"
                                            placeholder="Related Table" value="{{ request('related_table') }}">
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
                                        <td>{{ $log->user ? $log->user->fname_en . ' ' . $log->user->lname_en : 'Unknown' }}
                                        </td>
                                        <td>{{ $log->user ? $log->user->email : 'Unknown' }}</td>
                                        <td>{{ $log->action }}</td>
                                        <td class="log-level {{ strtolower($log->log_level) }}">
                                            {{ $log->log_level }}
                                        </td>
                                        <td style="width: 350px; word-wrap: break-word; white-space: normal;">
                                            {{ $log->message }}
                                        </td>
                                        <td>{{ $log->related_table }}</td>
                                        <td>{{ $log->related_id }}</td>
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
                {{ $logs->links() }}
            </div>

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



@endsection