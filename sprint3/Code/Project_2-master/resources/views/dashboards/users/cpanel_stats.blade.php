<div class="row">
    {{-- การ์ด Disk Usage --}}
    <div class="col-md-4">
        <div class="card border-info mb-3">
            <div class="card-header bg-info text-white">Disk Usage</div>
            <div class="card-body">
                @php
                    // แปลง Limit จาก MB → GB
                    $diskLimitGB = $diskLimit / 1024; // 1 GB = 1024 MB

                    // ปัดทศนิยมของ Disk Used (MB) → 2 ตำแหน่ง
                    $diskUsedMB = number_format($diskUsed, 2);

                    // ปัดทศนิยมของ Disk Limit (GB) → 2 ตำแหน่ง
                    $diskLimitGBFormatted = number_format($diskLimitGB, 2);

                    // คำนวณเปอร์เซ็นต์ (จาก MB / MB-limit)
                    // (ถ้าอยากใช้ MB/GB-limit ก็ต้องคำนวณอีกแบบหนึ่ง แต่ส่วนใหญ่ cPanel ใช้ MB / MB-limit ภายใน แล้วแปลง limit เป็น GB)
                    $diskPercent = 0;
                    if ($diskLimit > 0) {
                        $diskPercent = round(($diskUsed / $diskLimit) * 100, 2);
                    }
                @endphp
                <h5 class="card-title">
                    {{-- แสดงค่าตามสไตล์ cPanel: "491.68 MB / 1.17 GB (40.97%)" --}}
                    {{ $diskUsedMB }} MB / {{ $diskLimitGBFormatted }} GB ({{ $diskPercent }}%)
                </h5>
                <p class="card-text">This is your disk usage.</p>
            </div>
        </div>
    </div>


    {{-- การ์ด File Usage (Inodes) --}}
    <div class="col-md-4">
        <div class="card border-warning mb-3">
            <div class="card-header bg-warning text-dark">File Usage</div>
            <div class="card-body">
                @php
                    $filePercent = ($fileLimit && $fileLimit > 0)
                        ? round(($fileUsage / $fileLimit) * 100, 2)
                        : 0;
                @endphp
                <h5 class="card-title">
                    {{ number_format($fileUsage, 0) }} / {{ number_format($fileLimit, 0) }} ({{ $filePercent }}%)
                </h5>
                <p class="card-text">This is your file usage (inodes).</p>
            </div>
        </div>
    </div>
</div>