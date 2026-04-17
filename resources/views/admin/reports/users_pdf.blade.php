<html>
<head>
  <meta charset="utf-8">
  <title>Users Report</title>
  <style>
    body{font-family: DejaVu Sans, sans-serif}
    table{width:100%;border-collapse:collapse}
    td,th{border:1px solid #ddd;padding:6px;font-size:12px}
    th{background:#f5f5f5}
  </style>
</head>
<body>
  <h2>Users Report</h2>
  <table>
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th></tr></thead>
    <tbody>
      @foreach($users as $u)
        <tr>
          <td>{{ $u->id }}</td>
          <td>{{ $u->name }}</td>
          <td>{{ $u->email }}</td>
          <td>{{ $u->role }}</td>
          <td>{{ $u->created_at }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
