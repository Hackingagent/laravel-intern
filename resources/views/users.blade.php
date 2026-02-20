<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Laravel Inactive User Reminder</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e1e1e1;
        }

        th {
            background: #f8f9fa;
            color: #555;
            font-weight: 600;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .status-never {
            background: #e2e3e5;
            color: #383d41;
        }

        .btn {
            padding: 8px 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #5568d3;
        }

        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
        }

        .form-inline {
            display: inline;
        }

        .form-inline input {
            width: 60px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }

        .links {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e1e1;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            margin-right: 15px;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d7ff;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            color: #004085;
        }

        .command-box {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-family: monospace;
            font-size: 13px;
        }

        .command-box code {
            color: #50fa7b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Users</h1>
        <p class="subtitle">Manage and view user activity status</p>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="info-box">
            <strong>Inactivity Period:</strong> 7 days (configurable)<br>
            <strong>Reminder:</strong> Sent once per day maximum per user
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Last Login</th>
                    <th>Reminder Sent</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->last_login_at)
                            {{ $user->last_login_at->format('Y-m-d H:i:s') }}
                        @else
                            Never
                        @endif
                    </td>
                    <td>
                        @if($user->reminder_sent_at)
                            {{ $user->reminder_sent_at->format('Y-m-d H:i:s') }}
                        @else
                            Not sent
                        @endif
                    </td>
                    <td>
                        @if(!$user->last_login_at)
                            <span class="status status-never">Never Logged In</span>
                        @elseif(\Carbon\Carbon::parse($user->last_login_at)->diffInDays(now()) >= 7)
                            <span class="status status-inactive">Inactive</span>
                        @else
                            <span class="status status-active">Active</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="/users/{{ $user->id }}/update-login" class="form-inline">
                            @csrf
                            <input type="number" name="days" value="7" min="1" max="365" required>
                            <button type="submit" class="btn btn-small">Set Inactive</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="command-box">
            <strong>Run the scheduler manually:</strong><br>
            <code>php artisan schedule:run</code><br><br>
            <strong>Run the queue worker:</strong><br>
            <code>php artisan queue:work</code>
        </div>

        <div class="links">
            <a href="/login">Login</a>
            <a href="/">Home</a>
        </div>
    </div>
</body>
</html>
