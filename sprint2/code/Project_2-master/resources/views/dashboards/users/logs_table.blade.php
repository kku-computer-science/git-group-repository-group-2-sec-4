@foreach ($logs as $log)
<tr>
    <td>{{ $log->log_id }}</td>
    <td>{{ $log->user_id ?? 'Guest' }}</td>
    <td>{{ $log->user ? "{$log->user->fname_en} {$log->user->lname_en}" : 'Unknown' }}</td>
    <td>{{ $log->user ? $log->user->email : 'Unknown' }}</td>
    <td>{{ $log->user && $log->user->roles->count() ? $log->user->roles->first()->name : 'Unknown' }}</td>
    <td>{{ $log->action }}</td>
    <td class="log-level {{ strtolower($log->log_level) }}">{{ $log->log_level }}</td>
    <td style="width: 350px; word-wrap: break-word;">{{ $log->message }}</td>
    <td>{{ $log->ip_address }}</td>
    <td>{{ $log->created_at }}</td>
</tr>
@endforeach
